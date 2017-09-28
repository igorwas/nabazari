<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Helpers;

require_once ARICF7CONNECTOR_MAILCHIMP_3RDPARTY_LOADER;

use \DrewM\MailChimp\MailChimp as MailChimp_API;
use \DrewM\MailChimp\Batch;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Helper as Helper;
use Ari\Utils\Utils as Utils;
use Ari\Utils\Array_Helper as Array_Helper;

class Mailchimp {
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
            $mailchimp = new MailChimp_API( $this->api_key );

            $response = $mailchimp->get(
                '',

                array(
                    'fields' => 'account_name',
                )
            );

            if ( ! $mailchimp->success() ) {
                $error = $response['title'];
                if ( empty( $error ) ) {
                    $error = sprintf(
                        'Could not validate `%s` API key. Unknown error occurs.',
                        $this->api_key
                    );
                }

                $this->set_error( $error );
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

        $cache_key = md5( 'mailchimp_lists_' . $this->api_key );
        if ( ! $reload ) {
            $lists = get_transient( $cache_key );

            if ( false !== $lists ) {
                return $lists;
            }
        }

        try {
            $mailchimp = new MailChimp_API( $this->api_key );

            $response = $mailchimp->get(
                'lists',

                array(
                    'fields' => 'lists.id,lists.name',

                    'count' => 9999,
                )
            );

            if ( $mailchimp->success() ) {
                $lists = array();
                if ( ! empty ( $response['lists'] ) && is_array( $response['lists'] ) && count( $response['lists'] ) > 0 ) {
                    foreach ( $response['lists'] as $list ) {
                        $list_obj = new \stdClass();
                        $list_obj->id = $list['id'];
                        $list_obj->name = $list['name'];

                        $lists[] = $list_obj;
                    }

                    $lists = Array_Helper::sort_assoc( $lists, 'name' );
                    set_transient( $cache_key, $lists, ARICF7CONNECTOR_CACHE_LIFETIME );
                }
            } else {
                $this->set_error( $this->get_last_mailchimp_error( $mailchimp, $response ) );
            }
        } catch (\Exception $ex) {
            $this->set_error( $ex->getMessage() );
        }

        return $lists;
    }

    public function get_list_fields( $list_id, $reload = false ) {
        $result = $this->get_lists_fields( array( $list_id ), $reload );

        if ( ! is_array( $result ) )
            return $result;

        if ( count( $result ) > 0 && isset( $result[$list_id] ) )
            return $result[$list_id];

        return array();
    }

    public function get_lists_fields( $lists, $reload = false ) {
        $this->clear_error();

        if ( ! $this->is_valid_key() )
            return false;

        $lists = $this->prepare_lists_id( $lists );

        $lists_fields = array();
        if ( ! $reload ) {
            $load_lists = array();
            foreach ( $lists as $list_id ) {
                $cache_key = md5( 'mailchimp_listfields_' . $list_id );

                $list_fields = get_transient( $cache_key );

                if ( false !== $list_fields ) {
                    $lists_fields[$list_id] = $list_fields;
                } else {
                    $load_lists[] = $list_id;
                }
            }

            $lists = $load_lists;
        }

        if ( count( $lists ) == 0 ) {
            return $lists_fields;
        }

        try {
            $ignore_field_tags = array();
            $mailchimp = new MailChimp_API( $this->api_key );

            foreach ( $lists as $list_id ) {
                $response = $mailchimp->get(
                    'lists/' . $list_id . '/merge-fields',

                    array(
                        'fields' => 'merge_fields.merge_id,merge_fields.name,merge_fields.tag',

                        'count' => 999,
                    )
                );

                if ( $mailchimp->success() ) {
                    $list_fields = array();
                    if ( ! empty ( $response['merge_fields'] ) && is_array( $response['merge_fields'] ) && count( $response['merge_fields'] ) > 0 ) {
                        foreach ( $response['merge_fields'] as $field ) {
                            if ( in_array( $field['tag'], $ignore_field_tags ) )
                                continue ;

                            $field_obj = new \stdClass();
                            $field_obj->field_id = $field['merge_id'];
                            $field_obj->system_id = $list_id . '_' . $field['merge_id'];
                            $field_obj->name = $field['name'];
                            $field_obj->tag = $field['tag'];

                            $list_fields[] = $field_obj;
                        }

                        $cache_key = md5( 'mailchimp_listfields_' . $list_id );
                        $list_fields = Array_Helper::sort_assoc( $list_fields, 'name' );
                        set_transient( $cache_key, $list_fields, ARICF7CONNECTOR_CACHE_LIFETIME );
                    }

                    $lists_fields[$list_id] = $list_fields;
                }
            }
        } catch (\Exception $ex) {
            $lists_fields = null;
            $this->set_error( $ex->getMessage() );
        }

        return $lists_fields;
    }

