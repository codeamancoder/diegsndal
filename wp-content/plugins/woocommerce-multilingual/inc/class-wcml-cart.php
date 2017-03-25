<?php
class WCML_Cart
{

    private $woocommerce_wpml;
    private $sitepress;
    private $woocommerce;

    public function __construct( &$woocommerce_wpml, &$sitepress, &$woocommerce )
    {
        $this->woocommerce_wpml = $woocommerce_wpml;
        $this->sitepress = $sitepress;
        $this->woocommerce = $woocommerce;

        if( $this->woocommerce_wpml->settings[ 'cart_sync' ][ 'currency_switch' ] == WCML_CART_CLEAR || $this->woocommerce_wpml->settings[ 'cart_sync' ][ 'lang_switch' ] == WCML_CART_CLEAR ){
            $this->enqueue_dialog_ui();

            add_action( 'wcml_removed_cart_items', array( $this, 'wcml_removed_cart_items_widget' ) );
            add_action( 'wp_ajax_wcml_cart_clear_removed_items', array( $this, 'wcml_cart_clear_removed_items' ) );
            add_action( 'wp_ajax_nopriv_wcml_cart_clear_removed_items', array( $this, 'wcml_cart_clear_removed_items' ) );

            add_filter( 'wcml_switch_currency_exception', array( $this, 'cart_switching_currency' ), 10, 3 );
            add_action( 'wcml_before_switch_currency', array( $this, 'switching_currency_empty_cart_if_needed' ), 10, 2 );
        }
        else{
            //cart widget
            add_action( 'wp_ajax_woocommerce_get_refreshed_fragments', array( $this, 'wcml_refresh_fragments' ), 0 );
            add_action( 'wp_ajax_woocommerce_add_to_cart', array( $this, 'wcml_refresh_fragments' ), 0 );
            add_action( 'wp_ajax_nopriv_woocommerce_get_refreshed_fragments', array( $this, 'wcml_refresh_fragments' ), 0 );
            add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart', array( $this, 'wcml_refresh_fragments' ), 0 );

            //cart
            add_action( 'woocommerce_before_calculate_totals', array( $this, 'woocommerce_calculate_totals' ), 100 );
            add_action( 'woocommerce_get_cart_item_from_session', array( $this, 'translate_cart_contents' ), 10 );
            add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'translate_cart_subtotal' ) );
            add_action( 'woocommerce_before_checkout_process', array( $this, 'wcml_refresh_cart_total' ) );

            add_filter('woocommerce_paypal_args', array($this, 'filter_paypal_args'));
            add_filter( 'woocommerce_add_to_cart_sold_individually_quantity', array( $this, 'woocommerce_add_to_cart_sold_individually_quantity' ), 10, 5 );

