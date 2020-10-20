<?php
/*************************************************************************

Plugin Name: _stool
Plugin URI: https://vincurekf.cz/stool
Description: Simple Tools that bring custom post types, cached queries, metaboxes and AJAX capabilities to your theme.
Version: 1.0.8
Author: Filip Vincůrek
Author URI: https://vincurekf.cz

 **************************************************************************

MIT License

Copyright (c) 2020 Filip Vincůrek (https://vincurekf.cz)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

 **************************************************************************/

// Plugin Version
define('_STOOL_VERSION', '1.0.8');
define('_STOOL_TESTEDWP', '5.5.1');

// If this file is called directly, abort.
if (!defined('WPINC')) {
	return;
}

// Plugin Root Folder
define('_STOOL_ROOT', dirname(__FILE__));
// Plugin Root Folder
define('_STOOL_URI', plugin_dir_url(__FILE__));
// Cache Config
define('_STOOL_USECACHE', get_option('_stool_transient_use'));
// Disable comments
define('_STOOL_DISABLE_COMMENTS', get_option('_stool_disable_comments'));
// Sanitize filenames
define('_STOOL_SANITIZE_FILENAMES', get_option('_stool_sanitize_filenames'));
// Is current session admins
define('_STOOL_IS_ADMIN', is_admin());
// Purge cache on post save
define('_STOOL_PURGE_CACHE', get_option('_stool_purge_cache'));
// Keep track of displayed posts
define('_STOOL_TRACK_POSTS', get_option('_stool_keep_track_of_post_ids'));

if (_STOOL_USECACHE) {
	$_stool_transient_expiry = get_option('_stool_transient_expiry');
	define('_STOOL_CACHEEXP', $_stool_transient_expiry ? $_stool_transient_expiry * 60 : 5 * 60);
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/class/_autoload.php';

//
$dynamicFields = json_decode(get_option('_stool_posttypes', null), true);
if (!is_null($dynamicFields) && !empty($dynamicFields)) {
	foreach ($dynamicFields as $type) {
		//
		_stool\Posts::addType($type);
		//
		if (isset($type["metaboxes"]) && !empty($type["metaboxes"])) {
			$posmetabox = new _stool\Metabox($type["slug"], 'main_metabox', $type["main"] . ' Meta', 'side');
			foreach ($type["metaboxes"] as $metabox) {
				$posmetabox->addField(array(
					"key" => $metabox["key"],
					"label" => $metabox["label"],
					"type" => $metabox["type"],
					"media_type" => $metabox["media_type"],
					"options" => isset($metabox["options"]) ? $metabox["options"] : array()
				));
			}
			$posmetabox->init();
		}
	}
}

//
_stool\Core::init();
_stool\Posts::init();
_stool\Customizer::init();

//
if (_STOOL_IS_ADMIN) {
	_stool\Admin::init();
	_stool\Dashboard::init();

	//
	_stool\Settings::addSection("global", array(
		"title" => "Global",
		"fields" => array(
			"_stool_transient_use" => array(
				"label" => "Use cache for posts",
				"tooltip" => "Cache posts internaly (turning this option ON will help decrease server load)",
				"type" => "checkbox",
				"default" => null
			),
			"_stool_transient_expiry" => array(
				"label" => "Cache expiration time",
				"tooltip" => "For how long to keep post queries in cache",
				"type" => "range",
				"options" => array(
					"min" => 5,
					"max" => 1440,
					"step" => 5,
					"unit" => "minutes"
				),
				"default" => 15,
				"condition" => "form.data._stool_transient_use === true"
			),
			"_stool_purge_cache" => array(
				"label" => "Purge cache upon save",
				"tooltip" => "Purge internal cache upon post save",
				"type" => "checkbox",
				"default" => null
			),
			"_stool_disable_comments" => array(
				"label" => "Completely disbale comments on site",
				"tooltip" => "This toggle will completely disable comments everywhere on your site, including admin menu and old posts.",
				"type" => "checkbox",
				"default" => null
			),
			"_stool_sanitize_filenames" => array(
				"label" => "Sanitize uploaded file names",
				"tooltip" => "Checking this will make sure that all special and illegal characters are removed from uploaded file names.",
				"type" => "checkbox",
				"default" => null
			),
			"_stool_keep_track_of_post_ids" => array(
				"label" => "Keep track of displayed posts",
				"tooltip" => "Adds hook to the_post() action to keep track of displayed posts to ignore them later (in posts widget for example)",
				"type" => "checkbox",
				"default" => null
			)
		)
	));
	//
	_stool\Settings::addSection("customizer", array(
		"title" => "Customizer",
		"fields" => array(
			"_stool_customizer" => array(
				"label" => "Customizer Fields",
				"type" => "customizer",
				"tooltip" => "",
				"default" => null
			)
		)
	));
	//
	_stool\Settings::addSection("posttypes", array(
		"title" => "Post Types",
		"fields" => array(
			"_stool_posttypes" => array(
				"label" => "Custom Post Types",
				"type" => "posttypes",
				"tooltip" => "",
				"default" => null
			)
		)
	));
	//
	_stool\Settings::init();

	new _stool\Updater(__FILE__, '_stool', null);
}

//
if (_STOOL_DISABLE_COMMENTS) {
	_stool\Comments::disable();
}