    public function subscribe( $email, $name, $lists, $options = array() ) {
        $this->clear_error();
        $result = false;

        if ( ! $this->is_valid_key() )
            return $result;

        $email = trim( $email );

        if ( strlen( $email ) == 0 ) {
            $this->set_error( 'MailChimp: It is not possible to subscribe a user to the selected lists. An empty email is provided.' );

            return $result;
        }

        if ( ! is_array( $lists ) || count( $lists ) == 0 ) {
            return $result;
        }

        if ( count( $lists ) == 1 ) {
            $list = reset( $lists );

            return $this->single_subscribe( $email, $name, $list, $options );
        }

        try {
            $update_existing = (bool) Utils::get_value( $options, 'update_existing', false );
            $mailchimp = new MailChimp_API( $this->api_key );
            $batch = $mailchimp->new_batch();

            foreach ( $lists as $list ) {
                $list_id = $list['id'];
                $op_code = $list_id;
                $params = $this->prepare_subscription_parameters( $email, $name, $list, $options );

                if ( $update_existing ) {
                    $batch->put(
                        $op_code,

                        'lists/' . $list_id . '/members/' . $mailchimp->subscriberHash( $email ),

                        $params
                    );
                } else {
                    $batch->post(
                        $op_code,

                        'lists/' . $list_id . '/members',

                        $params
                    );
                }
            }

            $response = $batch->execute();
            if ( $mailchimp->success() ) {
                $result = true;
            } else {
                $this->set_error( $this->get_last_mailchimp_error( $mailchimp, $response ) );
            }
        } catch (\Exception $ex) {
            $result = false;
            $this->set_error( $ex->getMessage() );
        }

        return $result;
    }

    public function single_subscribe( $email, $name, $list, $options = array() ) {
        $this->clear_error();
        $result = false;

        if ( ! $this->is_valid_key() )
            return $result;

        $email = trim( $email );

        if ( strlen( $email ) == 0 ) {
            $this->set_error( 'MailChimp: It is not possible to subscribe a user to the selected lists. An empty email is provided.' );

            return $result;
        }

        if ( ! is_array( $list ) && ( ! is_string( $list ) || strlen( $list ) == 0 ) ) {
            $this->set_error( 'MailChimp: An empty list is provided.' );

            return $result;
        }

        if ( is_string( $list ) ) {
            $list = array( 'id' => $list );
        }

        try {
            $update_existing = (bool) Utils::get_value( $options, 'update_existing', false );
            $list_id = $list['id'];
            $response = null;
            $params = $this->prepare_subscription_parameters( $email, $name, $list, $options );
            $mailchimp = new MailChimp_API( $this->api_key );

            if ( $update_existing ) {
                $response = $mailchimp->put(
                    'lists/' . $list_id . '/members/' . $mailchimp->subscriberHash( $email ),

                    $params
                );
            } else {
                $response = $mailchimp->post(
                    'lists/' . $list_id . '/members',

                    $params
                );
            }

            if ( $mailchimp->success() ) {
                $result = true;
            } else {
                $this->set_error( $this->get_last_mailchimp_error( $mailchimp, $response ) );
            }
        } catch (\Exception $ex) {
            $result = false;
            $this->set_error( $ex->getMessage() );
        }

        return $result;
    }

    private function prepare_subscription_parameters( $email, $name, $list, $options = array() ) {
        $double_optin = (bool) Utils::get_value( $options, 'double_optin', false );

        $params = array(
            'email_address' => $email,

            'status' => $double_optin ? 'pending' : 'subscribed',
        );

        $fields = Utils::get_value( $list, 'fields' );
        $merge_fields = ( is_array( $fields) && count( $fields ) > 0 ) ? $fields : array();

        if ( strlen( $name ) > 0 ) {
            if ( ! empty( $merge_fields['LNAME'] ) || ! empty( $merge_fields['FNAME'] ) ) {
                if ( empty( $merge_fields['LNAME'] ) )
                    $merge_fields['LNAME'] = $name;
                else if ( empty( $merge_fields['FNAME'] ) )
                    $merge_fields['FNAME'] = $name;
            } else {
                $name_parts = preg_split( '/\s+/', $name );
                if ( count( $name_parts ) > 1 ) {
                    $merge_fields['LNAME'] = array_pop( $name_parts );
                }

                $merge_fields['FNAME'] = implode( ' ', $name_parts );
            }
        }

        if ( count( $merge_fields ) > 0 )
            $params['merge_fields'] = $merge_fields;

        return $params;
    }

    private function prepare_lists_id( $lists ) {
        $prepared_lists = array();
        $lists = Array_Helper::ensure_array( $lists );

        foreach ( $lists as $idx => $list_id ) {
            $list_id = trim( $list_id );
            if ( empty( $list_id ) )
                continue ;

            $prepared_lists[] = $list_id;
        }

        $prepared_lists = array_unique( $prepared_lists );

        return $prepared_lists;
    }

    private function get_last_mailchimp_error( $mailchimp, $response = null ) {
        $error = $mailchimp->getLastError();
        if ( is_null( $response ) )
            $response = $mailchimp->getLastResponse();

        if ( $response && ! empty( $response['title'] ) ) {
            $error = sprintf(
                '%1$s: %2$s',
                $response['title'],
                $response['detail']
            );
        }

        return $error;
    }
}
