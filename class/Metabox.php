<?php
namespace _stool {
	class Metabox
	{
		//
		public $id;
		public $fields;
		public $title;
		public $position;
		public $post_type;
		public $metaboxID;
		//

		public function __construct($post_type = 'post', $id = null, $title = '', $position = 'side')
		{
			$this->fields = array();
			$this->id = $id;
			$this->post_type = $post_type;
			$this->title = $title;
			$this->position = $position; // 'normal', 'side', and 'advanced'
			$this->metaboxID = '_stool_' . $post_type . '_' . $id . '_meta';
		}

		public function init()
		{
			add_action('add_meta_boxes', array($this, 'addMetabox'));
			add_action('save_post_' . $this->post_type, array($this, 'saveMeta'), 1, 2);
			// DISPLAY CUSTOM AD FIELDS IN ADMIN LIST
			add_filter('manage_' . $this->post_type . '_posts_columns', array($this, 'tableHead'));
			add_action('manage_' . $this->post_type . '_posts_custom_column', array($this, 'tableContent'), 10, 2);
			add_filter('post_class', array($this, 'tableAddRowClass'));
			//
			Ajax::add("_stool\Metabox", "updateCheckbox", true);
		}

		public static function updateCheckbox()
		{
			$req = Ajax::data();
			$metaId = isset($req["get"]["meta_id"]) ? $req["get"]["meta_id"] : null;
			$postId = isset($req["get"]["post_id"]) ? $req["get"]["post_id"] : null;
			if (is_null($metaId) || is_null($postId)) {
				return Ajax::SuccessResponse(array(
					"success" => false,
					"error" => "No meta or post ID"
				));
			}
			//
			$current = get_post_meta($postId, $metaId, true);
			//
			if ($current == "on") {
				delete_post_meta($postId, $metaId);
				$current = null;
			} else {
				update_post_meta($postId, $metaId, "on");
				$current = "on";
			}
			//
			return Ajax::SuccessResponse(array(
				"success" => true,
				"val" => $current
			));
		}

		public function addField($field = null)
		{
			$key = $this->makeKey($field);
			$this->fields[$key] = $field;
		}

		public function addMetabox()
		{
			//
			add_meta_box($this->metaboxID, $this->title, array($this, 'renderMetabox'), $this->post_type, $this->position, 'default');
		}

		public function makeKey($field = null)
		{
			if (is_null($field)) {
				return null;
			}
			if (is_null($field["key"])) {
				return null;
			}
			return '_meta_' . $field["key"];
		}

