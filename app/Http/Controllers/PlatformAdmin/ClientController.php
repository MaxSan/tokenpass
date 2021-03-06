<?php

namespace TKAccounts\Http\Controllers\PlatformAdmin;

use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\OAuthClientRepository;
use Tokenly\CurrencyLib\CurrencyUtil;
use Tokenly\PlatformAdmin\Controllers\ResourceController;
use Input;

class ClientController extends ResourceController
{

    protected $view_prefix      = 'client';
    protected $repository_class = OAuthClientRepository::class;

    protected function getValidationRules() {
        return [
            'name' => 'required|max:255',
        ];
    }    


}
