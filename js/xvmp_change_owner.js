var VimpChangeOwner = {

	ajax_base_url: '',

	base_url: '',

	search_user: function() {
		search_results = $('#xvmp_search_results');
		var username = $('#xvmp_username').val();
		if (username == '') {
			search_results.html('Bitte Benutzernamen eingeben.');
		}
		spinner = $('#xvmp_spinner');
		spinner.show();

		$.get({
			url: this.ajax_base_url,
			data: {
				"username": username,
			}
		}).always(function(response) {
			response_object = JSON.parse(response);
			users = response_object.users;
			if (users === null) {
				search_results.html('Keine Ergebnisse');
			} else {
				user_string = '';
				console.log(users);
				$(users.user).each(function(key, user) {
					user_string +=
						'<a href="' + VimpChangeOwner.base_url + '&uid=' + user.uid + '&username=' + user.username + '" style="clear:both;display:block;">' +
							'<img height="25px" width="25px" style="margin-right:5px;" src="' + user.avatar + '">'
							+ user.username + ' (' + user.email + ')' +
						'</a>';
				});
				search_results.html(user_string);
			}
			spinner.hide();
		});
	}
}