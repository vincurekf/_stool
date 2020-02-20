<?php
namespace _stool {
	class Comments
	{
		public function __construct()
		{
		}

		/**
		 *
		 */
		public static function disableCommentsPostTypesSupport()
		{
			$post_types = get_post_types();
			foreach ($post_types as $post_type) {
				if (post_type_supports($post_type, 'comments')) {
					remove_post_type_support($post_type, 'comments');
					remove_post_type_support($post_type, 'trackbacks');
				}
			}
		}
		public static function disableCommentsStatus()
		{
			return false;
		}
		public static function disableCommentsHideExistingComments($comments)
		{
			$comments = array();
			return $comments;
		}
		public static function disableCommentsAdminMenu()
		{
			remove_menu_page('edit-comments.php');
		}
		public static function disableCommentsAdminMenuRedirect()
		{
			global $pagenow;
			if ('edit-comments.php' === $pagenow) {
				wp_redirect(admin_url());
				exit();
			}
		}
		public static function disableCommentsDashboard()
		{
			remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		}
		public static function disableCommentsAdminBar()
		{
			if (is_admin_bar_showing()) {
				remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
			}
		}

		/**
		 * Completely disable Comments
		 */
		public static function disable()
		{
			// Disable support for comments and trackbacks in post types
			add_action('admin_init', array('_stool\Comments', 'disableCommentsPostTypesSupport'));
			// Close comments on the front-end
			add_filter('comments_open', array('_stool\Comments', 'disableCommentsStatus'), 20, 2);
			add_filter('pings_open', array('_stool\Comments', 'disableCommentsStatus'), 20, 2);
			// Hide existing comments
			add_filter('comments_array', array('_stool\Comments', 'disableCommentsHideExistingComments'), 10, 2);
			// Remove comments page in menu
			add_action('admin_menu', array('_stool\Comments', 'disableCommentsAdminMenu'));
			// Redirect any user trying to access comments page
			add_action('admin_init', array('_stool\Comments', 'disableCommentsAdminMenuRedirect'));
			// Remove comments metabox from dashboard
			add_action('admin_init', array('_stool\Comments', 'disableCommentsDashboard'));
			// Remove comments links from admin bar
			add_action('init', array('_stool\Comments', 'disableCommentsAdminBar'));
		}
	}
}
