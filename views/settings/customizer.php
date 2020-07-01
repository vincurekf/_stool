<?php
$value = !empty($value) ? "{}" != $value ? $value : '[]' : '[]';
?>

<r-grid columns=12 id="customizer-fields">

  <r-cell span=row>
    <div class="_stool-customizer-info">
      <p>Here you can define your own Customizer sections and fields.<br>These fields will then show up on the <a href="<?php echo esc_url(admin_url("customize.php")); ?>">Customizer</a></p>
    </div>
    <label class="_stool-label"<?php echo $tooltip; ?>><span class="_stool-label-title"><?php echo $label; ?></span></label>
    <textarea style="display: none;" id="<?php echo $id; ?>" class="_stool-input" name="<?php echo $id; ?>" ng-model="form.data.<?php echo $id; ?>"><?php echo $value; ?></textarea>
  </r-cell>

  <r-cell span=row>

    <div data-as-sortable="customizer.dragControlListeners" data-ng-model="customizer.sections">
      <div class="_stool-customizer-section" data-ng-repeat="section in customizer.sections" data-as-sortable-item>
        <r-grid columns=12 class="_stool-customizer-section-header">
          <r-cell span=10><h3 class="_stool-customizer-section-title">{{ section.title }}</h3></r-cell>
          <r-cell span=1 class="text-center"><i class="mdi mdi-delete" ng-click="customizer.removeSection(section)" title="Delete Section"></i></r-cell>
          <r-cell span=1 class="text-right">
            <i class="mdi mdi-cursor-move" data-as-sortable-item-handle></i>
            <i class="mdi mdi-chevron-down" ng-click="customizer.collapse[section.key] = !customizer.collapse[section.key]"></i>
          </r-cell>
        </r-grid>
        <div class="_stool-customizer-section-fields" ng-class="{'collapsed':customizer.collapse[section.key]}">
          <r-grid columns=12 class="_stool-customizer-section-subtitle">
            <r-cell span=row>
              <h3>Section</h3>
            </r-cell>
          </r-grid>
          <r-grid columns=12 class="_stool-customizer-section-config">
            <r-cell span=4>
              <label><span>Title</span><input type="text" class="_stool-customizer-section-title" ng-model="section.title" placeholder="Section Title"/></label>
            </r-cell>
            <r-cell span=4>
              <label><span>Priority</span><input type="number" ng-model="section.priority" placeholder="Section Priority"/></label>
            </r-cell>
            <r-cell span=4>
              <label><span>KEY</span><input type="text" ng-model="section.key" placeholder="Section KEY"/></label>
              <!--input type="text" ng-model="section.key" placeholder="Section Key" ng-class="{\'disabled\':section.locked}"/-->
            </r-cell>
          </r-grid>
          <r-grid columns=12 class="_stool-customizer-section-subtitle">
            <r-cell span=row>
              <h3>Section Fields</h3>
            </r-cell>
          </r-grid>
          <r-grid columns=12 class="_stool-customizer-row _stool-customizer-row-header">
            <r-cell span=3>Label</r-cell>
            <r-cell span=3>Key</r-cell>
            <r-cell span=2>Type</r-cell>
            <r-cell span=2>Default</r-cell>
            <r-cell span=1 class="text-center">Delete</r-cell>
            <r-cell span=1 class="text-center">Move</r-cell>
          </r-grid>
          <div data-as-sortable="customizer.dragControlListeners" data-ng-model="section.fields">
            <div class="_stool-customizer-field" data-ng-repeat="field in section.fields" data-as-sortable-item>
              <r-grid columns=12 class="_stool-customizer-row">
                <r-cell span=3><input type="text" ng-model="field.label" placeholder="Field Label"/></r-cell>
                <r-cell span=3><input type="text" ng-model="field.key" placeholder="Field Key"/></r-cell>
                <r-cell span=2>
                  <select ng-model="field.type">
                    <option ng-repeat="option in customizer.typeSelect" value="{{option}}">{{helper.ucfirst(option)}}</option>
                  </select>
                  <div ng-if="['select','radio'].includes(field.type)" class="_stool-select-options-wrap">
                    <span class="_stool-select-option" ng-repeat="option in field.choices track by $index">
                      <input type="text" ng-model="field.choices[$index]"><span class="_stool-remove-option" ng-click="customizer.removeOption(field,$index)"><i class="mdi mdi-delete"></i></span>
                    </span>
                    <span class="button _stool-add-option" ng-click="customizer.addOption(field)">Add option <i class="mdi mdi-plus"></i></span>
                  </div>
                </r-cell>
                <r-cell span=2><input type="text" ng-model="field.default" placeholder="Field Default"/></r-cell>
                <r-cell span=1 class="text-center"><i class="mdi mdi-delete" ng-click="customizer.removeField(section,field)" title="Delete Field"></i></r-cell>
                <r-cell span=1 class="text-center" data-as-sortable-item-handle><i class="mdi mdi-cursor-move"></i></r-cell>
              </r-grid>
            </div>
          </div>
          <div class="_stool-customizer-add">
            <div class="_stool-customizer-add-input">
              <input ng-model="customizer.newFieldLabel[section.key]" placeholder="New Field Key"/>
              <span class="button" ng-click="customizer.addNewField(section)" title="Add Field"><i class="mdi mdi-plus"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="_stool-customizer-add">
      <div class="_stool-customizer-add-input _stool-customizer-add-section">
        <input ng-model="customizer.newSectionTitle" placeholder="New Section Title"/>
        <span class="button" ng-click="customizer.addNewSection()" title="Add Section"><i class="mdi mdi-plus"></i></span>
      </div>
    </div>
  </r-cell>

</r-grid>