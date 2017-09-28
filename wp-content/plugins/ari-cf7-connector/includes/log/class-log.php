<?php
namespace Ari_Cf7_Connector\Log;

abstract class Log {
    static protected function create_logger() {
        throw new \BadMethodCallException( 'Not implemented' );
    }

    static protected function instance() {
        static $log;

        if ( ! is_null( $log ) )
            return $log;

        $log = static::create_logger();

        return $log;
    }

    static protected function log_enabled() {
        return true;
    }

    static public function __callStatic( $name, $arguments ) {
        if ( ! static::log_enabled() )
            return ;

        $log = static::instance();

        $bad_method = true;
        if ( method_exists( $log, $name ) ) {
            $reflection = new \ReflectionMethod( $log, $name );
            if ( $reflection->isPublic() ) {
                $bad_method = false;
            }
        }

        if ( $bad_method )
            throw new \BadMethodCallException( '"' . $name . '" method does not exist' );

        return call_user_func_array(
            array( $log, $name ),

            $arguments
        );
    }
}