		public function tableHead($columns)
		{
			if (!empty($this->fields)) {
				foreach ($this->fields as $key => $field) {
					$columns[$key] = '<img class="_stool-post-lists-icon" src="' . _STOOL_URI . 'assets/img/logo_symbol_black.svg">' . $field["label"];
				}
			}
			return $columns;
		}
		public function tableContent($column_name, $post_id)
		{
			//
			if (!empty($this->fields)) {
				if (isset($this->fields[$column_name])) {
					//
					$field = $this->fields[$column_name];
					$field_value = get_post_meta($post_id, $column_name, true);
					//
					if ("checkbox" == $field["type"]) {
						$active = $field_value == "on";
						$check = isset($field["check"]) ? $field["check"] : "✔";
						echo '<span data-stool-checkbox data-post-id="' . $post_id . '" data-meta-name="' . $column_name . '" class="_stool-checkbox-wrap' . ($active ? " active" : "") . '" title="Click to change">';
						echo '<i class="mdi mdi-checkbox-marked-circle-outline _stool-checkbox-active"></i>';
						echo '<i class="mdi mdi-radiobox-blank _stool-checkbox-inactive"></i>';
						echo '</span>';
					} elseif (!empty($field_value)) {
						if ("date" == $field["type"]) {
							try {
								$field_value = \DateTime::createFromFormat('U', $field_value)->format('c');
								echo get_date_from_gmt($field_value, 'j. n. Y');
							} catch (\Error $e) {
								echo $field_value;
							}
						} elseif ("datetime" == $field["type"]) {
							try {
								$field_value = \DateTime::createFromFormat('U', $field_value)->format('c');
								echo get_date_from_gmt($field_value, 'j. n. Y H:i');
							} catch (\Error $e) {
								echo $field_value;
							}
						} elseif ("media" == $field["type"]) {
							if ("image" == $field["media_type"]) {
								echo '<img class="_stool-posts-list-image" src="' . $field_value . '">';
							} elseif ("video" == $field["media_type"]) {
								echo '<video class="_stool-posts-list-video" controls src="' . $field_value . '"/>';
							}
						} elseif ("select" == $field["type"]) {
							if (isset($field["valmask"]) && !empty($field["valmask"])) {
								echo str_replace("%value%", $field["options"][$field_value], $field["valmask"]);
							} else {
								echo $field["options"][$field_value];
							}
						} elseif ("text" == $field["type"] || "textarea" == $field["type"] || "tinymce" == $field["type"]) {
							//
							$txt_arr_ln = explode(' ', strip_tags($field_value));
							$txt_arr = explode(' ', strip_tags($field_value), 14);
							$longer = count($txt_arr_ln) > count($txt_arr);
							$ellipsis = $longer ? '…' : false;
							if ($longer) {
								array_pop($txt_arr);
								echo join(' ', $txt_arr) . $ellipsis;
							} else {
								echo join(' ', $txt_arr);
							}
						} else {
							if (isset($field["valmask"]) && !empty($field["valmask"])) {
								echo str_replace("%value%", $field_value, $field["valmask"]);
							} else {
								echo $field_value;
							}
						}
					} else {
						echo "—";
					}
					//
				}
			}
			//
		}
		public function tableAddRowClass($classes)
		{
			global $post;
			//
			$classes[] = 'test-class';
			//
			return $classes;
		}

