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
		var row = $('#xvmp_row_'+mid);

		ajax_url = this.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				cmd: 'addVideo',
				mid: mid,
				visible: visible
			}
		}).always(function(response) {
			try {
				response = JSON.parse(response);
			} catch (error) {
				response = '{"success" = false}';
			}

			if (response.success) {
				add_button.hide();
				remove_button.show();
				row.removeClass('xvmp_row_not_added');
				row.addClass('xvmp_row_added');
				xoctWaiter.hide();
			} else {
				xoctWaiter.hide();
				alert('Authentication failed. Please log in again.');
			}
		});
	},

	remove: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();

		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);
		var row = $('#xvmp_row_'+mid);

		ajax_url = this.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				cmd: 'removeVideo',
				mid: mid
			}
		}).always(function(response) {
			try {
				response = JSON.parse(response);
			} catch (error) {
				response = '{"success" = false}';
			}

			if (response.success) {
				remove_button.hide();
				add_button.show();
				row.addClass('xvmp_row_not_added');
				row.removeClass('xvmp_row_added');
				xoctWaiter.hide();
			} else {
				xoctWaiter.hide();
				alert('Authentication failed. Please log in again.');
			}
		});
	}
}

