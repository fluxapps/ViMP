var VimpContent = {

	selected_media: [],

	url_load_tile: String,

	embed_codes: [],

	video_titles: [],

	loadTiles: function () {
		console.log(this.selected_media);
		ajax_url = this.url_load_tile;
		$(this.selected_media).each(function(key, mid) {
			$.get({
				url: ajax_url,
				data: {
					"mid": mid
				}
			}).always(function(response) {
				$('div#xvmp_tile_'+mid).html(response);
				$('div#xvmp_tile_'+mid).removeClass('waiting');
			});
		});
	},

	loadTilesInOrder: function(key) {
		console.log('loadTilesInOrder ' + key);
		ajax_url = this.url_load_tile;
		var mid = VimpContent.selected_media[key];
		$.get({
			url: ajax_url,
			data: {
				"mid": mid
			}
		}).always(function(response) {
			$('div#xvmp_tile_'+mid).html(response);
			$('div#xvmp_tile_'+mid).removeClass('waiting');
			if (typeof VimpContent.selected_media[key + 1] !== 'undefined') {
				VimpContent.loadTilesInOrder(key + 1);
			}
		});
	},

	playVideo: function (mid) {
		console.log('playVideo ' + mid)
		xoctWaiter.show();
		var $modal = $('#xvmp_modal_player');
		$modal.modal('show');
		$modal.find('h4.modal-title').html(this.video_titles[mid]);
		$modal.find('section').html(this.embed_codes[mid]);
		$iframe = $('iframe').load(function() {
			xoctWaiter.hide();
		});
	}

}

