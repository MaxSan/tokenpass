<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', [
    'as'   => 'welcome',
    'uses' => 'WelcomeController@index'
]);


// -------------------------------------------------------------------------
// User login and registration

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Bitcoin Authentication routes...
Route::get('auth/bitcoin', 'Auth\AuthController@getBitcoinLogin');
Route::post('auth/bitcoin', 'Auth\AuthController@postBitcoinLogin');
Route::get('auth/sign', 'Auth\AuthController@getSignRequirement');
Route::post('auth/signed', 'Auth\AuthController@setSigned');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Update routes...
Route::get('auth/update', 'Auth\AuthController@getUpdate');
Route::post('auth/update', 'Auth\AuthController@postUpdate');

// Email confirmations...
Route::get('auth/sendemail', 'Auth\EmailConfirmationController@getSendEmail');
Route::post('auth/sendemail', 'Auth\EmailConfirmationController@postSendEmail');
Route::get('auth/verify/{token}', ['as' => 'auth.verify', 'uses' => 'Auth\EmailConfirmationController@verifyEmail']);

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// Connected apps routes...
Route::get('auth/connectedapps', 'Auth\ConnectedAppsController@getConnectedApps');
Route::get('auth/revokeapp/{clientid}', 'Auth\ConnectedAppsController@getRevokeAppForm');
Route::post('auth/revokeapp/{clientid}', 'Auth\ConnectedAppsController@postRevokeAppForm');

//token inventory management
Route::get('inventory', 'Inventory\InventoryController@index');
Route::post('inventory/address/new', 'Inventory\InventoryController@registerAddress');
Route::post('inventory/address/{address}/edit', 'Inventory\InventoryController@editAddress');
Route::post('inventory/address/{address}/verify', 'Inventory\InventoryController@verifyAddressOwnership');
Route::post('inventory/address/{address}/toggle', 'Inventory\InventoryController@toggleAddress');
Route::post('inventory/address/{address}/toggleLogin', 'Inventory\InventoryController@toggleLogin');
Route::get('inventory/address/{address}/delete', 'Inventory\InventoryController@deleteAddress');
Route::get('inventory/refresh', 'Inventory\InventoryController@refreshBalances');
Route::get('inventory/check-refresh', 'Inventory\InventoryController@checkPageRefresh');
Route::post('inventory/asset/{asset}/toggle', 'Inventory\InventoryController@toggleAsset');

// new route/controller for pockets
Route::get('pockets', 'Inventory\InventoryController@getPockets');

//client applications / API keys
Route::get('auth/apps', 'Auth\AppsController@index');
Route::post('auth/apps/new', 'Auth\AppsController@registerApp');
Route::post('auth/apps/{app}/edit', 'Auth\AppsController@updateApp');
Route::get('auth/apps/{app}/delete', 'Auth\AppsController@deleteApp');

// -------------------------------------------------------------------------
// User routes

// User routes...
Route::get('dashboard', [
    'as'         => 'user.dashboard',
    'middleware' => 'auth',
    'uses'       => 'Accounts\DashboardController@getDashboard'
]);



// -------------------------------------------------------------------------
// oAuth routes

// oAuth authorization form...
Route::get('oauth/authorize', [
    'as'         => 'oauth.authorize.get',
    'middleware' => ['check-authorization-params', 'auth', 'csrf',],
    'uses'       => 'OAuth\OAuthController@getAuthorizeForm'
]);
Route::post('oauth/authorize', [
    'as'         => 'oauth.authorize.post',
    'middleware' => ['check-authorization-params', 'auth', 'csrf',],
    'uses'       => 'OAuth\OAuthController@postAuthorizeForm'
]);

// oAuth access token
Route::post('oauth/access-token', [
    'as'   => 'oauth.accesstoken',
    'uses' => 'OAuth\OAuthController@postAccessToken'
]);

// oAuth user
Route::get('oauth/user', [
    'as'         => 'oauth.user',
    'middleware' => ['oauth',],
    'uses'       => 'OAuth\OAuthController@getUser'
]);


// -------------------------------------------------------------------------
// API endpoints

