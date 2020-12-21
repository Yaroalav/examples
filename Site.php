<?php

namespace App\Models;

// @codingStandardsIgnoreStart
use App\Managers\Dictionary;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * App\Models\Site
 *
 * @property int $id
 * @property string $email
 * @property string $category
 * @property string $url
 * @property string $kw
 * @property string|null $baseline
 * @property string|null $notes
 * @property string|null $tags
 * @property string $region
 * @property string $language
 * @property int|null $has_featured
 * @property string|null $has_featured_url
 * @property int $ignore_local
 * @property int $ignore_featured
 * @property string|null $gmb
 * @property string|null $near
 * @property int $favored
 * @property string $type
 * @property string $timestamp
 * @property int $active
 * @property string $uindex
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[]
 *     $notifications
 * @property-read ViewKey $viewKey
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereBaseline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereFavored($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereGmb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereHasFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereHasFeaturedUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereIgnoreFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereIgnoreLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereKw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereNear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereUindex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereUrl($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KeywordMonthlyVolume[] $monthlyVolume
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SerpResults[] $serpResults
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site withoutTimestamps()
 * @property string|null $serp_features
 * @property int|null $page_ranking_current
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site wherePageRankingCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Site whereSerpFeatures($value)
 */
// @codingStandardsIgnoreEnd

class Site extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    const CREATED_AT = null;
    const UPDATED_AT = 'timestamp';

    const SITE_INACTIVE = 0;
    const SITE_ACTIVE = 1;
    const SITE_ARCHIVED = 2;

    public static $statuses = [
        null => 'EMPTY',
        self::SITE_INACTIVE => 'INACTIVE',
        self::SITE_ACTIVE => 'ACTIVE',
        self::SITE_ARCHIVED => 'ARCHIVED'
    ];

    protected $attributes = [
        'region' => 'google.com',
        'language' => 'en',
        'ignore_local' => false,
        'ignore_featured' => true,
        // TODO implement list of types
        'type' => 'se',
        'active' => self::SITE_ACTIVE,
        'near' => '',
    ];

    protected $rules = [
        'email' => 'required',
        'category' => 'required',
        'url' => 'required',
        'type' => 'required|in:se,sem,yt,map',

        //'uindex' => 'unique:sites,kw',
       // 'language' => 'required|in:'.explode(self::$languages),
    ];

    protected $casts = [
        'favored' => 'boolean',
        'ignore_local' => 'boolean',
        'ignore_featured' => 'boolean',
    ];

    protected $dates = [
        'timestamp',
        'keywords_last_updated_at',
    ];

    /**
     * @var array Fillable attributes
     */
    protected $fillable = [
        // Email SHOULD NEVER be fillable, otherwise we allow a user to move entities to other account!
        // 'email',
        'category',
        'url',
        'kw',

        //'baseline',
        'notes',
        'tags',
        'region',
        'language',
        //'has_featured',
        //'has_featured_url',
        'ignore_local',
        'ignore_featured',
        'type',
        'gmb',
        'near',
        'favored',
        'active',
        // Specific substitutions from Request, doesn't exists as Model attributes
        'status',
        'isfav',
    ];

    // @codingStandardsIgnoreStart
    public static $ytregions = '{"google.com":{"hl":"","gl":""},"google.ca":{"hl":"","gl":"CA"},"google.co.uk":{"hl":"en-GB","gl":"GB"},"google.com.au":{"hl":"en-GB","gl":"AU"},"google.es":{"hl":"es","gl":"ES"},"google.co.nz":{"hl":"en-GB","gl":"NZ"},"google.de":{"hl":"de","gl":"DE"},"google.nl":{"hl":"nl","gl":"NL"},"google.ae":{"hl":"ar","gl":"AE"},"google.as":{"hl":"","gl":""},"google.at":{"hl":"de","gl":"AT"},"google.az":{"hl":"az","gl":"AZ"},"google.ba":{"hl":"hr","gl":"BA"},"google.be":{"hl":"","gl":"BE"},"google.bg":{"hl":"bg","gl":"BG"},"google.cat":{"hl":"ca","gl":"ES"},"google.ch":{"hl":"","gl":"CH"},"google.cl":{"hl":"es-419","gl":"CL"},"google.co.id":{"hl":"id","gl":"ID"},"google.co.il":{"hl":"en","gl":"IL"},"google.co.in":{"hl":"en-GB","gl":"IN"},"google.co.jp":{"hl":"ja","gl":"JP"},"google.co.ke":{"hl":"en-GB","gl":"KE"},"google.co.kr":{"hl":"ko","gl":"KR"},"google.co.ma":{"hl":"ar","gl":"MA"},"google.co.th":{"hl":"th","gl":"TH"},"google.co.tz":{"hl":"sw","gl":"TZ"},"google.co.ug":{"hl":"en-GB","gl":"UG"},"google.co.za":{"hl":"en-GB","gl":"ZA"},"google.co.zw":{"hl":"en-GB","gl":"ZW"},"google.com.ar":{"hl":"es-419","gl":"AR"},"google.com.bh":{"hl":"ar","gl":"BH"},"google.com.br":{"hl":"pt","gl":"BR"},"google.com.co":{"hl":"es-419","gl":"CO"},"google.com.eg":{"hl":"ar","gl":"EG"},"google.com.gh":{"hl":"en-GB","gl":"GH"},"google.com.hk":{"hl":"zh-HK","gl":"HK"},"google.com.jm":{"hl":"en","gl":"JM"},"google.com.kw":{"hl":"ar","gl":"KW"},"google.com.lb":{"hl":"ar","gl":"LB"},"google.com.ly":{"hl":"ar","gl":"LY"},"google.com.mx":{"hl":"es-419","gl":"MX"},"google.com.my":{"hl":"ms","gl":"MY"},"google.com.ng":{"hl":"en-GB","gl":"NG"},"google.com.om":{"hl":"ar","gl":"OM"},"google.com.pe":{"hl":"es-419","gl":"PE"},"google.com.ph":{"hl":"en","gl":"PH"},"google.com.pk":{"hl":"ur","gl":"PK"},"google.com.qa":{"hl":"ar","gl":"QA"},"google.com.sa":{"hl":"ar","gl":"SA"},"google.com.sg":{"hl":"en-GB","gl":"SG"},"google.com.tr":{"hl":"tr","gl":"TR"},"google.com.tw":{"hl":"zh-TW","gl":"TW"},"google.com.ua":{"hl":"uk","gl":"UA"},"google.cz":{"hl":"cs","gl":"CZ"},"google.dk":{"hl":"da","gl":"DK"},"google.ee":{"hl":"et","gl":"EE"},"google.fi":{"hl":"fi","gl":"FI"},"google.fm":{"hl":"","gl":""},"google.fr":{"hl":"fr","gl":"FR"},"google.ge":{"hl":"ka","gl":"GE"},"google.gr":{"hl":"el","gl":"GR"},"google.hr":{"hl":"hr","gl":"HR"},"google.hu":{"hl":"hu","gl":"HU"},"google.ie":{"hl":"en-GB","gl":"IE"},"google.iq":{"hl":"ar","gl":"IQ"},"google.is":{"hl":"is","gl":"IS"},"google.it":{"hl":"it","gl":"IT"},"google.jo":{"hl":"ar","gl":"JO"},"google.kz":{"hl":"kk","gl":"KZ"},"google.lk":{"hl":"si","gl":"LK"},"google.lt":{"hl":"lt","gl":"LT"},"google.lu":{"hl":"fr","gl":"LU"},"google.lv":{"hl":"lv","gl":"LV"},"google.me":{"hl":"sr","gl":"ME"},"google.mk":{"hl":"en-GB","gl":"MK"},"google.no":{"hl":"no","gl":"NO"},"google.nu":{"hl":"","gl":""},"google.pl":{"hl":"pl","gl":"PL"},"google.pt":{"hl":"pt-PT","gl":"PT"},"google.ro":{"hl":"ro","gl":"RO"},"google.rs":{"hl":"sr","gl":"RS"},"google.ru":{"hl":"ru","gl":"RU"},"google.se":{"hl":"sv","gl":"SE"},"google.si":{"hl":"sl","gl":"SI"},"google.sk":{"hl":"sk","gl":"SK"},"google.sn":{"hl":"fr","gl":"SN"}}';
    //$ytregions = json_decode($ytregions, true);
    // @codingStandardsIgnoreEnd

    /**
     * Add some dynamically generated rules.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules + [
            'language' => 'required|in:' . strtolower(implode(',', Dictionary::getLocales())),
            'region' => 'required|in:' . strtolower(implode(',', Dictionary::getRegions())),
            'kw' => [
                'required',
                'max:150',
                Rule::unique($this->getTable())
                    ->ignoreModel($this)
                    ->using(function (\Illuminate\Database\Query\Builder $q) {
                        return $q->where([
                            'url' => $this->url,
                            'category' => $this->category,
                            'region' => $this->region,
                            'language' => $this->language,
                        ]);
                    }),
            ],
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (isset(\Auth::user()->email)) {
            $this->email = \Auth::user()->email;
        }
    }

    /**
     * Register actions.
     */
    protected static function boot()
    {
        self::saving(
            function (Site $site) {
                //if (!$site->uindex) {
                // Here we update uindex whenever model is saving.
                    $site->uindex = md5("{$site->email}|{$site->category}|{$site->url}|" . stripslashes($site->kw) . "|{$site->region}|{$site->language}");//hashes with apostophies might be off
                //}
                if (!$site->viewKey) {
                    ViewKey::createForSite($site);
                }
            }
        );

        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function viewKey()
    {
        return $this->hasOne(ViewKey::class, 'category', 'category')
            // If there is more than one viewKey for any reason - take the last created one.
            ->orderByDesc('timestamp')
            ->whereEmail($this->email);
    }

    public function monthlyVolume()
    {
        return $this->hasMany(KeywordMonthlyVolume::class, 'keyword', 'kw');
    }

    public function scopeWithoutTimestamps()
    {
        $this->timestamps = false;
        return $this;
    }

    public function serpResults()
    {
        /** @var SerpResults $serpResults */
        $serpResults = new SerpResults;
        // TODO change this to User relation.
        if (isset(\Auth::user()->email)) {
            $serpResults->bind(\Auth::user()->email);
        }

        return $this->newHasMany($serpResults->newQuery(), $this, 'category', 'category')
            ->orderByDesc('date')
            ->where('date', '>', Carbon::now()->subMonth()->toDateString());
        //return $this->hasMany(SerpResults::class, 'category', 'category');
    }

    /**
     * We lower case language to make it conform to our existing infra and allow validator to do the job.
     * @param string $value
     */
    public function setLanguageAttribute($value)
    {
        // for type [map, yt] language should be 'en' only.
        if (in_array($this->type, ['map', 'yt'])) {
            $value = 'en';
        }
        $this->attributes['language'] = strtolower($value);
    }

    public function setTypeAttribute($value)
    {
        if (in_array($value, ['map', 'yt'])) {
            $this->language = 'en';
        }
        $this->attributes['type'] = $value;
    }

    /**
     * We lower case regions.
     * @param string $value
     */
    public function setRegionAttribute($value)
    {
        $this->attributes['region'] = strtolower($value);
    }

    public function setKwAttribute($value)
    {
        $this->attributes['kw'] = trim(
            preg_replace(
                // Remove UTF BOM.
                "/^\xEF\xBB\xBF/",
                '',
                str_replace(["\r", "\n"], '', strtolower(urldecode($value)))
            )
        );
    }

    public function setUrlAttribute($value)
    {
        $url = preg_replace(
            ['|^https?://|', '|[^A-Za-z0-9\.\:\/\-\_\?\=\#\$\@\!\&\%]|'],
            '',
            trim(urldecode($value))
        );
        //if it's youtube, but no www., append it
        if (preg_match('|^youtube.com|', $url)) {
            $url = "www." . $url;
        }

        //prepend a blank Google+ page ID
        if ($this->type === 'map' && strpos($url, '|') === false) {
            $url = '|' . $url;
        }

        $this->attributes['url'] = $url;
    }

    /**
     * Modify URL based on `exact` settings.
     * @param bool $exact
     * @return Site
     */
    public function setExactDomain(bool $exact = true)
    {
        $url = $this->url;

        // Its a kind of magic from legacy code.
        $host = parse_url("http://" . $url, PHP_URL_HOST);

        if ($url == $host && $exact) {
            $url = $url . "/";
        } elseif ($url != $host && !$exact) {
            $url = parse_url('http://' . $url, PHP_URL_HOST);
        }

        if ($url == $host) {
            $url = parse_url("http://" . $url, PHP_URL_HOST);
        }
        $this->url = $url;

        return $this;
    }

    public function scopeActiveKeywords($query)
    {
        return $query->where('active', self::SITE_ACTIVE);
    }

    public static function makeTrends($results, $baseline, $cycle, $gaps = 0)
    {
//var_dump($results);
        if ($gaps == 1) {
//echo "gaps filled";
            $positions = array();
            $arrows = array();
            foreach ($results as $result) {
                if ($prank2 > 0 && $prank == 0 && $result > 0) {
                    $positions[count($positions) - 1] = $result;
                }

//if($row['result'] == 0 && isset($pranklast)){
//  $positions[] = $pranklast;
//}else{
                $positions[] = $result;
//}

                $prank2 = $prank;
                $prank = $result;
            }
            $results = $positions;
        }

        if ($cycle == 'weekly') {
            $carrows = array(@$results[1], @$results[2], @$results[4], $baseline);
        } elseif ($cycle == 'monthly') {
            $carrows = array(@$results[1], @$results[2], @$results[3], $baseline);
        } else {
            $carrows = array(@$results[1], @$results[6], @$results[29], $baseline);
        }
//var_dump($carrows);
//var_dump($results);
        $rank = @$results[0];
        $resultsdepth = 100;//how many results does it go to
        foreach ($carrows as $oldrank) {
            if (!isset($oldrank)) {
                $arrows[] = 0;
                continue;
            }
            if ($oldrank == 0) {
                $oldrank = $resultsdepth;
            }
            if ($rank == 0) {
                $rank = $resultsdepth;
            }
            $change = $oldrank - $rank;
            $arrows[] = $change;
            continue;
        }
//var_dump($arrows);
        return $arrows;
    }
}
