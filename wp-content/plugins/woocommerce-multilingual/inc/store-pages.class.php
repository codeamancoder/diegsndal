<?php
  
class WCML_Store_Pages{
    
    function __construct(){
        
        add_action('init', array($this, 'init'));
        add_filter( 'woocommerce_create_pages', array( $this, 'switch_pages_language' ), 9 );
        add_filter( 'woocommerce_create_pages', array( $this, 'install_pages_action' ), 11 );
        //update wc pages ids after change default language or create new if not exists
        add_action( 'icl_after_set_default_language', array( $this, 'after_set_default_language' ), 10, 2 );
        // Translate shop page ids
        $this->add_filter_to_get_shop_translated_page_id();
        add_filter( 'template_include', array( $this, 'template_loader' ), 100 );
    }   
    
    function init(){        
        global $woocommerce_wpml;
        if(!is_admin()){
            add_filter('pre_get_posts', array($this, 'shop_page_query'), 9);
            add_filter('icl_ls_languages', array($this, 'translate_ls_shop_url'));
            add_filter('parse_request', array($this, 'adjust_shop_page'));
        }
        
        add_filter( 'woocommerce_create_page_id', array( $this, 'check_store_page_id'), 10 ,3 );

        $nonce = filter_input( INPUT_POST, 'wcml_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if (isset($_POST['create_pages']) && wp_verify_nonce($nonce, 'create_pages')) {
            $this->create_missing_store_pages();
        }
        
        // table rate shipping support        
        if(defined('TABLE_RATE_SHIPPING_VERSION')){
            add_filter('woocommerce_table_rate_query_rates_args', array($this, 'default_shipping_class_id'));
        }
        
        $this->front_page_id = get_option('page_on_front');
        $this->shop_page_id =  wc_get_page_id('shop');
        $this->shop_page = get_post( $this->shop_page_id );
        
        
        $this->localize_flat_rates_shipping_classes();
        
    }   
    
    function switch_pages_language( $pages ){
        global $sitepress,$woocommerce_wpml;

        $default_language = $sitepress->get_default_language();

        if( $sitepress->get_current_language() != $default_language ){
            foreach( $pages as $key => $page ){

                switch( $key ){
                    case 'shop':
                        $page['name'] = 'shop';
                        $page['title'] = 'Shop';
                        break;
                    case 'cart':
                        $page['name'] = 'cart';
                        $page['title'] = 'Cart';
                        break;
                    case 'checkout':
                        $page['name'] = 'checkout';
                        $page['title'] = 'Checkout';
                        break;
                    case 'myaccount':
                        $page['name'] = 'my-account';
                        $page['title'] = 'My account';
                        break;
                }

                if( $sitepress->get_default_language() != 'en' ){
                    $page['name'] = $woocommerce_wpml->terms->get_translation_from_woocommerce_mo_file( 'Page slug'.$page['name'], $default_language );
                    $page['title'] = $woocommerce_wpml->terms->get_translation_from_woocommerce_mo_file( 'Page title'.$page['title'], $default_language );
                }
            }
        }

        return $pages;
    }
    
