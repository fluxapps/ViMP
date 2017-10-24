var VimpConfig = {
	test_connection: function (event) {
		event.preventDefault();

		display_result = $('span#xvmp_connection_status');
		display_result.text('Sending Request...');
		ajax_url = $('a#xvmp_test_connection').attr('href');
		api_key = $('input#api_key').val();
		api_url = $('input#api_url').val();
		url = api_url.replace(/\/+$/,'') + '/version';

		$.ajax({
			url: ajax_url,
			type: "GET",
			data: "apikey=" + api_key + "&apiurl=" + api_url
			// timeout: 5000
		}).always(function(data, textStatus, jqXHR) {
			display_result.text(data);
		});
	}
}

