require('angular');
require('ng-sortable');
var _ = require('underscore');

(function ($){

	var _tinyMCE;
	try {
		_tinyMCE = window.tinyMCE;
	} catch (error) {
		_tinyMCE = undefined;
	}

	var _stool = angular.module('_stool', ['as.sortable']);
	_stool.run(['$rootScope', '$http', function ($rootScope, $http) {
		//
		console.log('_stool::settings');

		//
		$rootScope.form = {
			loading: false,
			success: false,
			error: false,
			data: {},
			responsetimeout: null,
			init: function () {
				var self = this;
				self.form = $('#_stool-settings-form');
				self.watch();
			},
			different: function(){
				var self = this;
				if(_.isUndefined(self.initial)) return false;
				//console.log(self.initial);
				//console.log(angular.copy(self.data));
				return !_.isEqual(self.initial, angular.copy(self.data));
			},
			reset: function(){
				var self = this;
				self.data = angular.copy(self.initial);
				$rootScope.customizer.sections = angular.copy($rootScope.customizer.initial);
				$rootScope.posttypes.sections = angular.copy($rootScope.posttypes.initial);
			},
			submit: function (event) {
				event.preventDefault();
				event.stopPropagation();
				var self = this;
				//
				if (!_.isUndefined(_tinyMCE)) _tinyMCE.triggerSave();
				//
				self.loading = true;
				self.success = false;
				self.error = false;
				if (self.responsetimeout) clearTimeout(self.responsetimeout);
				var form = $('#_stool-settings-form');
				$.ajax({
					url: _stool_ajax.options_url,
					type: self.form.attr('method'),
					data: self.form.serialize()
				}).done(function (result) {
					var old_data = angular.copy(self.initial);
					//
					self.loading = false;
					self.success = true;
					self.initial = angular.copy(self.data);
					$rootScope.customizer.initial = angular.copy($rootScope.customizer.sections);
					$rootScope.posttypes.initial = angular.copy($rootScope.posttypes.sections);
					$rootScope.$apply();
					//
					/*
					$http({
						method: 'POST',
						url: _stool_ajax.ajax_url,
						params: {
							action: '_stool_settings_saved',
						},
						data: {
							old_data: old_data,
							new_data: angular.copy(self.data),
						}
					}).then(function successCallback(response) {
						console.log(response);
					}, function errorCallback(response) {
						console.error(response);
					});
					*/
					//
					self.responsetimeout = setTimeout(function () {
						self.success = false;
						$rootScope.$apply();
					}, 5000);
				}).fail(function (error) {
					console.log('error', error);
					self.loading = false;
					self.error = true;
					$rootScope.$apply();
					self.responsetimeout = setTimeout(function () {
						self.error = false;
						$rootScope.$apply();
					}, 5000);
				});
			},
			watch: function () {
				var self = this;
				//
				window.onload = function() {
					self.initial = angular.copy(self.data);
				};
				//
				window.onbeforeunload = function () {
					if (self.different()) {
						return 'Are you sure you want to leave?';
					}
				};
				//
				$rootScope.$watch('form.data', function () {
					//$rootScope.image.test();
				}, true);
			}
		}; $rootScope.form.init();
		//

		$rootScope.image = {
			padding: function(){
				var ratio = $rootScope.form.data._stool_thumbnails_aspect;
				if ($rootScope.form.data._stool_thumbnails_aspect == 'custom') ratio = $rootScope.form.data._stool_thumbnails_custom;
				//
				ratio = ratio.split(':');
				var ratio_height = ratio[1] / ratio[0] * 100;
				return ratio_height + '%;';
			}
		}

		//
		$rootScope.tabs = {
			activeTab: 'panel-global',
			init: function () {
				var self = this;
				self.check();
			},
			changetab: function(id){
				var self = this;
				self.activeTab = id;
				self.setHash(id);
				$('._stool-color').wpColorPicker({
					change: function (event){
						var val = $(event.target).val();
						var model = $(event.target).attr('ng-model');
								model = model.split('.');
								model = model[model.length-1];
						//
						$rootScope.form.data[model] = val;
						$rootScope.$apply();
					}
				});
			},
			check: function () {
				var self = this;
				if (location.hash) {
					//window.scrollTo(0, 0);
					self.changetab(location.hash.substr(1));
					$rootScope.$apply();
				}
			},
			setHash: function (id) {
				if (!_.isEmpty(id)) {
					if (history.pushState) {
						history.pushState(null, null, '#' + id);
						//window.scrollTo(0, 0);
					}
				}
			}
		}; $rootScope.tabs.init();
		//

		//
		$rootScope.mmenu = {
			active: false,
			init: function () {

			},
			toggle: function () {
				this.active = !this.active;
			},
			class: function () {
				var formdata = $rootScope.form.data;
				if (!formdata._stool_menu_transition) return '';
				return '_stool-easing ' + formdata._stool_menu_transition_type;
			},
			style: function () {
				var formdata = $rootScope.form.data;
				if (!formdata._stool_menu_transition) return '';
				var styles = 'transition-property: all;' +
					'transition-duration: ' + formdata._stool_menu_transition_time + 's;' +
					'width: ' + formdata._stool_menu_width + '%;';
				return styles;
			}
		}
		//

		//
		$rootScope.customizer = {
			collapse: {},
			muster_section: {
				"key": "",
				"title": "",
				"priority": 1000,
				"fields": []
			},
			muster_field: {
				"key": "",
				"default": "",
        "label": "",
				"type": "text",
				"choices": []
			},
			typeSelect: [
				"image",
				"checkbox",
				"text",
				"radio",
				"select",
				"textarea",
				"email",
				"url",
				"number",
				"date",
				"color"
			],
			ucfirst: function(string){
				return string.charAt(0).toUpperCase() + string.slice(1);
			},
			sections: [],
			init: function(){
				var self = this;
				var val = $('#_stool_customizer').val();
				self.sections = JSON.parse(val);
				self.initial = JSON.parse(val);
				//
				$rootScope.$watch('customizer.sections',function(newVal,oldVal){
					//
					var json = JSON.stringify(angular.copy(newVal));
					$('#_stool_customizer').val(json);
					//
					$rootScope.form.data._stool_customizer = json;
					//console.log($rootScope.form.data._stool_customizer);
					//
				}, true);
			},
			newSectionTitle: '',
			addNewSection: function(){
				var exists = _.find(this.sections, {'key':this.newSectionTitle} );
				if( exists ) return alert('Does exist');
				var title = angular.copy(this.newSectionTitle);
				var key = $rootScope.helper.slug(title);
				this.newSectionTitle = '';
				//
				var newSection = angular.copy(this.muster_section);
				newSection.key = key;
				newSection.title = title;
				this.sections.push(newSection);
			},
			newFieldLabel: {},
			addNewField: function(section){
				var exists = _.find(section.fields, {'key':this.newFieldLabel[section.key]} );
				if( exists ) return alert('Does exist');
				var label = angular.copy(this.newFieldLabel[section.key]);
				var key = section.key + '_' + $rootScope.helper.slug(label);
				this.newFieldLabel[section.key] = '';
				//
				var newField = angular.copy(this.muster_field);
				newField.key = key;
				newField.label = label;
				console.log('newField',newField);
				section.fields.push(newField);
				console.log('section',section);
			},
			removeSection: function(section){
				var sections = angular.copy(section.sections);
				var filtered = _.reject(this.sections, function(val){
					return val.key === section.key;
				});
				this.sections = filtered;
				//
				console.log('this.sections',this.sections);
			},
			removeField: function(section,field){
				var fields = angular.copy(section.fields);
				console.log('fields',fields);
				//
				var filtered = _.reject(fields, function(val){
					return val.key === field.key;
				});
				section.fields = filtered;
				//
				console.log('section.fields',section.fields);
			},
			addOption: function(field){
				console.log(field);
				if(_.isUndefined(field.choices)){
					field.choices = [];
				}
				field.choices.push("");
			},
			removeOption: function(field,option){
				console.log(field,option);
				field.choices.splice(option, 1);
				console.log(field.choices);
			},
			slug: function(str) {
				str = str.replace(/^\s+|\s+$/g, ''); // trim
				str = str.toLowerCase();
				// remove accents, swap ñ for n, etc
				var from = "ÁÄÂÀÃÅČÇĆĎÉĚËÈÊẼĔȆÍÌÎÏŇÑÓÖÒÔÕØŘŔŠŤÚŮÜÙÛÝŸŽáäâàãåčçćďéěëèêẽĕȇíìîïňñóöòôõøðřŕšťúůüùûýÿžþÞĐđßÆa·/_,:;";
				var to   = "aaaaaacccdeeeeeeeeiiiinnoooooorrstuuuuuyyzaaaaaacccdeeeeeeeeiiiinnooooooorrstuuuuuyyzbbddbaa------";
				for (var i=0, l=from.length ; i<l ; i++) {
					str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
				}
				str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
					.replace(/\s+/g, '_') // collapse whitespace and replace by -
					.replace(/-+/g, '_'); // collapse dashes
				return str;
			},
			dragControlListeners: {
				//override to determine drag is allowed or not. default is true.
				accept: function (sourceItemHandleScope, destSortableScope) {
					//console.log(sourceItemHandleScope);
					//console.log(destSortableScope);
					return true
				},
				itemMoved: function (event) {},
				orderChanged: function(event) {},
				containerPositioning: 'relative',
				containment: '#wpwrap',
				allowDuplicates: false,
				//clone: true,
			}
		};
		$rootScope.customizer.init();
		//

		//
		$rootScope.posttypes = {
			collapse: {},
			muster_section: {
				"slug": "",
				"main": "",
				"single": "",
				"add": "",
				"of": "",
				"icon": "",
				"metaboxes": []
			},
			muster_field: {
				"key": "",
				"default": "",
        "label": "",
        "type": "text",
				"media_type": "image",
				"options": []
			},
			typeSelect: [
				"checkbox",
				"date",
				"datetime",
				"media",
				"text",
				"textarea",
				"tinymce",
				"color",
				"select",
			],
			mediaSelect: [
				"image",
				"video"
			],
			iconSelect: [
				"dashicons-menu",
				"dashicons-admin-site",
				"dashicons-dashboard",
				"dashicons-admin-post",
				"dashicons-admin-media",
				"dashicons-admin-links",
				"dashicons-admin-page",
				"dashicons-admin-comments",
				"dashicons-admin-appearance",
				"dashicons-admin-plugins",
				"dashicons-admin-users",
				"dashicons-admin-tools",
				"dashicons-admin-settings",
				"dashicons-admin-network",
				"dashicons-admin-home",
				"dashicons-admin-generic",
				"dashicons-admin-collapse",
				"dashicons-welcome-write-blog",
				"dashicons-welcome-add-page",
				"dashicons-welcome-view-site",
				"dashicons-welcome-widgets-menus",
				"dashicons-welcome-comments",
				"dashicons-welcome-learn-more",
				"dashicons-format-aside",
				"dashicons-format-image",
				"dashicons-format-gallery",
				"dashicons-format-video",
				"dashicons-format-status",
				"dashicons-format-quote",
				"dashicons-format-chat",
				"dashicons-format-audio",
				"dashicons-camera",
				"dashicons-images-alt",
				"dashicons-images-alt2",
				"dashicons-video-alt",
				"dashicons-video-alt2",
				"dashicons-video-alt3",
				"dashicons-image-crop",
				"dashicons-image-rotate-left",
				"dashicons-image-rotate-right",
				"dashicons-image-flip-vertical",
				"dashicons-image-flip-horizontal",
				"dashicons-undo",
				"dashicons-redo",
				"dashicons-editor-bold",
				"dashicons-editor-italic",
				"dashicons-editor-ul",
				"dashicons-editor-ol",
				"dashicons-editor-quote",
				"dashicons-editor-alignleft",
				"dashicons-editor-aligncenter",
				"dashicons-editor-alignright",
				"dashicons-editor-insertmore",
				"dashicons-editor-spellcheck",
				"dashicons-editor-distractionfree",
				"dashicons-editor-kitchensink",
				"dashicons-editor-underline",
				"dashicons-editor-justify",
				"dashicons-editor-textcolor",
				"dashicons-editor-paste-word",
				"dashicons-editor-paste-text",
				"dashicons-editor-removeformatting",
				"dashicons-editor-video",
				"dashicons-editor-customchar",
				"dashicons-editor-outdent",
				"dashicons-editor-indent",
				"dashicons-editor-help",
				"dashicons-editor-strikethrough",
				"dashicons-editor-unlink",
				"dashicons-editor-rtl",
				"dashicons-align-left",
				"dashicons-align-right",
				"dashicons-align-center",
				"dashicons-align-none",
				"dashicons-lock",
				"dashicons-calendar",
				"dashicons-visibility",
				"dashicons-post-status",
				"dashicons-edit",
				"dashicons-trash",
				"dashicons-arrow-up",
				"dashicons-arrow-down",
				"dashicons-arrow-right",
				"dashicons-arrow-left",
				"dashicons-arrow-up-alt",
				"dashicons-arrow-down-alt",
				"dashicons-arrow-right-alt",
				"dashicons-arrow-left-alt",
				"dashicons-arrow-up-alt2",
				"dashicons-arrow-down-alt2",
				"dashicons-arrow-right-alt2",
				"dashicons-arrow-left-alt2",
				"dashicons-sort",
				"dashicons-leftright",
				"dashicons-list-view",
				"dashicons-exerpt-view",
				"dashicons-share",
				"dashicons-share-alt",
				"dashicons-share-alt2",
				"dashicons-twitter",
				"dashicons-rss",
				"dashicons-facebook",
				"dashicons-facebook-alt",
				"dashicons-googleplus",
				"dashicons-networking",
				"dashicons-hammer",
				"dashicons-art",
				"dashicons-migrate",
				"dashicons-performance",
				"dashicons-wordpress",
				"dashicons-wordpress-alt",
				"dashicons-pressthis",
				"dashicons-update",
				"dashicons-screenoptions",
				"dashicons-info",
				"dashicons-cart",
				"dashicons-feedback",
				"dashicons-cloud",
				"dashicons-translation",
				"dashicons-tag",
				"dashicons-category",
				"dashicons-yes",
				"dashicons-no",
				"dashicons-no-alt",
				"dashicons-plus",
				"dashicons-minus",
				"dashicons-dismiss",
				"dashicons-marker",
				"dashicons-star-filled",
				"dashicons-star-half",
				"dashicons-star-empty",
				"dashicons-flag",
				"dashicons-location",
				"dashicons-location-alt",
				"dashicons-vault",
				"dashicons-shield",
				"dashicons-shield-alt",
				"dashicons-search",
				"dashicons-slides",
				"dashicons-analytics",
				"dashicons-chart-pie",
				"dashicons-chart-bar",
				"dashicons-chart-line",
				"dashicons-chart-area",
				"dashicons-groups",
				"dashicons-businessman",
				"dashicons-id",
				"dashicons-id-alt",
				"dashicons-products",
				"dashicons-awards",
				"dashicons-forms",
				"dashicons-portfolio",
				"dashicons-book",
				"dashicons-book-alt",
				"dashicons-download",
				"dashicons-upload",
				"dashicons-backup",
				"dashicons-lightbulb",
				"dashicons-smiley",
			],
			sections: [],
			init: function(){
				var self = this;
				var val = $('#_stool_posttypes').val();
				self.sections = JSON.parse(val);
				self.initial = JSON.parse(val);
				//
				$rootScope.$watch('posttypes.sections',function(newVal,oldVal){
					//
					var json = JSON.stringify(angular.copy(newVal));
					$('#_stool_posttypes').val(json);
					//
					$rootScope.form.data._stool_posttypes = json;
					//console.log($rootScope.form.data._stool_posttypes);
					//
				}, true);
			},
			newSectionTitle: '',
			addNewSection: function(){
				//
				var exists = _.find(this.sections, {'slug':this.newSectionTitle} );
				if( exists ) return alert('Does exist');
				var title = angular.copy(this.newSectionTitle);
				var slug = $rootScope.helper.slug(title);
				this.newSectionTitle = '';
				//
				var newSection = angular.copy(this.muster_section);
				newSection.slug = slug;
				newSection.main = title;
				newSection.single = title;
				newSection.add = title;
				newSection.of = title;
				this.sections.push(newSection);
				//
			},
			newFieldLabel: {},
			addNewField: function(section){
				var exists = _.find(section.metaboxes, {'key':this.newFieldLabel[section.slug]} );
				if( exists ) return alert('Does exist');
				var label = angular.copy(this.newFieldLabel[section.slug]);
				var key = section.slug + '_' + $rootScope.helper.slug(label);
				this.newFieldLabel[section.slug] = '';
				//
				var newField = angular.copy(this.muster_field);
				newField.key = key;
				newField.label = label;
				console.log('newField',newField);
				section.metaboxes.push(newField);
				console.log('section',section);
			},
			removeSection: function(section){
				var sections = angular.copy(section.sections);
				var filtered = _.reject(this.sections, function(val){
					return val.key === section.key;
				});
				this.sections = filtered;
				//
				console.log('this.sections',this.sections);
			},
			removeField: function(section,field){
				var metaboxes = angular.copy(section.metaboxes);
				console.log('metaboxes',metaboxes);
				//
				var filtered = _.reject(metaboxes, function(val){
					return val.key === field.key;
				});
				section.metaboxes = filtered;
				//
				console.log('section.metaboxes',section.metaboxes);
			},
			addOption: function(field){
				console.log(field);
				if(_.isUndefined(field.options)){
					field.options = [];
				}
				field.options.push("");
			},
			removeOption: function(field,option){
				console.log(field,option);
				field.options.splice(option, 1);
				console.log(field.options);
			},
			slug: function(str) {
				str = str.replace(/^\s+|\s+$/g, ''); // trim
				str = str.toLowerCase();
				// remove accents, swap ñ for n, etc
				var from = "ÁÄÂÀÃÅČÇĆĎÉĚËÈÊẼĔȆÍÌÎÏŇÑÓÖÒÔÕØŘŔŠŤÚŮÜÙÛÝŸŽáäâàãåčçćďéěëèêẽĕȇíìîïňñóöòôõøðřŕšťúůüùûýÿžþÞĐđßÆa·/_,:;";
				var to   = "aaaaaacccdeeeeeeeeiiiinnoooooorrstuuuuuyyzaaaaaacccdeeeeeeeeiiiinnooooooorrstuuuuuyyzbbddbaa------";
				for (var i=0, l=from.length ; i<l ; i++) {
					str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
				}
				str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
					.replace(/\s+/g, '_') // collapse whitespace and replace by -
					.replace(/-+/g, '_'); // collapse dashes
				return str;
			},
			dragControlListeners: {
				//override to determine drag is allowed or not. default is true.
				accept: function (sourceItemHandleScope, destSortableScope) {
					//console.log(sourceItemHandleScope);
					//console.log(destSortableScope);
					return true
				},
				itemMoved: function (event) {},
				orderChanged: function(event) {},
				containerPositioning: 'relative',
				containment: '#wpwrap',
				allowDuplicates: false,
				//clone: true,
			}
		};
		$rootScope.posttypes.init();
		//



		//
		$rootScope.helper = {
			ucfirst: function(string){
				return string.charAt(0).toUpperCase() + string.slice(1);
			},
			slug: function(str) {
				str = str.replace(/^\s+|\s+$/g, ''); // trim
				str = str.toLowerCase();
				// remove accents, swap ñ for n, etc
				var from = "ÁÄÂÀÃÅČÇĆĎÉĚËÈÊẼĔȆÍÌÎÏŇÑÓÖÒÔÕØŘŔŠŤÚŮÜÙÛÝŸŽáäâàãåčçćďéěëèêẽĕȇíìîïňñóöòôõøðřŕšťúůüùûýÿžþÞĐđßÆa·/_,:;";
				var to   = "aaaaaacccdeeeeeeeeiiiinnoooooorrstuuuuuyyzaaaaaacccdeeeeeeeeiiiinnooooooorrstuuuuuyyzbbddbaa------";
				for (var i=0, l=from.length ; i<l ; i++) {
					str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
				}
				str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
					.replace(/\s+/g, '_') // collapse whitespace and replace by -
					.replace(/-+/g, '_'); // collapse dashes
				return str;
			},
		};
		//

	}]);

})(jQuery);