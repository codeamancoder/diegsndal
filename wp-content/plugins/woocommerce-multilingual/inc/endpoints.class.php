<?php

class WCML_Endpoints{

    var $endpoints_strings = array();

    function __construct(){

        //endpoints hooks
        $this->register_endpoints_translations();
        $this->maybe_flush_rules();
        add_action( 'icl_ajx_custom_call', array( $this, 'rewrite_rule_endpoints' ), 11, 2 );
        add_action( 'woocommerce_update_options', array( $this, 'update_endpoints_rules' ) );
        add_filter( 'pre_update_option_rewrite_rules', array( $this, 'update_rewrite_rules' ), 100, 2 );

        add_filter( 'page_link', array( $this, 'endpoint_permalink_filter' ), 10, 2 ); //after WPML

        if(!is_admin()){
            add_filter('pre_get_posts', array($this, 'check_if_endpoint_exists'));
        }

        add_filter( 'woocommerce_get_endpoint_url', array( $this, 'filter_get_endpoint_url' ), 10, 4 );


    }


    function register_endpoints_translations(){
        if( !class_exists( 'woocommerce' ) || !defined( 'ICL_SITEPRESS_VERSION' ) || ICL_PLUGIN_INACTIVE || version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) return false;

        $wc_vars = WC()->query->query_vars;

        if ( !empty( $wc_vars ) ){
            $query_vars = array(
                // Checkout actions
                'order-pay'          => $this->get_endpoint_translation( 'order-pay', $wc_vars['order-pay'] ),
                'order-received'     => $this->get_endpoint_translation( 'order-received', $wc_vars['order-received'] ),

                // My account actions
                'view-order'         => $this->get_endpoint_translation( 'view-order', $wc_vars['view-order'] ),
                'edit-account'       => $this->get_endpoint_translation( 'edit-account', $wc_vars['edit-account'] ),
                'edit-address'       => $this->get_endpoint_translation( 'edit-address', $wc_vars['edit-address'] ),
                'lost-password'      => $this->get_endpoint_translation( 'lost-password', $wc_vars['lost-password'] ),
                'customer-logout'    => $this->get_endpoint_translation( 'customer-logout', $wc_vars['customer-logout'] ),
                'add-payment-method' => $this->get_endpoint_translation( 'add-payment-method', $wc_vars['add-payment-method'] ),
            );

            WC()->query->query_vars = $query_vars;

        }

    }

    function get_endpoint_translation( $key, $endpoint, $language = null ){
        global $wpdb;

        $string = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}icl_strings WHERE name = %s AND value = %s ", 'Endpoint slug: ' . $key, $endpoint ) );

        if( !$string && function_exists( 'icl_register_string' ) ){
            do_action('wpml_register_single_string', 'WordPress', 'Endpoint slug: ' . $key, $endpoint );
        }else{
            $this->endpoints_strings[] = $string;
        }

