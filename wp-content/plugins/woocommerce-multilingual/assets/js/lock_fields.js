jQuery(document).ready(function($){
    var i;
    var ids = ['_virtual','_downloadable','product-type','_backorders','_manage_stock','_stock','_sold_individually','comment_status','_tax_status','_tax_class','parent_id','crosssell_ids','upsell_ids'];

    $('.wcml_prod_hidden_notice').prependTo('#woocommerce-product-data');

    for (i = 0; i < ids.length; i++) {
        $('#'+ids[i]).attr('disabled','disabled');
        $('#'+ids[i]).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    }

    var buttons = ['add_variation','link_all_variations','attribute_taxonomy','save_attributes','add_new_attribute','product_attributes .remove_row','add_attribute','select_all_attributes','select_no_attributes'];
    for (i = 0; i < buttons.length; i++) {
        $('.'+buttons[i]).attr('disabled','disabled');
        $('.'+buttons[i]).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show().css('float','right'));
    }

    $('.remove_variation').each(function(){
        $(this).attr('disabled','disabled');
        $(this).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show().css('float','right'));
    });

    var inpt_names = ['_width','_height','_sku','_length','_weight','product_length','_regular_price','_sale_price','_sale_price_dates_from','_sale_price_dates_to'];

    if( unlock_fields.menu_order == 1 ){
        inpt_names.push('menu_order');
    }

    for (i = 0; i < inpt_names.length; i++) {
        $('input[name="'+inpt_names[i]+'"]').attr('readonly','readonly');
        $('input[name="'+inpt_names[i]+'"]').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());

    }

    $('.woocommerce_attribute_data td textarea,.attribute_values,.attribute_name').each(function(){
       $(this).attr('readonly','readonly');
       $(this).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    });


    $('.woocommerce_attribute_data input[type="checkbox"]').each(function(){
        $(this).attr('disabled','disabled');
        $(this).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    });

    $('form#post input[type="submit"]').click(function(){
        for (i = 0; i < ids.length; i++) {
            $('#'+ids[i]).removeAttr('disabled');
        }
        $('.woocommerce_variation select,#variable_product_options .toolbar select,.woocommerce_variation input[type="checkbox"],.woocommerce_attribute_data input[type="checkbox"]').each(function(){
            $(this).removeAttr('disabled');
        });
    });


    //quick edit fields
    for (i = 0; i < ids.length; i++) {
        $('.inline-edit-product [name="'+ids[i]+'"]').attr('disabled','disabled');
        $('.inline-edit-product [name="'+ids[i]+'"]').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    }

    for (i = 0; i < inpt_names.length; i++) {
        $('.inline-edit-product [name="'+inpt_names[i]+'"]').attr('readonly','readonly');
        $('.inline-edit-product [name="'+inpt_names[i]+'"]').after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    }

});

var wcml_lock_variation_fields = function(){

    var check_attr = jQuery('.woocommerce_variation>h3 select').attr('disabled');

    if (typeof check_attr !== typeof undefined && check_attr !== false) {
        return;
    }

    jQuery('.woocommerce_variation>h3 select, #variable_product_options .toolbar select, .show_if_variation_manage_stock select').each(function(){

        jQuery(this).attr('disabled','disabled');
        jQuery(this).after(jQuery('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    });

    var i = 0;
    var inpt_names = ['_width','_height','_sku','_length','_weight','product_length','_regular_price','_sale_price','_sale_price_dates_from','_sale_price_dates_to','_stock','_download_limit','_download_expiry'];

    for (i = 0; i < inpt_names.length; i++) {

        //variation fields
        jQuery('input[name^="variable'+inpt_names[i]+'"]').each(function(){
            jQuery(this).attr('readonly','readonly');
            jQuery(this).after(jQuery('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
        });
    }

    //variation fields
    var var_checkboxes = ['_enabled','_is_downloadable','_is_virtual','_manage_stock'];
    for (i = 0; i < var_checkboxes.length; i++) {
        jQuery('input[name^="variable'+var_checkboxes[i]+'"]').each(function(){
            jQuery(this).attr('disabled','disabled');
            jQuery(this).after(jQuery('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
        });
    }

    var var_selectboxes = ['_stock_status','_shipping_class','_tax_class'];
    for (i = 0; i < var_selectboxes.length; i++) {
        jQuery('select[name^="variable'+var_selectboxes[i]+'"]').each(function(){
            jQuery(this).attr('disabled','disabled');
            jQuery(this).after(jQuery('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
        });
    }

}




