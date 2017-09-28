<?php
namespace Ari_Cf7_Connector\Log;

require_once ARICF7CONNECTOR_3RDPARTY_LOADER;

use Ari_Cf7_Connector\Helpers\Settings as Settings;
use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;

final class App_Log extends Log {
    static private $log_enabled;

    static protected function create_logger() {
        $logger = new Logger( 'cf7connector_app' );
        $logger->pushHandler(
            new StreamHandler( ARICF7CONNECTOR_APP_LOG_PATH, Logger::INFO )
        );

        return $logger;
    }

    static protected function log_enabled() {
        if ( ! is_null( self::$log_enabled ) )
            return self::$log_enabled;

        self::$log_enabled = (bool) Settings::instance()->get_option( 'app_log' );

        return self::$log_enabled;
    }
}
