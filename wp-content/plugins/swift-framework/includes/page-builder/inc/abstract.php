<?php

    /*
    *
    *	Swift Page Builder - Abstract Class
    *	------------------------------------------------
    *	Swift Framework
    * 	Copyright Swift Ideas 2016 - http://www.swiftideas.com
    *
    */

    abstract class SFPageBuilderAbstract {

        public static $version;
        public static $config;

        public function __construct() {
        }

        public function init( $settings ) {
            self::$config = (array) $settings;
        }

        public function addAction( $action, $method, $priority = 10 ) {
            add_action( $action, array( $this, $method ), $priority );
        }

        public function addFilter( $filter, $method, $priority = 10 ) {
            add_action( $filter, array( $this, $method ), $priority );
        }

        /* Shortcode methods */
        public function addShortCode( $tag, $func ) {
            add_shortcode( $tag, $func );
        }

        public function doShortCode( $content ) {
            do_shortcode( $content );
        }

        public function removeShortCode( $tag ) {
            remove_shortcode( $tag );
        }

        public function post( $param ) {
            return isset( $_POST[ $param ] ) ? $_POST[ $param ] : null;
        }

        public function get( $param ) {
            return isset( $_GET[ $param ] ) ? $_GET[ $param ] : null;
        }

        public function assetURL( $asset ) {
            return self::$config['SPB_ASSETS'] . $asset;
        }

        public function frontendAssetURL( $asset ) {
            return self::$config['SPB_ASSETS_FRONTEND'] . $asset;
        }
    }


    interface SPBTemplateInterface {

        public function output( $post = null );

    }

?>