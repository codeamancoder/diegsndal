<?php
  
class WCML_Dependencies{
    
      
    private $missing = array();
    private $err_message = '';
    private $allok = true;

    function __construct(){
        
        if(is_admin()){
            add_action('wp_ajax_wcml_fix_strings_language', array($this, 'fix_strings_language')); // TODO: remove after WPML release  with support strings in different languages
            
            add_action('init', array($this, 'check_wpml_config'), 100);    
        }
        
        
    }      
      
    function check(){
        global $woocommerce_wpml, $sitepress, $woocommerce;

        if(!defined('ICL_SITEPRESS_VERSION') || ICL_PLUGIN_INACTIVE || is_null( $sitepress ) || !class_exists('SitePress')){
             $this->missing['WPML'] = WCML_Links::generate_tracking_link('https://wpml.org/');
             $this->allok = false;
        } elseif(version_compare(ICL_SITEPRESS_VERSION, '3.4', '<')){
            add_action('admin_notices', array($this, '_old_wpml_warning'));
            $this->allok = false;
        }

        if(!class_exists('woocommerce')){
            $this->missing['WooCommerce'] = 'http://www.woothemes.com/woocommerce/';
            $this->allok = false;
        }elseif( ( defined('WC_VERSION') && version_compare( WC_VERSION , '2.1', '<' ) ) || ( isset( $woocommerce->version ) && version_compare( $woocommerce->version , '2.1', '<' ) ) ){
            add_action('admin_notices', array($this, '_old_wc_warning'));
            $this->allok = false;
        }

        if(!defined('WPML_TM_VERSION')){
            $this->missing['WPML Translation Management'] = WCML_Links::generate_tracking_link('https://wpml.org/');
            $this->allok = false;
        }elseif(version_compare(WPML_TM_VERSION, '1.9', '<')){
            add_action('admin_notices', array($this, '_old_wpml_tm_warning'));
            $this->allok = false;
        }

        if(!defined('WPML_ST_VERSION')){
            $this->missing['WPML String Translation'] = WCML_Links::generate_tracking_link('https://wpml.org/');
            $this->allok = false;
        }elseif(version_compare(WPML_ST_VERSION, '2.0', '<')){
            add_action('admin_notices', array($this, '_old_wpml_st_warning'));
            $this->allok = false;
        }

        if(!defined('WPML_MEDIA_VERSION')){
            $this->missing['WPML Media'] = WCML_Links::generate_tracking_link('https://wpml.org/');
            $this->allok = false;
        }elseif(version_compare(WPML_MEDIA_VERSION, '2.1', '<')){
            add_action('admin_notices', array($this, '_old_wpml_media_warning'));
            $this->allok = false;
        }

        if ($this->missing) {
            add_action('admin_notices', array($this, '_missing_plugins_warning'));
        }
        
        if($this->allok){
            $this->check_for_incompatible_permalinks();
        }

        if(isset($sitepress)){
            $this->allok = $this->allok & $sitepress->setup();
        }else{
            $this->load_twig_support();
        }

        return $this->allok;
    }
      
