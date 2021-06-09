VimpProgressBar = {
  init: (mid, url) => {
    let i = setInterval(() => {
      VimpProgressBar.updateProgress(mid, url, (data) => {
        if (!data || data === '100%') {
          clearInterval(i);
        }
      });
    }, 5000)
  },

  updateProgress: (mid, url, callback) => {
    VimpProgressBar.getProgress(mid, url, (data) => {
      $('#xvmp_progress_' + mid).text(data + '%');
      $('#xvmp_progress_bar_' + mid).css('width', data + '%').attr('aria-valuenow', data);
      callback(data);
    })
  },

  getProgress: (mid, url, callback) => {
    $.get(url.replace(/&amp;/g, '&'), (data) => {
      callback(data);
    });
  }
}
