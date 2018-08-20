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
		else add_shortcode('nyhet', array($this, 'add_shortcode'));

		// loan thumbnail
		// if (!shortcode_exists('nyhet-bilde')) add_shortcode('nyhet-bilde', array($this, 'add_shortcode_bilde'));
		// else add_shortcode('nyhet-bilde', array($this, 'add_shortcode_bilde'));

		// // loan button
		// if (!shortcode_exists('nyhet-bestill')) add_shortcode('nyhet-bestill', array($this, 'add_shortcode_bestill'));
		// else add_shortcode('nyhet-bestill', array($this, 'add_shortcode_bestill'));


		add_filter('search_first', array($this, 'add_serp'));
	}


	/**
	 * returns a list of loans
	 */
	public function add_shortcode($atts, $content = null) {
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$type = 'nyhet';
		if ($atts['name']) $type = get_post_types(['public' => true]);


		$args = [
			'post_type' 		=> $type,
			'posts_per_page' 	=> -1
		];

		if (!$atts['name']) {
			$args['orderby'] = [
				'meta_value_num' => 'ASC',
				'date' => 'DESC'
			];

			$args['meta_key'] = 'nyhet_sort'.($atts['nyhet'] ? '_'.sanitize_text_field($atts['nyhet']) : '');
		}


		$type = false;
		if (isset($atts['nyhet'])) $type = $atts['nyhet'];
		if ($type)
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'nyhettype',
						'field' => 'slug',
						'terms' => sanitize_text_field($type)
					)
				);


		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		
		$exclude = get_option('nyhet_exclude');

		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;

		$posts = get_posts($args);

		// wp_die('<xmp>'.print_r($args, true).'</xmp>');

		$sorted_posts = [];
		if ($names) {
			foreach(explode(',', preg_replace('/ /', '', $atts['name'])) as $n)
				foreach($posts as $p) 
					if ($n === $p->post_name) array_push($sorted_posts, $p);
		
			$posts = $sorted_posts;
		}
		
		// $columns = ' 1fr 1fr 1fr';

		if (!$atts['colnr']) $atts['colnr'] = 3;
		elseif (intval($atts['colnr']) > 6) $atts['colnr'] = 6;

		$float = false;
		if ($atts['float']) {
			switch (intval($atts['colnr'])) {
				case 2: $atts['colnr'] = 2; break;
				default: $atts['colnr'] = 1;
			}

			switch($atts['float']) {
				case 'left': $floated = 'left'; break;
				case 'right': $floated = 'right';
			}
		}

		$columns = '';

		switch (intval($atts['colnr'])) {
			case 6: $columns = ' 1fr';
			case 5: $columns .= ' 1fr';
			case 4: $columns .= ' 1fr';
			case 3: $columns .= ' 1fr';
			case 2: $columns .= ' 1fr';
			case 1: $columns .= ' 1fr';
		}


		$width = false;

		if ($atts['width']) $width = intval($atts['width']) / 10;


		$html = '<ul class="nyhet-ul" style="grid-template-columns:'.$columns.';'.($floated ? (' float:'.$floated.'; width: '.($width ? $width : '20').'rem;') : '').'">';

		// else $html .= $this->get_html($posts);

		$first = true;
		if ($atts['colnr'] == 1) $first = false;
		elseif ($atts['float']) $first = false;

		$title = true;
		if (in_array('notitle', $atts)) $title = false;

		$text = true;
		if (in_array('notext', $atts)) $text = false;


		// RANDOM random or random=x
		if ($atts['random'] == '') $posts = [$posts[rand(0, sizeof($posts)-1)]];
		elseif ($atts['random']) {

			$random = intval($atts['random']);

			// max value of random: size of array of Posts
			if ($random > sizeof($posts)) $random = sizeof($posts);

			// min value of random: 1
			if ($random < 1) $random = 1;

			// getting randoms
			$p = [];
			for ($i = 0; $i < $random; $i++) {
				$r = array_rand($posts);

				array_push($p, $posts[$r]);
				unset($posts[$r]);
			}

			// setting random posts
			$posts = $p;
		}



		$html .= $this->get_html($posts, $first, $title, $text);

		$html .= '</ul>';

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
	private function get_html($posts, $first = true, $title = true, $text = true) {

		$html = '';
		// $first = true;

		foreach ($posts as $p) {

			setup_postdata($p);

			$meta = get_post_meta($p->ID, 'nyhet_data');

			if (isset($meta[0])) $meta = $meta[0];
			else $meta = [];

			// sanitize meta
			$meta = $this->esc_kses($meta);

			// grid container
			$html .= '<li class="nyhet-container"'.($first ? ' style="grid-column: 1 / span 2;"' : '').'>';

			$first = false;

			$html .= '<a class="nyhet-link" href="'.get_permalink($p).'">';

			$html .= '<img class="nyhet-logo" src="'.esc_url(get_the_post_thumbnail_url($p)).'">';


			$show = false;

			if ($title || $text) $show = true;
			if ($meta['title'] == 'none' && $meta['text'] == 'none') $show = false;

			if ($show) {
				$html .= '<span class="nyhet-text">';

				if ($title && $meta['title'] != 'none') $html .= '<span class="nyhet-title">'.($meta['title'] ? $meta['title'] : sanitize_text_field($p->post_title)).'</span>';

				if ($text && $meta['text'] != 'none') $html .= '<span class="nyhet-description">'.($meta['text'] ? $meta['text'] : get_the_excerpt($p)).'</span>';

				$html .= '</a>';

				$html .= '</span>';
			}
			// $html .= print_r($p, true);

			$html .= '</li>';

			wp_reset_postdata();
		}


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

		if ($post->post_type != 'nyhet') return $data;

		$exclude = get_option('nyhet_exclude');

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