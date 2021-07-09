var VimpObserver = {

	mid: 0,

	time_ranges: [],

	send_request: false,

	init: function(mid, time_ranges) {
		VimpObserver.mid = mid;
		VimpObserver.time_ranges = time_ranges;
		window.setInterval(VimpObserver.observe, 5000);
	},

	observe: function() {
		VimpObserver.send_request = false;
		var played = $('video')[0].played;
		var i = 0;
		var ranges = [];
		while (i < played.length) {
			ranges.push({s: played.start(i), e: played.end(i)});
			i++;
		}

		VimpObserver.merge_ranges(ranges);

		if (VimpObserver.send_request === false) {
			// no changes
			return;
		}

		$.post({
			url: VimpContent.ajax_base_url + '&cmd=updateProgress',
			data: {
				"mid": VimpObserver.mid,
				"ranges": JSON.stringify(VimpObserver.time_ranges)
			}
		}).always(function(response){
			console.log(response);
		});
	},

	merge_ranges: function(merge) {
		merge.forEach(function(m, j) {
			VimpObserver.merge_single(m);
		});
	},

	merge_single: function(m) {
		var merged = 0;
		VimpObserver.time_ranges.forEach(function(t, i) {
			if (merged === 1) {
				return;
			}
			if (VimpObserver.overlap(t, m)) { // "(overlap) AND NOT(m is in t)"
				merged = 1;
				if (VimpObserver.contains(t, m)) { // don't merge if a entirely contains b
					return;
				}
				VimpObserver.send_request = true;
				// merge
				VimpObserver.time_ranges[i] = {s: Math.min(m.s, t.s), e: Math.max(m.e, t.e)};
				// remove element and merge it again (since the newly created element could overlap with existing)
				VimpObserver.merge_ranges(VimpObserver.time_ranges.splice(i, 1));
			}
		});
		if (merged === 0) {
			VimpObserver.send_request = true;
			VimpObserver.time_ranges.push(m);
		}
	},

	overlap: function(a, b) {
		return (a.s <= b.e && b.s <= a.e);
	},

	contains: function(a, b) {
		return (b.s >= a.s && b.e <= a.e);
	}
}