<?php

    /*
    *
    *	Swift Page Builder - Testimonial Carousel Shortcode
    *	------------------------------------------------
    *	Swift Framework
    * 	Copyright Swift Ideas 2016 - http://www.swiftideas.com
    *
    */

    class SwiftPageBuilderShortcode_spb_testimonial_carousel extends SwiftPageBuilderShortcode {

        public function content( $atts, $content = null ) {

            $title = $order = $page_link = $items = $item_class = $el_class = $width = $el_position = '';

            extract( shortcode_atts( array(
                'title'       => '',
                'item_count'  => '-1',
                'order'       => '',
                'category'    => 'all',
                'pagination'  => 'no',
                'showcase'    => 'no',
                'page_link'   => '',
                'el_class'    => '',
                'el_position' => '',
                'width'       => '1/2'
            ), $atts ) );

            // Enqueue
            wp_enqueue_script( 'owlcarousel' );
            
            
            $output = '';

            /* SIDEBAR CONFIG
            ================================================== */
            global $sf_sidebar_config, $sf_options;

            $sidebars = '';
            if ( ( $sf_sidebar_config == "left-sidebar" ) || ( $sf_sidebar_config == "right-sidebar" ) ) {
                $sidebars = 'one-sidebar';
            } else if ( $sf_sidebar_config == "both-sidebars" ) {
                $sidebars = 'both-sidebars';
            } else {
                $sidebars = 'no-sidebars';
            }

            // CATEGORY SLUG MODIFICATION
            if ( $category == "All" ) {
                $category = "all";
            }
            if ( $category == "all" ) {
                $category = '';
            }
            $category_slug = str_replace( '_', '-', $category );


            // TESTIMONIAL QUERY SETUP

            global $post, $wp_query, $sf_carouselID;

            if ( $sf_carouselID == "" ) {
                $sf_carouselID = 1;
            } else {
                $sf_carouselID ++;
            }

            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

            $testimonials_args = array(
                'orderby'               => $order,
                'post_type'             => 'testimonials',
                'post_status'           => 'publish',
                'paged'                 => $paged,
                'testimonials-category' => $category_slug,
                'posts_per_page'        => $item_count,
                'no_found_rows'         => 1,
            );

            $testimonials = new WP_Query( $testimonials_args );

            $sidebar_config = spb_get_post_meta( get_the_ID(), 'sf_sidebar_config', true );

            $sidebars = '';
            if ( ( $sidebar_config == "left-sidebar" ) || ( $sidebar_config == "right-sidebar" ) ) {
                $sidebars = 'one-sidebar';
            } else if ( $sidebar_config == "both-sidebars" ) {
                $sidebars = 'both-sidebars';
            } else {
                $sidebars = 'no-sidebars';
            }

            $columns = 1;
            $fw_mode = false;
            $list_class = '';
            if ( $showcase == "yes" && $width == "1/1" && $sidebars == "no-sidebars" ) {
                $columns = 3;
                $fw_mode = true;
                $el_class .= ' showcase_testimonial_widget';
                $list_class = "showcase-carousel";
                $items .= '<div class="testimonial-carousel carousel-wrap">';
                $items .= spb_carousel_arrows(true);
                $items .= '<div class="container">';
            } 
            $items .= '<ul id="carousel-' . $sf_carouselID . '" class="testimonials carousel-items clearfix ' . $list_class . '" data-columns="' . $columns . '" data-auto="false">';

            // TESTIMONIAL LOOP

            while ( $testimonials->have_posts() ) : $testimonials->the_post();

                $testimonial_text         = get_the_content();
                $testimonial_cite         = spb_get_post_meta( $post->ID, 'sf_testimonial_cite', true );
                $testimonial_cite_subtext = spb_get_post_meta( $post->ID, 'sf_testimonial_cite_subtext', true );
                $testimonial_image        = rwmb_meta( 'sf_testimonial_cite_image', 'type=image', $post->ID );

                foreach ( $testimonial_image as $detail_image ) {
                    $testimonial_image_url = $detail_image['url'];
                    break;
                }

                if ( ! $testimonial_image ) {
                    $testimonial_image     = get_post_thumbnail_id();
                    $testimonial_image_url = wp_get_attachment_url( $testimonial_image, 'full' );
                }

                $testimonial_size = apply_filters( 'spb_testimonial_image_size', 70 );
                $testimonial_image = spb_image_resizer( $testimonial_image_url, $testimonial_size, $testimonial_size, true, false );

                $items .= '<li class="testimonial carousel-item col-sm-12 clearfix">';
                $items .= '<div class="testimonial-text">' . do_shortcode( $testimonial_text ) . '</div>';
                $items .= '<div class="testimonial-cite">';
                if ( $testimonial_image ) {
                    $items .= '<img src="' . $testimonial_image[0] . '" width="' . $testimonial_image[1] . '" height="' . $testimonial_image[2] . '" alt="' . $testimonial_cite . '" />';
                    $items .= '<div class="cite-text has-cite-image"><span class="cite-name">' . $testimonial_cite . '</span><span class="cite-subtext">' . $testimonial_cite_subtext . '</span></div>';
                } else {
                    $items .= '<div class="cite-text"><span class="cite-name">' . $testimonial_cite . '</span><span class="cite-subtext">' . $testimonial_cite_subtext . '</span></div>';
                }
                $items .= '</div>';
                $items .= '</li>';

            endwhile;

            wp_reset_postdata();

            if ( $showcase == "yes" ) { 
                $items .= '</ul></div></div>';
            } else {
                $items .= '</ul>';    
            }

            if ( $page_link == "yes" ) {
                global $sf_options;
                $testimonials_page = __( $sf_options['testimonial_page'], 'swift-framework-plugin' );
                $testimonials_page = apply_filters('wpml_object_id', $testimonials_page, 'page', true);
                $testimonial_link_icon = apply_filters( 'spb_testimonial_view_all_icon', '<i class="ssnavigate-right"></i>' );
                if ( $testimonials_page ) {
                    $items .= '<a href="' . get_permalink( $testimonials_page ) . '" class="read-more"><span>' . __( "More", 'swift-framework-plugin' ) . '</span>' . $testimonial_link_icon . '</a>';
                }
            }

            $width    = spb_translateColumnWidthToSpan( $width );
            $el_class = $this->getExtraClass( $el_class );

            $el_class .= ' testimonial';

            $output .= "\n\t" . '<div class="spb_testimonial_carousel_widget carousel-asset spb_content_element ' . $width . $el_class . '">';
            $output .= "\n\t\t" . '<div class="spb-asset-content">';
            if ( $showcase == "yes" ) { 
                $output .= "\n\t\t" . '<div class="title-wrap center-title clearfix">';
            } else {
                $output .= "\n\t\t" . '<div class="title-wrap clearfix">';
            }
            if ( $title != '' && $showcase == "yes" ) {
                $output .= '<h2 class="spb-heading"><span>' . $title . '</span></h2>';
            } else if ( $title != '' ) {
                $output .= '<h3 class="spb-heading"><span>' . $title . '</span></h3>';
            }
            if ( $showcase == "no" ) { 
            $output .= spb_carousel_arrows();
            }
            $output .= '</div>';

            $output .= "\n\t\t\t\t" . $items;
            $output .= "\n\t\t" . '</div> ' . $this->endBlockComment( '.spb_wrapper' );
            $output .= "\n\t" . '</div> ' . $this->endBlockComment( $width );

            $output = $this->startRow( $el_position, '', $fw_mode ) . $output . $this->endRow( $el_position, '', $fw_mode );

            global $sf_include_carousel, $sf_include_isotope;
            $sf_include_carousel = true;
            $sf_include_isotope  = true;

            return $output;
        }
    }


    /* PARAMS
    ================================================== */
    $params = array(
            array(
                "type"        => "textfield",
                "heading"     => __( "Widget title", 'swift-framework-plugin' ),
                "param_name"  => "title",
                "value"       => "",
                "description" => __( "Heading text. Leave it empty if not needed.", 'swift-framework-plugin' )
            ),
            array(
                "type"        => "textfield",
                "class"       => "",
                "heading"     => __( "Number of items", 'swift-framework-plugin' ),
                "param_name"  => "item_count",
                "value"       => "6",
                "description" => __( "The number of testimonials to show per page. Leave blank to show ALL testimonials.", 'swift-framework-plugin' )
            ),
            array(
                "type"        => "dropdown",
                "heading"     => __( "Testimonials Order", 'swift-framework-plugin' ),
                "param_name"  => "order",
                "value"       => array(
                    __( 'Random', 'swift-framework-plugin' ) => "rand",
                    __( 'Latest', 'swift-framework-plugin' ) => "date"
                ),
                "description" => __( "Choose the order of the testimonials.", 'swift-framework-plugin' )
            ),
            array(
                "type"        => "select-multiple",
                "heading"     => __( "Testimonials category", 'swift-framework-plugin' ),
                "param_name"  => "category",
                "value"       => sf_get_category_list( 'testimonials-category' ),
                "description" => __( "Choose the category for the testimonials.", 'swift-framework-plugin' )
            ),
            array(
                "type"        => "buttonset",
                "heading"     => __( "Testimonials page link", 'swift-framework-plugin' ),
                "param_name"  => "page_link",
                "value"       => array(
                    __( 'No', 'swift-framework-plugin' )  => "no",
                    __( 'Yes', 'swift-framework-plugin' ) => "yes"
                ),
                "buttonset_on"  => "yes",
                "description" => __( "Include a link to the testimonials page (which you must choose in the theme options).", 'swift-framework-plugin' )
            )
    );

    if ( spb_get_theme_name() == "uplift" ) {
        $params[] = array(
            "type"        => "buttonset",
            "heading"     => __( "Showcase Mode", 'swift-framework-plugin' ),
            "param_name"  => "showcase",
            "value"       => array(
                __( 'No', 'swift-framework-plugin' )  => "no",
                __( 'Yes', 'swift-framework-plugin' ) => "yes"
            ),
            "buttonset_on"  => "yes",
            "std"         => 'no',
            "description" => __( "If you enable this option, then the carousel will show 3 at once. The asset is required to be set to 1/1 width and no sidebar on the page.", 'swift-framework-plugin' )
        );
    }

    $params[] = array(
            "type"        => "textfield",
            "heading"     => __( "Extra class", 'swift-framework-plugin' ),
            "param_name"  => "el_class",
            "value"       => "",
            "description" => __( "If you wish to style this particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'swift-framework-plugin' )
        );


    /* SPBMap
    ================================================== */
    SPBMap::map( 'spb_testimonial_carousel', array(
        "name"          => __( "Testimonials Carousel", 'swift-framework-plugin' ),
        "base"          => "spb_testimonial_carousel",
        "class"         => "spb_testimonial_carousel spb_carousel",
        "icon"          => "icon-testimonials-carousel",
        "wrapper_class" => "clearfix",
        "params"        => $params
    ) );
