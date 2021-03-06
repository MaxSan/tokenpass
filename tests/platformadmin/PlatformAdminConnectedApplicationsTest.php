<?php

use TKAccounts\TestHelpers\UserHelper;
use Illuminate\Support\Facades\App;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* PlatformAdminConnectedApplicationsTest
*/
class PlatformAdminConnectedApplicationsTest extends TestCase {

    protected $use_database = true;

    public function testPlatformAdminConnectedApplicationsRoutes() {
        $helper = $this->setupPlatformAdminHelper();
        $helper->beAuthorizedUser();

        $helper->testCreate(collect(app('ClientConnectionHelper')->newSampleConnectionVars())->all());
        $helper->testUpdate(['client_id' => 'I123456', 'user_id' => 1001]);
        $helper->testDelete();
    }


    ////////////////////////////////////////////////////////////////////////

    public function setUp() {
        // putenv('PLATFORM_ADMIN_DEVELOPMENT_MODE_ENABLED=1');
        return parent::setUp();
    }


    protected function setupPlatformAdminHelper() {
        $platform_admin_helper = app('Tokenly\PlatformAdmin\TestHelper\PlatformAdminTestHelper');

        $platform_admin_helper
            ->setRoutePrefix('connectedapps')
            ->setRepository(app('TKAccounts\Repositories\ClientConnectionRepository'))
            ->setCreateFunction(function() {
                return app('ClientConnectionHelper')->newSampleConnection();
            });

        return $platform_admin_helper;
    }


}
