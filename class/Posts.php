<?php
/**
 * @format
 */

namespace _stool {
	class Posts
	{
		public static $transient_prefix = 'stoolt_';

		public static $types = array();
		public static $slugs = array();
		public static $queriedIDs = array();

		public function __construct()
		{
		}

		public static function init()
		{
			//
			add_action('init', array('_stool\Posts', 'registerTypes'));
			add_action('admin_enqueue_scripts', array('_stool\Posts', 'registerScripts'));
			//
			foreach (self::$types as $key => $type) {
				add_filter('manage_' . $key . '_posts_columns', array('_stool\Posts', 'tableHead'));
			}
			//
			if( _STOOL_PURGE_CACHE ){
				add_action('save_post', array('_stool\Posts', 'autoPurgeCache'));
			}
			//
			if( _STOOL_TRACK_POSTS ){
				add_action('the_post', array('_stool\Posts', 'addPostToIgnored'));
			}
		}

		public static function registerScripts()
		{
			$current_screen = get_current_screen();
			//
			//if( in_array( $current_screen->post_type, self::$slugs) ){}
			if (in_array($current_screen->base, array('post', 'edit', 'page'))) {
				wp_enqueue_script('jquery');
				//
				wp_enqueue_media();
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				//
				wp_enqueue_script('_stool-admin-post', _STOOL_URI . 'assets/js/admin-post.js', array('jquery'), _STOOL_VERSION, true);
				wp_enqueue_style('_stool-admin-post', _STOOL_URI . 'assets/css/admin-post.css', array(), _STOOL_VERSION);
				//
				wp_localize_script('_stool-admin-post', '_stool_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php')
				));
			}
			//
		}

		public static function exclude($id = null)
		{
			self::$queriedIDs[] = $id;
		}
		public static function addType($names = null)
		{
			//
			$slug = isset($names["slug"]) ? $names["slug"] : Tools::makeSlug(strtolower($names["main"]));
			$names["slug"] = $slug;
			//
			self::$types[$slug] = $names;
			array_push(self::$slugs, $slug);
		}
		public static function registerTypes()
		{
			//
			foreach (self::$types as $key => $names) {
				$slug = $key;
				//
				self::registerCategory($slug, $names);
				//
				self::registerTag($slug, $names);
				//
				$labels = array(
					'name' => _x($names["main"], 'Post Type General Name', '_stool'),
					'singular_name' => _x($names["single"], 'Post Type Singular Name', '_stool'),
					'menu_name' => __($names["main"], '_stool'),
					'name_admin_bar' => __('Add') . ': ' . $names["main"],
					//'archives'              => __( 'Archive of' ) . ': ' . $names["of"],
					'parent_item_colon' => __('Parent Item:', '_stool'),
					'all_items' => __('All') . ': ' . $names["main"],
					'add_new_item' => __('Add') . ': ' . $names["add"],
					'add_new' => __('Add') . ': ' . $names["add"],
					'new_item' => __('New') . ': ' . $names["single"],
					'edit_item' => __('Edit') . ': ' . $names["add"],
					'update_item' => __('Edit') . ': ' . $names["add"],
					'view_item' => __('View') . ': ' . $names["main"],
					'search_items' => __('Search'),
					'not_found' => __('Not found'),
					'not_found_in_trash' => __('Not found in trash'),
					'featured_image' => __('Image for') . ': ' . $names["add"],
					'set_featured_image' => __('New image for') . ': ' . $names["add"],
					'remove_featured_image' => __('Delete image for') . ': ' . $names["add"],
					'use_featured_image' => __('Use as image for') . ': ' . $names["add"],
					'insert_into_item' => __('Add to') . ': ' . $names["main"],
					'uploaded_to_this_item' => __('Uploaded to') . ': ' . $names["main"],
					'items_list' => __('List') . ': ' . $names["main"],
					'items_list_navigation' => __('Navigation for') . ': ' . $names["main"],
					'filter_items_list' => __('Filter') . ': ' . $names["main"]
				);
				$args = array(
					'label' => __($names["main"], '_stool'),
					'description' => __($names["main"] . ' description', '_stool'),
					//'labels'                => $labels,
					'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'author', 'attachments'),
					'taxonomies' => array($slug . '_category', $slug . '_tag'),
					'hierarchical' => false,
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'menu_position' => 10,
					'menu_icon' => $names['icon'] ?: 'dashicons-admin-post',
					'show_in_admin_bar' => true,
					'show_in_nav_menus' => true,
					'can_export' => true,
					'has_archive' => true,
					'exclude_from_search' => false,
					'publicly_queryable' => true,
					'capability_type' => 'post',
					'show_in_rest' => true
				);
				//
				register_post_type($slug, $args);
			}
			//
		}
		public static function registerTag($slug, $names)
		{
			$taxonomy_name = $slug . '_tag';
			//
			$labels = array(
				'menu_name' => __('Tags'),
				'name' => __('Tags') . ' ' . _x($names["main"], 'taxonomy general name', 'textdomain'),
				'singular_name' => __('Tags') . ' ' . _x($names["single"], 'taxonomy singular name', 'textdomain'),
				'search_items' => __('Search Tags'),
				'all_items' => __('All Tags'),
				'parent_item' => __('Parent Tag'),
				'parent_item_colon' => __('Parent Tag:'),
				'edit_item' => __('Edit Tag'),
				'update_item' => __('Update Tag'),
				'add_new_item' => __('Add New Tag'),
				'new_item_name' => __('New Tag Name')
			);
			//
			$args = array(
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'hierarchical' => false,
				'show_in_rest' => true,
				'show_admin_column' => true,
				'rewrite' => array(
					'with_front' => false,
					'slug' => ltrim(sprintf('%s/' . Tools::makeSlug(__('Tag')), $slug), '/')
				)
			);
			register_taxonomy($taxonomy_name, $slug, $args);
			//
		}
		public static function registerCategory($slug, $names)
		{
			$taxonomy_name = $slug . '_category';
			//
			$labels = array(
				'menu_name' => __('Categories'),
				'name' => __('Categories') . ' ' . _x($names["main"], 'taxonomy general name', 'textdomain'),
				'singular_name' => __('Category') . ' ' . _x($names["single"], 'taxonomy singular name', 'textdomain'),
				'search_items' => __('Search Categories'),
				'all_items' => __('All Categories'),
				'parent_item' => __('Parent Category'),
				'parent_item_colon' => __('Parent Category:'),
				'edit_item' => __('Edit Category'),
				'update_item' => __('Update Category'),
				'add_new_item' => __('Add New Category'),
				'new_item_name' => __('New Category Name')
			);
			//
			$args = array(
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'show_admin_column' => true,
				'show_tagcloud' => false,
				'rewrite' => array(
					'with_front' => false,
					'hierarchical' => true,
					'slug' => ltrim(sprintf('%s/' . Tools::makeSlug(__('Category')), $slug), '/')
				),
				'_builtin' => true
			);
			register_taxonomy($taxonomy_name, $slug, $args);
			//
		}
		public static function tableHead($columns)
		{
			if (!empty(self::$types)) {
				foreach (self::$types as $key => $type) {
					$tax_key = 'taxonomy-' . $key . '_category';
					if (isset($columns[$tax_key])) {
						$columns[$tax_key] = __('Categories');
					}

					$tax_key = 'taxonomy-' . $key . '_tag';
					if (isset($columns[$tax_key])) {
						$columns[$tax_key] = __('Tags');
					}
				}
			}
			return $columns;
		}

		public static function get($params = 6, $category = array(), $type = array('post'), $custom_params = null, $nocache = false, $noqueried = true)
		{
			//
			$debug = false;
			//
			if (is_array($params)) {
				//
				$type = isset($params["post_type"]) ? $params["post_type"] : array('post');
				$count = isset($params["count"]) ? $params["count"] : 6;
				$category = isset($params["category"]) ? $params["category"] : array();
				$nocache = isset($params["nocache"]) ? $params["nocache"] : false;
				$noqueried = isset($params["noqueried"]) ? $params["noqueried"] : true;
				$exclude = isset($params["exclude"]) ? $params["exclude"] : array();
				$custom_params = isset($params["custom_params"]) ? $params["custom_params"] : null;
				$debug = isset($params["debug"]) ? $params["debug"] : false;
				$bymeta = isset($params["bymeta"]) ? $params["bymeta"] : null;
				//
			} else {
				$count = $params;
			}
			//
			$type_str = is_array($type) && !empty($type) ? implode(",", $type) : $type;
			$category_str = is_array($category) && !empty($category) ? implode(",", $category) : '';
			//
			$exclude_str = implode("", array_merge(self::$queriedIDs, $exclude));
			//
			$custom_params_str = base64_encode(json_encode($custom_params));
			//
			$wp_query_params = array(
				'post_type' => $type,
				'posts_per_page' => $count
			);
			//
			if ($noqueried) {
				if (!empty($exclude)) {
					$wp_query_params['post__not_in'] = array_merge(self::$queriedIDs, $exclude);
				} else {
					$wp_query_params['post__not_in'] = self::$queriedIDs;
				}
			} else {
				if (!empty($exclude)) {
					$wp_query_params['post__not_in'] = $exclude;
				}
			}
			//
			if (!is_null($category) && !empty($category)) {
				if (is_string($category)) {
					$wp_query_params['category_name'] = $category;
				} elseif (is_array($category)) {
					$wp_query_params['category__in'] = $category;
				}
			}
			//
			if (!is_null($custom_params)) {
				foreach ($custom_params as $key => $param) {
					$wp_query_params[$key] = $param;
				}
			}
			//
			if (isset($bymeta) && !is_null($bymeta)) {
				//
				$wp_query_params["meta_query"] = array();
				//
				foreach ($bymeta as $meta) {
					$meta["key"] = "_meta_" . $meta["key"];
					$wp_query_params["meta_query"][] = $meta;
				}
				//
			}
			//
			if (_STOOL_USECACHE && !$nocache) {
				//
				$transient_params = '|type-' . $type_str;
				$transient_params .= '|count-' . $count;
				$transient_params .= '|category-' . $category_str;
				$transient_params .= '|exclude-' . $exclude_str;
				$transient_params .= '|customparams-' . $custom_params_str;
				if ($noqueried) {
					$transient_params .= '|noqueried';
				}
				//
				$transient = self::$transient_prefix . hash('sha256', $transient_params, false);
				//
				if (false === ($_stool_posts = get_transient($transient))) {
					$_stool_posts = new \WP_Query($wp_query_params);
					//
					set_transient($transient, $_stool_posts, _STOOL_CACHEEXP);
				}
			} else {
				$_stool_posts = new \WP_Query($wp_query_params);
			}
			//
			if ($debug) {
				Tools::debug($_stool_posts);
			}
			//
			self::$queriedIDs = array_merge(self::$queriedIDs, self::getIds($_stool_posts->posts));
			//
			return $_stool_posts;
		}

		public static function autoPurgeCache( $post_id )
		{
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}
			//
			global $wpdb;
			//
			$sql_qery = "DELETE FROM wp_options WHERE option_name LIKE ('%" . self::$transient_prefix . "%')";
			$result = $wpdb->get_results($sql_qery);
			//
			wp_cache_flush();
		}

		public static function purgeCache( $post_id = null )
		{
			global $wpdb;
			//
			$sql_qery = "DELETE FROM wp_options WHERE option_name LIKE ('%" . self::$transient_prefix . "%')";
			$result = $wpdb->get_results($sql_qery);
			//
			wp_cache_flush();
			//
			return Ajax::SuccessResponse(array(
				"success" => true,
				"result" => $result,
				"query" => $sql_qery
			));
		}

		public static function title($charlength = 80, $title = null)
		{
			if (is_null($title)) {
				$title = get_the_title();
			}

			$charlength++;
			if (mb_strlen($title) > $charlength) {
				$subex = mb_substr($title, 0, $charlength - 5);
				$exwords = explode(' ', $subex);
				$excut = -mb_strlen($exwords[count($exwords) - 1]);
				if ($excut < 0) {
					echo mb_substr($subex, 0, $excut);
				} else {
					echo $subex;
				}
				echo '…';
			} else {
				echo $title;
			}
		}

		public static function excerpt($charlength = 120, $id = null, $echo = true, $content = null)
		{
			$id = !is_null($id) ? $id : get_the_ID();
			//
			$str = '';
			//
			$excerpt = get_the_excerpt($id);
			if (empty($excerpt)) {
				$excerpt = $content;
			}

			$charlength++;
			if (mb_strlen($excerpt) > $charlength) {
				$subex = mb_substr($excerpt, 0, $charlength - 5);
				$exwords = explode(' ', $subex);
				$excut = -mb_strlen($exwords[count($exwords) - 1]);
				if ($excut < 0) {
					$str .= mb_substr($subex, 0, $excut);
				} else {
					$str .= $subex;
				}
				$str .= '…';
			} else {
				$str .= $excerpt;
			}
			if ($echo) {
				echo $str;
			} else {
				return $str;
			}
		}

		public static function meta($key = null, $postid = null)
		{
			//
			if (is_null($postid)) {
				$postid = get_the_ID();
			}
			if (is_null($key) || is_null($postid)) {
				return '';
			}
			if (substr($key, 0, 6) !== "_meta_") {
				$key = "_meta_" . $key;
			}
			//
			return get_post_meta($postid, $key, true);
			//
    }

		public static function getIds($posts = null)
		{
			$IDs = array();
			foreach ($posts as $key => $post) {
				array_push($IDs, $post->ID);
			}
			return $IDs;
		}

		public static function addPostToIgnored($post = null){
			self::exclude($post->ID);
		}
	}
}
