$(document).ready(function () {
	var fixHelper = function (e, ui) {
		ui.children().each(function () {
			$(this).width($(this).width());
		});
		return ui;
	};

	var reSort = function (e, ui) {
		xoctWaiter.show();
		var order = [];
		$("div.ilTableOuter table tbody tr").each(function () {
			order.push($(this).attr('id').substring(9));
		});

		ajax_url = VimpSelected.base_link + '&cmd=reorder';
		$.ajax({
			url: ajax_url,
			type: "POST",
			data: {
				"ids": order
			}
		}).always(function(data, textStatus, jqXHR) {
			xoctWaiter.hide();
		});
	};

	$("div.ilTableOuter table tbody").sortable({
		helper: fixHelper,
		items: '.xvmpSortable',
		stop: reSort
	}).disableSelection();
});
