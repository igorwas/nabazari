<?php

class Wpzoom_Instagram_Widget_API {
    /**
     * @var Wpzoom_Instagram_Widget_API The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Instagram Access Token
     *
     * @var string
     */
    protected $access_token;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Wpzoom_Instagram_Widget_API The *Singleton* instance.
     */
    public static function getInstance()
    {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct() {
        $options = get_option( 'wpzoom-instagram-widget-settings' );
        $this->access_token = $options['access-token'];
    }

    /**
     * @param $screen_name string Instagram username
     * @param $image_limit int    Number of images to retrieve
     * @param $image_width int    Desired image width to retrieve
     *
     * @return array|bool Array of tweets or false if method fails
     */
    public function get_items( $image_limit, $image_width ) {

        $transient = 'zoom_instagram_is_configured';

        $response = wp_remote_get( sprintf( 'https://api.instagram.com/v1/users/self/media/recent/?access_token=%s&count=%s', $this->access_token, $image_limit ) );

        if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
            set_transient( $transient, false, MINUTE_IN_SECONDS );

            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ) );

        $result = array();
        $username = '';

        foreach ( $data->data as $item ) {

            if ( empty( $username ) ) {
                $username = $item->user->username;
            }

            $crop_function = false;
            $thumbnail     = $item->images->thumbnail->url;
            preg_match( "/\/([c]\d{1,4}\.\d{1,4}\.\d{1,4}\.\d{1,4})\//", $thumbnail, $crop_function );

            $result[] = array(
                'link'          => $item->link,
                'image-url'     => $this->get_crop_url( $item->images->{$this->get_best_size( $image_width )}, $crop_function ),
                'image-caption' => ! empty( $item->caption->text ) ? esc_attr($item->caption->text) : ''
            );
        }

        $result = array('items' => $result, 'username'=>$username);
        set_transient( $transient, $result, 30 * MINUTE_IN_SECONDS );

        return $result;
    }

    /**
     *  Inject in url a call to crop method from Instagram for landscape and portrait images.
     *
     * @param $item
     *
     * @return mixed
     */
    public function get_crop_url( $item, $crop_function ) {

        if ( ! empty( $crop_function ) ) {
            $sizes           = array( 320, 640 );
            $max_dim         = in_array( $item->height, $sizes ) ? $item->height : $item->width;
            $squared_string  = sprintf( 's%dx%d', $max_dim, $max_dim );
            $portrait_string = sprintf( 'p%dx%d', $max_dim, $max_dim );
            $basename        = basename( $item->url );

            $item->url = str_replace( array(
                $basename,
                $portrait_string
            ), array( $crop_function[1] . '/' . $basename, $squared_string ), $item->url );
        }

        return $item->url;
    }

    /**
     * @param $screen_name string Instagram username
     *
     * @return bool|int Instagram user id or false on error
     */
    protected function get_user_id( $screen_name ) {
        $user_id_option = 'zoom_instagram_uid_' . $screen_name;

        if ( false !== ( $user_id = get_option( $user_id_option ) ) ) {
            return $user_id;
        }

        $response = wp_remote_get( sprintf( 'https://api.instagram.com/v1/users/search?q=%s&access_token=%s', $screen_name, $this->access_token ) );

        if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $result = json_decode( wp_remote_retrieve_body( $response ) );

        if ( ! isset( $result->data ) ) {
            return false;
        }

        $user_id = false;

        foreach ( $result->data as $user ) {
            if ( $user->username === $screen_name ) {
                $user_id = $user->id;

                break;
            }
        }

        update_option( $user_id_option, $user_id );

        return $user_id;
    }

    /**
     * @param $desired_width int Desired image width in pixels
     *
     * @return string Image size for Instagram API
     */
    protected function get_best_size( $desired_width ) {
        $size = 'thumbnail';
        $sizes = array(
            'thumbnail'           => 150,
            'low_resolution'      => 306,
            'standard_resolution' => 640
        );

        $diff = PHP_INT_MAX;

        foreach ( $sizes as $key => $value ) {
            if ( abs( $desired_width - $value ) < $diff ) {
                $size = $key;
                $diff = abs( $desired_width - $value );
            }
        }

        return $size;
    }

    /**
     * Check if given access token is valid for Instagram Api.
     */
    public static function is_access_token_valid( $access_token ) {
        $response = wp_remote_get( sprintf( 'https://api.instagram.com/v1/users/self/?access_token=%s', $access_token ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        return true;
    }

    public function is_configured() {
        $transient = 'zoom_instagram_is_configured';

        if ( false !== ( $result = get_transient( $transient ) ) ) {
            if ( 'yes' === $result ) {
                return true;
            }

            if ( 'no' === $result ) {
                return false;
            }
        }

        $condition = $this->is_access_token_valid( $this->access_token );

        if ( true === $condition ) {
            set_transient( $transient, 'yes', DAY_IN_SECONDS );

            return true;
        }

        set_transient( $transient, 'no', DAY_IN_SECONDS );

        return false;
    }

    public static function reset_cache() {
        delete_transient( 'zoom_instagram_is_configured' );
    }

    public function get_access_token() {
        return $this->access_token;
    }

    public function set_access_token( $access_token ) {
        $this->access_token = $access_token;
    }
}
