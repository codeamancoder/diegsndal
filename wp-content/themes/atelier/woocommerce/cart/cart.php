<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.8
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.3.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

?>

<?php

	$cart_count = sf_product_items_text(WC()->cart->cart_contents_count);

	do_action( 'woocommerce_before_cart' ); ?>

	<form action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

		<div class="row">

			<div class="col-sm-9 cart-items-wrap">

				<h3 class="bag-summary"><?php _e('Your selection', 'swiftframework');?> <span>(<?php echo esc_attr($cart_count); ?>)</span></h3>

				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<table class="shop_table shop_table_responsive cart" cellspacing="0">
					<thead>
						<tr>
							<th class="product-thumbnail"><?php _e( 'Item', 'swiftframework' ); ?></th>
							<th class="product-name"><?php _e( 'Description', 'swiftframework' ); ?></th>
							<th class="product-price"><?php _e( 'Unit Price', 'swiftframework' ); ?></th>
							<th class="product-quantity"><?php _e( 'Quantity', 'swiftframework' ); ?></th>
							<th class="product-subtotal"><?php _e( 'Subtotal', 'swiftframework' ); ?></th>
							<th class="product-remove">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php do_action( 'woocommerce_before_cart_contents' ); ?>

						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								?>
								<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

									<td class="product-thumbnail">
										<?php
											$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

											if ( ! $_product->is_visible() )
												echo $thumbnail;
											else
												printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
										?>
									</td>

									<td class="product-name">
										<?php
											if ( ! $_product->is_visible() )
												echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';
											else
												echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s </a>', $_product->get_permalink(), $_product->get_title() ), $cart_item, $cart_item_key );

											// Meta data
											echo WC()->cart->get_item_data( $cart_item );

				               				// Backorder notification
				               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
				               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'swiftframework' ) . '</p>';
										?>
									</td>

									<td class="product-price">
										<?php
											echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
										?>
									</td>

									<td class="product-quantity">
										<?php
											if ( $_product->is_sold_individually() ) {
												$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
											} else {
												$product_quantity = woocommerce_quantity_input( array(
													'input_name'  => "cart[{$cart_item_key}][qty]",
													'input_value' => $cart_item['quantity'],
													'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
													'min_value'   => '0'
												), $_product, false );
											}

											echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
										?>
									</td>

									<td class="product-subtotal">
										<?php
											echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
										?>
									</td>

									<td class="product-remove">
										<?php
											echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'swiftframework' ) ), $cart_item_key );
										?>
									</td>
								</tr>
								<?php
							}
						}

						do_action( 'woocommerce_cart_contents' );
						?>
						<tr>
							<td colspan="6" class="actions">

								<?php if ( wc_coupons_enabled() ) { ?>
									<div class="coupon">

										<label for="coupon_code"><?php _e( 'Coupon:', 'swiftframework' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'swiftframework' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply Coupon', 'swiftframework' ); ?>" />

										<?php do_action( 'woocommerce_cart_coupon' ); ?>

									</div>
								<?php } ?>

								<input type="submit" class="button" name="update_cart" value="<?php _e( 'Update Cart', 'swiftframework' ); ?>" />

								<?php do_action( 'woocommerce_cart_actions' ); ?>

								<?php wp_nonce_field( 'woocommerce-cart' ); ?>
							</td>
						</tr>
						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</tbody>
				</table>

				<?php do_action( 'woocommerce_after_cart_table' ); ?>

			</div>

			<div class="col-sm-3 cart-totals-wrap">

				<h3 class="bag-totals"><?php _e('Cart Totals', 'swiftframework');?></h3>

				<?php woocommerce_cart_totals(); ?>

				<a class="continue-shopping accent" href="<?php echo apply_filters( 'woocommerce_continue_shopping_redirect', get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php _e('Continue shopping', 'swiftframework'); ?></a>

			</div>

		</div>

	</form>

	<div class="cart-collaterals"><?php do_action( 'woocommerce_cart_collaterals' ); ?></div>

	<?php do_action( 'woocommerce_after_cart' ); ?>
