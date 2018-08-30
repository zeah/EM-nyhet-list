<?php 
defined('ABSPATH') or die('Blank Space');


/*
*/
final class Nyhet_edit {
	/* singleton */
	private static $instance = null;


	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();
		return self::$instance;
	}



	private function __construct() {

		add_action('admin_enqueue_scripts', array($this, 'admin_sands'));


		add_action('manage_nyhet_posts_columns', array($this, 'column_head'));
		add_filter('manage_nyhet_posts_custom_column', array($this, 'custom_column'));
		add_filter('manage_edit-nyhet_sortable_columns', array($this, 'sort_column'));
		add_action('pre_get_posts', array($this, 'sort_column_by'));

		
		/* metabox, javascript */
		add_action('add_meta_boxes_nyhet', array($this, 'create_meta'));
		/* hook for page saving/updating */
		add_action('save_post_nyhet', array($this, 'save'));


		add_filter('emtheme_doc', array($this, 'add_doc'), 99);

	}

	public function admin_sands() {
		wp_enqueue_style('em-nyhet-admin-style', NYHET_PLUGIN_URL . 'assets/css/admin/em-nyhet.css', array(), '1.0.1');
	}

	/**
	 * theme filter for populating documentation
	 * 	
	 * @param [array] $data [array passing through theme filter]
	 */
	public function add_doc($data) {
		$data['nyhet']['title'] = '<h1 id="nyhet">Nyheter (Plugin)</h1>';

		$data['nyhet']['index'] = '<li><h2><a href="#nyhet">Nyheter (Plugin)</a></h2>
											<ul class="emtheme-documentation-alpha">
												<li><a href="#nyhet-editor">Editor</a></li>
												<li><a href="#nyhet-shortcode">Shortcode</a></li>
												<li><a href="#nyhet-aldri">Aldri vis</a></li>
												<li><a href="#nyhet-sort">Sorting order</a></li>
												<li><a href="#nyhet-overview">Overview</a></li>
												<li><a href="#nyhet-tags">Html classes</a></li>
												<li><a href="#nyhet-examples">Shortcode Examples</a></li>
											</ul>
										</li>';
		$data['nyhet']['info'] = '<li id="nyhet-editor"><h2>Editor</h2>
										<ul>
											<li>Meta
												<p>If fields are empty, title will fall back on post title 
												<br>and text will fall back on post excerpt.</p>

												<p>If either field is set to "none" then 
												<br>the field will not show up at all on front-end.</p>
											</li>
										</ul>
									</li>
									<li id="nyhet-shortcode"><h2>Shortcodes</h2>
										<ul>
											<li><b>[nyhet]</b>
											<p>Will show all nyheter.</p></li>

											<li><b>[nyhet nr=x]</b>
											<p>Will return x number of nyheter. Default is all.</p></li>

											<li><b>[nyhet name="xx,yy"]</b>
											<p>If name attribute is used, then post type can be post, page and nyhet.
											<br>If a post/page/nyhet share slug-name with another, then both will be shown.</p></li>
											
											<li><b>[nyhet nyhet=zz]</b>
											<p>Will show all nyheter from nyhet category.</p></li>

											<li><b>[nyhet colnr=x]</b>
											<p>Default value is 3, min is 1 and max is 6.</p></li>
											
											<li><b>[nyhet float=left/right]</b>
											<p>When float is set:
												<ul>
													<li>Width can be set and default value is 200px.</li>
													<li>Colnr is defaulted at 1 and max is 2.</li>
													<li>Floated elements have a 20px margin.
												</ul>
												Eks: [nyhet float=right width=500px]
											</p>
											</li>
											
											<li><b>[nyhet notitle notext]</b>
											<p>If notitle is set, then no titles will be shown.
											<br>Same goes for notext.</p></li>

											<li><b>[nyhet random=x]</b>
											<p>Will return a x random hits from your shortcode.
											<br>If value given is greater than results, then all will be returned.
											<br>Eks: [nyhet nyhet=frontpage random=5] will show 5 random nyheter from the category frontpage.</p></li>
											
										
										</ul>
									</li>

									<li id="nyhet-aldri"><h2>Aldri vis</h2>
										<p>If tagged, then the nyhet will never appear on the front-end.
										<br>Second choice will make it not show up in internal search.</p>
									</li>

									<li id="nyhet-sort"><h2>Sorting order</h2>
										<p>The nyheter will be shown with the lowest "Sort"-value first.
										<br>If of equal sort order, then newest date will be shown first.
										<br>When only showing a specific category on nyheter page, then the sort order column will reflect 
										<br>that category\'s sort order.</p>
									</li>

									<li id="nyhet-overview"><h2>Overview</h2>
										<p> The <a target="_blank" href="'.get_site_url().'/wp-admin/edit.php?post_type=nyhet&page=nyhet-overview">overview page</a> will show every post and page and whether or not there are
										<br>any lan shortcodes in them.
										<br>You can sort the columns alphabetically</p>
									</li>

									<li id="nyhet-tags"><h2>Html classes</h2>
										<ul>
											<li>.nyhet-ul
											<p>List element</p></li>

											<li>.nyhet-ul-floated
											<p>List element when floated.</p></li>

											<li>.nyhet-container
											<p>List item element (each post)</p></li>

											<li>.nyhet-link
											<p>Link surrounding logo, title and description.</p></li>

											<li>.nyhet-logo
											<p>The image tag.</p></li>

											<li>.nyhet-text
											<p>Container element for title and description.</p></li>

											<li>.nyhet-title
											<p>Element for title. (meta field or post title)</p></li>

											<li>.nyhet-description
											<p>Element for description. (meta field or excerpt)</p></li>
										</ul>
									</li>

									<li id="nyhet-examples"><h2>Shortcode Examples</h2>
										<ul>
											<li></li>
										</ul>
									</li>
										';

		return $data;
	}

	/**
	 * wp filter for adding columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function column_head($defaults) {
		$defaults['nyhet_sort'] = 'Sorting Order';
		return $defaults;
	}


	/**
	 * filter for populating columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function custom_column($column_name) {
		global $post;
		// echo $_SERVER['QUERY_STRING'];

		// echo parse_url()
		
		// echo print_r($q_out, true);

		if ($column_name == 'nyhet_sort') {
			$q_out = null;
			parse_str($_SERVER['QUERY_STRING'], $q_out);

			$meta = 'nyhet_sort';
			if (isset($q_out['nyhettype'])) $meta = $meta.'_'.$q_out['nyhettype'];

			$meta = get_post_meta($post->ID, $meta);
			
			if (isset($meta[0])) echo $meta[0];
		}
	}


	/**
	 * filter for sorting by columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function sort_column($columns) {
		$columns['nyhet_sort'] = 'nyhet_sort';
		return $columns;
	}


	public function sort_column_by($query) {
		
		if (!is_admin()) return;
		if (!$query->is_main_query()) return;
		if ($query->get('post_type') != 'nyhet') return;

		$sort = 'nyhet_sort';
		if ($query->get('nyhettype')) $sort .= '_'.$query->get('nyhettype');

		$query->set('order_by', 'meta_value');
		$query->set('meta_key', $sort);
	    $query->set('meta_type', 'numeric');

	}

	/*
		creates wordpress metabox
		adds javascript
	*/
	public function create_meta() {

		/* lan info meta */
		add_meta_box(
			'nyhet_meta', // name
			'Nyhet Info (Shortcode)', // title 
			array($this,'create_meta_box'), // callback
			'nyhet' // page
		);

		/* to show or not on front-end */
		add_meta_box(
			'nyhet_exclude',
			'Aldri vis',
			array($this, 'exclude_meta_box'),
			'nyhet',
			'side'
		);

	
		
		/* adding admin css and js */
		// wp_enqueue_style('em-nyhet-admin-style', NYHET_PLUGIN_URL . 'assets/css/admin/em-nyhet.css', array(), '1.0.1');
		wp_enqueue_script('em-nyhet-admin', NYHET_PLUGIN_URL . 'assets/js/admin/em-nyhet.js', array(), '1.0.1', true);
	}


	/*
		creates content in metabox
	*/
	public function create_meta_box($post) {
		wp_nonce_field('em'.basename(__FILE__), 'nyhet_nonce');

		$meta = get_post_meta($post->ID, 'nyhet_data');
		$sort = get_post_meta($post->ID, 'nyhet_sort');

		$tax = wp_get_post_terms($post->ID, 'nyhettype');

		$taxes = [];
		if (is_array($tax))
			foreach($tax as $t)
				array_push($taxes, $t->slug);

		$json = [
			'meta' => isset($meta[0]) ? $this->sanitize($meta[0]) : '',
			'nyhet_sort' => isset($sort[0]) ? floatval($sort[0]) : '',
			'tax'  => $taxes
		];

		$ameta = get_post_meta($post->ID);
		foreach($ameta as $key => $value) {
			$norsk = ['æ', 'ø', 'å'];
			$english = ['ae', 'o', 'a'];

			$key = str_replace($norsk, $english, $key);

			if (strpos($key, 'nyhet_sort_') !== false && isset($value[0])) $json[$key] = esc_html($value[0]);
		}

		wp_localize_script('em-nyhet-admin', 'nyhet_meta', json_decode(json_encode($json), true));
		echo '<div class="nyhet-meta-container"></div>';
	}
 

 	/**
 	 * [exclude_meta_box description]
 	 */
	public function exclude_meta_box() {
		global $post;

		$exclude = get_option('nyhet_exclude');
		if (!is_array($exclude)) $exclude = [];

		$exclude_serp = get_option('nyhet_exclude_serp');
		if (!is_array($exclude_serp)) $exclude_serp = [];


		echo '<p><input name="nyhet_exclude" id="nyhet_exc" type="checkbox"'.(in_array($post->ID, $exclude) ? ' checked' : '').'><label for="nyhet_exc">Nyhet vil ikke vises på front-end når boksen er markert.</label></p>
	  		  <p><input name="nyhet_exclude_serp" id="nyhet_exc_serp" type="checkbox"'.(in_array($post->ID, $exclude_serp) ? ' checked' : '').'><label for="nyhet_exc_serp">Ikke vis i internal SERP.</label></p>';
		// echo '<input name="nyhet_exclude" id="nyhet_exc" type="checkbox"'.(array_search($post->ID, $exclude) !== false ? ' checked' : '').'><label for="nyhet_exc">Nyhet vil ikke vises på front-end når boksen er markert.</label>';
	}



	/**
	 * wp action when saving
	 */
	public function save($post_id) {
		// post type is nyhet
		if (!get_post_type($post_id) == 'nyhet') return;

		// is on admin screen
		if (!is_admin()) return;

		// user is logged in and has permission
		if (!current_user_can('edit_posts')) return;

		// nonce is sent
		if (!isset($_POST['nyhet_nonce'])) return;

		// nonce is checked
		if (!wp_verify_nonce($_POST['nyhet_nonce'], 'em'.basename(__FILE__))) return;

		// saves to wp option instead of post meta
		// when adding
		$this->u_option('nyhet_exclude', $post_id);
		$this->u_option('nyhet_exclude_serp', $post_id);
		// if (isset($_POST['nyhet_exclude'])) {
		// 	$option = get_option('nyhet_exclude');

		// 	// to avoid php error
		// 	if (!is_array($option)) $option = [];

		// 	// if not already added
		// 	if (array_search($post_id, $option) === false) {

		// 		// if to add to collection
		// 		if (is_array($option)) {
		// 			array_push($option, intval($post_id));

		// 			update_option('nyhet_exclude', $option);
		// 		}
				
		// 		// if to create collection (of one)
		// 		else update_option('nyhet_exclude', [$post_id]);
		// 	}
		// }
		// // when removing
		// else {
		// 	$option = get_option('nyhet_exclude');

		// 	if (array_search($post_id, $option) !== false) {
		// 		unset($option[array_search($post_id, $option)]);
		// 		update_option('nyhet_exclude', $option);
		// 	}
		// }

		// data is sent, then sanitized and saved
		if (isset($_POST['nyhet_data'])) update_post_meta($post_id, 'nyhet_data', $this->sanitize($_POST['nyhet_data']));
		if (isset($_POST['nyhet_sort'])) update_post_meta($post_id, 'nyhet_sort', floatval($_POST['nyhet_sort']));

		// saving nyhet_sort_***
		foreach($_POST as $key => $po) {
			if (strpos($key, 'nyhet_sort_') !== false)
				update_post_meta($post_id, sanitize_text_field(str_replace(' ', '', $key)), floatval($po));
		}

	}


	/**
	 * updating wp options
	 * @param  [type] $data  var to be saved to
	 * @param  [type] $value data to be saved
	 */
	private function u_option($data, $value) {
		$option = get_option($data);
		if (!is_array($option)) $option = []; // to avoid php error
		
		$value = intval($value);

		if (isset($_POST[$data])) {

			// if not already added
			if (array_search($value, $option) === false) {

				// if to add to collection
				if (is_array($option)) {
					array_push($option, $value);
					update_option($data, $option);
				}
				
				// if to create collection (of one)
				else update_option($data, [$value]);
			}
		}
		// when removing
		else {
			// $option = get_option($data);

			if (array_search($value, $option) !== false) {
				unset($option[array_search($value, $option)]);
				update_option($data, $option);
			}
		}
	}

	/*
		recursive sanitizer
	*/
	private function sanitize($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->sanitize($value);

		return $d;
	}
}