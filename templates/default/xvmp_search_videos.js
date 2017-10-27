var VimpSearch = {

	videos: [],

	base_link: String = "",


	add: function (event, mid, visible) {
		event.preventDefault();
		xoctWaiter.show();
		if (typeof visible == 'undefined') {
			visible = 1;
		}
		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);

		ajax_url = this.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				cmd: 'addVideo',
				mid: mid,
				visible: visible
			}
		}).always(function(data, textStatus, jqXHR) {
			add_button.hide();
			remove_button.show();
			xoctWaiter.hide();
		});
	},

	remove: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();

		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);

		ajax_url = this.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				cmd: 'removeVideo',
				mid: mid
			}
		}).always(function(data, textStatus, jqXHR) {
			console.log(remove_button);
			console.log(add_button);
			remove_button.hide();
			add_button.show();
			xoctWaiter.hide();
		});
	}
}

