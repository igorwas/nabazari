<?php
define( 'ARICF7CONNECTOR_VERSION', '1.1.3' );
define( 'ARICF7CONNECTOR_SLUG', 'contact-form-7-connector' );
define( 'ARICF7CONNECTOR_ASSETS_URL', ARICF7CONNECTOR_URL . 'assets/' );
define( 'ARICF7CONNECTOR_VERSION_OPTION', 'ari_cf7connector' );
define( 'ARICF7CONNECTOR_INSTALL_PATH', ARICF7CONNECTOR_PATH . 'install/' );
define( 'ARICF7CONNECTOR_3RDPARTY_LOADER', ARICF7CONNECTOR_PATH . 'libraries/vendor/vendor/autoload.php' );
define( 'ARICF7CONNECTOR_LOG_PATH', WP_CONTENT_DIR . '/cf7connector-log/' );
define( 'ARICF7CONNECTOR_APP_LOG_PATH', ARICF7CONNECTOR_LOG_PATH . 'cf7connector_app.log' );
define( 'ARICF7CONNECTOR_DEBUG_LOG_PATH', ARICF7CONNECTOR_LOG_PATH . 'cf7connector_system.log' );

define( 'ARICF7CONNECTOR_CACHE_LIFETIME', 24 * 60 * MINUTE_IN_SECONDS );

define( 'ARICF7CONNECTOR_SETTINGS_GROUP', 'ari_cf7connector' );
define( 'ARICF7CONNECTOR_SETTINGS_NAME', 'ari_cf7connector_settings' );

define( 'ARICF7CONNECTOR_MESSAGETYPE_SUCCESS', 'success' );
define( 'ARICF7CONNECTOR_MESSAGETYPE_NOTICE', 'notice' );
define( 'ARICF7CONNECTOR_MESSAGETYPE_ERROR', 'error' );
define( 'ARICF7CONNECTOR_MESSAGETYPE_WARNING', 'warning' );
