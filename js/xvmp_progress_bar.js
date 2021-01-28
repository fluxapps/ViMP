VimpProgressBar = {

  active_bars: [],

  init: () => {
    setInterval(() => {
      this.active_bars.forEach((url, mid) => {
        $.get(url, (data) => {
          $('#xvmp_progress_' + mid).val(data + '%');
        });
      });
    }, 1000)
  },

  add: (mid, url) => {
    this.active_bars[mid] = url;
  }
}