    function install_pages_action( $pages ){
        global $sitepress,$wpdb;

        foreach( $pages as $key => $page){

            if ( strlen( $page[ 'content' ] ) > 0 ) {
                // Search for an existing page with the specified page content (typically a shortcode)
                $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " as p LEFT JOIN {$wpdb->prefix}icl_translations AS icl ON icl.element_id = p.id WHERE post_type='page' AND post_content LIKE %s AND icl.element_type = 'post_page' AND icl.language_code = %s LIMIT 1;", "%{$page[ 'content' ]}%", $sitepress->get_default_language() ) );
            } else {
                // Search for an existing page with the specified page slug
                $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " as p LEFT JOIN {$wpdb->prefix}icl_translations AS icl ON icl.element_id = p.id WHERE post_type='page' AND post_name = %s AND icl.element_type = 'post_page' AND icl.language_code = %s LIMIT 1;", esc_sql( $page['name'] ), $sitepress->get_default_language() ) );
            }

            if( !$page_found ){
                $page_data = array(
                    'post_status'       => 'publish',
                    'post_type'         => 'page',
                    'post_author'       => 1,
                    'post_name'         => esc_sql( $page['name'] ),
                    'post_title'        => $page['title'],
                    'post_content'      => $page['content'],
                    'post_parent'       => ! empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '',
                    'comment_status'    => 'closed'
                );
                $page_id = wp_insert_post( $page_data );

                if ( 'woocommerce_' . $key . '_page_id' )
                    update_option( 'woocommerce_' . $key . '_page_id', $page_id );
            }

            unset( $pages[ $key ] );
        }

        return $pages;
    }
    
    function add_filter_to_get_shop_translated_page_id(){
        $woo_pages = array(
            'shop_page_id',
            'cart_page_id',
            'checkout_page_id',
            'myaccount_page_id',
            'lost_password_page_id',
            'edit_address_page_id',
            'view_order_page_id',
            'change_password_page_id',
            'logout_page_id',
            'pay_page_id',
            'thanks_page_id',
            'terms_page_id',
            'review_order_page_id'
        );

        foreach($woo_pages as $woo_page){
            add_filter('woocommerce_get_'.$woo_page, array($this, 'translate_pages_in_settings'));
            //I think following filter not needed because "option_woocommerce_..." not used in Woo, but I need ask David to confirm this
            add_filter('option_woocommerce_'.$woo_page, array($this, 'translate_pages_in_settings'));
        }

        add_filter('woocommerce_get_checkout_url', array($this, 'get_checkout_page_url'));
    }
    
    function translate_pages_in_settings($id) {
        global $pagenow;
        if( $pagenow == 'options-permalink.php' ){
            return $id;
        }

        return apply_filters( 'translate_object_id',$id, 'page', true);
    }
    
    function default_shipping_class_id($args){
        global $sitepress, $woocommerce_wpml;
        if($sitepress->get_current_language() != $sitepress->get_default_language() && !empty($args['shipping_class_id'])){
            
            $args['shipping_class_id'] = apply_filters( 'translate_object_id',$args['shipping_class_id'], 'product_shipping_class', false, $sitepress->get_default_language());
            
            if($woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT){
            // use unfiltred cart price to compare against limits of different shipping methods
            $args['price'] = $woocommerce_wpml->multi_currency->unconvert_price_amount($args['price']); 
            }
            
        }
        
        return $args;
    }
    
    /**
     * Filters WooCommerce query for translated shop page
     * 
     */
    function shop_page_query($q) {
        if ( ! $q->is_main_query() )
            return;

        //do not alter query_object and query_object_id (part 1 of 2)
        global $wp_query;
        $queried_object_original = isset($wp_query->queried_object) ? $wp_query->queried_object : null;
        $queried_object_id_original = isset($wp_query->queried_object_id) ? $wp_query->queried_object_id : null;

        if (
            !empty($this->shop_page) &&
            !empty($this->front_page_id) &&
            $q->get('post_type') != 'product' &&
            ( $q->get('page_id') !== $this->front_page_id  &&
                (
                    $this->shop_page_id == $q->get('page_id') ||
                    (
                        isset($q->is_home) &&
                        $q->is_home &&
                        isset($q->is_posts_page) &&
                        !$q->is_posts_page
                    )
                )
            )
        ){
            $q->set( 'post_type', 'product' );
            $q->set( 'page_id', '' );
            if ( isset( $q->query['paged'] ) )
                $q->set( 'paged', $q->query['paged'] );

            // Get the actual WP page to avoid errors
            // This is hacky but works. Awaiting http://core.trac.wordpress.org/ticket/21096
            global $wp_post_types;

            $q->is_page = true;

            $wp_post_types['product']->ID             = $this->shop_page->ID;
            $wp_post_types['product']->post_title     = $this->shop_page->post_title;
            $wp_post_types['product']->post_name      = $this->shop_page->post_name;

            // Fix conditional functions
            $q->is_singular = false;
            $q->is_post_type_archive = true;
            $q->is_archive = true;
        }

        //do not alter query_object and query_object_id (part 2 of 2)
        if(is_null($queried_object_original)){
            unset($wp_query->queried_object);
        }else{
            $wp_query->queried_object = $queried_object_original;
        }
        if(is_null($queried_object_id_original)){
            unset($wp_query->queried_object_id);
        }else{
            $wp_query->queried_object_id = $queried_object_id_original;
        }

    }


    /**
     * Translate shop url
     */
    function translate_ls_shop_url($languages) {
        global $sitepress;
        $shop_id = $this->shop_page_id;
        $front_id = apply_filters( 'translate_object_id',$this->front_page_id, 'page');
        foreach ($languages as &$language) {
            // shop page
            // obsolete?
            if (is_post_type_archive('product')) {
                if ($front_id == $shop_id) {
                    $url = $sitepress->language_url($language['language_code']);
                } else {
                    $url = get_permalink(apply_filters( 'translate_object_id',$shop_id, 'page', true, $language['language_code']));
                }
                $language['url'] = $url;
            }
        }
        return $languages;
    }

    function adjust_shop_page($q) {
        global $sitepress;
        if ($sitepress->get_default_language() != $sitepress->get_current_language()) {
            if (!empty($q->query_vars['pagename'])) {
                $shop_page = get_post( woocommerce_get_page_id('shop') );
                // we should explode by / for children page
                $query_var_page = explode('/',$q->query_vars['pagename']);
                if (isset($shop_page->post_name) && $shop_page->post_name == $query_var_page[count($query_var_page)-1]) {
                    unset($q->query_vars['page']);
                    unset($q->query_vars['pagename']);
                    $q->query_vars['post_type'] = 'product';
                }
            }
        }
    }
    
    /**
     * create missing pages
     */
    function create_missing_store_pages() {
        global $sitepress,$wp_rewrite,$woocommerce_wpml,$woocommerce;
        $miss_lang = $this->get_missing_store_pages();

        //dummy array for names
        $names = array( __('Cart','wpml-wcml'),
                        __('Checkout','wpml-wcml'),
                        __('Checkout &rarr; Pay','wpml-wcml'),
                        __('Order Received','wpml-wcml'),
                        __('My Account','wpml-wcml'),
                        __('Change Password','wpml-wcml'),
                        __('Edit My Address','wpml-wcml'),
                        __('Logout','wpml-wcml'),
                        __('Lost Password','wpml-wcml'),
                        __('View Order','wpml-wcml'),
                        __('Shop','wpml-wcml'));

        if (isset($miss_lang['codes'])) {
            $wp_rewrite = new WP_Rewrite();
            
            $check_pages = $this->get_wc_pages();
            $default_language = $sitepress->get_default_language();
            if(in_array($default_language, $miss_lang['codes'])){
                $miss_lang['codes'] = array_merge(array($default_language), array_diff($miss_lang['codes'], array($default_language)));
            }                               
            
            foreach ($miss_lang['codes'] as $mis_lang) {
                $args = array();

                unload_textdomain('wpml-wcml');
                $sitepress->switch_lang($mis_lang);
                load_textdomain( 'wpml-wcml', WCML_LOCALE_PATH . '/wpml-wcml-' . $sitepress->get_locale( $mis_lang ) . '.mo' );
                    
                foreach ($check_pages as $page) {
                    $orig_id = get_option($page);
                    $trid = $sitepress->get_element_trid($orig_id,'post_page');
                    $translations = $sitepress->get_element_translations($trid,'post_page',true);

                    if (!isset( $translations[ $mis_lang ] ) || ( !is_null( $translations[$mis_lang]->element_id ) && get_post_status( $translations[$mis_lang]->element_id )!='publish' ) ) {
                        $orig_page = get_post($orig_id);

                        switch( $page ){
                            case 'woocommerce_shop_page_id':
                                $page_title = __( 'Shop', 'wpml-wcml');
                                break;
                            case 'woocommerce_cart_page_id':
                                $page_title = __( 'Cart', 'wpml-wcml');
                                break;
                            case 'woocommerce_checkout_page_id':
                                $page_title = __( 'Checkout', 'wpml-wcml');
                                break;
                            case 'woocommerce_myaccount_page_id':
                                $page_title = __( 'My Account', 'wpml-wcml');
                                break;
                            default:
                                $page_title = __( $orig_page->post_title, 'wpml-wcml');
                                break;
                        }

                        $args['post_title'] = $page_title;
                        $args['post_type'] = $orig_page->post_type;
                        $args['post_content'] = $orig_page->post_content;
                        $args['post_excerpt'] = $orig_page->post_excerpt;
                        $args['post_status'] = ( isset($translations[$mis_lang]->element_id) && get_post_status($translations[$mis_lang]->element_id) != 'publish' ) ? 'publish' : $orig_page->post_status;
                        $args['menu_order'] = $orig_page->menu_order;
                        $args['ping_status'] = $orig_page->ping_status;
                        $args['comment_status'] = $orig_page->comment_status;
                        $post_parent = apply_filters( 'translate_object_id',$orig_page->post_parent, 'page', false, $mis_lang);
                        $args['post_parent'] = is_null($post_parent)?0:$post_parent;
                        $new_page_id = wp_insert_post($args);

                        if( isset($translations[$mis_lang]->element_id) && get_post_status($translations[$mis_lang]->element_id) == 'trash' && $mis_lang == $default_language){
                            update_option($page, $new_page_id);
                        }                        
                        
                        if( isset($translations[$mis_lang]->element_id) &&  !is_null($translations[$mis_lang]->element_id)){
                            $sitepress->set_element_language_details($translations[$mis_lang]->element_id, 'post_page', false, $mis_lang);
                        }

                        $trid = $sitepress->get_element_trid($orig_id, 'post_page');
                        $sitepress->set_element_language_details($new_page_id, 'post_page', $trid, $mis_lang);


                    }
                }
                unload_textdomain('wpml-wcml');
                $sitepress->switch_lang($default_language);
                load_textdomain( 'wpml-wcml', WCML_LOCALE_PATH . '/wpml-wcml-' . $sitepress->get_locale( $default_language ) . '.mo' );
            }
            
            wp_redirect(admin_url('admin.php?page=wpml-wcml')); exit;
        }
    }
    
     /**
     * get missing pages
     * return array;
     */
     function get_missing_store_pages() {

        $check_pages = $this->get_wc_pages();

        $missing_lang = '';
        $pages_in_progress = array();
        
        foreach ($check_pages as $page) {
            $page_id = get_option($page);
            $page_obj = get_post($page_id);
                if(!$page_id || !$page_obj || $page_obj->post_status != 'publish' ){
                    return 'non_exist';
                }
        }
        
        global $sitepress;
        $languages = $sitepress->get_active_languages();

        $missing_lang_codes = array();

            foreach ($check_pages as $page) {
                $store_page_id = get_option($page);
                $trid = $sitepress->get_element_trid($store_page_id,'post_page');
                $translations = $sitepress->get_element_translations($trid,'post_page',true);
                $pages_in_progress_miss_lang = '';
                foreach ($languages as $language) {
                    if ( !in_array( $language['code'], $missing_lang_codes ) &&
                        ( !isset( $translations[ $language['code'] ] ) || ( !is_null( $translations[$language['code']]->element_id ) && get_post_status( $translations[$language['code']]->element_id )!='publish' ) ) ) {

                        $missing_lang_codes[] = $language['code'];

                        if (!empty($missing_lang)) {
                            $missing_lang .= ', ' . $language['display_name'];
                        } else {
                            $missing_lang .= $language['display_name'];
                        }

                        continue;
                    }

                    if ( isset($translations[$language['code']] ) && is_null( $translations[$language['code']]->element_id ) ) {

                        if (!empty($pages_in_progress_miss_lang)) {
                            $pages_in_progress_miss_lang .= ', ' . $language['display_name'];
                        } else {
                            $pages_in_progress_miss_lang .= $language['display_name'];
                        }

                        $pages_in_progress[$store_page_id] = $pages_in_progress_miss_lang;

                    }
            }
        }

         $pages_in_progress_notice = '';
        foreach( $pages_in_progress as $key => $page_in_progress ){
            if (!empty($pages_in_progress_notice)) {
                $pages_in_progress_notice .= ', ' . get_the_title( $key ) .' ('.$page_in_progress.')';
            } else {
                $pages_in_progress_notice .= get_the_title( $key ) .' ('.$page_in_progress.')';
            }
        }

         $status = array();

        if (!empty($missing_lang)) {
            $status['lang'] = $missing_lang;
            $status['codes'] = $missing_lang_codes;
        }

         if (!empty($pages_in_progress_notice)) {
             $status['in_progress'] = $pages_in_progress_notice;
         }

         if(!empty($status)){
            return $status;
        }else {
            return false;
        }
    }
    
    /**
     * Filters WooCommerce checkout link.
     */
    function get_checkout_page_url(){
        return get_permalink(apply_filters( 'translate_object_id',get_option('woocommerce_checkout_page_id'), 'page', true));
    }
    
    function localize_flat_rates_shipping_classes(){
        global $woocommerce;
        
        if(is_ajax() && isset($_POST['action']) && $_POST['action'] == 'woocommerce_update_order_review'){
            $woocommerce->shipping->load_shipping_methods();
            $shipping_methods = $woocommerce->shipping->get_shipping_methods();
            foreach($shipping_methods as $method){
                if(isset($method->flat_rate_option)){
                    add_filter('option_' . $method->flat_rate_option, array($this, 'translate_shipping_classs'));
                }
            }
            
        }
        
        
    }
    
    function translate_shipping_classs($rates){
        
        if(is_array($rates)){
            foreach($rates as $shipping_class => $value){
                global $woocommerce_wpml;
                $term_id = $woocommerce_wpml->products->wcml_get_term_id_by_slug('product_shipping_class', $shipping_class );

                if($term_id && !is_wp_error($term_id)){
                    $translated_term_id = apply_filters( 'translate_object_id', $term_id, 'product_shipping_class', true);
                    if($translated_term_id != $term_id){
                        $term = $woocommerce_wpml->products->wcml_get_term_by_id( $translated_term_id, 'product_shipping_class' );
                        unset($rates[$shipping_class]);
                        $rates[$term->slug] = $value;
                        
                    }
                }
            }
        }   
        return $rates;    
    }
            
    function get_wc_pages(){
        return apply_filters('wcml_wc_installed_pages', array(
            'woocommerce_shop_page_id',
            'woocommerce_cart_page_id',
            'woocommerce_checkout_page_id',
            'woocommerce_myaccount_page_id'
        ));
    }

    function after_set_default_language( $code, $previous_code ){
        global $wpdb;
        $this->create_missing_store_pages();

        $pages = $this->get_wc_pages();

        foreach( $pages as $page ){

            if( $page_id = get_option($page) ){
                $trnsl_id = apply_filters( 'translate_object_id', $page_id, 'page', false, $code );
                if( !is_null( $trnsl_id ) ){
                    $wpdb->update( $wpdb->options, array( 'option_value' => $trnsl_id ), array( 'option_name' => $page ) );
                }
            }

        }
        
        // Clear any unwanted data
        wc_delete_product_transients();
        delete_transient( 'woocommerce_cache_excluded_uris' );
    }

    function template_loader( $template ){

        if ( is_product_taxonomy() ) {
            global $sitepress, $woocommerce_wpml;

            $current_language = $sitepress->get_current_language();
            $default_language = $sitepress->get_default_language();

            if( $current_language != $default_language ) {

                $templates = array( 'woocommerce.php' );

                $term = get_queried_object();

                if (is_tax('product_cat') || is_tax('product_tag')) {
                    $file = 'taxonomy-' . $term->taxonomy . '.php';
                } else {
                    $file = 'archive-product.php';
                }

                // check templates
                $term = get_queried_object();
                $taxonomy = $term->taxonomy;
                $prefix = 'taxonomy-'.$taxonomy;
                $original_term_id = icl_object_id($term->term_id, $taxonomy, true, $default_language);
                $original_term = $woocommerce_wpml->products->wcml_get_term_by_id( $original_term_id, $taxonomy );


                $terms_to_check = array( $term->term_id => $term->slug );
                if ($original_term) {
                    $terms_to_check[ $original_term_id ] = $original_term->slug;
                }

                $paths = array( '', WC()->template_path() );

                foreach( $paths as $path ){

                    foreach( $terms_to_check as $term_id => $term_slug ){

                        $templates[] = $path."$prefix-{$current_language}-{$term_slug}.php";
                        $templates[] = $path."$prefix-{$current_language}-{$term_id}.php";
                        $templates[] = $path."$prefix-{$term_slug}.php";
                        $templates[] = $path."$prefix-{$term_id}.php";

                    }

                    $templates[] = $path."$prefix-{$current_language}.php";
                    $templates[] = $path."$prefix.php";
                    $templates[] = $path.$file;
                }

                $template = locate_template( array_unique($templates) );

                if (!$template || WC_TEMPLATE_DEBUG_MODE) {

                    $template = WC()->plugin_path() . '/templates/' . $file;

                }

            }

        }

        return $template;

    }
    
}
