<?php 
/**
 * General Settings Page
 */

// Get plugin global
global $go_pricing;

// Get current user id
$user_id = get_current_user_id();

// Get general settings
$general_settings = get_option( self::$plugin_prefix . '_table_settings' );

?>
<!-- Top Bar -->
<div class="gwa-ptopbar">
	<div class="gwa-ptopbar-icon"></div>
	<div class="gwa-ptopbar-title">Go Pricing</div>
	<div class="gwa-ptopbar-content"><label><span class="gwa-label"><?php _e( 'Help', 'go_pricing_textdomain' ); ?></span><select data-action="help" class="gwa-w80"><option value="1"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 1 ? ' selected="selected"' : ''; ?>><?php _e( 'Tooltip', 'go_pricing_textdomain' ); ?></option><option value="2"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 2 ? ' selected="selected"' : ''; ?>><?php _e( 'Show', 'go_pricing_textdomain' ); ?></option><option value="0"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 0 ? ' selected="selected"' : ''; ?>><?php _e( 'None', 'go_pricing_textdomain' ); ?></option></select></label><a href="#" data-action="submit" title="<?php esc_attr_e( 'Save', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style1 gwa-ml20"><?php _e( 'Save', 'go_pricing_textdomain' ); ?></a></div>
</div>
<!-- /Top Bar -->

