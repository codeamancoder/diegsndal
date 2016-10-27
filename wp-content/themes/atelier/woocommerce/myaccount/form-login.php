<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $sf_options;
?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="my-account-login-wrap">

	<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
	
	<div class="u-columns col2-set" id="customer_login">
	
		<div class="u-column1 col-1">
	
	<?php endif; ?>
	
			<div class="login-wrap">
			    <h4 class="lined-heading"><span><?php _e( 'Registered customers', 'swiftframework' ); ?></span></h4>
	
				<form method="post" class="login">
		
					<?php do_action( 'woocommerce_login_form_start' ); ?>
		
					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="username"><?php _e( 'Username or email address', 'swiftframework' ); ?> <span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
					</p>
					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="password"><?php _e( 'Password', 'swiftframework' ); ?> <span class="required">*</span></label>
						<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" />
					</p>
		
					<?php do_action( 'woocommerce_login_form' ); ?>
					
					<p class="form-row">
					    <?php wp_nonce_field( 'woocommerce-login' ); ?>
					    <input type="submit" class="button" name="login"
					           value="<?php _e( 'Login', 'swiftframework' ); ?>"/>
					    <label for="rememberme" class="inline">
					        <input name="rememberme" type="checkbox" id="rememberme"
					               value="forever"/> <?php _e( 'Remember me', 'swiftframework' ); ?>
					    </label>
					</p>
					
					<p class="lost_password">
					    <a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'swiftframework' ); ?></a>
					</p>
		
					<?php do_action( 'woocommerce_login_form_end' ); ?>
		
				</form>
			</div>
			
	<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
	
		</div>
	
		<div class="u-column2 col-2">
	
			<h4 class="lined-heading"><span><?php _e( 'Not registered? No problem', 'swiftframework' ); ?></span></h4>
			
			<div class="new-user-text"><?php echo $sf_options['checkout_new_account_text']; ?></div>
	
			<form method="post" class="register">
	
				<?php do_action( 'woocommerce_register_form_start' ); ?>
	
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
	
					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="reg_username"><?php _e( 'Username', 'swiftframework' ); ?> <span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
					</p>
	
				<?php endif; ?>
	
				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_email"><?php _e( 'Email address', 'swiftframework' ); ?> <span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
				</p>
	
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
	
					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="reg_password"><?php _e( 'Password', 'swiftframework' ); ?> <span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
					</p>
	
				<?php endif; ?>
	
				<!-- Spam Trap -->
				<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'swiftframework' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>
	
				<?php do_action( 'woocommerce_register_form' ); ?>
				<?php do_action( 'register_form' ); ?>
	
				<p class="woocomerce-FormRow form-row form-submit">
					<?php wp_nonce_field( 'woocommerce-register' ); ?>
					<input type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'swiftframework' ); ?>" />
				</p>
	
				<?php do_action( 'woocommerce_register_form_end' ); ?>
	
			</form>
	
		</div>
	
	</div>
	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>