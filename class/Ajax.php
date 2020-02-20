<?php

namespace _stool {
	class Ajax
	{
		//
		public static $cache_tag = '_stool-cache';
		public static $actions = array();
		//

		public function __construct()
		{
		}

		/**
		 *
		 */
		public static function add($class = null, $id = null, $private = false)
		{
			if (is_null($class) || is_null($id)) {
				return;
			}
			add_action('wp_ajax__stool_' . $id, array($class, $id));
			if (!$private) {
				add_action('wp_ajax_nopriv__stool_' . $id, array($class, $id));
			}
		}

		/**
		 *
		 */
		public static function SuccessResponse($data)
		{
			//Typical headers
			header('Content-Type: text/json');
			send_nosniff_header();
			//Disable caching
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			//
			echo json_encode(array(
				"success" => true,
				"data" => $data
			));
			//
			exit();
		}

		/**
		 *
		 */
		public static function ErrorResponse($data)
		{
			//Typical headers
			header('Content-Type: text/json');
			send_nosniff_header();
			//Disable caching
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			//
			echo json_encode(array(
				"success" => false,
				"data" => $data
			));
			//
			exit();
		}

		public static function data()
		{
			//
			$postdata = file_get_contents("php://input");
			$post = json_decode($postdata);
			return array(
				"get" => $_REQUEST,
				"post" => $post
			);
		}
	}
}
