<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\JsonApiException;
use App\Http\ApiCodes;
use App\Http\Requests\GetViewKeyRequest;
use App\Http\Requests\JsonApiRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Site;
use App\Models\Transformers\ViewKeyOnlyTransformer;
use App\Models\Transformers\ViewKeyTransformer;
use App\Models\ViewKey;
use App\Models\User;
use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Teapot\StatusCode;

class GroupsController extends BaseApiController
{
    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->returnCollection($request, ViewKey::items()->get());
    }

    public function active(Request $request)
    {
        return $this->returnCollection($request, ViewKey::items(Site::SITE_ACTIVE)->get());
    }

    public function archived(Request $request)
    {
        return $this->returnCollection($request, ViewKey::items(Site::SITE_ARCHIVED)->get());
    }

    public function deleted(Request $request)
    {
        return $this->returnCollection($request, ViewKey::items(Site::SITE_INACTIVE)->get());
    }

    private function returnCollection(Request $request, $items)
    {
        // Build our transformed resource
        $resource = new Collection(
            $items,
            new ViewKeyTransformer(
                $this->getSparseFields($request, ViewKeyTransformer::JSON_OBJ_TYPE)
            ),
            ViewKeyTransformer::JSON_OBJ_TYPE
        );

        return $this->respondWithCollection($resource, null, $request->input('include'));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string $auth
     * @return \Illuminate\Http\JsonResponse
     * @throws JsonApiException
     */
    public function show(Request $request, $auth)
    {
        $item = $this->fetchGroup($auth);
        // Build our transformed resource
        $resource = new Item(
            $item,
            new ViewKeyTransformer(
                $this->getSparseFields($request, ViewKeyTransformer::JSON_OBJ_TYPE)
            ),
            ViewKeyTransformer::JSON_OBJ_TYPE
        );
        return $this->respondWithItem($resource, $request->input('include'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param JsonApiRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws JsonApiException
     */
    public function store(JsonApiRequest $request)
    {
        if (isset($request->json()->get('data')['id'])) {
            // JSON Api server MUST return 403 Forbidden in response to an unsupported request
            // to create a resource with a client-generated ID.
            throw new JsonApiException(
                'Server does not support a request to create a resource with a client-generated ID',
                trans('api.id_creation_not_supported'),
                ApiCodes::REQUEST_VALIDATION_ERROR,
                ApiCodes::ENDPOINT_FORBIDDEN
            );
        }

        $itemData = $request->json()->get('data')['attributes'];
        $itemData['email'] = \Auth::user()->email;

        $item = ViewKey::create($itemData);

        $resource = new Item($item, new ViewKeyTransformer, ViewKeyTransformer::JSON_OBJ_TYPE);
        return $this->respondWithItem(
            $resource,
            $request->input('include'),
            StatusCode::CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGroupRequest $request
     * @param string $auth
     * @return \Illuminate\Http\JsonResponse
     * @throws JsonApiException
     * @throws \App\Exceptions\ModelValidationException
     */
    public function update(UpdateGroupRequest $request, $auth)
    {
        $itemData = $request->json()->get('data')['attributes'];

        // Do not include counters if we're going to change keyword statuses.
        $item = $this->fetchGroup($auth, isset($itemData['status']) ? false : true);

        if (isset($itemData['status'])) {
            $item->sites()->update(['active' => $itemData['status']]);
            $item = $this->fetchGroup($auth);
        }

        if (isset($itemData['shortkey'])) {
            unset($itemData['shortkey']);
        }

        // Here in the model email and auth would be protected, so no worries.
        $item->fill($itemData);
        $item->save();

        $resource = new Item($item, new ViewKeyTransformer, ViewKeyTransformer::JSON_OBJ_TYPE);
        return $this->respondWithItem($resource);
    }

    public function archive($auth)
    {
        // Update status of Active Keywords to Archived.
        $this->fetchGroup($auth, false)
            ->sites(Site::SITE_ACTIVE)
            ->update(['active' => Site::SITE_ARCHIVED]);
        $item = $this->fetchGroup($auth);

        $resource = new Item($item, new ViewKeyTransformer, ViewKeyTransformer::JSON_OBJ_TYPE);
        return $this->respondWithItem($resource);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $auth
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($auth)
    {
        $item = $this->fetchGroup($auth, false);
        $item->sites()->update(['active' => Site::SITE_INACTIVE]);

//        mysql_query("INSERT INTO `logs_category_actions`(`email`, `addon`, `category`, `type`, `apikey`)
//     VALUES ('$username', '".mysql_real_escape_string($_SESSION['addon'])."', '$category', 'delete',
// '".mysql_real_escape_string($_GET['auth'])."')");
        // TODO: User::updateUsedKwCounter();
//        $kwcount = mysql_num_rows(mysql_query("SELECT * FROM sites WHERE email='$username' and active='1'"));
//        mysql_query("UPDATE users SET used=$kwcount WHERE email='$username'");

        //$item->delete();
        return $this->respondWithJsonSuccess(
            trans('api.group_deleted'),
            ApiCodes::GROUP_DELETED
        );
    }

    /**
     * @param GetViewKeyRequest $request
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     * @throws JsonApiException
     */
    public function getViewKeyInfo(GetViewKeyRequest $request, string $groupId)
    {
        /** @var ViewKey $item */
        $item = ViewKey::whereAuth($groupId)
            ->leftJoin('users', 'viewkeys.email', '=', 'users.email')
            ->whereShortkey($request->get('viewkey'))
            //->with('user')
            ->select(
                ['viewkeys.email', 'auth', 'category', 'viewkeys.id', 'viewkeys.password', 'viewkeys.company_name',
                    'viewkeys.company_desc', 'company_link', 'viewkeys.company_logo',
                    'users.company_name as user_company_name',
                    'users.company_desc as user_company_desc',
                    'users.company_url as user_company_link',
                    'users.company_logo as user_company_logo',
                    'hidecolumns'
                ]
            )->first();

        if (!$item) {
            throw new JsonApiException(
                'Viewkey not found',
                trans('api.viewkey_not_found'),
                ApiCodes::VIEWKEY_NOT_FOUND,
                StatusCode::NOT_FOUND
            );
        }

        // Prefill viewkey data from user if its not set for the viewkey.
        $item->company_name = trim($item->company_name) ? $item->company_name : $item->user_company_name;
        $item->company_desc = trim($item->company_desc) ? $item->company_desc : $item->user_company_desc;
        $item->company_link = trim($item->company_link) ? $item->company_link : $item->user_company_link;
        $item->company_logo = trim($item->company_logo) ? $item->company_logo : $item->user_company_logo;

        /** @var bool password_valid if viewkey protected and password match */
        $item->password_valid = $item->password ? $request->get('password') === $item->password : true;
        $item->vkinfo = in_array('vkinfo', explode(';', $item->hidecolumns));
        $item->rnotes = in_array('rnotes', explode(';', $item->hidecolumns));

        // Build our transformed resource
        $resource = new Item(
            $item,
            new ViewKeyOnlyTransformer(
                $this->getSparseFields($request, ViewKeyOnlyTransformer::JSON_OBJ_TYPE)
            ),
            ViewKeyOnlyTransformer::JSON_OBJ_TYPE
        );
        return $this->respondWithItem($resource, $request->input('include'));
    }
}
