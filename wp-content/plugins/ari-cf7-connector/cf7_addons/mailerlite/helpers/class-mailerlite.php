<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Helpers;

use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Helper as Helper;
use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Mailerlite_Api_Wrapper as MailerLiteApi;
use Ari\Utils\Utils as Utils;
use Ari\Utils\Array_Helper as Array_Helper;

class Mailerlite {
    private $has_error = false;

    private $last_error = '';

    private $api_key;

    function __construct( $api_key ) {
        $this->api_key = Helper::resolve_apikey( $api_key );
    }

    private function set_error( $error ) {
        $this->has_error = true;
        $this->last_error = $error;
    }

    public function get_last_error() {
        return $this->last_error;
    }

    public function clear_error() {
        $this->last_error = '';
        $this->has_error = false;
    }

    public function is_error() {
        return $this->has_error;
    }

    private function is_valid_key() {
        return strlen( $this->api_key ) > 0;
    }

    public function check_apikey() {
        $this->clear_error();

        $is_valid = false;

        if ( ! $this->is_valid_key() )
            return $is_valid;

        try {
            $mailer_lite = new MailerLiteApi( $this->api_key );
            $mailer_lite->fields()->get();

            if ( $mailer_lite->is_error() ) {
                $this->set_error( $mailer_lite->get_last_error() );
            } else {
                $is_valid = true;
            }
        } catch ( \Exception $ex ) {
            $is_valid = false;
            $this->set_error( $ex->getMessage() );
        }

        return $is_valid;
    }

    public function get_lists( $reload = false ) {
        $this->clear_error();
        $lists = false;

        if ( ! $this->is_valid_key() )
            return $lists;

        $cache_key = md5( 'mailerlite_lists_' . $this->api_key );
        if ( ! $reload ) {
            $lists = get_transient( $cache_key );

            if ( false !== $lists ) {
                return $lists;
            }
        }

        try {
            $mailer_lite = new MailerLiteApi( $this->api_key );

            $groups = $mailer_lite->groups()->get();

            if ( ! $mailer_lite->is_error() ) {
                $lists = array();

                if ( ! empty ( $groups ) && $groups->count() > 0 ) {
                    foreach ( $groups as $list ) {
                        $list_obj = new \stdClass();
                        $list_obj->id = $list->id;
                        $list_obj->name = $list->name;

                        $lists[] = $list_obj;
                    }

                    $lists = Array_Helper::sort_assoc( $lists, 'name' );
                    set_transient( $cache_key, $lists, ARICF7CONNECTOR_CACHE_LIFETIME );
                }
            } else {
                $this->set_error( $mailer_lite->get_last_error() );
            }
        } catch (\Exception $ex) {
            $this->set_error( $ex->getMessage() );
        }

        return $lists;
    }

    public function get_fields( $reload = false ) {
        $this->clear_error();

        if ( ! $this->is_valid_key() )
            return false;

        $fields = false;
        $cache_key = md5( 'mailerlite_fields_' . $this->api_key );
        if ( ! $reload ) {
            $fields = get_transient( $cache_key );

            if ( false !== $fields ) {
                return $fields;
            }
        }

        try {
            $mailer_lite = new MailerLiteApi( $this->api_key );
            $ml_fields = $mailer_lite->fields()->get();

            if ( ! $mailer_lite->is_error() ) {
                $fields = array();
                if ( ! empty ( $ml_fields ) && $ml_fields->count() > 0 ) {
                    foreach ( $ml_fields as $field ) {
                        $field_obj = new \stdClass();
                        $field_obj->id = $field->id;
                        $field_obj->name = $field->title;
                        $field_obj->key = $field->key;
                        $field_obj->type = $field->type;

                        $fields[] = $field_obj;
                    }

                    $fields = Array_Helper::sort_assoc( $fields, 'name' );
                    set_transient( $cache_key, $fields, ARICF7CONNECTOR_CACHE_LIFETIME );
                }
            } else {
                $this->set_error( $mailer_lite->get_last_error() );
            }
        } catch (\Exception $ex) {
            $fields = null;
            $this->set_error( $ex->getMessage() );
        }

        return $fields;
    }

    public function subscribe( $email, $name, $lists, $options = array() ) {
        $this->clear_error();
        $result = false;

        if ( ! $this->is_valid_key() )
            return $result;

        $email = trim( $email );

        if ( strlen( $email ) == 0 ) {
            $this->set_error( 'MailerLite: It is not possible to subscribe a user to the selected lists. An empty email is provided.' );

            return $result;
        }

        if ( ! is_array( $lists ) || count( $lists ) == 0 ) {
            return $result;
        }

        try {
            $result = true;
            $mailer_lite = new MailerLiteApi( $this->api_key );
            $groups_api = $mailer_lite->groups();

            foreach ( $lists as $list ) {
                $list_id = $list['id'];
                $params = $this->prepare_subscription_parameters( $email, $name, $list );

                $groups_api->addSubscriber(
                    $list_id,

                    $params
                );

                if ( $mailer_lite->is_error() ) {
                    $result = false;
                    $this->set_error( $mailer_lite->get_last_error() );
                }
            }
        } catch (\Exception $ex) {
            $result = false;
            $this->set_error( $ex->getMessage() );
        }

        return $result;
    }

    private function prepare_subscription_parameters( $email, $name, $list ) {
        $params = array(
            'email' => $email,
        );

        $fields = Utils::get_value( $list, 'fields' );
        $merge_fields = ( is_array( $fields) && count( $fields ) > 0 ) ? $fields : array();

        if ( strlen( $name ) > 0 ) {
            if ( ! empty( $merge_fields['last_name'] ) || ! empty( $merge_fields['name'] ) ) {
                if ( empty( $merge_fields['last_name'] ) )
                    $merge_fields['last_name'] = $name;
                else if ( empty( $merge_fields['name'] ) )
                    $params['name'] = $name;
            } else {
                $name_parts = preg_split( '/\s+/', $name );
                if ( count( $name_parts ) > 1 ) {
                    $merge_fields['last_name'] = array_pop( $name_parts );
                }

                $params['name'] = implode( ' ', $name_parts );
            }
        }

        if ( count( $merge_fields ) > 0 )
            $params['fields'] = $merge_fields;

        return $params;
    }
}
