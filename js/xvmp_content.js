var VimpContent = {

	selected_media: [],

	ajax_base_url: '',

	template: '',

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
					"cmd": "renderItem",
					"tpl": VimpContent.template,
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
				"cmd": "renderItem",
				"tpl": VimpContent.template,
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
		$('#xoct_waiter_modal').show();
		var $modal = $('#xvmp_modal_player');
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
			$('#xoct_waiter_modal').show();
			if (typeof VimpObserver != 'undefined') {
				VimpObserver.init(mid, response_object.time_ranges);
			}

			$modal.on('hidden', function() { // bootstrap 2.3.2
				$video = $('video')[0];
				if(typeof $video != 'undefined') {
					$video.pause();
				}
				$iframe = $('iframe');
				if (typeof $iframe != 'undefined') {
					$iframe.attr('src', '');
				}
			});

			$modal.on('hidden.bs.modal', function() {  // bootstrap 3
				$video = $('video')[0];
				if(typeof $video != 'undefined') {
					$video.pause();
				}
				$iframe = $('iframe');
				if (typeof $iframe != 'undefined') {
					$iframe.attr('src', '');
				}
			});
		});
	},

	copyDirectLink: function() {
		const link_tpl = document.getElementById('xvmp_direct_link_tpl').value;
		const el = document.createElement('textarea');
		el.value = link_tpl.replace('_TIME_', '');
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	},

	copyDirectLinkWithTime: function() {
		const link_tpl = document.getElementById('xvmp_direct_link_tpl').value;
		const el = document.createElement('textarea');
		el.value = link_tpl.replace('_TIME_', '_' + Math.floor(player.currentTime()));
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	}

}

