<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined ( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

if( ! class_exists( 'YITH_Frontend_Manager' ) ){
    
    class YITH_Frontend_Manager{

        /**
         * Main Instance
         *
         * @var string
         * @since 1.0
         * @access protected
         */
        protected static $_instance = null;

        /**
         * Main Admin Instance
         *
         * @var YITH_Frontend_Manager_Admin | YITH_Frontend_Manager_Admin_Premium
         * @since 1.0
         */
        public $backend = null;

        /**
         * Main GUI Instance
         *
         * @var YITH_Frontend_Manager_GUI | YITH_Frontend_Manager_GUI_Premium
         * @since 1.0
         */
        public $gui = null;

        /**
         * YITH Multi Vendor Integration
         *
         * @var mixed
         * @since 1.0
         */
        public $module_object = array();

        /**
         * Check if the plugin works on frontend or backend
         *
         * @var boolean
         * @since 1.0
         */
        public $is_admin;
        
        /**
         * Available Section
         *
         * @var mixed|Array
         * @since 1.0
         */
        public $available_sections = array();

        /**
         * Sections array
         *
         * @var array|mixed|void
         * @since 1.0.0.
         */
        protected $_sections = array();

        /**
         * Modules array
         *
         * @var array|mixed|void
         * @since 1.0.0.
         */
        protected $_modules = array();

        /**
         * Modules array
         *
         * @var array|mixed|void
         * @since 1.0.0.
         */
        public $third_party_modules = array();

        /**
         * Refresh Rewrite Rules Transient name
         *
         * @var string
         * @since 1.0
         */
        protected $_rewrite_rules_transient;

        /**
         * Check if WooCommerce run version 3 or greather
         *
         * @var string
         * @since 1.9.8
         */
        public $is_wc_3_0_or_greather;

        /**
         * Construct
         */
        public function __construct() {
            $this->_rewrite_rules_transient = yith_wcfm_get_rewrite_rules_transient();
            $this->is_admin = is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend' );
            $this->is_wc_3_0_or_greather = version_compare( WC()->version, '3.0', '>=' );

            /* === Classes Require === */
            $classes = apply_filters('yith_wcfm_required_classes', array(
                    'common' => array(
                        YITH_WCFM_CLASS_PATH . 'class.yith-frontend-manager-section.php',
                        YITH_WCFM_CLASS_PATH . 'class.yith-frontend-manager-media.php'
                    ),

                    'gui' => array(
                        YITH_WCFM_CLASS_PATH . 'class.yith-frontend-manager-gui.php'
                    ),

                    'backend' => array(
                        YITH_WCFM_CLASS_PATH . 'class.yith-frontend-manager-admin.php',
	                    YITH_WCFM_CLASS_PATH . 'functions.yith-frontend-manager-updates.php'
                    ),
                )
            );

            $this->_modules = array(
                /* === YITH WooCommerce Multi Vendor === */
                'YITH_Vendors' => array(
                    'class_path'             => YITH_WCFM_CLASS_PATH . 'module/multi-vendor/module.yith-multi-vendor.php',
                    'class_name'             => 'YITH_Frontend_Manager_For_Vendor',
                    'has_singleton_function' => true,
                    'context'                => 'common'
                )
            );

            $this->third_party_modules = apply_filters( 'yith_wcdm_third_party_modules', $this->third_party_modules );

            $this->available_sections = apply_filters( 'yith_wcfm_section_files', $this->_get_section_files() );

            $skip_context = $this->is_admin ? 'gui' : 'backend';
            foreach( $classes as $context => $required ){
                if( $skip_context != $context ){
                    foreach( $required as $class ){
                        require_once( $class );
                    }
                }
            }

            do_action( 'yith_wcfm_after_load_common_classes' );

            /* Load specific section class */
            foreach ( $this->available_sections as $class_name => $class_path ) {
               require_once( $class_path );
             }

            /* === Load YITH Plugin Framework === */
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            /* === Load Module === */
            add_action( 'init', array( $this, 'load_module' ), 15 );

            /* === Plugin Initializzation === */
            add_action( 'init', array( $this, 'init' ), 16 );
        }

        /**
         * Install sections
         *
         * @author Antonio La Rocca <antonio.larocca@yithemes.com>
         * @return void
         * @since 1.0.0
         */
        protected function _install_sections(){
            $sections = array_keys( YITH_Frontend_Manager()->available_sections );

            if( $sections ){
                foreach( $sections as $section ){
                    $section_obj = new $section();
                    $this->_sections[ $section_obj->get_id() ] = $section_obj;
                }
            }
        }

        /**
         * Get section files
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @access protected
         * @return array sections file to include
         */
        protected function _get_section_files(){
            $sections = array();
            foreach ( new DirectoryIterator( YITH_WCFM_SECTIONS_CLASS_PATH ) as $fileInfo ) {
                if( ! $fileInfo->isDot() && $fileInfo->isFile() ){
                    $fileName = $fileInfo->getFilename();
                    $className = ucwords( str_replace( array( 'class.', '.php', '-', 'yith' ), array( '', '', '_', 'YITH' ), $fileName ), '_' );

                    $sections[$className] = $fileInfo->getPathname();
                }
            }

            ksort( $sections );
            return $sections;
        }


        /**
         * Plugin Initializzation
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function init(){
            $this->_install_sections();
            
            if ( $this->is_admin ) {
                $this->backend = new YITH_Frontend_Manager_Admin();
            }

            else {
                $this->gui = new YITH_Frontend_Manager_GUI();
            }
        }

        /**
         * Load Module
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function load_module(){

            $modules = $this->_modules;

            if( ! empty( $this->third_party_modules ) ){
                $modules = array_merge( $modules, $this->third_party_modules );
            }

            $skip_context = $this->is_admin ? 'gui' : 'backend';

            foreach ( $modules as $plugin_main_class => $module_information ){
                if( ! empty( $module_information[ 'context' ] ) && $skip_context == $module_information['context'] ) {
                    continue;
                }

                if( class_exists( $plugin_main_class ) ){
                    file_exists( $module_information['class_path'] ) && require_once( $module_information['class_path'] );
                    if( class_exists( $module_information['class_name'] ) ) {
                        if( ! empty( $module_information['has_singleton_function'] ) ){
                            $module_information['class_name']();
                        }

                        else {
                            new $module_information['class_name']();
                        }
                    }
                }
            }
        }
        
        /**
         * Load plugin framework
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function plugin_fw_loader() {
            if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if ( ! empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * Main plugin Instance
         *
         * @return YITH_Frontend_Manager|YITH_Frontend_Manager_Premium Main instance
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function instance() {
            $self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

            if ( is_null( $self::$_instance ) ) {
                $self::$_instance = new $self;
            }

            return $self::$_instance;
        }

        /**
         * Get rewrite rules transient name
         *
         * @return string transient name 
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_rewrite_rules_transient(){
            return $this->_rewrite_rules_transient;
        }

        /**
         * Return section array
         *
         * @return array!mixed
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_section(){
            return $this->_sections;
        }
        
        /**
         * check if this is free or premium version of YITH WCFM
         * 
         * @since 1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return bool true for free, false otherwise
         *  
         */
        public function is_free(){
            return true;
        }

        /**
         * Force to regenerate rewrite rules
         *
         * @author Andrea Grillo <andrea.grillo@yitheme.com>
         * @since 1.0
         * @return void
         */
        public static function regenerate_transient_rewrite_rule_transient(){
            set_site_transient( YITH_Frontend_Manager()->get_rewrite_rules_transient(), true );

            if( is_ajax() ){
                wp_send_json( true, 200 );
            }
        }
    }
}