<?php
/*
Plugin Name: EM Nyhet List
Description: Nyheter i liste
Version: 1.0.2
GitHub Plugin URI: zeah/EM-nyhet-list
*/

defined('ABSPATH') or die('Blank Space');

// constant for plugin location
define('NYHET_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once 'inc/nyhet-posttype.php';
require_once 'inc/nyhet-shortcode.php';

function init_emnyhetlist() {
	Nyhet_posttype::get_instance();
	Nyhet_shortcode::get_instance();
}
add_action('plugins_loaded', 'init_emnyhetlist');


register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'nyhet_flush_rewrites' );

/* flusing rewrite rules when activating the plugin */
function nyhet_flush_rewrites() {
	// call your CPT registration function here (it should also be hooked into 'init')
	Nyhet_posttype::get_instance();

	flush_rewrite_rules();
}