<?php

namespace TKAccounts\Handlers\Monitoring;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tokenly\ConsulHealthDaemon\ServicesChecker;

/**
 * This is invoked when a new block is received
 */
class MonitoringHandler {

    public function __construct(ServicesChecker $services_checker) {
        $this->services_checker = $services_checker;
    }

    public function handleConsoleHealthCheck() {
        // check MySQL
        $this->services_checker->checkMySQLConnection();
    }

    public function subscribe($events) {
        $events->listen('consul-health.console.check', 'TKAccounts\Handlers\Monitoring\MonitoringHandler@handleConsoleHealthCheck');
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Checks
    
}
