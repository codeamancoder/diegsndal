<?php
/*
Plugin Name: WooCommerce Quickview
Plugin URI: http://www.jckemp.com
Description: Quickview plugin for WooCommerce
Version: 3.2.5
Author: James Kemp
Author Email: support@jckemp.com
*/

defined('JCK_WOO_QUICKVIEW_PLUGIN_PATH') or define('JCK_WOO_QUICKVIEW_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
defined('JCK_WOO_QUICKVIEW_PLUGIN_URL') or define('JCK_WOO_QUICKVIEW_PLUGIN_URL', plugin_dir_url( __FILE__ ));

class jckqv {

    /**
     * @var str Plugin name
     */
    public $name = 'WooCommerce Quickview';

    /**
     * @var str Plugin shortname
     */
    public $shortname = 'Quickview';

    /**
     * @var str Plugin slug
     */
    public $slug = 'jckqv';

    /**
     * @var str
     */
    public $version = "3.2.5";

    /**
     * @var str Absolute path to this plugin folder, trailing slash
     */
    public $plugin_path;

    /**
     * @var str URL to this plugin folder, no trailing slash
     */
    public $plugin_url;

    /**
     * @var JckSettingsFramework
     */
    public $settings_framework;

    /**
     * @var arr Settings array
     */
    public $settings;

    /**
     * @var str WooCommerce version number
     */
    public $woo_version;

    /**
     * @var JCK_Woo_Quickview_Template_Loader
     */
    public $templates;

    /**
     * Construct
     */
    public function __construct() {

        $this->plugin_path = JCK_WOO_QUICKVIEW_PLUGIN_PATH;
        $this->plugin_url = JCK_WOO_QUICKVIEW_PLUGIN_URL;

        $this->load_classes();

        $this->woo_version = $this->get_woo_version_number();
        $this->settings = $this->settings_framework->__getSettings();

        // Hook up to the init action
        add_action( 'init', array( $this, 'before_initiate' ), 0 );
        add_action( 'init', array( $this, 'initiate' ) );

    }


    /**
     * Load Classes
     */
    private function load_classes() {

        require_once $this->plugin_path.'/inc/class-template-loader.php';
        require_once $this->plugin_path .'/assets/options/jck-settings-framework/jck-settings-framework.php';

        $this->templates = new JCK_Woo_Quickview_Template_Loader();
        $this->settings_framework = new JckSettingsFramework( $this->plugin_path .'/assets/options/jckqv_settings.php', 'woocommerce_page_'.$this->slug );

    }


    /**
     * Runs just before the normal "init" method
     */
    public function before_initiate() {

        add_filter( 'wcml_multi_currency_is_ajax', array($this, 'add_ajax_action') );

    }


    /**
     * Runs on "init" hook
     */
    public function initiate() {

        // Setup localization
        load_plugin_textdomain( 'jckqv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if ( is_admin() ) {

            add_filter( 'jckqvsettings_settings_validate',  array( $this, 'sanitize_settings' ), 10, 1 );
            add_action( 'admin_menu',                       array( $this, 'admin_page' ) );

            // Ajax
            add_action( 'wp_ajax_jckqv', array( $this, 'modal' ) );
            add_action( 'wp_ajax_nopriv_jckqv', array( $this, 'modal' ) );
            add_action( 'wp_ajax_jckqv_add_to_cart', array( $this, 'add_to_cart' ) );
            add_action( 'wp_ajax_nopriv_jckqv_add_to_cart', array( $this, 'add_to_cart' ) );

            // Setup Modal (Ajax)
            add_action( 'jck_qv_summary', array( $this, 'modal_part_sale_flash' ), 5, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_title' ), 10, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_rating' ), 15, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_price' ), 20, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_desc' ), 25, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_add_to_cart' ), 30, 3 );
            add_action( 'jck_qv_summary', array( $this, 'modal_part_meta' ), 35, 3 );

            add_action( 'jck_qv_images', array( $this, 'modal_part_styles' ), 5, 3 );
            add_action( 'jck_qv_images', array( $this, 'modal_part_images' ), 10, 3 );

            add_action( 'jck_qv_after_summary', array( $this, 'modal_part_close' ), 5, 3 );
            add_action( 'jck_qv_after_summary', array( $this, 'modal_part_adding_to_cart' ), 10, 3 );

        } else {

            $this->register_scripts_and_styles();

            // Show Button
            if ($this->settings['trigger_position_autoinsert'] == 1) {

                if ($this->settings['trigger_position_position'] == 'beforeitem') {
                    add_action( 'woocommerce_before_shop_loop_item', array( $this, 'display_button' ) );
                } elseif ($this->settings['trigger_position_position'] == 'beforetitle') {
                    add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_button' ) );
                } elseif ($this->settings['trigger_position_position'] == 'aftertitle') {
                    add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'display_button' ) );
                } elseif ($this->settings['trigger_position_position'] == 'afteritem') {
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_button' ) );
                }

            }

        }

        $this->setup_shop_the_look();

    }

    /**
     * Setup Shop the look
     */
    public function setup_shop_the_look() {

        global $jck_shop_the_look;

        if( isset( $jck_shop_the_look ) && $jck_shop_the_look ) {

            add_action( 'jck_qv_summary', array( $jck_shop_the_look, 'shop_the_look_display' ), 30 );

        }

    }

    /**
     * Frontend: Modal Part: Close Button
     */
    public function modal_part_close() {

        echo '<button title="Close (Esc)" type="button" class="mfp-close">×</button>';

    }

    /**
     * Frontend: Modal Part: Adding to Cart Icon
     */
    public function modal_part_adding_to_cart() {

        echo '<div id="addingToCart"><div><i class="jckqv-icon-cw animate-spin"></i> <span>'.__('Adding to Cart...', 'jckqv').'</span></div></div>';

    }

    /**
     * Frontend: Modal Part: Styles
     */
    public function modal_part_styles() {

        include $this->templates->locate_template( 'styles.php' );

    }

    /**
     * Frontend: Modal Part: Images
     */
    public function modal_part_images() {

        include $this->templates->locate_template( 'images.php' );

    }

    /**
     * Frontend: Modal Part: Sale Flash
     */
    public function modal_part_sale_flash() {

        if ($this->settings['popup_content_showbanner'])
            include( $this->templates->locate_template( 'sale-flash.php' ) );

    }

    /**
     * Frontend: Modal Part: Title
     */
    public function modal_part_title() {

        if ($this->settings['popup_content_showtitle'])
            include( $this->templates->locate_template( 'title.php' ) );

    }

    /**
     * Frontend: Modal Part: Rating
     */
    public function modal_part_rating() {

        if ($this->settings['popup_content_showrating'])
            include( $this->templates->locate_template( 'rating.php' ) );

    }

    /**
     * Frontend: Modal Part: Price
     */
    public function modal_part_price() {

        if ($this->settings['popup_content_showprice'])
            include( $this->templates->locate_template( 'price.php' ) );

    }

    /**
     * Frontend: Modal Part: Description
     */
    public function modal_part_desc() {

        if ($this->settings['popup_content_showdesc'])
            include( $this->templates->locate_template( 'desc.php' ) );

    }

    /**
     * Frontend: Modal Part: Add to Cart
     */
    public function modal_part_add_to_cart() {

        if ($this->settings['popup_content_showatc'])
            include( $this->templates->locate_template( $this->get_add_to_cart_filename() ) );

    }

    /**
     * Frontend: Modal Part: Meta
     */
    public function modal_part_meta() {

        if ($this->settings['popup_content_showmeta'])
            include( $this->templates->locate_template( 'meta.php' ) );

    }


    /**
     * Add Ajax Action
     *
     * Adds 'jckqv' and 'jckqv_add_to_cart' actions in 'wcml_multi_currency_is_ajax'
     * to apply multi-currency filters (to convert prices to current currency)
     *
     * @param array $actions
     * @return array
     */
    public function add_ajax_action($actions) {

        $actions[] = 'jckqv';
        $actions[] = 'jckqv_add_to_cart';

        return $actions;

    }


    /**
     * Admin: Sanitize settings on save
     */
    public function sanitize_settings($settings) {

        // Validate Margins
        $i = 0; foreach ($settings['trigger_position_margins'] as $marVal) {
            $settings['trigger_position_margins'][$i] = ($marVal != "") ? preg_replace('/[^\d-]+/', '', $marVal) : 0;
            $i++; }

        return $settings;

    }


    /**
     * Admin: Add menu page
     */
    public function admin_page() {
        add_submenu_page( 'woocommerce', $this->name, $this->shortname, 'manage_options', $this->slug, array( $this, 'admin_page_options' ) );
    }


    /**
     * Admin: Display menu page
     */
    public function admin_page_options() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'jckqv' ) );
        }

        settings_errors();

        echo '<div class="wrap">';
        $this->settings_framework->displaySettings();
        echo '</div>';
    }


    /**
     * Frontend: Diplay quickview button
     */
    public function display_button($product_id = false) {

        global $post;
        $product_id = ($product_id) ? $product_id : $post->ID;

        $parent_id = wp_get_post_parent_id( $product_id );
        $product_id = ( $parent_id ) ? sprintf('%d:%d', $parent_id, $product_id) : $product_id;

        if ($product_id) {
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'medium' );
            echo '<span data-jckqvpid="'.$product_id.'" class="'.$this->slug.'Btn">'.($this->settings['trigger_styling_icon'] != 'none' ? '<i class="jckqv-icon-'.$this->settings['trigger_styling_icon'].'"></i>' : '').' '.$this->settings['trigger_styling_text'].'</span>';
        }

    }


    /**
     * Frontend: The Modal Request
     *
     * Triggered via AJAX, this is what gets displayed when the
     * quickview button is clicked!
     */
    public function modal() {

        global $post, $product, $woocommerce;

        $pid = $this->get_product_id();
        $post = get_post( $pid ); setup_postdata($post);
        $product = wc_get_product( $pid );

        echo '<div id="'.$this->slug.'" class="cf product-type-'.$product->product_type.'">';

            do_action('jck_qv_images', $pid, $post, $product);

            echo '<div id="'.$this->slug.'_summary">';

                do_action('jck_qv_summary', $pid, $post, $product);

            echo '</div>';

            do_action('jck_qv_after_summary', $pid, $post, $product);

        echo '</div>';

        wp_reset_postdata();

        die;

    }

    /**
     * Helper: Get product ID
     *
     * @return int
     */
    public function get_product_id() {

        if( !isset( $_REQUEST['pid'] ) )
            return false;

        $product_id = explode(':', $_REQUEST['pid']);

        if ( $product_id && isset( $product_id[1] ) )
            $this->get_selected_variation_request( $product_id[1] );

        return $product_id[0];

    }


    /**
     * Helper: Get Selected Variation Request
     *
     * @param int $variation_id
     */
    public function get_selected_variation_request( $variation_id ) {

        $variation = wc_get_product( absint( $variation_id ), array( 'product_type' => 'variable' ) );

        if ( $variation ) {

            $attributes = $variation->get_variation_attributes();

            if ( $attributes && !empty( $attributes ) ) {

                foreach ( $attributes as $name => $value ) {

                    $_REQUEST[ $name ] = $value;

                }

            }

        }

    }


    /**
     * Ajax: Add to cart
     */
    public function add_to_cart() {

        global $woocommerce;

        $varId = (isset($_GET['variation_id'])) ? $_GET['variation_id'] : '';
        $_GET['quantity'] = (isset($_GET['quantity'])) ? $_GET['quantity'] : 1;

        $variations = array();

        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 10) == "attribute_") {
                $variations[$key] = $value;
            }
        }

        if ( is_array( $_GET['quantity'] ) ) {

            foreach ($_GET['quantity'] as $product_id => $prodQty) {
                if ($prodQty > 0) {
                    $atc = $woocommerce->cart->add_to_cart($product_id, $prodQty, $varId, $variations);
                    if ($atc) { continue; } else { break; }
                }
            }
        }
        else {
            $atc = $woocommerce->cart->add_to_cart($_GET['product_id'], $_GET['quantity'], $varId, $variations);
        }

        if ($atc) {
            $woocommerce->cart->maybe_set_cart_cookies();
            $wc_ajax = new WC_AJAX();
            $wc_ajax->get_refreshed_fragments();
        }
        else {
            header('Content-Type: application/json');

            $soldIndv = get_post_meta($_GET['product_id'], '_sold_individually', true);

            if ($soldIndv == "yes") {
                $response = array('result' => 'individual');
                $response['message'] = __('Sorry, that item can only be added once.', 'jckqv');
            }
            else {
                $response = array('result' => 'fail');
                $response['message'] = __('Sorry, something went wrong. Please try again.', 'jckqv');
            }

            $response['get'] = $_GET;

            echo json_encode($response);
        }

        die;
    }


    /**
     * Frontend: Register scripts and styles
     */
    public function register_scripts_and_styles() {

        if ( !is_admin() ) {

            wp_enqueue_script( 'jquery-ui-spinner' );

            $this->load_file( $this->slug . '-script', '/assets/frontend/js/main.min.js', true );
            $this->load_file( $this->slug . '-minstyles', '/assets/frontend/css/main.css' );

            $imgsizes = array();
            $imgsizes['catalog'] = get_option( 'shop_catalog_image_size' );
            $imgsizes['single'] = get_option( 'shop_single_image_size' );
            $imgsizes['thumbnail'] = get_option( 'shop_thumbnail_image_size' );

            $scriptVars = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( "jckqv" ),
                'settings' => $this->settings,
                'imgsizes' => $imgsizes,
                'url' => get_bloginfo('url'),
                'text' => array(
                    'added' => __('Added!', 'jckqv'),
                    'adding' => __('Adding to Cart...', 'jckqv'),
                    'loading' => __('Loading...', 'jckqv'),
                )
            );

            wp_localize_script( $this->slug . '-script', $this->slug, $scriptVars );

            add_action( 'wp_head', array( $this, 'dynamic_css' ) );

        }

    }


    /**
     * Frontend: Add dynamic CSS
     *
     * This is CSS that uses values from the settings
     */
    public function dynamic_css() {

        include $this->templates->locate_template( 'button-styles.php' );

    }


    /**
     * Helper: Register and enqueue scripts and styles
     *
     * @param str $name The ID to register with WordPress
     * @param str $file_path
     * @param bool $is_script
     * @param arr $deps
     * @param bool $inFooter
     */
    private function load_file( $name, $file_path, $is_script = false, $deps = array('jquery'), $inFooter = true ) {

        $url = plugins_url($file_path, __FILE__);
        $file = plugin_dir_path(__FILE__) . $file_path;

        if ( file_exists( $file ) ) {
            if ( $is_script ) {
                wp_register_script( $name, $url, $deps, false, $inFooter ); //depends on jquery
                wp_enqueue_script( $name );
            } else {
                wp_register_style( $name, $url );
                wp_enqueue_style( $name );
            }
        }

    }


    /**
     * Helper: Get WooCommerce Version number
     *
     * @return str
     */
    public function get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) )
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            return $plugin_folder[$plugin_file]['Version'];

        } else {
            // Otherwise return null
            return NULL;
        }
    }


    /**
     * Helper: Get Product images
     *
     * @param obj $product
     * @return arr
     */
    public function get_product_images( $product ) {

        $prod_images = array();

        if ( has_post_thumbnail( $product->ID ) ) {

            $img_id = get_post_thumbnail_id( $product->ID );
            $img_src = wp_get_attachment_image_src($img_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ));
            $img_thumb_src = wp_get_attachment_image_src($img_id, 'thumbnail');

            $prod_images[$img_id]['slideId'][] = '-0-';
            $prod_images[$img_id]['img_src'] = $img_src[0];
            $prod_images[$img_id]['img_width'] = $img_src[1];
            $prod_images[$img_id]['img_height'] = $img_src[2];
            $prod_images[$img_id]['img_thumb_src'] = $img_thumb_src[0];

        } else {

            $prod_images[0]['slideId'][] = '-0-';
            $prod_images[0]['img_src'] = woocommerce_placeholder_img_src();
            $prod_images[0]['img_width'] = 800;
            $prod_images[0]['img_height'] = 800;
            $prod_images[0]['img_thumb_src'] = woocommerce_placeholder_img_src();

        }

        // Additional Images

        $attachment_ids = $product->get_gallery_attachment_ids();
        $attachment_count = count( $attachment_ids );

        if(!empty($attachment_ids)) {
            foreach($attachment_ids as $attachment_id) {

            $img_src = wp_get_attachment_image_src( $attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
            $img_thumb_src = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

            $prod_images[$attachment_id]['slideId'][] = '-0-';
            $prod_images[$attachment_id]['img_src'] = $img_src[0];
            $prod_images[$attachment_id]['img_width'] = $img_src[1];
            $prod_images[$attachment_id]['img_height'] = $img_src[2];
            $prod_images[$attachment_id]['img_thumb_src'] = $img_thumb_src[0];

            }
        }

        // !If is Varibale product

        if ( $product->product_type == 'variable' ) {

            $product_variations = $product->get_available_variations();

            if(!empty($product_variations)) {
                foreach($product_variations as $product_variation) {

                    if( has_post_thumbnail( $product_variation['variation_id'] ) ) {

                        $variation_thumbnail_id = get_post_thumbnail_id($product_variation['variation_id']);
                        $img_src = wp_get_attachment_image_src($variation_thumbnail_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ));
                        $img_thumb_src = wp_get_attachment_image_src($variation_thumbnail_id, 'thumbnail');

                        $prod_images[$variation_thumbnail_id]['slideId'][] = '-'.$product_variation['variation_id'].'-';
                        $prod_images[$variation_thumbnail_id]['img_src'] = $img_src[0];
                        $prod_images[$variation_thumbnail_id]['img_width'] = $img_src[1];
                        $prod_images[$variation_thumbnail_id]['img_height'] = $img_src[2];
                        $prod_images[$variation_thumbnail_id]['img_thumb_src'] = $img_thumb_src[0];

                    }

                }
            }

        }

        return $prod_images;

    }

    /**
     * Helper: Get Add to Cart Filename
     *
     * @return str
     */
    public function get_add_to_cart_filename() {

        if ( version_compare($this->woo_version, '2.1', '<') ) {
            return 'add-to-cart-old-2-1-down.php';
        } elseif( version_compare($this->woo_version, '2.5.0', '<') ) {
            return 'add-to-cart-old-2-5-down.php';
        }

        return 'add-to-cart.php';

    }

}


$jckqv = new jckqv();