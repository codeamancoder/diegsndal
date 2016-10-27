<?php

class WCML_Upgrade{
    
    private $versions = array(

        '2.9.9.1',
        '3.1',
        '3.2',
        '3.3',
        '3.5',
        '3.5.4',
        '3.6'

    );
    
    function __construct(){

        add_action('init', array($this, 'run'));
        add_action('init', array($this, 'setup_upgrade_notices'));
        add_action('admin_notices',  array($this, 'show_upgrade_notices'));
        
        add_action('wp_ajax_wcml_hide_notice', array($this, 'hide_upgrade_notice'));
        
    }   
    
    function setup_upgrade_notices(){
        
        $wcml_settings = get_option('_wcml_settings');
        $version_in_db = get_option('_wcml_version');
        
        if(!empty($version_in_db) && version_compare($version_in_db, '2.9.9.1', '<')){
            $n = 'varimages';
            $wcml_settings['notifications'][$n] = 
                array(
                    'show' => 1, 
                    'text' => __('Looks like you are upgrading from a previous version of WooCommerce Multilingual. Would you like to automatically create translated variations and images?', 'wcml').
                            '<br /><strong>' .
                            ' <a href="' .  admin_url('admin.php?page=' . basename(WCML_PLUGIN_PATH) . '/menu/sub/troubleshooting.php') . '">' . __('Yes, go to the troubleshooting page', 'wcml') . '</a> |' . 
                            ' <a href="#" onclick="jQuery.ajax({type:\'POST\',url: ajaxurl,data:\'action=wcml_hide_notice&notice='.$n.'\',success:function(){jQuery(\'#' . $n . '\').fadeOut()}});return false;">'  . __('No - dismiss', 'wcml') . '</a>' . 
                            '</strong>'
                );
            update_option('_wcml_settings', $wcml_settings);
        }
        
    }
    
    function show_upgrade_notices(){
        $wcml_settings = get_option('_wcml_settings');
        if(!empty($wcml_settings['notifications'])){ 
            foreach($wcml_settings['notifications'] as $k => $notification){
                
                // exceptions
                if(isset($_GET['page']) && $_GET['page'] == basename(WCML_PLUGIN_PATH) . '/menu/sub/troubleshooting.php' && $k == 'varimages') continue;
                
                if($notification['show']){
                    ?>
                    <div id="<?php echo $k ?>" class="updated">
                        <p><?php echo $notification['text']  ?></p>
                    </div>
                    <?php    
                }
            }
        }
    }
    
    function hide_upgrade_notice($k){
        
        if(empty($k)){
            $k = $_POST['notice'];
        }
        
        $wcml_settings = get_option('_wcml_settings');
        if(isset($wcml_settings['notifications'][$k])){
            $wcml_settings['notifications'][$k]['show'] = 0;
            update_option('_wcml_settings', $wcml_settings);
        }
    }
    
    function run(){
        
        $version_in_db = get_option('_wcml_version');
        
        // exception - starting in 2.3.2
        if(empty($version_in_db) && get_option('icl_is_wcml_installed')){
            $version_in_db = '2.3.2';
        }
        
        $migration_ran = false;
        
        if($version_in_db && version_compare($version_in_db, WCML_VERSION, '<')){
                        
            foreach($this->versions as $version){
                
                if(version_compare($version, WCML_VERSION, '<=') && version_compare($version, $version_in_db, '>')){

                    $upgrade_method = 'upgrade_' . str_replace('.', '_', $version);
                    
                    if(method_exists($this, $upgrade_method)){
                        $this->$upgrade_method();
                        $migration_ran = true;
                    }
                    
                }
                
            }
            
        }
        
        if($migration_ran || empty($version_in_db)){
            update_option('_wcml_version', WCML_VERSION);            
        }
    }
    
    function upgrade_2_9_9_1(){
        global $wpdb;
        
        //migrate exists currencies
        $currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "icl_currencies ORDER BY `id` DESC");
        foreach($currencies as $currency){
            if(isset($currency->language_code)){
            $wpdb->insert($wpdb->prefix .'icl_languages_currencies', array(
                    'language_code' => $currency->language_code,
                    'currency_id' => $currency->id
                )
            );
        }
        }

