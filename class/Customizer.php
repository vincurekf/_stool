<?php
namespace _stool {
	class Customizer
	{
		public static $sections = array();

		public function __construct()
		{
		}

		public static function init()
		{
			add_action('customize_register', array('_stool\Customizer', 'customizeSettings'));
		}

		public static function customizeSettings($wp_customize)
		{
			if (!is_null(self::$sections) && !empty(self::$sections)) {
				foreach (self::$sections as $section) {
					//
					self::registerSection($section, $wp_customize);
				}
			}
			$dynamicFields = json_decode(get_option('_stool_customizer', null), true);
			if (!is_null($dynamicFields) && !empty($dynamicFields)) {
				foreach ($dynamicFields as $section) {
					//
					self::registerSection($section, $wp_customize);
				}
			}
		}

		public static function registerSection($section, $wp_customize)
		{
			$section_key = $section["key"];
			$title = isset($section["title"]) ? $section["title"] : '';
			$priority = isset($section["priority"]) ? $section["priority"] : 0;
			//
			$wp_customize->add_section($section_key, array(
				'title' => $title,
				'priority' => $priority
			));
			foreach ($section["fields"] as $field) {
				$setting_id = strval($field["key"]);
				//
				$type = isset($field["type"]) ? $field["type"] : 'text';
				$default = isset($field["default"]) ? $field["default"] : '';
				$label = isset($field["label"]) ? $field["label"] : '';
				$choices = isset($field["choices"]) ? $field["choices"] : array('none' => "no options defined");
				//
				$wp_customize->add_setting($setting_id, array(
					'default' => $default,
					'transport' => 'refresh'
				));
				//
				switch ($type) {
					case 'color':
						$wp_customize->add_control(
							new \WP_Customize_Color_Control($wp_customize, $setting_id, array(
								'label' => $label,
								'section' => $section_key,
								'setting' => $setting_id
							))
						);
						break;
					case 'image':
						$wp_customize->add_control(
							new \WP_Customize_Image_Control($wp_customize, $setting_id, array(
								'label' => $label,
								'section' => $section_key,
								'setting' => $setting_id
							))
						);
						break;
					case 'radio':
					case 'select':
						$wp_customize->add_control($setting_id, array(
							'label' => $label,
							'section' => $section_key,
							'setting' => $setting_id,
							'type' => $type,
							'choices' => $choices
						));
						break;
					default:
						$wp_customize->add_control($setting_id, array(
							'label' => $label,
							'section' => $section_key,
							'setting' => $setting_id,
							// text, checkbox, radio, select, textarea, dropdown-pages, email, url, number, hidden, date
							'type' => $type
						));
						break;
				}
			}
		}

		public static function addSection($section = null)
		{
			if (is_null($section) || empty($section)) {
				return;
			}
			array_push(self::$sections, $section);
		}
	}
}
