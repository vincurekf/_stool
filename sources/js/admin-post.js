require('flatpickr');

(function ($) {

	console.log('_stool::admin-post');

	flatpickr.l10ns.default.firstDayOfWeek = 1; // Monday

	var _stoolMediaUpload = {
		uploader: {},
		fields: {},
		init: function () {
			var self = this;
			self.uploader = {};
			self.fields = {};
			//
			$('._stool-media-upload').each(function () {
				var $key = $(this).data('key');
				var $box = $(this);
				//
				self.fields[$key] = {};
				self.fields[$key].type = $box.data('type');
				//
				self.fields[$key].input = $box.children('._stool-media-input');
				self.fields[$key].url = self.fields[$key].input.val();
				//
				self.fields[$key].preview = $box.children('._stool-media-preview-wrap').children('._stool-media-preview');
				self.fields[$key].btn_erase = $('#' + $key + '_erase_button');
				self.fields[$key].btn_upload = $('#' + $key + '_upload_button');
				//
				if (_.isEmpty(self.fields[$key].url)) {
					self.fields[$key].btn_upload.show();
					self.fields[$key].btn_erase.hide();
					self.fields[$key].preview.hide();
				} else {
					self.fields[$key].btn_upload.hide();
				}
				//
				self.fields[$key].btn_erase.click(function (e) {
					self.fields[$key].btn_upload.show();
					self.fields[$key].btn_erase.hide();
					self.fields[$key].preview.hide();
					self.fields[$key].input.val('');
				});
				//
				self.fields[$key].btn_upload.click(function (e) {
					e.preventDefault();
					self.open_upload($key);
				});
				self.fields[$key].preview.click(function (e) {
					e.preventDefault();
					self.open_upload($key);
				});
				//
			});
		},
		open_upload: function ($key) {
			var self = this;
			//If the uploader object has already been created, reopen the dialog
			if (self.uploader[$key]) {
				self.uploader[$key].open();
				return;
			}
			//Extend the wp.media object
			self.uploader[$key] = wp.media.frames.file_frame = wp.media({
				title: "Choose a " + self.fields[$key].type,
				button: {
					text: "Select"
				},
				multiple: false
			});
			//When a file is selected, grab the URL and set it as the text field"s value
			self.uploader[$key].on("select", function () {
				var attachment = self.uploader[$key].state().get("selection").first().toJSON();
				//
				self.fields[$key].input.val(attachment.url);
				self.fields[$key].preview.attr("src", attachment.url);
				self.fields[$key].preview.show();
				self.fields[$key].btn_upload.hide();
				self.fields[$key].btn_erase.show();
				//
			});
			self.uploader[$key].open();
		}
	};

	if ($('._stool-media-upload').length > 0) {
		_stoolMediaUpload.init();
	};

	if ($('._stool-component-wrap').length > 0) {
		//
		$('._stool-component-wrap').each(function () {
			//
			var $wrap = $(this);
			var $parent = $wrap.parent().parent();
			var $iconurl = $wrap.data('icon');
			var $hndle = $parent.children('.hndle');
			//
			$hndle.attr('id', $parent.attr('id') + '_hndle');
			$hndle.addClass('_stool-component-hndle');
			$parent.addClass('_stool-component-postbox');
			//
			var $imagecontainer = document.getElementById($parent.attr('id') + '_hndle');
			if( $imagecontainer ) $imagecontainer.insertAdjacentHTML("afterbegin", '<img class="_stool-component-icon" src="' + $iconurl + '"></img>');
			//
		});
	};

	if ($('._stool-component > input[type="datetime-local"]').length > 0 ){
		//
		$('._stool-component > input[type="datetime-local"]').each(function(){
			$el = $(this);
			console.log($el);
			$el.flatpickr({
				dateFormat: 'U',
				enableTime: true,
				time_24hr: true,
				altInput: true,
				altFormat: 'j. n. Y H:i',
			});
			//
		})
	}

	if ($('._stool-component > input[type="date"]').length > 0 ){
		//
		$('._stool-component > input[type="date"]').each(function(){
			$el = $(this);
			console.log($el);
			$el.flatpickr({
				dateFormat: 'U',
				altInput: true,
				altFormat: 'j. n. Y',
			});
			//
		})
	}

	if ($(".color-field").length > 0) {
		$(".color-field").each(function () {
			$(this).wpColorPicker();
		});
	}

	if( $('[data-stool-checkbox]').length > 0 ){
		$('[data-stool-checkbox]').on('click',function(e){
			e.preventDefault();
			//
			var $check = $(this);
			var $id = $check.data('post-id');
			var $meta = $check.data('meta-name');
			//
			$.ajax({
				type: "POST",
				url: _stool_ajax.ajax_url,
				data: {
					action: "_stool_updateCheckbox",
					post_id: $id,
					meta_id: $meta
				},
				success: function(result){
					if(result.data.val === "on"){
						$check.addClass("active");
					}else{
						$check.removeClass("active");
					}
				}
			});
		});
  }

  if( $('.stool-dynamic-field').length > 0 ){
    $('.stool-dynamic-field').each(function(){
      var $el = $(this);
      //
      var initial = $( 'input[id='+$el.data('driver')+']' ).attr("checked");
      initial ? $el.slideDown() : $el.slideUp();
      //
      $( 'input[id='+$el.data('driver')+']' ).on("change",function(){
        $(this).attr("checked") ? $el.slideDown() : $el.slideUp();
      })
      //
    });
  }

})(jQuery);