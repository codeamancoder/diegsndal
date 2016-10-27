<?php
  
class WC_Currency_Switcher_Widget extends WP_Widget {

    function __construct() {

        parent::__construct( 'currency_sel_widget', __('Currency switcher', 'wpml-wcml'), __('Currency switcher', 'wpml-wcml'));
    }

    function widget($args, $instance) {

        echo $args['before_widget'];

        do_action('currency_switcher');

        echo $args['after_widget'];
    }

    function form( $instance ) {

        printf('<p><a href="%s">%s</a></p>',admin_url('admin.php?page=wpml-wcml#currency-switcher'),__('Configure options','wpml-wcml'));
        return;

    }
}