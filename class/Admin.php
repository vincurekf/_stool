<?php
namespace _stool {
	//
	class Admin
	{
		public function __construct()
		{
		}

		public static function init()
		{
			add_action('admin_enqueue_scripts', array('_stool\Admin', 'registerScripts'));
			//
			if (_STOOL_SANITIZE_FILENAMES) {
				add_filter('sanitize_file_name', array('_stool\Admin', 'sanitizeUploadFileNames'), 10, 1);
			}
			//
			if (_STOOL_USECACHE) {
				add_action('admin_bar_menu', array('_stool\Admin', 'stoolAdminPanelMenu'), 2000);
				Ajax::add("_stool\Posts", "purgeCache", true);
			}
		}

		public static function registerScripts()
		{
			$current_screen = get_current_screen();
			//
			wp_enqueue_script('jquery');
			//
			wp_enqueue_script('_stool-admin', _STOOL_URI . 'assets/js/admin.js', array('jquery'), _STOOL_VERSION, true);
			wp_enqueue_style('_stool-admin', _STOOL_URI . 'assets/css/admin.css', array(), _STOOL_VERSION);
			//
			wp_localize_script('_stool-admin', '_stool_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
			//
			if (in_array($current_screen->base, array('post', 'edit', 'page'))) {
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
			}
			//
			if (in_array($current_screen->base, array('widgets','customize'))) {
				wp_enqueue_style('_stool-admin-widgets', _STOOL_URI . 'assets/css/widgets.css', array(), _STOOL_VERSION);
			}
			//
		}

		/**
		 * Produces cleaner filenames for uploads
		 *
		 * @param  string $filename
		 * @return string
		 */
		public static function sanitizeUploadFileNames($filename)
		{
			$sanitized_filename = remove_accents($filename); // Convert to ASCII

			// Standard replacements
			$invalid = array(
				' ' => '-',
				'%20' => '-',
				'_' => '-'
			);
			$sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);

			$sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename); // Remove all non-alphanumeric except .
			$sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename); // Remove all but last .
			$sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename); // Replace any more than one - in a row
			$sanitized_filename = str_replace('-.', '.', $sanitized_filename); // Remove last - if at the end
			$sanitized_filename = strtolower($sanitized_filename); // Lowercase

			return $sanitized_filename;
		}

		/**
		 * Add Purge Transients/Cache link in admin bar
		 */
		public static function stoolAdminPanelMenu($wp_admin_bar)
		{
			$menu_id = 'stool-admin-panel-menu';
			$root_menu_item = array(
				'id' => $menu_id,
				'title' => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(_STOOL_URI . 'assets/img/logo_purge_cache.svg')) . '"> _stool',
				'href' => '#',
				'meta' => array(
					'class' => 'stool-admin-panel-menu'
				)
			);
			//
			$wp_admin_bar->add_menu($root_menu_item);
			$wp_admin_bar->add_menu(array(
				'parent' => $menu_id,
				'title' => __('Clear Cache'),
				'id' => 'stool-purge-cache',
				'href' => '#',
				'meta' => array(
					'title' => '_stool: Purge Post Cache',
					'class' => 'stool-component-purge-cache'
				)
			));
			//
		}
	}
}
