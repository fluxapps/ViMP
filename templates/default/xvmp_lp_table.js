var VimpLP = {

	ajax_base_url: String = '',

	setRelevance: function (event, mid) {
		xoctWaiter.show();
		var checkbox = $('input#xvmp_relevant_' + mid);
		var relevant = checkbox.is(':checked') ? 1 : 0;
		$.ajax({
			url: this.ajax_base_url,
			type: "GET",
			data: {
				"cmd": "setRelevance",
				"relevant": relevant,
				"mid": mid
			}
		}).always(function (data, textStatus, jqXHR) {
			xoctWaiter.hide();
		});
	}
}

