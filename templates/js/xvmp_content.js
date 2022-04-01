var VimpContent = {

  selected_media: [],

  ajax_base_url: '',

  template: '',

  copy_link_template: '<input type=\'text\' id=\'xvmp_direct_link_tpl\' value=\'\' hidden>',

  init: function () {
    addEventListener('xvmp_copy_direct_link', function (event) {
      this.copyDirectLink();
    });
    addEventListener('xvmp_copy_direct_link_with_time', function (event) {
      this.copyDirectLinkWithTime();
    });
  },

	loadTiles: function () {
		$(VimpContent.selected_media).each(function(key, mid) {
			$.get({
				url: VimpContent.ajax_base_url,
				data: {
					"cmd": "renderItem" + VimpContent.template,
					"mid": mid,
				}
			}).always(function(response) {
				if (response === 'deleted') {
						$('div#box_'+mid).hide();
				} else {
					$('div#xvmp_tile_'+mid).html(response);
					$('div#xvmp_tile_'+mid).removeClass('waiting');
				}
			});
		});
	},

	loadTilesInOrder: function(key) {
		var mid = VimpContent.selected_media[key];
		$.get({
			url: VimpContent.ajax_base_url,
			data: {
				"cmd": "render" + VimpContent.template,
				"mid": mid
			}
		}).always(function(response) {
			if (response === 'deleted') {
				$('div#box_'+mid).hide();
			} else {
				$('div#xvmp_tile_' + mid).html(response);
				$('div#xvmp_tile_' + mid).removeClass('waiting');
			}
			if (typeof VimpContent.selected_media[key + 1] !== 'undefined') {
				VimpContent.loadTilesInOrder(key + 1);
			}
		});
	},

	playVideo: function (mid) {
		console.log('playVideo ' + mid)
		var $modal = $('#xvmp_modal_player');
		$modal.find('h4.modal-title').html('');
		$modal.find('div#xvmp_video_container').html('');
		$modal.modal('show');
		$.get({
			url: this.ajax_base_url,
			data: {
				"cmd": "fillModalPlayer",
				"mid": mid
			}
		}).always(function(response) {
			response_object = JSON.parse(response);
			$modal.find('div#xvmp_video_container').html(response_object.html);
			$modal.find('h4.modal-title').html(response_object.video_title);
			if (typeof VimpObserver != 'undefined') {
				VimpObserver.init(mid, response_object.time_ranges);
			}

      $modal.on('hidden', function () { // bootstrap 2.3.2
        $video = $('video')[0];
        if (typeof $video != 'undefined') {
          $video.pause();
        }
        $iframe = $('iframe');
        if (typeof $iframe != 'undefined') {
          $iframe.attr('src', '');
        }
      });

      $modal.on('hidden.bs.modal', function () {  // bootstrap 3
        $video = $('video')[0];
        if (typeof $video != 'undefined') {
          $video.pause();
        }
        $iframe = $('iframe');
        if (typeof $iframe != 'undefined') {
          $iframe.attr('src', '');
        }
      });
    });
  },

  copyDirectLink: function (link_tpl) {
    this.copyToClipboard(link_tpl);
  },

  copyDirectLinkWithTime: function (link_tpl) {
    let currentTime = '_' + Math.floor(player.currentTime());
    let link = link_tpl.replace('_0.', currentTime + '.').replace('_0&', currentTime + '&');
    this.copyToClipboard(link);
  },

  copyToClipboard: function (textToCopy) {
    // navigator clipboard api needs a secure context (https)
    if (navigator.clipboard && window.isSecureContext) {
      // navigator clipboard api method'
      return navigator.clipboard.writeText(textToCopy);
    } else {
      // text area method
      let textArea = document.createElement("textarea");
      textArea.value = textToCopy;
      // make the textarea out of viewport
      textArea.style.position = "fixed";
      textArea.style.left = "-999999px";
      textArea.style.top = "-999999px";
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      return new Promise((res, rej) => {
        // here the magic happens
        document.execCommand('copy') ? res() : rej();
        textArea.remove();
      });
    }
  }

}

