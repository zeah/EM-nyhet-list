<?php 

/**
 * WP Shortcodes
 */
final class Nyhet_shortcode {
	/* singleton */
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}


	/**
	 * hooks for wp
	 */
	private function wp_hooks() {

		// loan list
		if (!shortcode_exists('nyhet')) add_shortcode('nyhet', array($this, 'add_shortcode'));
		else add_shortcode('emnyhet', array($this, 'add_shortcode'));

		// loan thumbnail
		// if (!shortcode_exists('nyhet-bilde')) add_shortcode('nyhet-bilde', array($this, 'add_shortcode_bilde'));
		// else add_shortcode('emnyhet-bilde', array($this, 'add_shortcode_bilde'));

		// // loan button
		// if (!shortcode_exists('nyhet-bestill')) add_shortcode('nyhet-bestill', array($this, 'add_shortcode_bestill'));
		// else add_shortcode('emnyhet-bestill', array($this, 'add_shortcode_bestill'));


		add_filter('search_first', array($this, 'add_serp'));
	}


	/**
	 * returns a list of loans
	 */
	public function add_shortcode($atts, $content = null) {

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$args = [
			'post_type' 		=> 'emnyhet',
			'posts_per_page' 	=> -1,
			'orderby'			=> [
										'meta_value_num' => 'ASC',
										'title' => 'ASC'
								   ],
			'meta_key'			=> 'emnyhet_sort'.($atts['nyhet'] ? '_'.sanitize_text_field($atts['nyhet']) : '')
		];


		$type = false;
		if (isset($atts['nyhet'])) $type = $atts['nyhet'];
		if ($type)
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'emnyhettype',
						'field' => 'slug',
						'terms' => sanitize_text_field($type)
					)
				);


		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		
		$exclude = get_option('emnyhet_exclude');

		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;

		$posts = get_posts($args);	

		$sorted_posts = [];
		if ($names) {
			foreach(explode(',', preg_replace('/ /', '', $atts['name'])) as $n)
				foreach($posts as $p) 
					if ($n === $p->post_name) array_push($sorted_posts, $p);
		
			$posts = $sorted_posts;
		}
				

		$html = $this->get_html($posts);

		return $html;
	}


	/**
	 * adding sands to head
	 */
	public function add_css() {
        wp_enqueue_style('em-nyhet-style', NYHET_PLUGIN_URL.'assets/css/pub/em-nyhet.css', array(), '1.0.1', '(min-width: 801px)');
        wp_enqueue_style('em-nyhet-mobile', NYHET_PLUGIN_URL.'assets/css/pub/em-nyhet-mobile.css', array(), '1.0.1', '(max-width: 800px)');
	}


	/**
	 * returns the html of a list of loans
	 * @param  WP_Post $posts a wp post object
	 * @return [html]        html list of loans
	 */
	private function get_html($posts) {
		$html = '<ul class="emnyhet-ul">';

		foreach ($posts as $p) {
			
			$meta = get_post_meta($p->ID, 'emnyhet_data');

			// skip if no meta found
			if (isset($meta[0])) $meta = $meta[0];
			else continue;

			// sanitize meta
			$meta = $this->esc_kses($meta);

			// grid container
			$html .= '<li class="emnyhet-container">';

			

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}



	/**
	 * wp filter for adding to internal serp
	 * array_push to $data
	 * $data['html'] to be printed
	 * 
	 * @param [Array] $data [filter]
	 */
	public function add_serp($data) {
		global $post;

		if ($post->post_type != 'emnyhet') return $data;

		$exclude = get_option('emnyhet_exclude');

		if (!is_array($exclude)) $exclude = [];

		if (in_array($post->ID, $exclude)) return $data;

		$html['html'] = $this->get_html([$post]);

		array_push($data, $html);
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return $data;
	}



	/**
	 * kisses the data
	 * recursive sanitizer
	 * 
	 * @param  Mixed $data Strings or Arrays
	 * @return Mixed       Kissed data
	 */
	private function esc_kses($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->esc_kses($value);

		return $d;
	}
}