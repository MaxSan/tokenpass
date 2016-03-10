<?php
namespace TKAccounts\Http\Controllers\API;
use TKAccounts\Http\Controllers\Controller;
use TKAccounts\Models\User, TKAccounts\Models\Address;
use TKAccounts\Providers\CMSAuth\CMSAccountLoader;
use TKAccounts\Models\OAuthClient as AuthClient;
use TKAccounts\Models\OAuthScope as Scope;
use DB, Exception, Response, Input, Hash;
use Illuminate\Http\JsonResponse;
use Tokenly\TCA\Access;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use TKAccounts\Repositories\ClientConnectionRepository;
use TKAccounts\Repositories\OAuthClientRepository;
use TKAccounts\Repositories\UserRepository;

class APIController extends Controller
{

    public function __construct(OAuthClientRepository $oauth_client_repository, ClientConnectionRepository $client_connection_repository)
    {
        $this->oauth_client_repository      = $oauth_client_repository;
        $this->client_connection_repository = $client_connection_repository;   
    }

	public function checkTokenAccess($username)
	{
		$input = Input::all();
		$output = array();
		$http_code = 200;
		
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		$client_id = $input['client_id'];
		unset($input['client_id']);
		
		$getUser = User::where('username', $username)->first();
		if(!$getUser){
			//try falling back to CMS - temporary
			$cms = new CMSAccountLoader(env('CMS_ACCOUNTS_HOST'));
			$failed = false;
			try{
				$check = $cms->checkTokenAccess($username, Input::all());
			}
			catch(Exception $e){
				$failed = true;
			}
			if(!$failed){
				$output['result'] = $check;
			}
			else{
				$http_code = 404;
				$output['result'] = false;
				$output['error'] = 'Username not found';
			}
		}
		else{
			
			//make sure user has authenticated with this application at least once
			$find_connect = DB::table('client_connections')->where('user_id', $getUser->id)->where('client_id', $valid_client->id)->first();
			if(!$find_connect OR count($find_connect) == 0){
				$output['error'] = 'User has not authenticated yet with client application';
				$output['result'] = false;
				return Response::json($output, 403);
			}
			
			//look for the TCA scope
			$get_scope = Scope::find('tca');
			if(!$get_scope){
				$output['error'] = 'TCA scope not found in system';
				$output['result'] = false;
				return Response::json($output, 500);
			}
			
			
			//make sure scope is applied to client connection
			$scope_connect = DB::table('client_connection_scopes')->where('connection_id', $find_connect->id)->where('scope_id', $get_scope->uuid)->get();
			if(!$scope_connect OR count($scope_connect) == 0){
				$output['error'] = 'User does not have TCA scope applied for this client application';
				$output['result'] = false;
				return Response::json($output, 403);
			}
			
			$ops = array();
			$stack_ops = array();
			$checks = array();
			$tca = new Access;
			foreach($input as $k => $v){
				$exp_k = explode('_', $k);
				$k2 = 0;
				if(isset($exp_k[1])){
					$k2 = intval($exp_k[1]);
				}
				if($exp_k[0] == 'op'){
					$ops[$k2] = $v;
				}
				elseif($exp_k[0] == 'stackop'){
					$stack_ops[$k2] = strtoupper($v);
				}
				else{
					$checks[] = array('asset' => strtoupper($k), 'amount' => round(floatval($v) * 100000000)); //convert amount to satoshis
				}
			}
			$full_stack = array();
			foreach($checks as $k => $row){
				$stack_item = $row;
				if(isset($ops[$k])){
					$stack_item['op'] = $ops[$k];
				}
				else{
					$stack_item['op'] = '>='; //default to greater or equal than
				}
				if(isset($stack_ops[$k])){
					$stack_item['stackOp'] = $stack_ops[$k];
				}
				else{
					$stack_item['stackOp'] = 'AND';
				}
				$full_stack[] = $stack_item;
			}
			$balances = Address::getAllUserBalances($getUser->id, true);
			$output['result'] = $tca->checkAccess($full_stack, $balances);
		}
		return Response::json($output, $http_code);
	}
	
