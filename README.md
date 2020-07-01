# \_stool

Wordpress plugin with some tools to get you started.
This plugin is intended for theme creators.

It has settings section where you can create:
- custom post types
- metaboxes
- customizer options
- disable comments globally for the website

And it has even some other features:
- widget for displaying posts with custom sorting
- transient caching of `_stool\Posts::get()` queries (applies to widgets too)

But the best way to use it directly in your theme.

## Usage
- Download the `_stool` plugin.
- Install plugin in your wordpress installation
- Start creating!

### Add Post Type
```php
_stool\Posts::addType(array(
	"slug" => "events",
	"main" => "Events",
	"single" => "Event",
	"add" => "Event",
	"of" => "Events",
	// Wordpress dashboard icon name / base64 image string
	// See: https://developer.wordpress.org/resource/dashicons
	"icon" => ""
));
```

### Add Metabox
```php
$_meta = new _stool\Metabox('post-type','metabox-uniqe-identifier','Metabox title');
// now you can add as many fields as you want
$_meta->addField(array(
	"key" => "uniqe_input_fileld_key",
	"label" => "Field label",
	// Types: text, date, checkbox, textarea, tinymce (WYSIWYG)
	"type" => "date",
));
// finish the metabox
$_meta->init();
```
Notice:
`date` fields are saved as UNIX timestamps:
For example, `20.02.2020` is saved as `1582156800`


### Add Customizer Fields

```php
_stool\Customizer::addSection(array(
	"key" => "my_section",
	"title" => "My Section",
	"fields" => array(
		array(
			"key" => "my_theme_field",
			"label" => "My theme field",
			// Types: color, image, text, checkbox, radio, select,
			// textarea email, url, number, hidden, date
			"type" => "text",
			// default value
			"default" => null,
		),
	),
));
```
Retrieve filed in your theme with `get_theme_mod( 'my_theme_field', "Custom default value" )`.

### Add AJAX end point
Create new file for your Ajax class.

Your Ajax will be accesible as `_stool_[your_callback_name]`.
```php
namespace _stool {
	// define function with ajax callback
	class MyAjax extends Ajax {
		public static function test(){
			$req = self::data();
			//
			// generate your response data
			//
			return self::SuccessResponse( $req );
		}
	}
	// register the callback
	Ajax::add( "_stool\MyAjax", "test" );
}
```

To be able to use ajax in your theme you must pass in the ajax url:
```php
wp_enqueue_script('your-script', get_template_directory_uri() . 'your-script.js', array(), time(), true);
wp_localize_script('your-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
```

So in this case, the Ajax call would be:
```js
$.ajax({
	type: "POST",
	dataType: "json",
	url: ajax_object.ajax_url,
	data: {
		action: "_stool_test"
	},
	success: function(response) {
		alert(response);
	}
});
```

## Get Posts
To get posts in your theme:
```php
// Available parametters with default values
$params = array(
	// Or any other post type
	"post_type" => array("post"),
	// How many posts to retrieve
	"count" => 6,
	// Array of category IDs or single category name
	"category" => array(),
	// Skip cache, good for using in development
	"nocache" => false,
	// Skip post that have been already queried before this statement
	// (works only with posts queried with _stool\Posts::get() )
	"noqueried" => true,
	// Array of post IDs to exclude
	"exclude" => array(),
	// Any additional WP_Query() parametters
	// See https://developer.wordpress.org/reference/classes/wp_query/
	"custom_params" => null,
	// prints hidden <pre> block with querry information and raw results
	"debug" => false,
	// array of meta keys with parametters
	// See https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters
	"bymeta" => null,
);
$my_posts = _stool\Posts::get($params);
while ( $my_posts->have_posts() ) : $my_posts->the_post();
	// continue just as in standard wordpress loop
endwhile;
```

## Append to Dashboard widget
```php
function append_to_stoll_dashboard_widget(){
	echo 'This will show up in dashboard widget panel.<br>';
}
add_action('_stool_dashboard_render_widget', 'append_to_stoll_dashboard_widget');
```

## Get meta fields
In wordpress posts loop call:
```php
$my_field = _stool\Posts::meta('my_field');
```
Or if you are outside the posts loop call it with the post ID:
```php
$my_field = _stool\Posts::meta('my_field',1);
```

## Changelog

**v1.0.6**
- Updated posts widget setting styles
- Option to keep track of displayed posts to ignore them in widget automatically

**v1.0.5**
- Better input[range] styles

**v1.0.2**
- Added option for automatic cache celaning on post save

**v1.0.1**
- Code cleanup
- Focus on core functions

**v1.0.0**
- First public release

## Disclaimer
You are using this plugin at your own risk!

This plugin was mainly developed to help me make themes faster, without the need of reinventing the same techniqes over and over again. This plugin is not on the Wordpress official plugin repository and I am not plannig on putting it there.

## Licence
[GNU GPLv3](LICENCE.txt)
