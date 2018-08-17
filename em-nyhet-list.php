<?php
/*
Plugin Name: EM Nyhet List
Description: Nyheter i liste
Version: 0.0.1
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