<!-- Page Content -->
<div class="gwa-pcontent" data-ajax="<?php echo esc_attr( isset( $general_settings['admin']['ajax'] ) ? "true" : "false" ); ?>" data-help="<?php echo esc_attr( isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) ? $_COOKIE['go_pricing']['settings']['help'][$user_id] : '' ); ?>" data-unload="<?php _e( 'Are you sure you want to leave without saving?', 'go_pricing_textdomain' ); ?>">
	<form id="go-pricing-form" name="settings-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="_action" value="general_settings">
		<?php wp_nonce_field( $this->nonce, '_nonce' ); ?>
		
		<!-- Admin Box -->
		<div class="gwa-abox">
			<div class="gwa-abox-header">
				<div class="gwa-abox-header-icon"><i class="fa fa-cogs"></i></div>
				<div class="gwa-abox-title"><?php _e( 'Admin & General', 'go_pricing_textdomain' ); ?></div>
				<div class="gwa-abox-ctrl"></div>
			</div>
			<div class="gwa-abox-content-wrap">
				<div class="gwa-abox-content">
					<table class="gwa-table">
						<tr>
							<th><label><?php _e( 'Enable AJAX', 'go_pricing_textdomain' ); ?></label></th>
							<td><p><label><span class="gwa-checkbox<?php echo !empty( $general_settings['admin']['ajax'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="admin[ajax]" value="1" <?php echo !empty( $general_settings['admin']['ajax'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>							
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to enable AJAX request mode in admin area.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>				    							                                                       
					</table>					
					<div class="gwa-table-separator"></div>
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<table class="gwa-table">
						<tr>
							<th><label><?php _e( 'Select User Role', 'go_pricing_textdomain' ); ?></label></th>
							<td>
								<select name="capability">
									<option value="manage_options" <?php echo isset( $general_settings['capability'] ) && $general_settings['capability'] == 'manage_options' ? 'selected="selected"' : ''; ?>><?php _e( 'Administrator', 'go_pricing_textdomain' ); ?></option>
									<option value="edit_private_posts" <?php echo isset( $general_settings['capability'] ) && $general_settings['capability'] == 'edit_private_posts' ? 'selected="selected"' : ''; ?>><?php _e( 'Editor', 'go_pricing_textdomain' ); ?></option>
									<option value="publish_posts" <?php echo isset( $general_settings['capability'] ) && $general_settings['capability'] == 'publish_posts' ? 'selected="selected"' : ''; ?>><?php _e( 'Author', 'go_pricing_textdomain' ); ?></option>
									<option value="edit_posts" <?php echo isset( $general_settings['capability'] ) && $general_settings['capability'] == 'edit_posts' ? 'selected="selected"' : ''; ?>><?php _e( 'Contributor', 'go_pricing_textdomain' ); ?></option>
								</select>								
							</td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Set user access to the plugin.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>				    							                                                       
					</table>
					<div class="gwa-table-separator"></div>						
					<table class="gwa-table">
						<tr>
							<th><label><?php _e( 'Live Preview Safe Mode', 'go_pricing_textdomain' ); ?></label></th>
							<td><p><label><span class="gwa-checkbox<?php echo !empty( $general_settings['safe-preview'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="safe-preview" value="1" <?php echo !empty( $general_settings['safe-preview'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>							
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to enable Safe Mode for Live Previews. Required if the direct access of PHP files in "wp-content" folder is restricted. e.g. The "Restrict wp-content access" option of Sucuri Security plugin is enabled.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>				    							                                                       
					</table>									
					<div class="gwa-table-separator"></div>
					<?php endif; ?>				
					<table class="gwa-table">
						<tr>
							<th><label><?php _e( 'Enable CSS Transitions', 'go_pricing_textdomain' ); ?></label></th>
							<td><p><label><span class="gwa-checkbox<?php echo isset( $general_settings['transitions'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="transitions" value="1" <?php echo isset( $general_settings['transitions'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to enable CSS transitions.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>						
					</table>																																													
				</div>
			</div>
		</div>
		<!-- /Admin Box -->
				
		<!-- Submit -->
		<div class="gwa-submit"><button type="submit" class="gwa-btn-style1"><?php _e( 'Save', 'go_pricing_textdomain' ); ?></button></div>
		<!-- /Submit -->
				
		<!-- Admin Box -->
		<div class="gwa-abox" id="foo">
			<div class="gwa-abox-header">
				<div class="gwa-abox-header-icon"><i class="fa fa-dollar"></i></div>
				<div class="gwa-abox-title"><?php _e( 'Currency', 'go_pricing_textdomain' ); ?></div>
				<div class="gwa-abox-ctrl"></div>
			</div>
			<div class="gwa-abox-content-wrap">
				<div class="gwa-abox-content">
					<?php 
					if ( empty( $general_settings['currency'] ) ) {
						$general_settings['currency'][] = 'e';
						
					}
					if ( !empty( $general_settings['currency'] ) ) :
					foreach( (array)$general_settings['currency'] as $currency_index => $currency_value ) : 
					?>
					<table class="gwa-table">
						<tr>
							<th><label><?php _e( 'Currency', 'go_pricing_textdomain' ); ?></label></th>
							<td>
								<select name="currency[<?php echo $currency_index; ?>][currency]">
									<?php 
									foreach ( (array)$go_pricing['currency'] as $currency ) : 
									?>
									<option value="<?php echo esc_attr( !empty( $currency['id'] ) ? $currency['id'] : '' ); ?>"<?php echo ( !empty( $currency['id'] ) && ( $currency['id'] == $currency_value['currency'] )  ? ' selected="selected"' : '' ); ?>><?php echo ( !empty( $currency['name'] ) && !empty( $currency['symbol'] ) ? sprintf( '%1$s (%2$s)', $currency['name'], $currency['symbol'] )  : '' ); ?></option>
									<?php 
									endforeach;
									?>
								<select>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Currency of the price.', 'go_pricing_textdomain' ); ?></p></td>
						</tr>							
						<tr>
							<th><label><?php _e( 'Currency Position', 'go_pricing_textdomain' ); ?></label></th>
							<td>
								<select name="currency[<?php echo $currency_index; ?>][position]">
									<option value="left"<?php echo !empty( $currency_value['position'] ) && $currency_value['position'] == 'left' ? ' selected="selected"' : '' ; ?>><?php _e( 'Left (e.g. $100)', 'go_pricing_textdomain' ); ?></option>
									<option value="right"<?php echo !empty( $currency_value['position'] ) && $currency_value['position'] == 'right' ? ' selected="selected"' : '' ; ?>><?php _e( 'Right (e.g. 100$)', 'go_pricing_textdomain' ); ?></option>						
								<select>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Currency position of the price.', 'go_pricing_textdomain' ); ?></p></td>
						</tr>
						<tr>
							<th><label><?php _e( 'Thousand Separator', 'go_pricing_textdomain' ); ?></label></th>
							<td><input type="text" name="currency[<?php echo $currency_index; ?>][thousand-sep]" id="go-pricing-secondary-font" value="<?php echo isset( $currency_value['thousand-sep'] ) && $currency_value['thousand-sep'] != '' ? esc_attr( $currency_value['thousand-sep'] ) : ','; ?>"></td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Thuousand separator of the price.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>							
						<tr>
							<th><label><?php _e( 'Decimal Separator', 'go_pricing_textdomain' ); ?></label></th>
							<td><input type="text" name="currency[<?php echo $currency_index; ?>][decimal-sep]" id="go-pricing-secondary-font-css" value="<?php echo isset( $currency_value['decimal-sep'] ) && $currency_value['decimal-sep'] != '' ? esc_attr( $currency_value['decimal-sep'] ) : '.'; ?>"></td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Decimal separator of the price.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>
						<tr>
							<th><label><?php _e( 'Number of Decimals', 'go_pricing_textdomain' ); ?></label></th>
							<td><input type="text" name="currency[<?php echo $currency_index; ?>][decimal-no]" id="go-pricing-secondary-font-css" value="<?php echo isset( $currency_value['decimal-no'] ) && $currency_value['decimal-no'] != '' ? esc_attr( (int)$currency_value['decimal-no'] ) : 2; ?>"></td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Maximum number of decimals to show in price.', 'go_pricing_textdomain' ); ?></p></td>									
						</tr>																																									
					</table>
					<?php 
					endforeach;
					endif;
					?>																																																										
				</div>
			 </div>
		</div>
		<!-- /Admin Box -->
		
		<!-- Submit -->
		<div class="gwa-submit"><button type="submit" class="gwa-btn-style1"><?php _e( 'Save', 'go_pricing_textdomain' ); ?></button></div>
		<!-- /Submit -->

		<!-- Admin Box -->
		<div class="gwa-abox">
			<div class="gwa-abox-header">
				<div class="gwa-abox-header-icon"><i class="fa fa-code"></i></div>
				<div class="gwa-abox-title"><?php _e( 'Custom CSS Code', 'go_pricing_textdomain' ); ?></div>
				<div class="gwa-abox-ctrl"></div>
			</div>
			<div class="gwa-abox-content-wrap">
				<div class="gwa-abox-content">
					<table class="gwa-table">							
						<tr class="gwa-row-fullwidth">
							<th><label><?php _e( 'Code', 'go_pricing_textdomain' ); ?></label></th>
							<td><textarea name="custom-css" rows="10"><?php echo isset( $general_settings['custom-css'] ) ? ( $general_settings['custom-css'] ) : ''; ?></textarea></td>
							<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'The code will be added to the plugin frontend style.', 'go_pricing_textdomain' ); ?></p></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<!-- /Admin Box -->
		
		<!-- Submit -->
		<div class="gwa-submit"><button type="submit" class="gwa-btn-style1"><?php _e( 'Save', 'go_pricing_textdomain' ); ?></button></div>
		<!-- /Submit -->
		
	</form>
</div>
<!-- /Page Content -->