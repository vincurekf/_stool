<?php

// Register and load the widget
add_action('widgets_init', '_stool_load_tags_widget');
function _stool_load_tags_widget()
{
	register_widget('TagsWidget');
}

// Creating the widget
class TagsWidget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			// Base ID of your widget
			'_stool_tag_widget',
			// Widget name will appear in UI
			'Stool ' . __('Tags'),
			// Widget description
			array('description' => 'Stool ' . __('Tags') . ' Widget')
		);
	}

	// Creating widget front-end
	public function widget($args, $instance)
	{
		$title = apply_filters('widget_title', $instance['title']);
		$post_type = $instance['post_type'];
		$hide_empty = isset($instance['hide_empty']) ? "on" == $instance["hide_empty"] : false;
		$show_count = isset($instance['show_count']) ? "on" == $instance["show_count"] : false;
		//
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		//
		$tags = get_terms(array(
			'taxonomy' => $post_type . '_tag',
			'hide_empty' => $hide_empty
		));
		//
		if ($tags && !is_wp_error($tags)) {
			//_stool\Tools::debug($tags);
			usort($tags, function ($a, $b) {
				return $b->count - $a->count;
			});
			//
			$max = 0;
			foreach ($tags as $tag) {
				if ($tag->count > $max) {
					$max = $tag->count;
				}
			}
			$step = $max / 5;
			//
			//
			echo '<ul class="taxonomy-cloud stool-widget-tag">';
			foreach ($tags as $tag) {
				$count = '';
				if ($show_count && $tag->count > 0) {
					$count = ' <span class="count">' . $tag->count . '</span>';
				}

				//
				// adjust the class based on the size
				if ($tag->count > $step * 4) {
					$class = 'x-large';
				} elseif ($tag->count > $step * 3) {
					$class = 'large';
				} elseif ($tag->count > $step * 2) {
					$class = 'medium';
				} elseif ($tag->count > $step) {
					$class = 'small';
				} else {
					$class = 'x-small ';
				}
				//
				if (isset($tag->term_id) && isset($tag->name)) {
					echo '<li class="tag tag-item tag-' . $tag->term_id . ' stool-tag ' . $class . '"><a href="' . get_term_link($tag) . '">' . $tag->name . $count . '</a></li>';
				}
			}
			echo '</ul>';
		} else {
			echo '<ul class="taxonomy-cloud"><li class="tag-item-none">' . __('No tags') . '</li></ul>';
		}

		echo $args['after_widget'];
	}

	// Widget Backend
	public function form($instance)
	{
		//
		$title = isset($instance['title']) ? $instance["title"] : '';
		$post_type = isset($instance['post_type']) ? $instance['post_type'] : null;
		$hide_empty = isset($instance['hide_empty']) ? "on" == $instance["hide_empty"] : false;
		$show_count = isset($instance['show_count']) ? "on" == $instance["show_count"] : false;

		// Widget admin form
		$types = _stool\Posts::$types;
		$types = !empty($types) ? $types : array();
		//
		array_unshift($types, array(
			"slug" => "post",
			"main" => __('Post')
		));

		//
		?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Post Type'); ?>:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
        <?php foreach ($types as $_type) {
        	echo '<option value="' . $_type["slug"] . '" ' . ($_type["slug"] == $post_type ? "selected" : "") . ' >' . $_type["main"] . '</option>';
        } ?>
        </select>
      </p>
      <p>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" <?php echo $hide_empty ? 'checked' : ''; ?> >
        <label for="<?php echo $this->get_field_id('hide_empty'); ?>">Hide empty</label>
        <br>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" <?php echo $show_count ? 'checked' : ''; ?> >
        <label for="<?php echo $this->get_field_id('show_count'); ?>">Show count</label>
      </p>
    <?php
	}

	// Updating widget replacing old instances with new
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
		$instance['post_type'] = !empty($new_instance['post_type']) ? strip_tags($new_instance['post_type']) : '';
		$instance['hide_empty'] = !empty($new_instance['hide_empty']) ? strip_tags($new_instance['hide_empty']) : '';
		$instance['show_count'] = !empty($new_instance['show_count']) ? strip_tags($new_instance['show_count']) : '';
		return $instance;
	}
}
