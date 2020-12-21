<?php

namespace Tests\Unit;

use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Mockery;
use stdClass;
use Stripe\Card;
use App\Models\InvoiceOptions;
use Tests\Helpers\StripeCustomer;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use StripeCustomer;

    public function testAssignLastSubscriptionWithoutTrial()
    {
        $stripeSubscription = (object) [
            'plan' => (object) [
                'id' => 'plan_123'
            ]
        ];

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldReceive('asStripeSubscription')->once()->withNoArgs()->andReturn($stripeSubscription);
        $subscription->shouldReceive('delete')->once()->withNoArgs();

        $newSubscription = Mockery::mock();
        $newSubscription->shouldReceive('skipTrial')->once()->withNoArgs();
        $newSubscription->shouldReceive('create')->once()->withNoArgs();

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getAttribute')->once()->with('package_name')->andReturn('free 25');
        $user->shouldReceive('newSubscription')->once()->with('free 25', 'plan_123')->andReturn($newSubscription);

        $user->assignLastSubscriptionWithoutTrial($subscription);
    }

    public function testCurrentSubscriptionActive()
    {
        $user = new User;
        $knownDate = Carbon::create(2019, 9, 23, 12, 10, 15);
        Carbon::setTestNow($knownDate);

        $user->active = -1;
        $user->subscription = Carbon::now();
        $this->assertFalse($user->currentSubscriptionActive());

        // Positive test
        $user->active = 0;
        $user->subscription = Carbon::now();
        $this->assertTrue($user->currentSubscriptionActive());

        // Negative test
        $user->active = 0;
        $user->subscription = Carbon::now()->subSecond();
        $this->assertFalse($user->currentSubscriptionActive());

        // Positive test
        $user->active = 1;
        $user->subscription = Carbon::now();
        $this->assertTrue($user->currentSubscriptionActive());

        // Negative test
        $user->active = 1;
        $user->subscription = Carbon::now()->subSecond();
        $this->assertFalse($user->currentSubscriptionActive());

        // Positive test
        $user->active = 2;
        $user->subscription = Carbon::now();
        $this->assertTrue($user->currentSubscriptionActive());

        // Negative test
        $user->active = 2;
        $user->subscription = Carbon::now()->subSecond();
        $this->assertFalse($user->currentSubscriptionActive());
    }

    public function testCurrentSubscriptionActiveByTimestamp()
    {
        $user = new User;
        $knownDate = Carbon::create(2019, 9, 23, 12, 10, 15);
        Carbon::setTestNow($knownDate);

        $user->active = 0;
        $user->subscription = Carbon::now();
        $this->assertTrue($user->currentSubscriptionActive());

        $user->active = 0;
        $user->subscription = Carbon::now()->addSecond();
        $this->assertTrue($user->currentSubscriptionActive());

        $user->active = 0;
        $user->subscription = Carbon::now()->subSecond();
        $this->assertFalse($user->currentSubscriptionActive());
    }

    public function testCustomStripeInvoiceOptions()
    {
        $stripeCustomer = $this->createStripeCustomerObject('custom memo');

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('asStripeCustomer')->once()->withNoArgs()->andReturn($stripeCustomer);

        $options = $user->getInvoiceOptions();

        $this->assertInstanceOf(InvoiceOptions::class, $options);
        $this->assertEquals('custom memo', $options->memo);
        $this->assertEquals(null, $options->footer);
        $this->assertEquals([
            'field1' => '',
            'field2' => '',
            'field3' => '',
        ], $options->getCustomFieldsArray());
    }

    public function testUpdateInvoiceOptions()
    {
        $options = [
            'invoice_settings' => [
                'custom_fields' => [],
                'footer' => 'footer',
            ],
            'metadata' => [
                'invoice_memo' => 'memo'
            ]
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('updateStripeCustomer')->once()->with($options);

        $user->updateInvoiceOptions(new InvoiceOptions('memo', 'footer'));
    }

    public function testUpdateInvoiceOptionsWithCustomFields()
    {
        $options = [
            'invoice_settings' => [
                'custom_fields' => [
                    [
                        'name' => 'PO Number',
                        'value' => '111111'
                    ],
                    [
                        'name' => 'Region',
                        'value' => 'region1'
                    ],
                    [
                        'name' => 'Contractor',
                        'value' => 'JohnDoe'
                    ],
                ],
                'footer' => 'footer-val-1',
            ],
            'metadata' => [
                'invoice_memo' => 'memo-val-2'
            ]
        ];
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('updateStripeCustomer')->once()->with($options);

        $customFields = [
            [
                'name' => 'footer',
                'value' => 'footer-val-1'
            ],
            [
                'name' => 'memo',
                'value' => 'memo-val-2'
            ],
            [
                'name' => 'field1',
                'value' => '111111'
            ],
            [
                'name' => 'field2',
                'value' => 'region1'
            ],
            [
                'name' => 'field3',
                'value' => 'JohnDoe'
            ],
        ];
        $opts = new InvoiceOptions('memo-val-2', 'footer-val-1');
        $opts->addNewCustomFields($customFields);
        $user->updateInvoiceOptions($opts);
    }

    public function testGetIntercomUserHash()
    {
        $user = new User;
        $user->id = 1;

        $this->assertNotEmpty($user->getIntercomUserHash());
    }

    public function testGetColumnsOptions()
    {
        $user = new User;
        $this->assertEquals([], $user->getColumnsOptions());

        $user->hidecolumns = '';
        $this->assertEquals([], $user->getColumnsOptions());

        $user->hidecolumns = 'social_facebook;subcat;';
        $this->assertEquals(['social_facebook', 'subcat'], $user->getColumnsOptions());

        $user->hidecolumns = 'social_facebook;subcat';
        $this->assertEquals(['social_facebook', 'subcat'], $user->getColumnsOptions());
    }

    public function testHasColumnOption()
    {
        $user = new User;
        $user->hidecolumns = 'social_facebook;subcat;';

        $this->assertTrue($user->hasColumnOption('social_facebook'));
        $this->assertTrue($user->hasColumnOption('subcat'));
        $this->assertFalse($user->hasColumnOption('unknown'));
    }
}