        if( function_exists('icl_t') ){
            return apply_filters( 'wpml_translate_single_string', $endpoint, 'WordPress', 'Endpoint slug: '. $key, $language );
        }else{
            return $endpoint;
        }
    }

    function rewrite_rule_endpoints( $call, $data ){
        if( $call == 'icl_st_save_translation' && in_array( $data['icl_st_string_id'], $this->endpoints_strings ) ){
            $this->add_endpoints();
            add_option( 'flush_rules_for_endpoints_translations', true );
        }
    }

    function maybe_flush_rules(){
        if( get_option( 'flush_rules_for_endpoints_translations' ) ){
            WC()->query->init_query_vars();
            WC()->query->add_endpoints();
            flush_rewrite_rules();
            delete_option( 'flush_rules_for_endpoints_translations' );
        }
    }

    function update_rewrite_rules( $value, $old_value ){
        remove_filter( 'pre_update_option_rewrite_rules', array( $this, 'update_rewrite_rules' ), 100, 2 );
        $this->add_endpoints();
        flush_rewrite_rules();
        return $value;
    }

    function update_endpoints_rules(){
        $this->add_endpoints();
    }

    function add_endpoints(){
        if( !isset( $this->endpoints_strings ) )
            return;

        global $wpdb;
        //add endpoints and flush rules
        foreach( $this->endpoints_strings as $string_id ){

            $strings = $wpdb->get_results( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE string_id = %s AND status = %s", $string_id , ICL_STRING_TRANSLATION_COMPLETE) );

            foreach( $strings as $string ){
                add_rewrite_endpoint( $string->value, EP_ROOT | EP_PAGES );
            }
        }

    }

    function endpoint_permalink_filter( $p, $pid ){
        global $post;

        if( isset($post->ID) && !is_admin() && version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) && defined( 'ICL_SITEPRESS_VERSION' ) && !ICL_PLUGIN_INACTIVE ){
            global $wp,$sitepress;

            $current_lang = $sitepress->get_current_language();
            $page_lang = $sitepress->get_language_for_element( $post->ID, 'post_page');
            if( $current_lang != $page_lang && apply_filters( 'translate_object_id', $pid, 'page', false, $page_lang ) == $post->ID  ){

                $endpoints = WC()->query->get_query_vars();

                foreach( $endpoints as $key => $endpoint ){
                    if( isset($wp->query_vars[$key]) ){
                        if( in_array( $key, array( 'pay', 'order-received' ) ) ){
                            $endpoint = get_option( 'woocommerce_checkout_'.str_replace( '-','_',$key).'_endpoint' );
                        }else{
                            $endpoint = get_option( 'woocommerce_myaccount_'.str_replace( '-','_',$key).'_endpoint' );
                        }

                        $p = $this->get_endpoint_url( $this->get_endpoint_translation( $key, $endpoint, $current_lang ), $wp->query_vars[ $key ], $p, $page_lang );
                    }
                }
            }
        }

        return $p;
    }

    function get_endpoint_url($endpoint, $value = '', $permalink = '', $page_lang = false ){
        global $sitepress;

        if( $page_lang ){
            $edit_address_shipping = $this->get_translated_edit_address_slug( 'shipping', $page_lang );
            $edit_address_billing = $this->get_translated_edit_address_slug( 'billing', $page_lang );

            if( $edit_address_shipping == urldecode( $value ) ){
                $value = $this->get_translated_edit_address_slug( 'shipping', $sitepress->get_current_language() );
            }elseif( $edit_address_billing == urldecode( $value ) ){
                $value = $this->get_translated_edit_address_slug( 'billing', $sitepress->get_current_language() );
            }

        }


        if ( get_option( 'permalink_structure' ) ) {
            if ( strstr( $permalink, '?' ) ) {
                $query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
                $permalink    = current( explode( '?', $permalink ) );
            } else {
                $query_string = '';
            }
            $url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
        } else {
            $url = add_query_arg( $endpoint, $value, $permalink );
        }
        return $url;
    }

    /*
     * We need check special case - when you manually put in URL default not translated endpoint it not generated 404 error
     */
    function check_if_endpoint_exists($q){
        global $wp_query;

        $my_account_id = wc_get_page_id('myaccount');

        $current_id = $q->query_vars['page_id'];
        if(!$current_id){
            $current_id = $q->queried_object_id;
        }

        if( !$q->is_404 && $current_id == $my_account_id ){

            $uri_vars = array_filter( explode( '/', $_SERVER['REQUEST_URI']) );
            $endpoints =  WC()->query->get_query_vars();
            $endpoint_in_url = urldecode( end( $uri_vars ) );

            $endpoints['shipping'] = urldecode(  $this->get_translated_edit_address_slug( 'shipping' ) );
            $endpoints['billing'] = urldecode(  $this->get_translated_edit_address_slug( 'billing' )  );

            if( urldecode( $q->query['pagename'] ) != $endpoint_in_url && !in_array( $endpoint_in_url,$endpoints ) && is_numeric( $endpoint_in_url ) && !in_array( urldecode( prev( $uri_vars ) ) ,$endpoints ) ){
                $wp_query->set_404();
                status_header(404);
                include( get_query_template( '404' ) );
                die();
            }

        }

    }

    function get_translated_edit_address_slug( $slug, $language = false ){
        global $woocommerce_wpml;

        $strings_language = $woocommerce_wpml->strings->get_wc_context_language();

        if( $strings_language == $language ){
            return $slug;
        }

        $translated_slug = apply_filters( 'wpml_translate_single_string', $slug, 'woocommerce', 'edit-address-slug: '.$slug, $language );

        if( $translated_slug == $slug ){

            if( $language ){
                $translated_slug = $woocommerce_wpml->terms->get_translation_from_woocommerce_mo_file( 'edit-address-slug'. chr(4) .$slug, $language );
            }else{
                $translated_slug = _x( $slug, 'edit-address-slug', 'woocommerce' );
            }

        }

        return $translated_slug;
    }

    function filter_get_endpoint_url( $url, $endpoint, $value, $permalink ){

        // return translated edit account slugs
        if( isset( WC()->query->query_vars[ 'edit-address' ] ) && isset( WC()->query->query_vars[ 'edit-address' ] )  == $endpoint && in_array( $value, array('shipping','billing'))){
            remove_filter('woocommerce_get_endpoint_url', array( $this, 'filter_get_endpoint_url'),10,4);
            $url = wc_get_endpoint_url( 'edit-address', $this->get_translated_edit_address_slug( $value ) );
            add_filter('woocommerce_get_endpoint_url', array( $this, 'filter_get_endpoint_url'),10,4);


        }

        return $url;
    }


}
