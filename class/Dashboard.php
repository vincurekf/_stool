<?php
use _stool\Posts;

namespace _stool {
	class Dashboard
	{
		public function __construct()
		{
		}

		public static function init()
		{
			add_action('admin_enqueue_scripts', array('_stool\Dashboard', 'registerScripts'));
			add_action('wp_dashboard_setup', array('_stool\Dashboard', 'registerWidgets'));
		}

		public static function registerScripts()
		{
			$current_screen = get_current_screen();
			//
			if ('dashboard' == $current_screen->base) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('_stool-dashboard', _STOOL_URI . 'assets/js/dashboard.js', array('jquery'), _STOOL_VERSION, true);
				wp_enqueue_style('_stool-dashboard', _STOOL_URI . 'assets/css/dashboard.css', array(), _STOOL_VERSION);
			}
		}

		/**
		 * Dashboard Widget
		 * @format
		 */
		public static function registerWidgets()
		{
			global $wp_meta_boxes;
			wp_add_dashboard_widget('_stool_main_widget', '_STOOL: Main Widget', array('_stool\Dashboard', 'renderMainWidget'));
		}

		/**
		 * Main Widget
		 */
		public static function renderMainWidget()
		{
			//
			$dynamicFields = json_decode(get_option('_stool_posttypes', null), true);
			//
			do_action('_stool_dashboard_render_widget');
			//
			if (!is_null(Posts::$types) && !empty(Posts::$types)) {
				$ext = count(Posts::$types) > 1 ? "s" : "";
				echo "<strong>" . count(Posts::$types) . "</strong> custom post type" . $ext . ":";
				echo "<ul>";
				foreach (Posts::$types as $type) {
					echo "<li>" . $type["main"] . "</li>";
				}
				echo "</ul>";
			}
			//
			echo '<div style="font-size:11px; text-align:right; border-top:1px dotted #ddd; margin-top:4px;"><i class="mdi mdi-code-tags"> Version: ' . _STOOL_VERSION . '</i></div>';
		}
	}
}
