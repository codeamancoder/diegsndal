<?php
  
/* PHP 5.3 - start */

if(false === function_exists('lcfirst'))
{
    /**
     * Make a string's first character lowercase
     *
     * @param string $str
     * @return string the resulting string.
     */
    function lcfirst( $str ) {
        $str[0] = strtolower($str[0]);
        return (string)$str;
    }
}

if (get_magic_quotes_gpc()) {
    if(!function_exists('stripslashes_deep')){
        function stripslashes_deep($value)
        {
            $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

            return $value;
        }
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}
/* PHP 5.3 - end */
  
//WPML
add_action('plugins_loaded', 'wcml_check_wpml_functions');

function wcml_check_wpml_functions(){
    if(defined('ICL_SITEPRESS_VERSION') && version_compare(preg_replace('#-(.+)$#', '', ICL_SITEPRESS_VERSION), '3.1.5', '<')){
        
        function wpml_is_ajax() {
            if ( defined( 'DOING_AJAX' ) ) {
                return true;
            }

            return ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) ? true : false;
        }
        
    }

    if( !has_filter( 'translate_object_id' ) ){
        add_filter( 'translate_object_id', 'icl_object_id', 10, 4 );
    }

    if( !has_action( 'wpml_register_single_string' ) ){
        if( function_exists( 'wpml_register_single_string_action' ) ) {
            add_action('wpml_register_single_string', 'wpml_register_single_string_action', 10, 4);
        }elseif ( function_exists( 'icl_register_string' ) ){
            add_action('wpml_register_single_string', 'icl_register_string', 10, 4);
        }
    }

    if( !has_filter( 'wpml_translate_single_string' ) ){
        add_filter( 'wpml_translate_single_string', 'wcml_translate_single_string_filter', 10, 6 );
    }

}

function wcml_translate_single_string_filter( $original_value, $context, $name, $language_code = null, $has_translation = null, $disable_auto_register = false ) {
    if( is_string($name) && function_exists( 'icl_t' ) ){
        return icl_t( $context, $name, $original_value, $has_translation, $disable_auto_register, $language_code );
    }else{
        return $original_value;
    }
}
?>
