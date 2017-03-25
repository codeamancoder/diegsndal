<?php

class WCML_Product_Bundles{

    /**
     * @var WPML_Element_Translation_Package
     */
    public $tp;

    /**
     * @var SitePress
     */
    private $sitepress;

    /**
     * @var woocommerce_wpml
     */
    private $woocommerce_wpml;

	/**
	 * @var WCML_WC_Product_Bundles_Items
	 */
	private $product_bundles_items;

    /**
     * WCML_Product_Bundles constructor.
     */
    function __construct(  &$sitepress, &$woocommerce_wpml, &$product_bundles_items ){

        $this->sitepress = $sitepress;
        $this->woocommerce_wpml = $woocommerce_wpml;
        $this->product_bundles_items = $product_bundles_items;

		add_action( 'woocommerce_get_cart_item_from_session', array( $this, 'resync_bundle' ), 5, 3 );
		add_filter( 'woocommerce_cart_loaded_from_session', array( $this, 'resync_bundle_clean' ), 10 );

        if( is_admin() ){
            $this->tp = new WPML_Element_Translation_Package();

            add_filter( 'wpml_tm_translation_job_data', array( $this, 'append_bundle_data_translation_package' ), 10, 2 );
            add_action( 'wpml_translation_job_saved',   array( $this, 'save_bundle_data_translation' ), 10, 3 );

	        add_action( 'wcml_gui_additional_box_html', array( $this, 'custom_box_html' ), 10, 3 );
	        add_filter( 'wcml_gui_additional_box_data', array( $this, 'custom_box_html_data' ), 10, 4 );

	        add_action( 'wcml_after_duplicate_product_post_meta', array( $this, 'sync_bundled_ids' ), 10, 2 );
	        add_action( 'wcml_update_extra_fields', array( $this, 'bundle_update' ), 10, 4 );

	        add_action( 'save_post', array( $this, 'sync_product_bundle_meta_with_translations' ) );
        }

    }

	private function get_product_bundle_data( $bundle_id ){
		$product_bundle_data = array();

		$bundle_items = $this->product_bundles_items->get_items( $bundle_id );
		foreach( $bundle_items as $key => $bundle_item ){
			$product_bundle_data[ $bundle_item->item_id ] = $this->product_bundles_items->get_item_data( $bundle_item );
		}

		return $product_bundle_data;
	}

	private function save_product_bundle_data( $bundle_id, $product_bundle_data ){

		$bundle_items = $this->product_bundles_items->get_items( $bundle_id );

		foreach( $bundle_items as $item_id => $bundle_item ){

			$bundled_item_data = $this->product_bundles_items->get_item_data_object( $item_id );

			foreach( $product_bundle_data[ $item_id ] as $key => $value ){
				$this->product_bundles_items->update_item_meta( $bundled_item_data,  $key,  $value );
			}

			$this->product_bundles_items->save_item_meta( $bundled_item_data );

		}

	}

	private function sync_product_bundle_meta( $bundle_id, $translated_bundle_id ){

		$bundle_items = $this->product_bundles_items->get_items( $bundle_id );
		$fields_to_sync = array(
			'optional',
			'stock_status',
			'max_stock',
			'quantity_min',
			'quantity_max',
			'shipped_individually',
			'priced_individually',
			'single_product_visibility',
			'cart_visibility',
			'order_visibility',
			'single_product_price_visibility',
			'cart_price_visibility',
			'order_price_visibility'
		);

		$target_lang = $this->sitepress->get_language_for_element( $translated_bundle_id, 'post_product' );

		foreach( $bundle_items as $item_id => $bundle_item ){

			$item_meta = $this->product_bundles_items->get_item_data( $bundle_item );
			$translated_product_id = apply_filters( 'translate_object_id', $item_meta['product_id'], get_post_type( $item_meta['product_id'] ), false, $target_lang );

			if( $translated_product_id ){
				$translated_item_id = $this->get_item_id_for_product_id( $translated_product_id, $translated_bundle_id );

				$translated_item = $this->product_bundles_items->get_item_data_object( $translated_item_id );
				foreach( $fields_to_sync as $key ){
					$this->product_bundles_items->update_item_meta( $translated_item, $key, $item_meta[$key] );
				}
				$this->product_bundles_items->save_item_meta( $translated_item );
			}

		}



	}

