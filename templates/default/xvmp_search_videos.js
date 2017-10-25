var VimpSearch = {

	add: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();
		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);

		ajax_url = add_button.attr('href');
		$.ajax({
			url: ajax_url,
			type: "GET"
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

		ajax_url = remove_button.attr('href');
		$.ajax({
			url: ajax_url,
			type: "GET"
		}).always(function(data, textStatus, jqXHR) {
			remove_button.hide();
			add_button.show();
			xoctWaiter.hide();
		});
	}
}

