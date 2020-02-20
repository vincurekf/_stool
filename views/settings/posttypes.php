<?php
$value = !empty($value) ? "{}" != $value ? $value : '[]' : '[]';
?>

<grid columns=12 id="posttypes-fields">

  <c span=row>
    <div class="_stool-customizer-info">
      <p>Here you can define your own Post Types</p>
    </div>
    <label class="_stool-label"<?php echo $tooltip; ?>><span class="_stool-label-title"><?php echo $label; ?></span></label>
    <textarea style="display: none;" id="<?php echo $id; ?>" class="_stool-input" name="<?php echo $id; ?>" ng-model="form.data.<?php echo $id; ?>"><?php echo $value; ?></textarea>
  </c>

  <c span=row>

    <div data-as-sortable="posttypes.dragControlListeners" data-ng-model="posttypes.sections">
      <div class="_stool-customizer-section" data-ng-repeat="section in posttypes.sections" data-as-sortable-item>
        <grid columns=12 class="_stool-customizer-section-header">
          <c span=10><h3 class="_stool-customizer-section-title">{{ section.main }}</h3></c>
          <c span=1 class="text-center"><i class="mdi mdi-delete" ng-click="posttypes.removeSection(section)" title="Delete Section"></i></c>
          <c span=1 class="text-right">
            <i class="mdi mdi-cursor-move" data-as-sortable-item-handle></i>
            <i class="mdi mdi-chevron-down" ng-click="posttypes.collapse[section.slug] = !posttypes.collapse[section.slug]"></i>
          </c>
        </grid>
        <div class="_stool-customizer-section-fields" ng-class="{'collapsed':posttypes.collapse[section.slug]}">
          <grid columns=12 class="_stool-customizer-section-subtitle">
            <c span=row>
              <h3>Names</h3>
            </c>
          </grid>
          <grid columns=12 class="_stool-customizer-section-config">
            <c span=4>
              <label><span>Slug:</span><input type="text" ng-model="section.slug" placeholder="posttype"/></label>
              <label><span>Main:</span><input type="text" class="_stool-customizer-section-title" ng-model="section.main" placeholder="PostType"/></label>
            </c>
            <c span=4>
              <label><span>Single:</span><input type="text" ng-model="section.single" placeholder="PostType"/></label>
              <label><span>Add:</span><input type="text" ng-model="section.add" placeholder="PostType"/></label>
            </c>
            <c span=4>
              <label><span>Of:</span><input type="text" ng-model="section.of" placeholder="PostTypes"/></label>
              <label><span><div class="dashicons dashicons-before {{ section.icon }}"></div> Icon:</span>
                <select ng-model="section.icon" class="_stool-customizer-section-select">
                  <option ng-repeat="option in posttypes.iconSelect" value="{{option}}">{{ option }}</option>
                </select>
              </label>
            </c>
          </grid>
          <grid columns=12 class="_stool-customizer-section-subtitle">
            <c span=row>
              <h3>Metabox Fields</h3>
            </c>
          </grid>
          <grid columns=12 class="_stool-customizer-row _stool-customizer-row-header">
            <c span=3>Label</c>
            <c span=3>Key</c>
            <c span=2>Type</c>
            <c span=2>Default</c>
            <c span=1 class="text-center">Delete</c>
            <c span=1 class="text-center">Move</c>
          </grid>
          <div data-as-sortable="posttypes.dragControlListeners" data-ng-model="section.metaboxes">
            <div class="_stool-customizer-field" data-ng-repeat="field in section.metaboxes" data-as-sortable-item>
              <grid columns=12 class="_stool-customizer-row">
                <c span=3><input type="text" ng-model="field.label" placeholder="Field Label"/></c>
                <c span=3><input type="text" ng-model="field.key" placeholder="Field Key"/></c>
                <c span=2>
                  <select ng-model="field.type">
                    <option ng-repeat="option in posttypes.typeSelect" value="{{option}}">{{helper.ucfirst(option)}}</option>
                  </select>
                  <select ng-model="field.media_type" ng-if="field.type == 'media'">
                    <option ng-repeat="option in posttypes.mediaSelect" value="{{option}}">{{helper.ucfirst(option)}}</option>
                  </select>
                  <div ng-if="['select','radio'].includes(field.type)" class="_stool-select-options-wrap">
                    <span class="_stool-select-option" ng-repeat="option in field.options track by $index">
                      <input type="text" ng-model="field.options[$index]"><span class="_stool-remove-option" ng-click="posttypes.removeOption(field,$index)"><i class="mdi mdi-delete"></i></span>
                    </span>
                    <span class="button _stool-add-option" ng-click="posttypes.addOption(field)">Add option <i class="mdi mdi-plus"></i></span>
                  </div>
                </c>
                <c span=2><input type="text" ng-model="field.default" placeholder="Field Default"/></c>
                <c span=1 class="text-center"><i class="mdi mdi-delete" ng-click="posttypes.removeField(section,field)" title="Delete Field"></i></c>
                <c span=1 class="text-center" data-as-sortable-item-handle><i class="mdi mdi-cursor-move"></i></c>
              </grid>
            </div>
          </div>
          <div class="_stool-customizer-add">
            <div class="_stool-customizer-add-input">
              <input ng-model="posttypes.newFieldLabel[section.slug]" placeholder="New Field Key"/>
              <span class="button" ng-click="posttypes.addNewField(section)" title="Add Field"><i class="mdi mdi-plus"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="_stool-customizer-add">
      <div class="_stool-customizer-add-input _stool-customizer-add-section">
        <input ng-model="posttypes.newSectionTitle" placeholder="New Post Type Name"/>
        <span class="button" ng-click="posttypes.addNewSection()" title="Add Post Type"><i class="mdi mdi-plus"></i></span>
      </div>
    </div>
  </c>

</grid>