Route::get('api/v1/tca/check/{username}', array('as' => 'api.tca.check', 'uses' => 'API\APIController@checkTokenAccess'));
Route::get('api/v1/tca/check-address/{address}', array('as' => 'api.tca.check-address', 'uses' => 'API\APIController@checkAddressTokenAccess'));
Route::get('api/v1/tca/check-sign/{address}', array('as' => 'api.tca.check-sign', 'uses' => 'API\APIController@checkSignRequirement'));
Route::post('api/v1/tca/set-sign', array('as' => 'api.tca.set-sign', 'uses' => 'API\APIController@setSignRequirement'));
Route::get('api/v1/tca/addresses/{username}', array('as' => 'api.tca.addresses', 'uses' => 'API\APIController@getAddresses'));
Route::get('api/v1/tca/addresses/{username}/refresh', array('as' => 'api.tca.addresses.refresh', 'uses' => 'API\APIController@getRefreshedAddresses'));
Route::get('api/v1/tca/addresses/{username}/{address}', array('as' => 'api.tca.addresses.details', 'uses' => 'API\APIController@getAddressDetails'));
Route::post('api/v1/tca/addresses/{username}/{address}', array('as' => 'api.tca.addresses.verify', 'uses' => 'API\APIController@verifyAddress'));
Route::patch('api/v1/tca/addresses/{username}/{address}', array('as' => 'api.tca.addresses.edit', 'uses' => 'API\APIController@editAddress'));
Route::delete('api/v1/tca/addresses/{username}/{address}', array('as' => 'api.tca.addresses.delete', 'uses' => 'API\APIController@deleteAddress'));
Route::post('api/v1/tca/addresses', array('as' => 'api.tca.addresses.new', 'uses' => 'API\APIController@registerAddress'));
Route::get('api/v1/tca/provisional', array('as' => 'api.tca.provisional.list', 'uses' => 'API\APIController@getProvisionalTCASourceAddressList'));
Route::post('api/v1/tca/provisional/register', array('as' => 'api.tca.provisional.register', 'uses' => 'API\APIController@registerProvisionalTCASourceAddress'));
Route::get('api/v1/tca/provisional/tx', array('as' => 'api.tca.provisional.tx.list', 'uses' => 'API\APIController@getProvisionalTCATransactionList'));
Route::post('api/v1/tca/provisional/tx', array('as' => 'api.tca.provisional.tx.register', 'uses' => 'API\APIController@registerProvisionalTCATransaction'));
Route::get('api/v1/tca/provisional/tx/{id}', array('as' => 'api.tca.provisional.tx.get', 'uses' => 'API\APIController@getProvisionalTCATransaction'));
Route::patch('api/v1/tca/provisional/tx/{id}', array('as' => 'api.tca.provisional.tx.update', 'uses' => 'API\APIController@updateProvisionalTCATransaction'));
Route::delete('api/v1/tca/provisional/tx/{id}', array('as' => 'api.tca.provisional.tx.delete', 'uses' => 'API\APIController@deleteProvisionalTCATransaction'));
Route::delete('api/v1/tca/provisional/{address}', array('as' => 'api.tca.provisional.delete', 'uses' => 'API\APIController@deleteProvisionalTCASourceAddress'));
Route::post('api/v1/oauth/request', array('as' => 'api.oauth.request', 'uses' => 'API\APIController@requestOAuth', 'middleware' => ['check-authorization-params']));
Route::post('api/v1/oauth/token', array('as' => 'api.oauth.token', 'uses' => 'API\APIController@getOAuthToken', 'middleware' => ['check-authorization-params']));
Route::get('api/v1/oauth/logout', array('as' => 'api.oauth.logout', 'uses' => 'API\APIController@invalidateOAuth'));
Route::patch('api/v1/update', array('as' => 'api.update-account', 'uses' => 'API\APIController@updateAccount'));
Route::post('api/v1/register', array('as' => 'api.register', 'uses' => 'API\APIController@registerAccount'));
Route::post('api/v1/login', array('as' => 'api.login', 'uses' => 'API\APIController@loginWithUsernameAndPassword'));
Route::get('api/v1/lookup/address/{address}', array('as' => 'api.lookup.address', 'uses' => 'API\APIController@lookupUserByAddress'));
Route::post('api/v1/lookup/address/{address}', array('as' => 'api.lookup.address.post', 'uses' => 'API\APIController@lookupUserByAddress'));
Route::get('api/v1/lookup/user/{username}', array('as' => 'api.lookup.user', 'uses' => 'API\APIController@lookupAddressByUser'));
Route::post('api/v1/instant-verify/{username}', array('as' => 'api.instant-verify', 'uses' => 'API\APIController@instantVerifyAddress'));

// ------------------------------------------------------------------------
// XChain Receiver

// webhook notifications
Route::post('_xchain_client_receive', ['as' => 'xchain.receive', 'uses' => 'XChain\XChainWebhookController@receive']);