    /**
    * Adds admin notice.
    */
    public function _old_wpml_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It is not compatible with  <a href="%s">WPML</a> versions prior %s.',
                    'woocommerce-multilingual'), WCML_Links::generate_tracking_link('https://wpml.org/'), '3.4'); ?></p></div>
    <?php }

    function _old_wc_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It is not compatible with  <a href="%s">Woocommerce</a> versions prior %s.',
                    'woocommerce-multilingual'), 'http://www.woothemes.com/woocommerce/', '2.1' ); ?></p></div>
    <?php }

    public function _old_wpml_tm_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It is not compatible with  <a href="%s">WPML Translation Management</a> versions prior %s.',
                    'woocommerce-multilingual'), WCML_Links::generate_tracking_link('https://wpml.org/'), '1.9'); ?></p></div>
    <?php }

    public function _old_wpml_st_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It is not compatible with  <a href="%s">WPML String Translation</a> versions prior %s.',
                    'woocommerce-multilingual'), WCML_Links::generate_tracking_link('https://wpml.org/'), '2.0'); ?></p></div>
    <?php }

    public function _old_wpml_media_warning(){
        ?>
        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It is not compatible with  <a href="%s">WPML Media</a> versions prior %s.',
                    'woocommerce-multilingual'), WCML_Links::generate_tracking_link('https://wpml.org/'), '2.1'); ?></p></div>
    <?php }
    
      
    /**
    * Adds admin notice.
    */
    public function _missing_plugins_warning(){

        $missing = '';
        $counter = 0;
        foreach ($this->missing as $title => $url) {
            $counter ++;
            if ($counter == sizeof($this->missing)) {                
                $sep = '';
            } elseif ($counter == sizeof($this->missing) - 1) {              
                $sep = ' ' . __('and', 'woocommerce-multilingual') . ' ';
            } else {                    
                $sep = ', ';
            }
            $missing .= '<a href="' . $url . '">' . $title . '</a>' . $sep;              
        } ?>

        <div class="message error"><p><?php printf(__('WooCommerce Multilingual is enabled but not effective. It requires %s in order to work.', 'woocommerce-multilingual'), $missing); ?></p></div>
        <?php
    }
      
    /**
    * For all the urls to work we need either:
    * 1) the shop page slug must be the same in all languages
    * 2) or the shop prefix disabled in woocommerce settings
    * one of these must be true for product urls to work
    * if none of these are true, display a warning message
    */
    private function check_for_incompatible_permalinks() {        
        global $sitepress, $sitepress_settings, $pagenow;

        // WooCommerce 2.x specific checks
        $permalinks = get_option('woocommerce_permalinks', array('product_base' => ''));
        if (empty($permalinks['product_base'])) {                
            return;
        }
        
        $message = sprintf('Because this site uses the default permalink structure, you cannot use slug translation for product permalinks.', 'woocommerce-multilingual');
        $message .= '<br /><br />';
        $message .= sprintf('Please choose a different permalink structure or disable slug translation.', 'woocommerce-multilingual');
        $message .= '<br /><br />';            
        $message .= '<a href="' . admin_url('options-permalink.php') . '">' . __('Permalink settings', 'woocommerce-multilingual') . '</a>';
        $message .= ' | ';
        $message .= '<a href="' . admin_url('admin.php?page=' . WPML_TM_FOLDER . '/menu/main.php&sm=mcsetup#icl_custom_posts_sync_options') . '">' . __('Configure products slug translation', 'woocommerce-multilingual') . '</a>';
        

        // Check if translated shop pages have the same slug (only 1.x)
        $allsame = true;        
        if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
        } else {
            $shop_page_id = get_option('woocommerce_shop_page_id', false);
            if (!empty($shop_page_id)) {              
                $slug = @get_post($shop_page_id)->post_name;
                $languages = $sitepress->get_active_languages();
                if (sizeof($languages) < 2) {                  
                    return;
                }
                foreach ($languages as $language) {                    
                    if ($language['code'] != $sitepress->get_default_language()) {
                        $translated_shop_page_id = apply_filters( 'translate_object_id',$shop_page_id, 'page', false, $language['code']);
                        if (!empty($translated_shop_page_id)) {                            
                            $translated_slug = get_post($translated_shop_page_id)->post_name;
                            if (!empty($translated_slug) && $translated_slug != $slug) {                                
                                $allsame = false;                                
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Check if slug translation is enabled
        $compatible = true;
        $permalink_structure = get_option('permalink_structure');        
        if ( empty($permalink_structure)
            && !empty($sitepress_settings['posts_slug_translation']['on'])
            && !empty($sitepress_settings['posts_slug_translation']['types'])
            && $sitepress_settings['posts_slug_translation']['types']['product']) {
            $compatible = false;
        }
        
        // display messages
        if (!$allsame) {
            $this->err_message = '<div class="message error"><p>'.printf(__('If you want different slugs for shop pages (%s/%s), you need to disable the shop prefix for products in <a href="%s">WooCommerce Settings</a>', 'woocommerce-multilingual'),
                $slug, $translated_slug, admin_url("admin.php?page=woocommerce_settings&tab=pages")).'</p></div>';
            add_action('admin_notices', array($this,'plugin_notice_message'));
        }

        if (!$compatible && ($pagenow == 'options-permalink.php' || (isset($_GET['page']) && $_GET['page'] == 'wpml-wcml'))) {
            $this->err_message = '<div class="message error"><p>'.$message.'    </p></div>';
            add_action('admin_notices', array($this,'plugin_notice_message'));
        }
    }

    public function plugin_notice_message(){
        echo $this->err_message;
    }
    
    public function fix_strings_language(){
        $nonce = filter_input( INPUT_POST, 'wcml_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if(!$nonce || !wp_verify_nonce($nonce, 'wcml_fix_strings_language')){
            die('Invalid nonce');
        }

        $ret = array();
            
        $ret['_wpnonce'] = wp_create_nonce('icl_sw_form');

        $ret['success_1'] = '&nbsp;' . sprintf(__('Finished! You can visit the %sstrings translation%s screen to translate the strings now.', 'woocommerce-multilingual'), '<a href="' . admin_url('admin.php?page=' . WPML_ST_FOLDER . '/menu/string-translation.php') . '">', '</a>');

        
        echo json_encode($ret);
        
        exit;
        
    }
    
    public function check_wpml_config(){
        global $sitepress_settings;
        
        if(empty($sitepress_settings)) return;
        
        $file = realpath(WCML_PLUGIN_PATH  . '/wpml-config.xml');
        if(!file_exists($file)){
            $this->xml_config_errors[] = __('wpml-config.xml file missing from WooCommerce Multilingual folder.', 'woocommerce-multilingual');
        }else{
            $config = icl_xml2array(file_get_contents($file));    
            
            if(isset($config['wpml-config'])){

                //custom-fields
                if(isset($config['wpml-config']['custom-fields'])){
                    if(isset($config['wpml-config']['custom-fields']['custom-field']['value'])){ //single
                        $cfs[] = $config['wpml-config']['custom-fields']['custom-field'];
                    }else{
                        foreach($config['wpml-config']['custom-fields']['custom-field'] as $cf){
                            $cfs[] = $cf;
                        }
                    }
                    
                    if($cfs)
                    foreach($cfs as $cf){
                        if(!isset($sitepress_settings['translation-management']['custom_fields_translation'][$cf['value']])) continue; 
                                               
                        $effective_config_value = $sitepress_settings['translation-management']['custom_fields_translation'][$cf['value']];
                        $correct_config_value   = $cf['attr']['action'] == 'copy' ? 1 : ($cf['attr']['action'] == 'translate' ? 2: 0);
                        
                        if($effective_config_value != $correct_config_value){
                            $this->xml_config_errors[] = sprintf(__('Custom field %s configuration from wpml-config.xml file was altered!', 'woocommerce-multilingual'), '<i>' . $cf['value'] . '</i>');
                        }
                    }
                    
                }
                
                //custom-types
                if(isset($config['wpml-config']['custom-types'])){
                    if(isset($config['wpml-config']['custom-types']['custom-type']['value'])){ //single
                        $cts[] = $config['wpml-config']['custom-types']['custom-type'];
                    }else{
                        foreach($config['wpml-config']['custom-types']['custom-type'] as $cf){
                            $cts[] = $cf;
                        }
                    }
                    
                    if($cts)
                    foreach($cts as $ct){
                        if(!isset($sitepress_settings['custom_posts_sync_option'][$ct['value']])) continue;
                        $effective_config_value = $sitepress_settings['custom_posts_sync_option'][$ct['value']];
                        $correct_config_value   = $ct['attr']['translate'];
                        
                        if($effective_config_value != $correct_config_value){
                            $this->xml_config_errors[] = sprintf(__('Custom type %s configuration from wpml-config.xml file was altered!', 'woocommerce-multilingual'), '<i>' . $ct['value'] . '</i>');
                        }
                    }
                    
                }

                //taxonomies
                if(isset($config['wpml-config']['taxonomies'])){
                    if(isset($config['wpml-config']['taxonomies']['taxonomy']['value'])){ //single
                        $txs[] = $config['wpml-config']['taxonomies']['taxonomy'];
                    }else{
                        foreach($config['wpml-config']['taxonomies']['taxonomy'] as $cf){
                            $txs[] = $cf;
                        }
                    }
                    
                    if($txs)
                    foreach($txs as $tx){
                        if(!isset($sitepress_settings['taxonomies_sync_option'][$tx['value']])) continue;
                        $effective_config_value = $sitepress_settings['taxonomies_sync_option'][$tx['value']];
                        $correct_config_value   = $tx['attr']['translate'];
                        
                        if($effective_config_value != $correct_config_value){
                            $this->xml_config_errors[] = sprintf(__('Custom taxonomy %s configuration from wpml-config.xml file was altered!', 'woocommerce-multilingual'), '<i>' . $tx['value'] . '</i>');
                        }
                    }
                    
                }
            }
        }
        
    }

    public function required_plugin_install_link($repository = 'wpml'){

        if( class_exists('WP_Installer_API') ){
            $url = WP_Installer_API::get_product_installer_link($repository);
        }else{
            $url = WCML_Links::generate_tracking_link('https://wpml.org/');
        }

        return $url;
    }

    /**
     * The support for the Twig templates comes from WPML by default
     * When WPML is not active, WCML will load it
     */
    private function load_twig_support(){

        if ( !class_exists( 'Twig_Autoloader' ) ){
            Twig_Autoloader::register();
        }

    }
    
}
  