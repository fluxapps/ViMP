var VimpSearch = {

	add: function (event, mid) {
		event.preventDefault();
		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);
		var spinner = $('#xvmp_spinner_'+mid);

		add_button.hide();
		spinner.show();
		ajax_url = add_button.attr('href');
		$.ajax({
			url: ajax_url,
			type: "GET"
		}).always(function(data, textStatus, jqXHR) {
			spinner.hide();
			remove_button.show();
		});
	},

	remove: function (event, mid) {
		event.preventDefault();
		var remove_button = $('#xvmp_remove_'+mid);
		var add_button = $('#xvmp_add_'+mid);
		var spinner = $('#xvmp_spinner_'+mid);

		remove_button.hide();
		spinner.show();
		ajax_url = remove_button.attr('href');
		$.ajax({
			url: ajax_url,
			type: "GET"
		}).always(function(data, textStatus, jqXHR) {
			spinner.hide();
			add_button.show();
		});
	}
}