        $cols = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->prefix}icl_currencies");        
        if(in_array('language_code', $cols)){
            $wpdb->query("ALTER TABLE {$wpdb->prefix}icl_currencies DROP COLUMN language_code");
        }
        
        // migrate settings
        $new_settings = array(
            'is_term_order_synced'       => get_option('icl_is_wcml_term_order_synched'),
            'file_path_sync'             => get_option('wcml_file_path_sync'),
            'is_installed'               => get_option('icl_is_wpcml_installed'),
            'dismiss_doc_main'           => get_option('wpml_dismiss_doc_main'),
            'enable_multi_currency'      => get_option('icl_enable_multi_currency'),
            'currency_converting_option' => get_option('currency_converting_option')
        );
        
        if(!get_option('_wcml_settings')){
            add_option('_wcml_settings', $new_settings, false, true);
        }
        
        delete_option('icl_is_wcml_term_order_synced');
        delete_option('wcml_file_path_sync');
        delete_option('icl_is_wpcml_installed');
        delete_option('wpml_dismiss_doc_main');
        delete_option('icl_enable_multi_currency');
        delete_option('currency_converting_option');
        
        
    }
    
    function upgrade_3_1(){
        global $wpdb,$sitepress;
        $wcml_settings = get_option('_wcml_settings');
        
        if(isset($wcml_settings['enable_multi_currency']) && $wcml_settings['enable_multi_currency'] == 'yes'){
            $wcml_settings['enable_multi_currency'] = WCML_MULTI_CURRENCIES_INDEPENDENT;
        }else{
            $wcml_settings['enable_multi_currency'] = WCML_MULTI_CURRENCIES_DISABLED;
        }
        
        $wcml_settings['products_sync_date'] = 1;
        
        
        update_option('_wcml_settings', $wcml_settings);
        
        // multi-currency migration
        if($wcml_settings['enable_multi_currency'] == 'yes' && $wcml_settings['currency_converting_option'] == 2){
            
            // get currencies exchange rates
            $results = $wpdb->get_results("SELECT code, value FROM {$wpdb->prefix}icl_currencies");
            foreach($results as $row){
                $exchange_rates[$row->code] = $row->value;    
            }
            
            // get languages currencies map
            $results = $wpdb->get_results("SELECT l.language_code, c.code FROM {$wpdb->prefix}icl_languages_currencies l JOIN {$wpdb->prefix}icl_currencies c ON l.currency_id = c.id");
            foreach($results as $row){
                $language_currencies[$row->language_code] = $row->code;    
            }
            
            
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, t.trid, t.element_type 
                FROM {$wpdb->posts} p JOIN {$wpdb->prefix}icl_translations t ON t.element_id = p.ID AND t.element_type IN ('post_product', 'post_product_variation')
                WHERE 
                    p.post_type in ('product', 'product_variation') AND t.language_code = %s
                    
            ", $sitepress->get_default_language()));
            
            // set custom conversion rates
            foreach($results as $row){
                $translations = $sitepress->get_element_translations($row->trid, $row->element_type);
                $meta = get_post_meta($row->ID);
                $original_prices['_price']    = !empty($meta['_price']) ? $meta['_price'][0] : 0;
                $original_prices['_regular_price'] = !empty($meta['_regular_price']) ? $meta['_regular_price'][0] : 0;
                $original_prices['_sale_price']    = !empty($meta['_sale_price']) ? $meta['_sale_price'][0] : 0;
                
                
                $ccr = array();
                
                foreach($translations as $translation){
                    if($translation->element_id != $row->ID){
                        
                        $meta = get_post_meta($translation->element_id);
                        $translated_prices['_price'] = $meta['_price'][0];
                        $translated_prices['_regular_price'] = $meta['_regular_price'][0];
                        $translated_prices['_sale_price']    = $meta['_sale_price'][0];

                        if(!empty($translated_prices['_price']) && !empty($original_prices['_price']) && $translated_prices['_price'] != $original_prices['_price']){
                            
                            $ccr['_price'][$language_currencies[$translation->language_code]] = $translated_prices['_price'] / $original_prices['_price'];
                            
                        }                
                        if(!empty($translated_prices['_regular_price']) && !empty($original_prices['_regular_price']) && $translated_prices['_regular_price'] != $original_prices['_regular_price']){
                            
                            $ccr['_regular_price'][$language_currencies[$translation->language_code]] = $translated_prices['_regular_price'] / $original_prices['_regular_price'];
                            
                        }                
                        if(!empty($translated_prices['_sale_price']) && !empty($original_prices['_sale_price']) && $translated_prices['_sale_price'] != $original_prices['_sale_price']){
                            
                            $ccr['_sale_price'][$language_currencies[$translation->language_code]] = $translated_prices['_sale_price'] / $original_prices['_sale_price'] ;
                            
                        }                
                        
                        
                    }
                }
                
                if($ccr){
                    update_post_meta($row->ID, '_custom_conversion_rate', $ccr);    
                }
                
                
            }
            
            
        }        
        
    }
    
    function upgrade_3_2(){
        
        woocommerce_wpml::set_up_capabilities();
        
        //delete not existing currencies in WC
        global $wpdb;
        $currencies = $wpdb->get_results("SELECT id,code FROM " . $wpdb->prefix . "icl_currencies ORDER BY `id` DESC");
        $wc_currencies = get_woocommerce_currencies();
        foreach ($currencies as $currency){
            if(!array_key_exists($currency->code,$wc_currencies)){
                $wpdb->delete( $wpdb->prefix . 'icl_currencies', array( 'ID' => $currency->id ) );
            }
        }
        
    }
    
    function upgrade_3_3(){
        global $wpdb, $woocommerce_wpml;
        
        woocommerce_wpml::set_up_capabilities();

        $currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "icl_currencies ORDER BY `id` ASC", OBJECT);
        if($currencies)
        foreach($this->currencies as $currency){
            
            $woocommerce_wpml->settings['currency_options'][$currency->code]['rate']      = $currency->value;
            $woocommerce_wpml->settings['currency_options'][$currency->code]['updated']   = $currency->changed;
            $woocommerce_wpml->settings['currency_options'][$currency->code]['position']  = 'left';
            $woocommerce_wpml->settings['currency_options'][$currency->code]['languages'] = $woocommerce_wpml->settings['currencies_languages'];
            unset($woocommerce_wpml->settings['currencies_languages']);
            
            $woocommerce_wpml->update_settings();
            
        }
        
        $wpdb->query("DROP TABLE `{$wpdb->prefix}icl_currencies`");
        
    }

    function upgrade_3_5()
    {
        global $wpdb;
        $wcml_settings = get_option('_wcml_settings');

        $wcml_settings['products_sync_order'] = 1;

        update_option('_wcml_settings', $wcml_settings);
    }

    function upgrade_3_5_4()
    {
        flush_rewrite_rules( );
    }

    function upgrade_3_6()
    {
        global $wpdb;
        $wcml_settings = get_option('_wcml_settings');

        $wcml_settings['display_custom_prices'] = 0;
        $wcml_settings['currency_switcher_product_visibility'] = 1;

        update_option('_wcml_settings', $wcml_settings);
    }

}