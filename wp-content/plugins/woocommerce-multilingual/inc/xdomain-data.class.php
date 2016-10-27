<?php

class xDomain_Data{
    
    function __construct(){

        add_filter( 'WPML_cross_domain_language_data', array( $this, 'pass_data_to_domain' ) );

        add_action( 'init', array( $this, 'check_request' ) );
    }

    function pass_data_to_domain( $data ){

        $wcml_session_id = md5( microtime() . uniqid(rand(), TRUE) );

        $data[ 'wcsid' ] = $wcml_session_id;

        $session_data = array();

        if( isset( $_COOKIE[ 'wp_woocommerce_session_' . COOKIEHASH ] ) ){

            $session_data[ 'session' ] = $_COOKIE[ 'wp_woocommerce_session_' . COOKIEHASH ];

        }

        if( isset( $_COOKIE[ 'woocommerce_cart_hash' ] ) ){

            $session_data[ 'hash' ] = $_COOKIE[ 'woocommerce_cart_hash' ];
            $session_data[ 'items' ] = $_COOKIE[ 'woocommerce_items_in_cart' ];

        }

        if ( !empty( $session_data ) ){
            update_option( 'wcml_session_data_'.$wcml_session_id, $session_data );
        }

        return $data;
    }

    function check_request(){

        if( isset($_GET['xdomain_data']) ){
            $xdomain_data = json_decode( base64_decode( $_GET['xdomain_data'] ), JSON_OBJECT_AS_ARRAY );
            if(isset($xdomain_data[ 'wcsid' ])){
                $this->set_session_data( $xdomain_data[ 'wcsid' ] );
            }
        }

    }

    function set_session_data( $wcml_session_id ){

        $data = maybe_unserialize( get_option( 'wcml_session_data_'.$wcml_session_id ) );

        if( !empty( $data ) ){

            $session_expiration  = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours
            $secure = apply_filters( 'wc_session_use_secure_cookie', false );

            if( isset( $data[ 'session' ] ) ){

                setcookie( 'wp_woocommerce_session_' . COOKIEHASH, $data['session'], $session_expiration, COOKIEPATH, COOKIE_DOMAIN, $secure );

            }

            if( isset( $data[ 'hash '] ) ){

                setcookie( 'woocommerce_cart_hash' , $data['hash'], $session_expiration, COOKIEPATH, COOKIE_DOMAIN, $secure );
                setcookie( 'woocommerce_items_in_cart' , $data['items'], $session_expiration, COOKIEPATH, COOKIE_DOMAIN, $secure );

            }

        }

        delete_option( 'wcml_session_data_'.$wcml_session_id );

    }

}