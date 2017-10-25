var VimpSelected = {

	base_link: String = '',

	init: function (base_link) {
		VimpSelected.base_link = base_link;
	},

	setVisibility: function (event, mid) {
		xoctWaiter.show();
		var checkbox = $('input#xvmp_visible_'+mid);
		var visible = checkbox.is(':checked') ? 1 : 0;
		ajax_url = VimpSelected.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				"cmd": "setVisibility",
				"visible": visible,
				"mid": mid
			}
		}).always(function(data, textStatus, jqXHR) {
			xoctWaiter.hide();
		});
	},

	up: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();
		var row = $('#xvmp_row_'+mid);
		ajax_url = VimpSelected.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				"cmd": "moveUp",
				"mid": mid
			}
		}).always(function(data, textStatus, jqXHR) {
			row.insertBefore(row.prev());
			xoctWaiter.hide();
		});
	},

	down: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();
		var row = $('#xvmp_row_'+mid);
		ajax_url = VimpSelected.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				"cmd": "moveDown",
				"mid": mid
			}
		}).always(function(data, textStatus, jqXHR) {
			row.insertAfter(row.next());
			xoctWaiter.hide();
		});
	},

	remove: function (event, mid) {
		event.preventDefault();
		xoctWaiter.show();

		ajax_url = VimpSelected.base_link;
		$.ajax({
			url: ajax_url,
			type: "GET",
			data: {
				"cmd": "removeVideo",
				"mid": mid
			}
		}).always(function(data, textStatus, jqXHR) {
			xoctWaiter.hide();
			$('#xvmp_row_'+mid).remove();
		});
	}
}

