var VimpContent = {

	selected_media: [],

	ajax_base_url: String,

	template: String,

	loadTiles: function () {
		// console.log(this.selected_media);
		$(this.selected_media).each(function(key, mid) {
			$.get({
				url: this.ajax_base_url,
				data: {
					"cmd": "renderItem",
					"tpl": this.template,
					"mid": mid
				}
			}).always(function(response) {
				$('div#xvmp_tile_'+mid).html(response);
				$('div#xvmp_tile_'+mid).removeClass('waiting');
			});
		});
	},

	loadTilesInOrder: function(key) {
		// console.log('loadTilesInOrder ' + key);
		var mid = VimpContent.selected_media[key];
		$.get({
			url: this.ajax_base_url,
			data: {
				"cmd": "renderItem",
				"tpl": this.template,
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
			$modal.find('section').html(response_object.html);
			$modal.find('h4.modal-title').html(response_object.video_title);
			$('iframe').load(function() {
				console.log('iframe loaded');
				$('#xoct_waiter_modal').hide();
			});
		});


	}

}