	public function sync_product_bundle_meta_with_translations( $bundle_id ){

		if ( WooCommerce_Functions_Wrapper::get_product_type( $bundle_id ) === 'bundle' ) {

			$trid        = $this->sitepress->get_element_trid( $bundle_id, 'post_product' );
			$tranlations = $this->sitepress->get_element_translations( $trid, 'post_product' );

			foreach ( $tranlations as $language => $translation ) {
				if ( $translation->original ) {
					$original_bundle_id = $translation->element_id;
					break;
				}

			}

			foreach ( $tranlations as $language => $translation ) {
				if ( $translation->element_id !== $original_bundle_id ) {
					$this->sync_product_bundle_meta( $original_bundle_id, $translation->element_id );
				}
			}

		}

	}

	private function get_product_id_for_item_id( $item_id ){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT product_id FROM {$wpdb->prefix}woocommerce_bundled_items WHERE bundled_item_id=%d", $item_id) );
	}

	private function get_item_id_for_product_id( $product_id, $bundle_id ){
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items WHERE product_id=%d AND bundle_id=%d",
			$product_id, $bundle_id
		) );
	}


	// Add Bundles Box to WCML Translation GUI
	public function custom_box_html( $obj, $bundle_id, $data ){

		$bundle_items = $this->product_bundles_items->get_items( $bundle_id );

		if( empty( $bundle_items ) ){
			return false;
		}

		$bundles_section = new WPML_Editor_UI_Field_Section( __( 'Product Bundles', 'woocommerce-multilingual' ) );

		end( $bundle_items );
		$last_item_id = key( $bundle_items );
		$divider = true;
		$flag = false;

		foreach ( $bundle_items as $item_id => $bundle_item ) {

			$translated_product = apply_filters( 'translate_object_id', $bundle_item->product_id, get_post_type( $bundle_item->product_id ), false, $obj->get_target_language() );
			if( !is_null($translated_product)) {

				$add_group = false;
				if( $item_id == $last_item_id ){
					$divider = false;
				}

				$bundle_item_data = $this->product_bundles_items->get_item_data( $bundle_item );

				$group = new WPML_Editor_UI_Field_Group( get_the_title( $bundle_item->product_id ), $divider );

				if( $bundle_item_data[ 'override_title' ] == 'yes' ) {
					$bundle_field = new WPML_Editor_UI_Single_Line_Field(
						'bundle_' . $bundle_item->product_id . '_title',
						__( 'Name', 'woocommerce-multilingual' ),
						$data,
						false
					);
					$group->add_field( $bundle_field );
					$add_group = true;
				}

				if( $bundle_item_data[ 'override_description' ] == 'yes' ){
					$bundle_field = new WPML_Editor_UI_Single_Line_Field(
						'bundle_'. $bundle_item->product_id . '_desc' ,
						__( 'Description', 'woocommerce-multilingual' ),
						$data,
						false
					);
					$group->add_field( $bundle_field );
					$add_group = true;
				}

				if( $add_group ){
					$bundles_section->add_field( $group );
					$flag = true;
				}

			}

		}

		if( $flag ){
			$obj->add_field( $bundles_section );
		}

	}

	public function custom_box_html_data( $data, $bundle_id, $translation, $lang ){

		$bundle_data = $this->get_product_bundle_data( $bundle_id );

		if( $translation ) {
			$translated_bundle_id = $translation->ID;
			$translated_bundle_data = $this->get_product_bundle_data( $translated_bundle_id );
		}

		if( empty( $bundle_data ) || $bundle_data == false ){
			return $data;
		}

		$product_bundles = array_keys( $bundle_data );

		foreach ( $product_bundles as $item_id ) {

			$product_id = $this->get_product_id_for_item_id( $item_id );

			$translated_product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $lang );
			if( $translation ){
				$translated_item_id = $this->get_item_id_for_product_id( $translated_product_id, $translated_bundle_id );
			}

			if( $bundle_data[ $item_id ][ 'override_title' ] == 'yes' ){
				$data[ 'bundle_'.$product_id.'_title' ] = array( 'original' => $bundle_data[ $item_id ][ 'title' ] );
				if( $translation && isset( $translated_bundle_data[ $translated_item_id ][ 'override_title' ] ) ){
					$data[ 'bundle_'.$product_id.'_title' ][ 'translation' ] = $translated_bundle_data[ $translated_item_id ][ 'title' ];
				}else{
					$data[ 'bundle_'.$product_id.'_title' ][ 'translation' ] = '';
				}
			}

			if( $bundle_data[ $item_id ][ 'override_description' ] == 'yes' ){
				$data[ 'bundle_'.$product_id.'_desc' ] = array( 'original' => $bundle_data[ $item_id ][ 'description' ] );
				if( $translation && isset( $translated_bundle_data[ $translated_item_id ][ 'override_description' ] ) ){
					$data[ 'bundle_'.$product_id.'_desc' ][ 'translation' ] = $translated_bundle_data[ $translated_item_id ][ 'description' ];
				}else{
					$data[ 'bundle_'.$product_id.'_desc' ][ 'translation' ] = '';
				}
			}
		}
		return $data;
	}

	public function append_bundle_data_translation_package( $package, $post ){

		if( $post->post_type == 'product' ) {

			$bundle_data = $this->get_product_bundle_data( $post->ID );

			if( $bundle_data ){

				$fields = array( 'title', 'description' );

				foreach( $bundle_data as $item_id => $product_data ){

					$product_id = $this->get_product_id_for_item_id( $item_id );
					foreach( $fields as $field ) {
						if ( $product_data[ 'override_' . $field ] == 'yes' && !empty( $product_data[ $field ] ) ) {
							$package[ 'contents' ][ 'product_bundles:' . $product_id . ':' . $field ] = array(
								'translate' => 1,
								'data' => $this->tp->encode_field_data( $product_data[ $field ], 'base64' ),
								'format' => 'base64'
							);
						}
					}
				}
			}
		}

		return $package;

	}

	// Update Bundled products title and descritpion after saving the translation
	public function bundle_update( $bundle_id, $translated_bundle_id, $data, $lang ){
		global $wpdb;

		$bundle_data = $this->get_product_bundle_data( $bundle_id );
		$translated_bundle_data = $this->get_product_bundle_data( $translated_bundle_id );

		if( empty( $bundle_data ) ){
			return;
		}

		$translate_bundled_item_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT product_id FROM {$wpdb->prefix}woocommerce_bundled_items WHERE bundle_id = %d", $translated_bundle_id ));

		foreach ( $bundle_data as $item_id => $bundle_item_data ) {

			$product_id = $this->get_product_id_for_item_id( $item_id );
			$translated_product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $lang );

			if( $translated_product_id ) {

				if ( ! in_array( $translated_product_id, $translate_bundled_item_ids ) ) {

					$menu_order = $wpdb->get_var( $wpdb->prepare( " 
	                    SELECT menu_order FROM {$wpdb->prefix}woocommerce_bundled_items
	                    WHERE bundle_id=%d AND product_id=%d
	                ", $bundle_id, $bundle_item_data['product_id'] ) );

					$wpdb->insert( $wpdb->prefix . 'woocommerce_bundled_items',
						array(
							'product_id' => $translated_product_id,
							'bundle_id'  => $translated_bundle_id,
							'menu_order' => $menu_order,
						)
					);
				}

				$translated_item_id = $this->get_item_id_for_product_id( $translated_product_id, $translated_bundle_id );

				//$this->product_bundles_items->copy_item_data( $item_id, $translated_item_id );

				if ( isset( $data[ md5( 'bundle_' . $product_id . '_title' ) ] ) ) {
					$translated_bundle_data[ $translated_item_id ]['title']          = $data[ md5( 'bundle_' . $product_id . '_title' ) ];
					$translated_bundle_data[ $translated_item_id ]['override_title'] = $bundle_item_data['override_title'];
				}

				if ( isset( $data[ md5( 'bundle_' . $product_id . '_desc' ) ] ) ) {
					$translated_bundle_data[ $translated_item_id ]['description']          = $data[ md5( 'bundle_' . $product_id . '_desc' ) ];
					$translated_bundle_data[ $translated_item_id ]['override_description'] = $bundle_item_data['override_description'];
				}

			}

		}

		$this->save_product_bundle_data( $translated_bundle_id, $translated_bundle_data );
		$this->sync_product_bundle_meta( $bundle_id, $translated_bundle_id );

		$this->sitepress->copy_custom_fields ( $bundle_id, $translated_bundle_id );

		return $translated_bundle_data;
	}

	// Sync product bundle data with translated values when the product is duplicated
    public function sync_bundled_ids( $bundle_id, $translated_bundle_id ){
		global $wpdb;

	    $bundle_data = $this->get_product_bundle_data( $bundle_id );
        if( $bundle_data ){
            $lang = $this->sitepress->get_language_for_element( $translated_bundle_id, 'post_product' );
            $translated_bundle_data = $this->get_product_bundle_data( $translated_bundle_id );

            foreach( $bundle_data as $item_id => $product_data ){

	            $product_id = $this->get_product_id_for_item_id( $item_id );
	            $translated_product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $lang );

	            if( $translated_product_id ){

	            	$translated_item_id = $this->get_item_id_for_product_id( $translated_product_id, $translated_bundle_id );
	            	if( !$translated_item_id ){
			            $menu_order = $wpdb->get_var( $wpdb->prepare( " 
                            SELECT menu_order FROM {$wpdb->prefix}woocommerce_bundled_items
	                        WHERE bundle_id=%d AND product_id=%d
	                        ", $bundle_id, $product_id ) );

			            $wpdb->insert( $wpdb->prefix . 'woocommerce_bundled_items',
				            array(
					            'product_id' => $translated_product_id,
					            'bundle_id'  => $translated_bundle_id,
					            'menu_order' => $menu_order,
				            )
			            );
			            $translated_item_id = $wpdb->insert_id;
		            }

		            $translated_bundle_data[ $translated_item_id ] = $product_data;
		            $translated_bundle_data[ $translated_item_id ]['product_id'] = $translated_product_id;

		            if( isset( $bundle_data[ 'title' ] ) ){
			            if( $bundle_data[ 'override_title' ] != 'yes' ){
				            $translated_bundle_data[ $translated_item_id ][ 'title' ] = get_the_title( $translated_product_id );
			            }
		            }

		            if( isset( $bundle_data[ 'title' ] ) ){
			            if( $bundle_data[ 'override_description' ] != 'yes' ){
				            $translated_bundle_data[ $translated_item_id ][ 'description' ] = get_the_title( $translated_product_id );
			            }
		            }

		            if( isset( $bundle_data[ 'filter_variations' ] ) && $bundle_data[ 'filter_variations' ] == 'yes' ){
			            $allowed_var = $bundle_data[ 'allowed_variations' ];
			            foreach( $allowed_var as $key => $var_id ){
				            $translated_var_id = apply_filters( 'translate_object_id', $var_id, get_post_type( $var_id ), true, $lang );
				            $translated_bundle_data[ $translated_item_id ][ 'allowed_variations' ][ $key ] = $translated_var_id;
			            }
		            }


		            if( isset( $bundle_data[ 'bundle_defaults' ] ) && !empty( $bundle_data[ 'bundle_defaults' ] ) ){
			            foreach( $bundle_data[ 'bundle_defaults' ] as $tax => $term_slug ){

				            $term_id = $this->woocommerce_wpml->terms->wcml_get_term_id_by_slug( $tax, $term_slug );
				            if( $term_id ){
					            // Global Attribute
					            $tr_def_id = apply_filters( 'translate_object_id', $term_id, $tax, true, $lang );
					            $tr_term = $this->woocommerce_wpml->terms->wcml_get_term_by_id( $tr_def_id, $tax );
					            $translated_bundle_data[ $translated_item_id ][ 'bundle_defaults' ][ $tax ] = $tr_term->slug;
				            }else{
					            // Custom Attribute
					            $args = array(
						            'post_type' => 'product_variation',
						            'meta_key' => 'attribute_'.$tax,
						            'meta_value' => $term_slug,
						            'meta_compare' => '='
					            );
					            $variationloop = new WP_Query( $args );
					            while ( $variationloop->have_posts() ) : $variationloop->the_post();
						            $tr_var_id = apply_filters( 'translate_object_id', get_the_ID(), 'product_variation', true, $lang );
						            $tr_meta = get_post_meta( $tr_var_id, 'attribute_'.$tax , true );
						            $translated_bundle_data[ $translated_item_id ][ 'bundle_defaults' ][ $tax ] = $tr_meta;
					            endwhile;
				            }
			            }
		            }

	            }


            }

            $this->save_product_bundle_data( $translated_bundle_id, $translated_bundle_data );

            return $translated_bundle_data;
        }

    }

    public function resync_bundle( $cart_item, $session_values, $cart_item_key ) {

    	if ( isset( $cart_item[ 'bundled_items' ] ) && $cart_item[ 'data' ]->product_type === 'bundle' ) {
    		$current_bundle_id = apply_filters( 'translate_object_id', $cart_item[ 'product_id' ], 'product', true );
			if ( $cart_item[ 'product_id' ] != $current_bundle_id ) {
				$old_bundled_item_ids      = array_keys( $cart_item[ 'data' ]->bundle_data );
				$cart_item[ 'data' ]       = wc_get_product( $current_bundle_id );
                if( isset($cart_item[ 'data' ]->bundle_data) && is_array( $cart_item[ 'data' ]->bundle_data ) ){
                    $new_bundled_item_ids      = array_keys( $cart_item[ 'data' ]->bundle_data );
                    $remapped_bundled_item_ids = array();
                    foreach ( $old_bundled_item_ids as $old_item_id_index => $old_item_id ) {
                        $remapped_bundled_item_ids[ $old_item_id ] = $new_bundled_item_ids[ $old_item_id_index ];
                    }
                    $cart_item[ 'remapped_bundled_item_ids' ] = $remapped_bundled_item_ids;
                    if ( isset( $cart_item[ 'stamp' ] ) ) {
                        $new_stamp = array();
                        foreach ( $cart_item[ 'stamp' ] as $bundled_item_id => $stamp_data ) {
                            $new_stamp[ $remapped_bundled_item_ids[ $bundled_item_id ] ] = $stamp_data;
                        }
                        $cart_item[ 'stamp' ] = $new_stamp;
                    }
                }
			}
    	}
    	if ( isset( $cart_item[ 'bundled_by' ] ) && isset( WC()->cart->cart_contents[ $cart_item[ 'bundled_by' ] ] ) ) {
    		$bundle_cart_item = WC()->cart->cart_contents[ $cart_item[ 'bundled_by' ] ];
    		if (
                isset( $bundle_cart_item[ 'remapped_bundled_item_ids' ] ) &&
                isset( $cart_item[ 'bundled_item_id' ] ) &&
                isset( $bundle_cart_item[ 'remapped_bundled_item_ids' ][ $cart_item[ 'bundled_item_id' ] ] )
            ) {
				$old_id                         = $cart_item[ 'bundled_item_id' ];
				$remapped_bundled_item_ids      = $bundle_cart_item[ 'remapped_bundled_item_ids' ];
				$cart_item[ 'bundled_item_id' ] = $remapped_bundled_item_ids[ $cart_item[ 'bundled_item_id' ] ];
    			if ( isset( $cart_item[ 'stamp' ] ) ) {
    				$new_stamp = array();
    				foreach ( $cart_item[ 'stamp' ] as $bundled_item_id => $stamp_data ) {
    					$new_stamp[ $remapped_bundled_item_ids[ $bundled_item_id ] ] = $stamp_data;
    				}
    				$cart_item[ 'stamp' ] = $new_stamp;
    			}
    		}
    	}

    	return $cart_item;
    }

    public function resync_bundle_clean( $cart ) {
    	foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
	    	if ( isset( $cart_item[ 'bundled_items' ] ) && WooCommerce_Functions_Wrapper::get_product_type( $cart_item[ 'product_id' ] ) === 'bundle' ) {
	    		if ( isset( $cart_item[ 'remapped_bundled_item_ids' ] ) ) {
	    			unset( WC()->cart->cart_contents[ $cart_item_key ][ 'remapped_bundled_item_ids' ] );
	    		}
	    	}
    	}
    }

    public function save_bundle_data_translation( $translated_bundle_id, $data, $job ){

        remove_action( 'wcml_after_duplicate_product_post_meta', array( $this, 'sync_bundled_ids' ), 10, 2 );

        $translated_bundle_data = $this->get_product_bundle_data( $translated_bundle_id );

        $bundle_id =& $job->original_doc_id;

        $bundle_data = $this->get_product_bundle_data( $bundle_id );

        foreach( $data as $value){

            if( preg_match( '/product_bundles:([0-9]+):(.+)/', $value[ 'field_type' ], $matches ) ){

                $product_id = $matches[1];
                $field      = $matches[2];

	            $translated_product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $job->language_code );
                $translated_item_id = $this->get_item_id_for_product_id( $translated_product_id, $translated_bundle_id );
                if( empty( $translated_item_id ) ){
	                $translated_item_id = $this->add_product_to_bundle( $translated_product_id, $translated_bundle_id, $bundle_id, $product_id );
                }

	            $item_id = $this->get_item_id_for_product_id( $product_id, $bundle_id );

                if( !isset( $translated_bundle_data[ $translated_item_id ] ) ){
	                $translated_bundle_data[ $translated_item_id ] = array(
                        'product_id'            => $translated_product_id,
                        'hide_thumbnail'        => $bundle_data[ $item_id ][ 'hide_thumbnail' ],
                        'override_title'        => $bundle_data[ $item_id ][ 'override_title' ],
                        'product_title'         => '',
                        'override_description'  => $bundle_data[ $item_id ][ 'override_description' ],
                        'product_description'   => '',
                        'optional'              => $bundle_data[ $item_id ][ 'optional' ],
                        'bundle_quantity'       => $bundle_data[ $item_id ][ 'bundle_quantity' ],
                        'bundle_quantity_max'   => $bundle_data[ $item_id ][ 'bundle_quantity_max' ],
                        'bundle_discount'       => $bundle_data[ $item_id ][ 'bundle_discount' ],
                        'single_product_visibility'  => $bundle_data[ $item_id ][ 'single_product_visibility' ],
                        'cart_visibility'            => $bundle_data[ $item_id ][ 'cart_visibility' ],
                        'order_visibility'           => $bundle_data[ $item_id ][ 'order_visibility' ],
                        'stock_status'               => $bundle_data[ $item_id ][ 'stock_status' ],
		                'max_stock'                  => $bundle_data[ $item_id ][ 'max_stock' ],
		                'quantity_min'               => $bundle_data[ $item_id ][ 'quantity_min' ],
						'quantity_max'               => $bundle_data[ $item_id ][ 'quantity_max' ],
						'shipped_individually'       => $bundle_data[ $item_id ][ 'shipped_individually' ],
						'priced_individually'        => $bundle_data[ $item_id ][ 'priced_individually' ],
						'single_product_price_visibility' => $bundle_data[ $item_id ][ 'single_product_price_visibility' ],
						'cart_price_visibility'           => $bundle_data[ $item_id ][ 'cart_price_visibility' ],
						'order_price_visibility'          => $bundle_data[ $item_id ][ 'order_price_visibility' ]
                    );
                }

	            $translated_bundle_data[ $translated_item_id ][ $field ] = $value[ 'data' ];
            }

        }

	    $this->save_product_bundle_data( $translated_bundle_id, $translated_bundle_data );
    }

    private function add_product_to_bundle( $product_id, $bundle_id, $original_bundle_id, $original_product_id ){
    	global $wpdb;
	    $menu_order = $wpdb->get_var( $wpdb->prepare( " 
                            SELECT menu_order FROM {$wpdb->prefix}woocommerce_bundled_items
	                        WHERE bundle_id=%d AND product_id=%d
	                        ", $original_bundle_id, $original_product_id ) );

	    $wpdb->insert( $wpdb->prefix . 'woocommerce_bundled_items',
		    array(
			    'product_id' => $product_id,
			    'bundle_id'  => $bundle_id,
			    'menu_order' => $menu_order,
		    )
	    );

	    return $wpdb->insert_id;
    }
}