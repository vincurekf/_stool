<?php
namespace _stool {
	class Settings
	{
		public static $menu_structure;

		public function __construct()
		{
		}

		public static function init()
		{
			add_action('admin_menu', array('_stool\Settings', 'createMenuItem'));
			add_action('admin_enqueue_scripts', array('_stool\Settings', 'registerScripts'));
			add_action('wp_ajax__stool_settings_saved', array('_stool\Settings', 'wpAjaxStoolSettingsSaved'));
		}

		/**
		 *
		 */
		public static function wpAjaxStoolSettingsSaved()
		{
			//Typical headers
			header('Content-Type: text/json');
			send_nosniff_header();
			//Disable caching
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			//
			$params = array(
				'request' => $_REQUEST,
				'post' => $_POST
			);
			//
			echo json_encode(array(
				"success" => true,
				"params" => $params
			));
			//
			exit();
		}

		public static function registerScripts()
		{
			$current_screen = get_current_screen();
			//
			if ('toplevel_page__stool-settings' == $current_screen->id) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-form');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('_stool-settings', _STOOL_URI . 'assets/js/settings.js', array('jquery', 'wp-color-picker'), _STOOL_VERSION, true);
				wp_enqueue_style('_stool-settings', _STOOL_URI . 'assets/css/settings.css', array('wp-color-picker'), _STOOL_VERSION);
				//
				wp_localize_script('_stool-settings', '_stool_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'options_url' => admin_url('options.php')
				));
			}
		}

		public static function createMenuItem()
		{
			//create new top-level menu
			add_menu_page(
				'_stool',
				'_stool',
				'administrator',
				'_stool-settings',
				array('_stool\Settings', '_stool_settings_render'),
				'data:image/svg+xml;base64,' . base64_encode(file_get_contents(_STOOL_URI . 'assets/img/logo_symbol_white.svg')),
				90
			);
			//call register settings function
			add_action('admin_init', array('_stool\Settings', 'registerStoolSettings'));
		}

		public static function addSection($key = null, $options = null)
		{
			//
			if (!is_null($key) && !is_null($options)) {
				self::$menu_structure[$key] = $options;
			}
			//
		}

		public static function registerStoolSettings()
		{
			//
			do_action('_stool_hook_before_settings');
			//
			foreach (self::$menu_structure as $key => $panel) {
				foreach ($panel["fields"] as $fieldkey => $field) {
					register_setting('_stool_settings', $fieldkey, $field["default"]);
				}
			}
		}

		/**
		 *
		 */
		public static function _stool_settings_render()
		{
			?>
      <div id="_stool-settings" ng-app="_stool">
        <form id="_stool-settings-form" method="post" action="options.php" ng-submit="form.submit($event)">

          <header>
            <r-grid columns=12>
              <r-cell span=row>
                <img class="_stool-theme-logo" src="<?php echo _STOOL_URI; ?>/assets/img/logo_symbol_white.svg">
                <h1>_stool</h1>
                <div class="_stool-submit-controls">
                  <span class="_stool-notice saving" ng-if="form.loading">Saving...<i class="mdi mdi-loading mdi-spin"></i></span>
                  <span class="_stool-notice success" ng-if="form.success">Changes were saved!<i class="mdi mdi-check-circle"></i></span>
                  <span class="_stool-notice error" ng-if="form.error">Changes were not saved!<i class="mdi mdi-alert-circle"></i></span>
                  <p class="reset" ng-if="form.different()">
                    <span class="_stool-notice error">Save your changes!<i class="mdi mdi-alert-circle"></i></span>
                    <input type="reset" name="reset" id="reset" class="button button-primary" value="Reset" ng-click="form.reset()">
                  </p>
                  <?php submit_button(); ?>
                </div>
              </r-cell>
            </r-grid>
          </header>

          <section class="_stool-settings-wrapper">
            <div class="_stool-settings-menu">
              <ul class="tab tab-block">
                <?php self::loopThroughMenu('tabs'); ?>
              </ul>
            </div>
            <div class="_stool-settings-content">
              <?php settings_fields('_stool_settings'); ?>
              <?php do_settings_sections('_stool_settings'); ?>
              <div class="tabs-content">
                <?php self::loopThroughMenu(); ?>
              </div>
            </div>
            <div class="clear"></div>
          </section>

          <footer>
            <r-grid columns=12>
              <r-cell span=row>
                <img class="_stool-theme-logo" src="<?php echo _STOOL_URI; ?>/assets/img/logo_symbol_black.svg">
                <div class="_stool-submit-controls">
                  <span class="_stool-notice saving" ng-if="form.loading">Saving...<i class="mdi mdi-loading mdi-spin"></i></span>
                  <span class="_stool-notice success" ng-if="form.success">Changes were saved!<i class="mdi mdi-check-circle"></i></span>
                  <span class="_stool-notice error" ng-if="form.error">Changes were not saved!<i class="mdi mdi-alert-circle"></i></span>
                  <p class="reset" ng-if="form.different()">
                    <span class="_stool-notice error">Save your changes!<i class="mdi mdi-alert-circle"></i></span>
                    <input type="reset" name="reset" id="reset" class="button button-primary" value="Reset" ng-click="form.reset()">
                  </p>
                  <?php submit_button(); ?>
                </div>
              </r-cell>
            </r-grid>
          </footer>

        </form>
      </div>
    <?php
		}

		/**
		 *
		 */
		public static function renderInput($id = null, $field = array())
		{
			//
			if (!is_null($id)) {
				$type = isset($field["type"]) ? $field["type"] : null;
				$label = isset($field["label"]) ? $field["label"] : null;
				$tooltip = isset($field["tooltip"]) ? ' title="' . $field["tooltip"] . '"' : '';
				$condition = isset($field['condition']) ? ' ng-class="{\'inactive\':!(' . $field['condition'] . ')}"' : '';
				//
				$range_min = isset($field['options']) && isset($field['options']['min']) ? $field['options']['min'] : 0;
				$range_max = isset($field['options']) && isset($field['options']['max']) ? $field['options']['max'] : 100;
				$range_step = isset($field['options']) && isset($field['options']['step']) ? $field['options']['step'] : 1;
				$range_unit = isset($field['options']) && isset($field['options']['unit']) ? $field['options']['unit'] : '';
				//
				$value = !(empty(get_option($id)) || is_null(get_option($id))) ? get_option($id) : (isset($field["default"]) ? $field["default"] : null);
				//
				echo '<div class="_stool-input-section"' . $condition . '>';
				if ('customizer' == $type) {
					include _STOOL_ROOT . '/views/settings/customizer.php';
				} elseif ('posttypes' == $type) {
					include _STOOL_ROOT . '/views/settings/posttypes.php';
				} elseif ('tinymce' == $type) {
					include _STOOL_ROOT . '/views/settings/tinymce.php';
				} else {
					echo '<r-grid columns=12>';
					echo '<r-cell span=3>';
					echo '<label class="_stool-label" for="' . $id . '"' . $tooltip . '>';
					echo '<span class="_stool-label-title">' . $label . '</span>';
					echo '</label>';
					echo '</r-cell>';
					echo '<r-cell span=9>';
					echo '<div class="_stool-input-wrapper _stool-input-' . $type . '"' . $tooltip . '>';
					if ('text' == $type) {
						echo '<input id="' . $id . '" class="_stool-input" type="text" name="' . $id . '" value="' . $value . '" ng-model="form.data.' . $id . '" ng-init="form.data.' . $id . '=\'' . $value . '\'" />';
					} elseif ('color' == $type) {
						echo '<input id="' . $id . '" class="_stool-input _stool-color" type="text" name="' . $id . '" value="' . $value . '" ng-model="form.data.' . $id . '" ng-init="form.data.' . $id . '=\'' . $value . '\'" />';
					} elseif ('textarea' == $type) {
						echo '<textarea id="' . $id . '" class="_stool-input" name="' . $id . '" ng-model="form.data.' . $id . '" ng-init="form.data.' . $id . '=\'' . $value . '\'">' . $value . '</textarea>';
					} elseif ('checkbox' == $type) {
						echo '<span class="_stool-checkbox">';
						echo '<input id="' .
							$id .
							'" class="_stool-input" type="checkbox" name="' .
							$id .
							'" ' .
							(!is_null($value) ? 'checked' : '') .
							' data-value="' .
							$value .
							'" ng-model="form.data.' .
							$id .
							'" ng-init="form.data.' .
							$id .
							'=' .
							('on' == $value ? 'true' : 'false') .
							'" />';
						echo '</span>';
					} elseif ('select' == $type) {
						echo '<select id="' . $id . '" class="_stool-input" name="' . $id . '" ng-model="form.data.' . $id . '" ng-init="form.data.' . $id . '=\'' . $value . '\'">';
						foreach ($field['options'] as $key => $option) {
							echo '<option value="' . $key . '" ' . ($value == $key ? 'selected' : '') . '>' . $option . '</option>';
						}
						echo '</select>';
					} elseif ('range' == $type) {
						echo '<input id="' .
							$id .
							'" class="_stool-input" type="range" name="' .
							$id .
							'" value="' .
							$value .
							'" min="' .
							$range_min .
							'" max="' .
							$range_max .
							'" step="' .
							$range_step .
							'" ng-model="form.data.' .
							$id .
							'" ng-init="form.data.' .
							$id .
							'=' .
							$value .
							'"/>';
						echo '<span class="_stool-range-value">{{ form.data.' . $id . ' }}&nbsp;' . $range_unit . '</span>';
					}
					echo '</div>';
					echo '</r-cell>';
					echo '</r-grid>';
				}
				echo '</div>';
			}
		}

		/**
		 *
		 */
		public static function loopThroughMenu($type = 'panel')
		{
			//
			foreach (self::$menu_structure as $key => $panel) {
				if ('tabs' == $type) {
					echo '<li class="tab-item" ng-class="{\'active\':tabs.activeTab === \'panel-' .
						$key .
						'\'}">' .
						'<a ng-click="tabs.changetab(\'panel-' .
						$key .
						'\')" aria-selected="true">' .
						$panel["title"] .
						'</a>' .
						'</li>';
				} else {
					// Render panel fields
					echo '<div class="tabs-panel" id="panel-' . $key . '" ng-class="{\'active\':tabs.activeTab === \'panel-' . $key . '\'}">';
					//
					echo '<r-grid columns=12>';
					echo '<r-cell span=row><span class="_stool-section-title">' . $panel["title"] . '</span></r-cell>';
					echo '<r-cell span=' . (isset($panel["sidebar"]) ? '8' : '12') . '>';
					foreach ($panel["fields"] as $fieldkey => $field) {
						self::renderInput($fieldkey, $field);
					}
					echo '</r-cell>';
					if (isset($panel["sidebar"])) {
						echo '<r-cell span=4>' . $panel["sidebar"] . '</r-cell>';
					}
					echo '</r-grid>';
					//
					echo '</div>';
				}
			}
		}
	}
}