		public function renderMetabox()
		{
			global $post;
			//
			wp_nonce_field(basename(__FILE__), $this->post_type . '_fields');
			//
			echo '<div class="components-base-control _stool-component-wrap" id="' . $this->metaboxID . '" data-icon="' . _STOOL_URI . 'assets/img/logo_symbol_black.svg">';
			//
			if (!empty($this->fields)) {
				foreach ($this->fields as $key => $field) {
					//
					$attr = "";
					$class = "";
					//
					$field_value = get_post_meta($post->ID, $key, true);
					$field_label = !empty($field["label"]) ? __($field["label"]) : $key;
					$media_type = isset($field["media_type"]) ? $field["media_type"] : '';
					if ("media" == $field["type"]) {
						$attr .= ' data-key="' . $key . '" data-type="' . $media_type . '"';
						$class .= " _stool-media-upload";
					}
					//
					if (isset($field["driver"])) {
						$class .= " _stool-component-driver";
					}
					//
					echo '<div class="components-base-control__field _stool-component' . $class . '"' . $attr . '>';
					//
					if (isset($field["dynamic"])) {
						echo '<div id="_stool_dynamic_' . $key . '" class="_stool_dynamic stool-dynamic-field" data-driver="_meta_' . $field["dynamic"] . '">';
					}
					//
					if ("checkbox" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label _stool-label-checkbox">';
						echo '<input id="' . $key . '" type="checkbox" name="' . $key . '" ' . ("on" == $field_value ? "checked" : "") . ' class="_stool_checkbox components-form-token-field__input">';
						echo '<div class="_stool_checkbox_toggle"><span></span></div>';
						echo '<span class="_stool_checkbox_label">' . $field_label . '</span>';
						echo '</label>';
						//
					} elseif ("date" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<input id="' . $key . '" type="date" name="' . $key . '" value="' . esc_textarea($field_value) . '" class="components-form-token-field__input">';
						//
					} elseif ("datetime" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<input id="' . $key . '" type="datetime-local" name="' . $key . '" value="' . esc_textarea($field_value) . '" class="components-form-token-field__input">';
						//
					} elseif ("media" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<input id="' . $key . '"                class="_stool-media-input" name="' . $key . '" type="text" value="' . $field_value . '" style="display: none;" />';
						echo '<input id="' . $key . '_upload_button"  class="button _stool_media_button" value="' . __("Select " . $media_type) . '" type="button" />';
						echo '<div class="_stool-media-preview-wrap">';
						if ("image" == $media_type) {
							echo '<img   id="' . $key . '_image_preview" class="_stool-media-preview" src="' . $field_value . '" title="' . __("Change image") . '"/>';
						}
						if ("video" == $media_type) {
							echo '<video id="' . $key . '_media_preview" class="_stool-media-preview" src="' . $field_value . '" title="' . __("Change video") . '" controls></video>';
						}
						echo '</div>';
						echo '<input id="' . $key . '_erase_button"   class="button _stool_media_button _stool_media_button_erase" value="× ' . __("Remove " . $media_type) . '" type="button" />';
						//
					} elseif ("textarea" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<textarea id="' . $key . '" name="' . $key . '" class="components-form-token-field__input">' . esc_textarea($field_value) . '</textarea>';
						//
					} elseif ("tinymce" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						wp_editor($field_value, $key, array(
							'wpautop' => true,
							'media_buttons' => false,
							'textarea_name' => $key,
							'textarea_rows' => 8,
							'teeny' => true
						));
						//
					} elseif ("color" == $field["type"]) {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<input id="' . $key . '" type="text" name="' . $key . '" value="' . esc_attr($field_value) . '" data-default-color="#333" class="components-form-token-field__input color-field"></input>';
						//
					} elseif ("select" == $field["type"]) {
						//
						if (isset($field["options"]) || !empty($field["options"])) {
							echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
							echo '<select id="' . $key . '" class="_stool_select" name="' . $key . '" class="components-form-token-field__input">';
							foreach ($field["options"] as $o_key => $option) {
								echo '<option value="' . $o_key . '" ' . (esc_textarea($field_value) == $o_key ? 'selected' : '') . '>' . $option . '</option>';
							}
							echo '</select>';
						} else {
							echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
							echo '<strong>No options defined</strong>';
						}
					} else {
						//
						echo '<label for="' . $key . '" class="components-base-control__label">' . $field_label . '</label>';
						echo '<input id="' . $key . '" type="text" name="' . $key . '" value="' . esc_textarea($field_value) . '" class="components-form-token-field__input">';
						//
					}
					//
					if (isset($field["help"])) {
						echo '<div class="_stool-component-help">';
						echo '<img class="_stool-component-help-icon" src="' . _STOOL_URI . 'assets/img/info.svg">';
						echo $field["help"];
						echo '</div>';
					}
					//
					if (isset($field["dynamic"])) {
						echo '</div>';
					}
					//
					echo '</div>';
					//
				}
			}
			//
			echo '<div class="_stool_metabox_notice">';
			echo '_stool v' . _STOOL_VERSION;
			echo '</div>';
			//
			echo '</div>';
		}

		public function saveMeta($post_id, $post)
		{
			// Return if the user doesn't have edit permissions.
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}

			// Verify this came from the our screen and with proper authorization, because save_post can be triggered at other times.
			if (!isset($_POST[$this->post_type . '_fields']) || !wp_verify_nonce($_POST[$this->post_type . '_fields'], basename(__FILE__))) {
				return $post_id;
			}

			//
			if (!empty($this->fields)) {
				foreach ($this->fields as $key => $field) {
					//
					if (!isset($_POST[$key])) {
						delete_post_meta($post_id, $key);
					} else {
						$value = $_POST[$key];
						if ("tinymce" != $field["type"]) {
							$value = esc_textarea($_POST[$key]);
						}

						//
						if ('revision' === $post->post_type) {
							return;
						}

						//
						if (get_post_meta($post_id, $key, false)) {
							update_post_meta($post_id, $key, $value);
						} else {
							add_post_meta($post_id, $key, $value);
						}
						if (!$value) {
							delete_post_meta($post_id, $key);
						}
					}
					//
				}
			}
			//
		}
	}
}
