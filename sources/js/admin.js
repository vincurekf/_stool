(function ($) {

  console.log('_stool::admin');

  var selects = {
    init: function () {
      var self = this;
      self.watch();
      $(document).on('widget-updated', function () {
        console.log('widget-updated');
        self.watch();
      });
    },
    watch: function () {
      var self = this;
      if ($('[data-stool-post-selects]').length > 0) {
        $('[data-stool-post-selects]').each(function () {
          //
          var $box = $(this);
          var $id = $box.data('stool-post-selects');
          var $type = $box.find('[data-stool-select="post-type"]');
          var $category = $box.find('[data-stool-select="post-category"]');
          //
          self.hideCats($id, $type.val());
          //
          $type.off().on('change', function () {
            var val = $type.val();
            //
            self.hideCats($id, val, $category);
            //
            var $cat = $category.find('[data-stool-cat="' + $category.val() + '"]');
            var $cat_type = $cat.data('stool-post-type');
            //
            if ($cat_type !== val && val !== 'all') {
              $category.val('all');
            }
          });
          //
        });
      };
    },
    hideCats: function (id, type) {
      console.log(type);
      if (type === 'all') {
        $('option.stool-box-' + id).show();
      } else {
        $('option.stool-box-' + id).each(function () {
          var _type = $(this).data('stool-post-type');
          if (_type === type) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }
    }
  };
  selects.init();

  // TRANSIENTS
  if ($('.stool-component-purge-cache').length > 0) {
    $('.stool-component-purge-cache').on('click', function (e) {
      e.preventDefault();
      var $check = $(this);
      //
      $.ajax({
        type: "POST",
        url: _stool_ajax.ajax_url,
        data: {
          action: "_stool_purgeCache",
          //post_id: $id,
          //meta_id: $meta
        },
        success: function (result) {
          console.log(result.data);
          _adminNotice('_stool' ,': Caches successfuly cleared.');
        }
      });
    });
  };

  /**
   * Create and show a dismissible admin notice
   */
  function _adminNotice(title, msg) {
    /*
    <div id="message" class="updated notice is-dismissible">
      <p>Plugin <strong>activated</strong>.</p>
      <button type="button" class="notice-dismiss">
        <span class="screen-reader-text">Dismiss this notice.</span>
      </button>
    </div>
    */
    /* create notice div */
    var div = document.createElement('div');
    div.classList.add('notice', 'notice-info', 'is-dismisable');
    /* create paragraph element to hold message */
    var p = document.createElement('p');
    var t = document.createElement('strong');
        t.appendChild(document.createTextNode(title));
        p.appendChild(t);
        p.appendChild(document.createTextNode(msg));
    /* Add the whole message to notice div */
    div.appendChild(p);
    /* Create Dismiss icon */
    var b = document.createElement('button');
        b.setAttribute('type', 'button');
        b.classList.add('notice-dismiss');
    /* Add screen reader text to Dismiss icon */
    var bSpan = document.createElement('span');
        bSpan.classList.add('screen-reader-text');
        bSpan.appendChild(document.createTextNode('Dismiss this notice'));
    b.appendChild(bSpan);
    //
    /* Add Dismiss icon to notice */
    div.appendChild(b);
    //
    /* Insert notice after the first h1 */
    var parent = document.getElementById('_stool-settings');
    if( parent ){
      parent.insertBefore(div, parent.childNodes[0]);
    } else{
      //
      parent = document.getElementsByClassName( 'wp-header-end' )[0];
      if( !parent ) parent = document.getElementsByTagName( 'h1' )[0];
      //
      parent.parentNode.insertBefore(div, parent.nextSibling);
    }
    /* Make the notice dismissable when the Dismiss icon is clicked */
    b.addEventListener('click', function () {
      div.parentNode.removeChild(div);
    });
  };

})(jQuery);