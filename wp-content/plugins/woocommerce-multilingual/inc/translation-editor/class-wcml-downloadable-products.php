<?php

class WCML_Downloadable_Products{

    private $woocommerce_wpml;
    private $sitepress;

	/**
	 * WCML_Downloadable_Products constructor.
	 *
	 * @param woocommerce_wpml $woocommerce_wpml
	 * @param SitePress        $sitepress
	 */
	public function __construct( &$woocommerce_wpml, &$sitepress ) {
		$this->woocommerce_wpml = &$woocommerce_wpml;
		$this->sitepress        = &$sitepress;

        //downloadable files custom
        add_action( 'woocommerce_product_options_downloads', array( $this, 'product_options_downloads_custom_option' ) );
        add_action( 'woocommerce_variation_options_download', array( $this, 'product_options_downloads_custom_option' ), 10, 3 );
    }

	/**
	 * @param bool         $loop
	 * @param bool         $variation_data
	 * @param bool|WP_post $variation
	 */
	public function product_options_downloads_custom_option( $loop = false, $variation_data = false, $variation = false ){
        global $pagenow;

        $product_id = false;
        $is_variation = false;
        if( $pagenow === 'post.php' && isset( $_GET['post'] ) ){
            $product_id =  $_GET['post'];
        }elseif( isset( $_POST['product_id'] ) ){
            $product_id = $_POST['product_id'];
        }

		if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' && isset( $_GET['source_lang'] ) )
		     || ( get_post_type( $product_id ) !== 'product' || ! $this->woocommerce_wpml->products->is_original_product( $product_id ) )
		){
            return;
        }

        $this->load_custom_files_js_css();

        if( $variation ) {
            $product_id = $variation->ID;
            $is_variation = true;
        }

        $download_options = new WCML_Custom_Files_UI( $this->woocommerce_wpml, $product_id, $is_variation );
        $download_options->show();

    }

    public function load_custom_files_js_css(){
        wp_register_style( 'wpml-wcml-files', WCML_PLUGIN_URL . '/res/css/wcml-files.css', null, WCML_VERSION );
        wp_register_script( 'wcml-scripts-files', WCML_PLUGIN_URL . '/res/js/files' . WCML_JS_MIN . '.js', array( 'jquery' ), WCML_VERSION );

        wp_enqueue_style('wpml-wcml-files');
        wp_enqueue_script('wcml-scripts-files');
    }

    public function sync_files_to_translations( $original_id, $trnsl_id, $data ){

        $custom_product_sync = get_post_meta( $original_id, 'wcml_sync_files', true );

	    if ( ( $custom_product_sync && $custom_product_sync === 'self' ) || ( ! $custom_product_sync && ! $this->woocommerce_wpml->settings['file_path_sync'] ) ){
            if( $data ){
                $orig_var_files = $this->get_files_data( $original_id );
                $file_paths_array = array();
                foreach( $orig_var_files as $key => $var_file ){
                    $key_file = md5( $data[ md5( 'file-url'.$key.$original_id ) ].$data[ md5( 'file-name'.$key.$original_id ) ] );
                    $file_paths_array[ $key_file ][ 'name' ] = $data[ md5( 'file-name'.$key.$original_id ) ];
                    $file_paths_array[ $key_file ][ 'file' ] = $data[ md5( 'file-url'.$key.$original_id ) ];
                }
                update_post_meta( $trnsl_id,'_downloadable_files',$file_paths_array );
            }
	    } elseif ( ( $custom_product_sync && $custom_product_sync === 'auto' ) || $this->woocommerce_wpml->settings['file_path_sync'] ){
            $orig_file_path = maybe_unserialize( get_post_meta( $original_id, '_downloadable_files', true ) );
            update_post_meta( $trnsl_id, '_downloadable_files', $orig_file_path );
        }
    }

    public function save_files_option( $post_id ){
        $nonce = filter_input( INPUT_POST, 'wcml_save_files_option_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	    if ( isset( $_POST['wcml_file_path_option'], $nonce ) && wp_verify_nonce( $nonce, 'wcml_save_files_option' ) ){
            if( isset( $_POST[ 'wcml_file_path_option' ][ $post_id ] ) ) {
	            if ( $_POST['wcml_file_path_option'][ $post_id ] === 'on' ){
                    update_post_meta( $post_id, 'wcml_sync_files', $_POST[ 'wcml_file_path_sync' ][ $post_id ] );
                }else{
                    delete_post_meta( $post_id, 'wcml_sync_files' );
                }
            }
        }
    }

    public function get_files_data( $product_id ){

        $files = maybe_unserialize( get_post_meta( $product_id, '_downloadable_files', true ) );
	    $files_data = array();
        if ( $files ) {
            foreach ($files as $file) {
                $files_info = array();
                $files_info['value'] = $file['file'];
                $files_info['label'] = $file['name'];
                $files_data[] = $files_info;
            }
        }

        return $files_data;
    }
}