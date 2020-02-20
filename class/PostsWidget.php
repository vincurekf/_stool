<?php

// Register and load the widget
add_action('widgets_init', '_stoolLoadPostsWidget');
function _stoolLoadPostsWidget()
{
	register_widget('PostsWidget');
}

// Creating the widget
class PostsWidget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			// Base ID of your widget
			'_stool_posts_widget',
			// Widget name will appear in UI
			'Stool ' . __('Posts'),
			// Widget description
			array('description' => 'Stool Posts Widget')
		);
		//
		add_shortcode('_posts', array($this, 'widgetShortCut'));
		//
	}

	public $default_atts = array(
		"before_title" => '<h2 class="widget-title">',
		"after_title" => '</h2>'
	);

	public function widgetShortCut($atts)
	{
		//
		$atts = shortcode_atts(
			array(
				'title' => '',
				'count' => 6,
				'sortby' => 'date',
				'sortway' => 'desc',
				'ignoresticky' => false,
				'ignorequeried' => false,
				'category' => 'all',
				'type' => 'all',
				'template' => null
			),
			$atts,
			'_posts'
		);
		//

		return $this->widget(array(), $atts);
		//
	}

	// Widget front-end
	public function widget($_args, $instance)
	{
		//
		$title = apply_filters('widget_title', $instance['title']);
		$title_link = isset($instance['title_link']) ? $instance["title_link"] : null;
		$count = isset($instance['count']) ? $instance["count"] : -1;
		$sortby = isset($instance['sortby']) ? $instance["sortby"] : 'date';
		$sortway = isset($instance['sortway']) ? $instance["sortway"] : 'desc';
		$ignoresticky = isset($instance['ignoresticky']) ? "on" == $instance["ignoresticky"] : false;
		$ignorequeried = isset($instance['ignorequeried']) ? "on" == $instance["ignorequeried"] : false;
		$posts_category = isset($instance['posts_category']) ? $instance['posts_category'] : 'all';
		$posts_type = isset($instance['posts_type']) ? $instance['posts_type'] : 'all';
		$custom_template = isset($instance['custom_template']) ? $instance['custom_template'] : null;
		//
		if (isset($_args['before_widget'])) {
			echo $_args['before_widget'];
		}
		//
		if (!empty($title)) {
			echo isset($_args['before_title']) ? $_args['before_title'] : '<h2 class="widget-title">';
			if (!is_null($title_link)) {
				echo '<a href="' . $title_link . '">';
			}
			echo $title;
			if (!is_null($title_link)) {
				echo '</a>';
			}
			echo isset($_args['after_title']) ? $_args["after_title"] : '</h2>';
		}
		//
		$query_args = array(
			"count" => $count,
			"custom_params" => array(
				"order" => $sortway,
				"ignore_sticky_posts" => $ignoresticky
			)
		);
		//
		$query_args["noqueried"] = $ignorequeried;
		//
		if ("all" != $posts_category) {
			$query_args["custom_params"]["cat"] = $posts_category;
		}
		//
		if ("all" != $posts_type) {
			$query_args["post_type"] = array($posts_type);
		} else {
			$types = _stool\Posts::$types;
			$types = !is_null($types) ? $types : array();
			$post_types = array();
			foreach ($types as $p_type) {
				array_push($post_types, $p_type["slug"]);
			}
			array_push($post_types, "post");
			$query_args["post_type"] = $post_types;
		}

		//
		if ("post_views" == $sortby) {
			$query_args["custom_params"]["orderby"] = $sortby;
			$query_args["custom_params"]["suppress_filters"] = false;
			$query_args["custom_params"]["fields"] = "";
		} else {
			$query_args["custom_params"]["orderby"] = $sortby;
		}
		//
		$widgetposts = _stool\Posts::get($query_args);
		//
		if (!is_null($custom_template) && !empty($custom_template) && locate_template(array($custom_template . '.php')) != '') {
			while ($widgetposts->have_posts()):
				$widgetposts->the_post();
				get_template_part($custom_template, get_post_type());
			endwhile;
		} else {
			_stool\Tools::jsDebug("warning", "Template not found", "Could not find template '" . $custom_template . "'. Using default.");
			while ($widgetposts->have_posts()):
				$widgetposts->the_post();
				echo '<article id="post-' . get_the_ID() . '" class="post post-sidebar post-type-' . get_post_type() . '">';
				echo '<header class="entry-header">';
				if (has_post_thumbnail()) {
					echo '<a href="' . esc_url(get_permalink()) . '">';
					the_post_thumbnail('post-thumbnail');
					echo '</a>';
				}
				echo '<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . get_the_title() . '</a></h2>';
				echo '</header><!-- .entry-header -->';
				echo '</article><!-- #post-' . get_the_ID() . ' -->';
			endwhile;
		}
		//
		if (isset($_args['after_widget'])) {
			echo $_args['after_widget'];
		}
	}

	// Widget back-end
	public function form($instance)
	{
		//
		$title = isset($instance['title']) ? $instance["title"] : '';
		$title_link = isset($instance['title_link']) ? $instance["title_link"] : '';
		$count = isset($instance['count']) ? $instance["count"] : 0;
		$sortby = isset($instance['sortby']) ? $instance["sortby"] : 'date';
		$sortway = isset($instance['sortway']) ? $instance["sortway"] : 'desc';
		$ignoresticky = isset($instance['ignoresticky']) ? "on" == $instance["ignoresticky"] : false;
		$ignorequeried = isset($instance['ignorequeried']) ? "on" == $instance["ignorequeried"] : false;
		$posts_category = isset($instance['posts_category']) ? $instance['posts_category'] : null;
		$posts_type = isset($instance['posts_type']) ? $instance['posts_type'] : null;
		$custom_template = isset($instance['custom_template']) ? $instance['custom_template'] : null;

		$templates = array();

		// Widget admin form
		$types = _stool\Posts::$types;
		$types = !empty($types) ? $types : array();
		//
		array_unshift($types, array(
			"slug" => "post",
			"main" => __('Post')
		));
		//
		//
		$sorting = array(
			"date" => __('Date'),
			"rand" => __('Random'),
			"modified" => __('Modified'),
			"title" => __('Title')
		);
		if (function_exists('pvc_post_views')) {
			$sorting["post_views"] = __('Post views');
		} //
		//
		//
		$categories = array();
		foreach ($types as $_type) {
			$_args = array(
				"hide_empty" => false
			);
			if ("post" != $_type["slug"]) {
				$_args["taxonomy"] = $_type["slug"] . "_category";
			}

			//
			$_cats = get_categories($_args);
			//_stool\Tools::debug($_cats, $_type["slug"] . "_cats");
			//
			foreach ($_cats as $key => $_cat) {
				$_cat->post_type = $_type;
				$_cat->list_name = $_type["main"] . ": " . $_cat->name;
				$categories[] = $_cat;
			}
			//
		}
		//
		$uuid = _stool\Tools::uuid();

		//
		?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('title_link'); ?>"><?php _e('Title link'); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('title_link'); ?>" name="<?php echo $this->get_field_name('title_link'); ?>" type="text" value="<?php echo esc_attr($title_link); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count'); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
      </p>
      <div data-stool-post-selects="<?php echo $uuid; ?>">
        <p>
          <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Post type'); ?>:</label>
          <select data-stool-select="post-type" class="widefat" id="<?php echo $this->get_field_id('posts_type'); ?>" name="<?php echo $this->get_field_name('posts_type'); ?>">
            <option value="all"<?php echo $posts_type == "all" ? " selected" : ""; ?>><?php _e('All'); ?></option>
            <?php foreach ($types as $_type) {
            	echo '<option value="' . $_type["slug"] . '"' . ($_type["slug"] == $posts_type ? " selected" : "") . '>' . __($_type["main"]) . '</option>';
            } ?>
          </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Post category'); ?>:</label>
          <select data-stool-select="post-category" class="widefat" id="<?php echo $this->get_field_id('posts_category'); ?>" name="<?php echo $this->get_field_name('posts_category'); ?>">
            <option value="all"<?php echo $posts_category == "all" ? " selected" : ""; ?>><?php _e('All'); ?></option>
            <?php foreach ($categories as $_category) { ?>
              <?php echo '<option data-stool-cat="' .
              	$_category->term_id .
              	'" data-stool-post-type="' .
              	$_category->post_type["slug"] .
              	'" class="stool-box-' .
              	$uuid .
              	'" value="' .
              	$_category->term_id .
              	'"' .
              	($_category->term_id == $posts_category ? " selected" : "") .
              	'>' .
              	__($_category->list_name) .
              	' (' .
              	$_category->count .
              	')</option>'; ?>
            <?php } ?>
          </select>
        </p>
      </div>
      <p>
        <label for="<?php echo $this->get_field_id('sortway'); ?>"><?php _e('Sorting'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('sortway'); ?>" name="<?php echo $this->get_field_name('sortway'); ?>">
          <option value="asc"<?php echo $sortway == "asc" ? " selected" : ""; ?>><?php _e('Ascending'); ?></option>
          <option value="desc"<?php echo $sortway == "desc" ? " selected" : ""; ?>><?php _e('Descending'); ?></option>
        </select>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sort by'); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>">
          <?php foreach ($sorting as $key => $option) { ?>
            <option value="<?php echo $key; ?>" <?php echo $sortby == $key ? " selected" : ""; ?>><?php echo $option; ?></option>
          <?php } ?>
        </select>
      </p>
      <p>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('ignoresticky'); ?>" name="<?php echo $this->get_field_name('ignoresticky'); ?>" <?php echo $ignoresticky ? 'checked' : ''; ?> >
        <label for="<?php echo $this->get_field_id('ignoresticky'); ?>">Ignorovat připnuté příspěvky</label>
      </p>
      <p>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('ignorequeried'); ?>" name="<?php echo $this->get_field_name('ignorequeried'); ?>" <?php echo $ignorequeried ? 'checked' : ''; ?> >
        <label for="<?php echo $this->get_field_id('ignorequeried'); ?>">Ignorovat již zobrazené příspěvky</label>
      </p>
      <hr>
      <h4>Expert settings</h4>
      <p>Please edit this ony if you know what you are doing.</p>
      <p>
        <label for="<?php echo $this->get_field_id('custom_template'); ?>"><?php _e('Custom post template'); ?> (e.g. "template-parts/content-post"):</label>
        <input class="widefat" id="<?php echo $this->get_field_id('custom_template'); ?>" name="<?php echo $this->get_field_name('custom_template'); ?>" type="text" value="<?php echo esc_attr($custom_template); ?>" />
      </p>
    <?php
	}

	// Updating widget replacing old instances with new
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
		$instance['title_link'] = !empty($new_instance['title_link']) ? strip_tags($new_instance['title_link']) : '';
		$instance['count'] = !empty($new_instance['count']) ? strip_tags($new_instance['count']) : '';
		$instance['sortby'] = !empty($new_instance['sortby']) ? strip_tags($new_instance['sortby']) : '';
		$instance['sortway'] = !empty($new_instance['sortway']) ? strip_tags($new_instance['sortway']) : '';
		$instance['ignoresticky'] = !empty($new_instance['ignoresticky']) ? strip_tags($new_instance['ignoresticky']) : '';
		$instance['ignorequeried'] = !empty($new_instance['ignorequeried']) ? strip_tags($new_instance['ignorequeried']) : '';
		$instance['posts_type'] = !empty($new_instance['posts_type']) ? strip_tags($new_instance['posts_type']) : '';
		$instance['posts_category'] = !empty($new_instance['posts_category']) ? strip_tags($new_instance['posts_category']) : '';
		$instance['custom_template'] = !empty($new_instance['custom_template']) ? $new_instance['custom_template'] : '';
		return $instance;
	}
}
