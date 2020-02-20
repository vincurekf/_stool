<?php

// Register and load the widget
add_action('widgets_init', '_stool_load_category_widget');
function _stool_load_category_widget()
{
	register_widget('CategoriesWidget');
}

// Creating the widget
class CategoriesWidget extends WP_Widget
{
	public function __construct()
	{
		parent::__construct(
			// Base ID of your widget
			'_stool_category_widget',
			// Widget name will appear in UI
			'Stool ' . __('Categories'),
			// Widget description
			array('description' => 'Stool ' . __('Categories') . ' Widget')
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
		$taxonomy = $post_type == 'post' ? 'category' : $post_type . '_category';
		$terms = get_terms(array(
			'taxonomy' => $taxonomy,
			'hide_empty' => $hide_empty
		));

		//
		if ($terms && !is_wp_error($terms)) {
			echo '<ul class="taxonomy-list stool-widget-category">';
			foreach ($terms as $term) {
				$count = '';
				if ($show_count && $term->count > 0) {
					$count = ' <span class="count">' . $term->count . '</span>';
				}
				//
				echo '<li class="cat-item cat-' . $term->term_id . ' stool-category"><a href="' . get_term_link($term) . '">' . $term->name . $count . '</a></li>';
			}
			echo '</ul>';
		} else {
			echo '<ul class="taxonomy-list"><li class="cat-item-none">' . __('No categories') . '</li></ul>';
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