            $this->localize_flat_rates_shipping_classes();
        }

    }

    public function enqueue_dialog_ui(){

        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

    }

    public function wcml_removed_cart_items_widget( $args = array() ){

        if( !empty( $this->woocommerce->session ) ){
            $removed_cart_items = new WCML_Removed_Cart_Items_UI( $args, $this->woocommerce_wpml, $this->sitepress, $this->woocommerce );
            $preview = $removed_cart_items->get_view();

            if ( !isset($args['echo']) || $args['echo'] ) {
                echo $preview;
            } else {
                return $preview;
            }
        }

    }

    public function switching_currency_empty_cart_if_needed( $currency, $force_switch ){
        if( $force_switch && $this->woocommerce_wpml->settings[ 'cart_sync' ][ 'currency_switch' ] == WCML_CART_CLEAR ) {
            $this->empty_cart_if_needed('currency_switch');
            $this->woocommerce->session->set('wcml_switched_type', 'currency');
        }
    }

    public function empty_cart_if_needed( $switching_type ){

            if( $this->woocommerce_wpml->settings[ 'cart_sync' ][ $switching_type ] == WCML_CART_CLEAR ){
                $removed_products = $this->woocommerce->session->get( 'wcml_removed_items' ) ? maybe_unserialize( $this->woocommerce->session->get( 'wcml_removed_items' ) ) : array();

                foreach( WC()->cart->get_cart_for_session() as $item_key => $cart ){
                    if( !in_array( $cart[ 'product_id' ], $removed_products ) ){
                        $removed_products[] = $cart[ 'product_id' ];
                    }
                    WC()->cart->remove_cart_item( $item_key );
                }

                if( !empty( $this->woocommerce->session ) ){
                    $this->woocommerce->session->set( 'wcml_removed_items', serialize( $removed_products ) );
                }
            }
        }

    public function wcml_cart_clear_removed_items( ){

        $nonce = filter_input( INPUT_POST, 'wcml_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if(!$nonce || !wp_verify_nonce($nonce, 'wcml_clear_removed_items')){
            die('Invalid nonce');
        }

        $this->woocommerce->session->__unset( 'wcml_removed_items' );
        $this->woocommerce->session->__unset( 'wcml_switched_type' );
    }

    public function cart_switching_currency( $exc, $current_currency, $new_currency, $return = false ){

        $cart_for_session = WC()->cart->get_cart_for_session();
        if( $this->woocommerce_wpml->settings[ 'cart_sync' ][ 'currency_switch' ] == WCML_CART_SYNC || empty( $cart_for_session ) ){
            return $exc;
        }

        $dialog_title = __( 'Switching currency?', 'woocommerce-multilingual');
        $confirmation_message = __( 'Your cart is not empty! After you switched the currency, all items from the cart will be removed and you have to add them again.', 'woocommerce-multilingual');
        $stay_in = sprintf( __( 'Keep using %s', 'woocommerce-multilingual'), $current_currency );
        $switch_to = __( 'Proceed', 'woocommerce-multilingual');

        ob_start();
        $this->cart_alert( $dialog_title, $confirmation_message, $switch_to, $stay_in, $new_currency, $current_currency );
        $html = ob_get_contents();
        ob_end_clean();

        if( $return ){
            return array( 'prevent_switching' => $html );
        }else{
            echo json_encode( array( 'prevent_switching' => $html ) );
        }

        return true;
    }

    public function cart_alert( $dialog_title, $confirmation_message, $switch_to, $stay_in, $switch_to_value, $stay_in_value = false, $language_switch = false ){
        ?>
        <div id="wcml-cart-dialog-confirm" title="<?php echo $dialog_title ?>">
            <p><?php echo $confirmation_message; ?></p>
        </div>

        <script type="text/javascript">
            jQuery( document).ready( function(){
                jQuery( "#wcml-cart-dialog-confirm" ).dialog({
                    resizable: false,
                    draggable: false,
                    height: "auto",
                    width: 500,
                    modal: true,
                    closeOnEscape: false,
                    dialogClass: "wcml-cart-dialog",
                    open: function(event, ui) {
                        jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                    },
                    buttons: {
                        "<?php echo $switch_to; ?>": function() {
                            jQuery( this ).dialog( "close" );
                            <?php if( $language_switch ): ?>
                                window.location = '<?php echo $switch_to_value; ?>';
                            <?php else: ?>
                                jQuery('.wcml_currency_switcher').parent().find('img').remove();
                                wcml_load_currency( "<?php echo $switch_to_value; ?>", true );
                            <?php endif; ?>

                        },
                        "<?php echo $stay_in; ?>": function() {
                            jQuery( this ).dialog( "close" );
                            <?php if( $language_switch ): ?>
                                window.location = '<?php echo $stay_in_value; ?>';
                            <?php else: ?>
                                jQuery('.wcml_currency_switcher').parent().find('img').remove();
                                jQuery('.wcml_currency_switcher').removeAttr('disabled');
                                jQuery('.wcml_currency_switcher').val( '<?php echo $stay_in_value; ?>' );
                            <?php endif; ?>
                        }
                    }
                });
            });
        </script>
        <?php
    }

    public function wcml_refresh_fragments(){
        WC()->cart->calculate_totals();
        $this->woocommerce_wpml->locale->wcml_refresh_text_domain();
    }

    /*
     *  Update cart and cart session when switch language
     */
    public function woocommerce_calculate_totals( $cart, $currency = false ){

        $current_language = $this->sitepress->get_current_language();
        $new_cart_data = array();

        foreach( $cart->cart_contents as $key => $cart_item ){
            $tr_product_id = apply_filters( 'translate_object_id', $cart_item[ 'product_id' ], 'product', false, $current_language );
            //translate custom attr labels in cart object

            if( version_compare( WC_VERSION , '2.7', '<' ) && isset( $cart_item[ 'data' ]->product_attributes ) ){
                foreach( $cart_item[ 'data' ]->product_attributes as $attr_key => $product_attribute ){
                    if( isset( $product_attribute[ 'is_taxonomy' ]) && !$product_attribute[ 'is_taxonomy' ] ){
                        $cart->cart_contents[ $key ][ 'data' ]->product_attributes[ $attr_key ][ 'name' ] = $this->woocommerce_wpml->strings->translated_attribute_label(
                                                                                                                $product_attribute[ 'name' ],
                                                                                                                $product_attribute[ 'name' ],
                                                                                                                $tr_product_id );
                    }
                }
            }

            //translate custom attr value in cart object
            if( isset( $cart_item[ 'variation' ] ) && is_array( $cart_item[ 'variation' ] ) ){
                foreach( $cart_item[ 'variation' ] as $attr_key => $attribute ){
                    $cart->cart_contents[ $key ][ 'variation' ][ $attr_key ] = $this->get_cart_attribute_translation(
                                                                                    $attr_key,
                                                                                    $attribute,
                                                                                    $cart_item[ 'variation_id' ],
                                                                                    $current_language,
                                                                                    $cart_item[ 'product_id' ],
                                                                                    $tr_product_id
                                                                                );
                }
            }

            if( $currency !== false ){
                $cart->cart_contents[ $key ][ 'data' ]->price = get_post_meta( $cart_item['product_id'], '_price', 1 );
            }

            if( $cart_item[ 'product_id' ] == $tr_product_id ){
                $new_cart_data[ $key ] = apply_filters( 'wcml_cart_contents_not_changed', $cart->cart_contents[$key], $key, $current_language );
                continue;
            }

            if( isset( $cart->cart_contents[ $key ][ 'variation_id' ] ) && $cart->cart_contents[ $key ][ 'variation_id' ] ){
                $tr_variation_id = apply_filters( 'translate_object_id', $cart_item[ 'variation_id' ], 'product_variation', false, $current_language );
                if( !is_null( $tr_variation_id ) ){
                    $cart->cart_contents[ $key ][ 'product_id' ] = intval( $tr_product_id );
                    $cart->cart_contents[ $key ][ 'variation_id' ] = intval( $tr_variation_id );
                    $cart->cart_contents[ $key ][ 'data' ]->id = intval( $tr_product_id );
                    $cart->cart_contents[ $key ][ 'data' ]->post = get_post( $tr_product_id );
                }
            }else{
                if( !is_null( $tr_product_id ) ){
                    $cart->cart_contents[ $key ][ 'product_id' ] = intval( $tr_product_id );
                    $cart->cart_contents[ $key ][ 'data' ]->id = intval( $tr_product_id );
                    $cart->cart_contents[ $key ][ 'data' ]->post = get_post( $tr_product_id );
                }
            }

            if( !is_null( $tr_product_id ) ){

                $new_key = $this->wcml_generate_cart_key( $cart->cart_contents, $key );
                $cart->cart_contents = apply_filters( 'wcml_update_cart_contents_lang_switch', $cart->cart_contents, $key, $new_key, $current_language );
                $new_cart_data[ $new_key ] = $cart->cart_contents[ $key ];

                $new_cart_data = apply_filters( 'wcml_cart_contents', $new_cart_data, $cart->cart_contents, $key, $new_key );
           }
        }

        $cart->cart_contents = $this->wcml_check_on_duplicate_products_in_cart( $new_cart_data );
        $this->woocommerce->session->cart = $cart->cart_contents;
        return $cart->cart_contents;
    }

    public function wcml_check_on_duplicate_products_in_cart( $cart_contents ){

        $exists_products = array();
        remove_action( 'woocommerce_before_calculate_totals', array( $this, 'woocommerce_calculate_totals' ), 100 );

        foreach( $cart_contents as $key => $cart_content ){
            $cart_contents = apply_filters( 'wcml_check_on_duplicated_products_in_cart', $cart_contents, $key, $cart_content );
            if( apply_filters( 'wcml_exception_duplicate_products_in_cart', false, $cart_content ) ){
                continue;
            }

            $quantity = $cart_content['quantity'];

            $search_key = $this->wcml_generate_cart_key( $cart_contents, $key );
            if( array_key_exists( $search_key, $exists_products ) ){
                unset( $cart_contents[ $key ] );
                $cart_contents[ $exists_products[ $search_key ] ][ 'quantity' ] = $cart_contents[ $exists_products[ $search_key ] ][ 'quantity' ] + $quantity;
                $this->woocommerce->cart->calculate_totals();
            }else{
                $exists_products[ $search_key ] = $key;
            }
        }

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'woocommerce_calculate_totals' ), 100 );
        return $cart_contents;
    }

    public function get_cart_attribute_translation( $attr_key, $attribute, $variation_id, $current_language, $product_id, $tr_product_id ){

        $attr_translation = $attribute;

        if( !empty( $attribute ) ){
            //delete 'attribute_' at the beginning
            $taxonomy = substr( $attr_key, 10, strlen( $attr_key ) - 1 );

            if( taxonomy_exists( $taxonomy ) ){
                if( $this->woocommerce_wpml->attributes->is_translatable_attribute( $taxonomy ) ) {
                    $term_id = $this->woocommerce_wpml->terms->wcml_get_term_id_by_slug( $taxonomy, $attribute );
                    $trnsl_term_id = apply_filters( 'translate_object_id', $term_id, $taxonomy, true, $current_language );
                    $term = $this->woocommerce_wpml->terms->wcml_get_term_by_id( $trnsl_term_id, $taxonomy );
                    $attr_translation = $term->slug;
                }
            }else{

                $trnsl_attr = get_post_meta( $variation_id, $attr_key, true );

                if( $trnsl_attr ){
                    $attr_translation = $trnsl_attr;
                }else{
                    $attr_translation = $this->woocommerce_wpml->attributes->get_custom_attr_translation( $product_id, $tr_product_id, $taxonomy, $attribute );
                }
            }
        }

        return $attr_translation;
    }

    public function wcml_generate_cart_key( $cart_contents, $key ){
        $cart_item_data = $this->get_cart_item_data_from_cart( $cart_contents[ $key ] );

        return $this->woocommerce->cart->generate_cart_id(
            $cart_contents[ $key ][ 'product_id' ],
            $cart_contents[ $key ][ 'variation_id' ],
            $cart_contents[ $key ][ 'variation' ],
            $cart_item_data
        );
    }

    //get cart_item_data from existing cart array ( from session )
    public function get_cart_item_data_from_cart( $cart_contents ){
        unset( $cart_contents[ 'product_id' ] );
        unset( $cart_contents[ 'variation_id' ] );
        unset( $cart_contents[ 'variation' ] );
        unset( $cart_contents[ 'quantity' ] );
        unset( $cart_contents[ 'line_total' ] );
        unset( $cart_contents[ 'line_subtotal' ] );
        unset( $cart_contents[ 'line_tax' ] );
        unset( $cart_contents[ 'line_subtotal_tax' ] );
        unset( $cart_contents[ 'line_tax_data' ] );
        unset( $cart_contents[ 'data' ] );

        return apply_filters( 'wcml_filter_cart_item_data', $cart_contents );
    }

    public function translate_cart_contents( $item ) {

        // translate the product id and product data
        $item[ 'product_id' ] = apply_filters( 'translate_object_id', $item[ 'product_id' ], 'product', true );
        if ($item[ 'variation_id' ]) {
            $item[ 'variation_id' ] = apply_filters( 'translate_object_id',$item[ 'variation_id' ], 'product_variation', true );
        }

        if( version_compare( WC()->version, '2.7', '>=' ) ){
        $item[ 'data' ]->set_name( get_the_title( $item[ 'product_id' ] ) );
        } else {
	        $item[ 'data' ]->post->post_title = get_the_title( $item[ 'product_id' ] );
        }

        return $item;
    }

    public function translate_cart_subtotal( $cart ) {

        if( isset( $_SERVER['REQUEST_URI'] ) ){
            //special case: check if attachment loading
            $attachments = array( 'png', 'jpg', 'jpeg', 'gif', 'js', 'css' );

            foreach( $attachments as $attachment ){
                $match = preg_match( '/\.'.$attachment.'$/',  $_SERVER['REQUEST_URI'] );
                if( !empty( $match ) ){
                    return false;
                }
            }
        }

        if( apply_filters( 'wcml_calculate_totals_exception', true, $cart ) ){
            $cart->calculate_totals();
        }

    }

    // refresh cart total to return correct price from WC object
    public function wcml_refresh_cart_total() {
        WC()->cart->calculate_totals();
    }


    public function localize_flat_rates_shipping_classes(){

        if(is_ajax() && isset($_POST['action']) && $_POST['action'] == 'woocommerce_update_order_review'){
            $this->woocommerce->shipping->load_shipping_methods();
            $shipping_methods = $this->woocommerce->shipping->get_shipping_methods();
            foreach($shipping_methods as $method){
                if(isset($method->flat_rate_option)){
                    add_filter('option_' . $method->flat_rate_option, array($this, 'translate_shipping_class'));
                }
            }

        }
    }

    public function translate_shipping_class($rates){

        if(is_array($rates)){
            foreach($rates as $shipping_class => $value){
                $term_id = $this->woocommerce_wpml->terms->wcml_get_term_id_by_slug('product_shipping_class', $shipping_class );

                if($term_id && !is_wp_error($term_id)){
                    $translated_term_id = apply_filters( 'translate_object_id', $term_id, 'product_shipping_class', true);
                    if($translated_term_id != $term_id){
                        $term = $this->woocommerce_wpml->terms->wcml_get_term_by_id( $translated_term_id, 'product_shipping_class' );
                        unset($rates[$shipping_class]);
                        $rates[$term->slug] = $value;

                    }
                }
            }
        }
        return $rates;
    }

    public function filter_paypal_args( $args ) {
        $args['lc'] = $this->sitepress->get_current_language();

        //filter URL when default permalinks uses
        $wpml_settings = $this->sitepress->get_settings();
        if( $wpml_settings[ 'language_negotiation_type' ] == 3 ){
            $args[ 'notify_url' ] = str_replace( '%2F&', '&', $args[ 'notify_url' ] );
        }

        return $args;
    }

    public function woocommerce_add_to_cart_sold_individually_quantity( $qt, $quantity, $product_id, $variation_id, $cart_item_data ){

        //check if product already added to cart in another language
        $current_product_trid = $this->sitepress->get_element_trid( $product_id, 'post_product' );

        foreach( WC()->cart->cart_contents as $cart_item ){
            $cart_element_trid = $this->sitepress->get_element_trid( $cart_item[ 'product_id' ], 'post_product' );
            if( apply_filters( 'wcml_add_to_cart_sold_individually', true, $cart_item_data, $product_id, $quantity ) && $current_product_trid == $cart_element_trid && $cart_item[ 'quantity' ] > 0 ){
                throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', esc_url( wc_get_cart_url() ), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), get_the_title( $product_id ) ) ) );
            }
        }

        return $qt;
    }
}