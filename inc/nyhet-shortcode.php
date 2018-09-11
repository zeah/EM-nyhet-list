<?php 
defined( 'ABSPATH' ) or die( 'Blank Space' ); 

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
		global $post;
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$type = 'nyhet';
		if ($atts['name']) $type = get_post_types(['public' => true]);

		$ppp = -1;

		if ($atts['nr']) $ppp = intval($atts['nr']);

		$args = [
			'post_type' 		=> $type,
			'posts_per_page' 	=> $ppp
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

		// news to ignore
		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		

		// stuff to exclude
		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;
		if ($post->post_type == 'nyhet') {
			if (is_array($args['post__not_in'])) array_push($args['post__not_in'], $post->ID);
			else $args['post__not_in'] = [$post->ID];
		}
		// wp_die('<xmp>'.print_r($post, true).'</xmp>');
		

		$exclude = get_option('nyhet_exclude');


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

		// $colone = false;
		// if ($floated && intval($atts['colnr']) === 1) $colone = true;

		// $coltwo = false;
		// if ($floated && intval($atts['colnr']) === 2) $coltwo = true;

		$floated_col = false;
		if ($floated)
			switch (intval($atts['colnr'])) {
				case 1: $floated_col = ' nyhet-ul-colone'; break;
				case 2: $floated_col = ' nyhet-ul-coltwo'; break;
			}


		if (wp_is_mobile()) $html = '<ul class="nyhet-ul">';
		else $html = '<ul class="nyhet-ul'.($floated ? ' nyhet-ul-floated'.$floated_col : '').'" style="grid-template-columns:'.$columns.'; -ms-grid-columns: '.$columns.';'.($floated ? (' float:'.$floated.'; width: '.($width ? $width : '20').'rem; margin: 0 2rem;') : '').'">';
		// $html = '<ul class="nyhet-ul'.($floated ? ' nyhet-ul-floated' : '').($colone ? ' nyhet-ul-colone' : '').($coltwo ? ' nyhet-ul-coltwo' : '').'" style="grid-template-columns:'.$columns.'; -ms-grid-columns: '.$columns.';'.($floated ? (' float:'.$floated.'; width: '.($width ? $width : '20').'rem; margin: 2rem;') : '').'">';

		// $html .= sizeof($posts);
		// else $html .= $this->get_html($posts);

		$first = true;
		if ($atts['colnr'] == 1) $first = false;
		elseif ($atts['float']) $first = false;

		$title = true;
		if (in_array('notitle', $atts)) $title = false;

		$text = true;
		if (in_array('notext', $atts)) $text = false;


		// RANDOM random or random=x
		if ($atts['random']) $posts = $this->get_random($posts, intval($atts['random']));
		elseif (in_array('random', $atts) && $atts['random'] == '') $posts = $this->get_random($posts);


		// if (in_array('random', $atts) && $atts['random'] == '') $posts = [$posts[rand(0, sizeof($posts)-1)]];
		// elseif ($atts['random']) {

		// 	$random = intval($atts['random']);

		// 	// max value of random: size of array of Posts
		// 	if ($random > sizeof($posts)) $random = sizeof($posts);

		// 	// min value of random: 1
		// 	if ($random < 1) $random = 1;

		// 	// getting randoms
		// 	$p = [];
		// 	for ($i = 0; $i < $random; $i++) {
		// 		$r = array_rand($posts);

		// 		array_push($p, $posts[$r]);
		// 		unset($posts[$r]);
		// 	}

		// 	// setting random posts
		// 	$posts = $p;
		// }

		wp_die('<xmp>'.print_r($posts, true).'</xmp>');

		$html .= $this->get_html($posts, $first, $title, $text);

		$html .= '</ul>';

		return $html;
	}


	/**
	 * adding sands to head
	 */
	public function add_css() {
        wp_enqueue_style('em-nyhet-style', NYHET_PLUGIN_URL.'assets/css/pub/em-nyhet.css', array(), '1.0.3', '(min-width: 801px)');
        wp_enqueue_style('em-nyhet-mobile', NYHET_PLUGIN_URL.'assets/css/pub/em-nyhet-mobile.css', array(), '1.0.3', '(max-width: 800px)');
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
			$html .= '<li class="nyhet-container'.($first ? ' nyhet-container-first' : '').'">';
			// $html .= '<li class="nyhet-container"'.($first ? ' style="grid-column: 1 / span 2;"' : '').'>';

			$first = false;

			$html .= '<a class="nyhet-link" href="'.get_permalink($p).'">';


			$thumbnail = get_the_post_thumbnail_url($p);

			if ($thumbnail) $html .= '<img class="nyhet-logo" src="'.esc_url($thumbnail).'">';


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

		$exclude_serp = get_option('nyhet_exclude_serp');
		if (!is_array($exclude_serp)) $exclude_serp = [];

		if (in_array($post->ID, $exclude_serp)) return $data;

		// $html['html'] = $this->get_html([$post]);

		// link
		$html['link'] = get_post_permalink($post->ID);

		// thumbnail
		$html['thumbnail'] = get_the_post_thumbnail($post, 'post-thumbnail');

		// title
		$html['title'] = $post->post_title;

		// excerpt
		$html['excerpt'] = get_the_excerpt($post);

		array_push($data, $html);
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return $data;
	}


	private function get_random($posts, $nr = 1) {
		// global $post;

		// $id = $post->ID;

		if (!is_array($posts)) return $posts;

		if ($nr < 1) $nr = 1;
		if ($nr > sizeof($posts)) $nr = sizeof($posts); 

		$p = [];

		for ($i = 0; $i < $nr; $i++) {

			$r = array_rand($posts);

			if ($posts[$r]) array_push($p, $posts[$r]);
			// if ($posts[$r] && $posts[$r]->ID !== $id) array_push($p, $posts[$r]);
			
			unset($posts[$r]);
		}

		return $p;
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