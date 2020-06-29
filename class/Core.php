<?php

namespace _stool {
	class Core
	{
		//
		public static $cache_tag = '_stool-cache';
		//

		public function __construct()
		{
		}

		public static function init()
		{
			add_action('wp_enqueue_scripts', array('_stool\Core', 'registerScripts'));
			add_action('widgets_init', array('_stool\Core', 'widgetsInit'));
		}

		/**
		 * Add Theme Scripts & Styles
		 */
		public static function registerScripts()
		{
			if( is_user_logged_in() && current_user_can('administrator') ) {
				add_action('wp_head', array('_stool\Core', 'wpHead'), 100);
				add_action('wp_footer', array('_stool\Tools', 'showDebugger'));
			}
		}

		/**
		 * HEADER Styles
		 */
		public static function wpHead()
		{
			// ADMIN Debug Javascript
			echo '<script id="_stool_debug">var _stool_debug = [];</script>';
		}

		/**
		 * Register widget area.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
		 */
		public static function widgetsInit()
		{
			register_sidebar(array(
				'name' => esc_html__('Sidebar', '_stool'),
				'id' => 'sidebar-1',
				'description' => esc_html__('Add widgets here.', '_stool'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget' => '</section>',
				'before_title' => '<h2 class="widget-title">',
				'after_title' => '</h2>'
			));
		}
	}
}