	public function getAddresses($username)
	{
		$output = array();
		$http_code = 200;
		$input = Input::all();
		
		//check if a valid application client_id
		$valid_client = false;
		if(isset($input['client_id'])){
			$get_client = AuthClient::find(trim($input['client_id']));
			if($get_client){
				$valid_client = $get_client;
			}
		}
		if(!$valid_client){
			$output['error'] = 'Invalid API client ID';
			$output['result'] = false;
			return Response::json($output, 403);
		}		
		
		$user = User::where('username', $username)->first();
		if(!$user){
			$http_code = 404;
			$output['result'] = false;
			$output['error'] = 'Username not found';
		}
		
		//make sure user has authenticated with this application at least once
		$find_connect = DB::table('client_connections')->where('user_id', $user->id)->where('client_id', $valid_client->id)->first();
		if(!$find_connect OR count($find_connect) == 0){
			$output['error'] = 'User has not authenticated yet with client application';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		//look for the TCA scope
		$get_scope = Scope::find('tca');
		if(!$get_scope){
			$output['error'] = 'TCA scope not found in system';
			$output['result'] = false;
			return Response::json($output, 500);
		}
		
		
		//make sure scope is applied to client connection
		$scope_connect = DB::table('client_connection_scopes')->where('connection_id', $find_connect->id)->where('scope_id', $get_scope->uuid)->get();
		if(!$scope_connect OR count($scope_connect) == 0){
			$output['error'] = 'User does not have TCA scope applied for this client application';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		$address_list = Address::getAddressList($user->id, 1);
		if(!$address_list OR count($address_list) == 0){
			$output['addresses'] = array();
		}
		else{
			$balances = array();
			foreach($address_list as $address){
				$balances[] = array('address' => $address->address, 'balances' => Address::getAddressBalances($address->id));
			}
			$output['result'] = $balances;
		}
		return Response::json($output, $http_code);
	}
	
	public function checkAddressTokenAccess($address)
	{
		$input = Input::all();
		$output = array();
		
		if(!isset($input['sig']) OR trim($input['sig']) == ''){
			$output['error'] = 'Proof-of-ownership signature required (first 10 characters of address)';
			$output['result'] = false;
			return Response::json($output, 400);
		}
		
		$xchain = app('Tokenly\XChainClient\Client');
		$validate = $xchain->validateAddress($address);	
		if(!$validate['result']){
			$output['error'] = 'Invalid address';
			$output['result'] = false;
			return Response::json($output, 400);
		}	
		
		$first_bits = substr($address, 0, 10);
		$check_sig = $xchain->verifyMessage($address, $input['sig'], $first_bits);
		if(!$check_sig['result']){
			$output['error'] = 'Invalid proof-of-ownership signature';
			$output['result'] = false;
			return Response::json($output, 403);
		}
		
		$sig = $input['sig'];
		unset($input['sig']);
		
		$tca = new Access(true);
		$ops = array();
		$stack_ops = array();
		$checks = array();
		$tca = new Access(true);
		foreach($input as $k => $v){
			$exp_k = explode('_', $k);
			$k2 = 0;
			if(isset($exp_k[1])){
				$k2 = intval($exp_k[1]);
			}
			if($exp_k[0] == 'op'){
				$ops[$k2] = $v;
			}
			elseif($exp_k[0] == 'stackop'){
				$stack_ops[$k2] = strtoupper($v);
			}
			else{
				$checks[] = array('asset' => strtoupper($k), 'amount' => round(floatval($v) * 100000000)); //convert amount to satoshis
			}
		}
		$full_stack = array();
		foreach($checks as $k => $row){
			$stack_item = $row;
			if(isset($ops[$k])){
				$stack_item['op'] = $ops[$k];
			}
			else{
				$stack_item['op'] = '>='; //default to greater or equal than
			}
			if(isset($stack_ops[$k])){
				$stack_item['stackOp'] = $stack_ops[$k];
			}
			else{
				$stack_item['stackOp'] = 'AND';
			}
			$full_stack[] = $stack_item;
		}
		

		$output['result'] = $tca->checkAccess($full_stack, false, $address);
		
		return Response::json($output);
	}
	
	public function requestOAuth()
	{
		$input = Input::all();
		$output = array();
		$error = false;
		
		if(!isset($input['state'])){
			$error = true;
			$output['error'] = 'State required';
		}
		
		if(!isset($input['client_id'])){
			$error = true;
			$output['error'] = 'Client ID required';
		}

		$client_id = $input['client_id'];
		$client = $this->oauth_client_repository->findById($client_id);
        if (!$client){ 
			$error = true;
			$output['error'] = "Unable to find oauth client for client ".$client_id;
		}				
		
		if(!isset($input['scope'])){
			$error = true;
			$output['error'] = 'Scope required';
		}
        $scope_param = Input::get('scope');
        $scopes = array();
		if($scope_param AND count($scopes) == 0){
			$scopes = explode(',', $scope_param);
		}		
		
		if(!isset($input['response_type']) OR $input['response_type'] != 'code'){
			$error = true;
			$output['error'] = 'Invalid response type';
		}	
		
		if(!isset($input['username'])){
			$error = true;
			$output['error'] = 'Username required';
		}
		
		if(!isset($input['password'])){
			$error = true;
			$output['error'] = 'Password required';
		}
		
		$user = User::where('username', $input['username'])->first();
		if(!$user){
			$error = true;
			$output['error'] = 'Invalid credentials';
		}
		else{
			$checkPass = Hash::check($input['password'], $user->password);
			if(!$checkPass){
				$error = true;
				$output['error'] = 'Invalid credentials';
			}
		}
		
		$already_connected = $this->client_connection_repository->isUserConnectedToClient($user, $client);
		if(!$already_connected){	
			$grant_access = false;
			if(isset($input['grant_access']) AND intval($input['grant_access']) === 1){
				$grant_access = true;
			}
			if(!$grant_access){
				$error = true;
				$output['error'] = 'Application denied access to account';
			}
		}		
		
		if(!$error){
			$code_params =  Authorizer::getAuthCodeRequestParams();
			$code_url = Authorizer::issueAuthCode('user', $user->id, $code_params);
			$parse = parse_str(parse_url($code_url)['query'], $parsed);
			$output['code'] = $parsed['code'];
			$output['state'] = $parsed['state'];			
			if(!$already_connected){
				$this->client_connection_repository->connectUserToClient($user, $client, $scopes);
			}
		}
		
		return Response::json($output);
	}
	
	public function getOAuthToken()
	{
		$output = array();
        try {
			$output = Authorizer::issueAccessToken();
        } catch (\Exception $e) {
            Log::error("Exception: ".get_class($e).' '.$e->getMessage());
			$output['error'] = 'Failed getting access token';
        }
        return Response::json($output);
	}
	
}
