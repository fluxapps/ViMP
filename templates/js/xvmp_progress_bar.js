VimpProgressBar = {
  lng: { transcoded: '' },

  init: (mid, url) => {
    let i = setInterval(() => {
      VimpProgressBar.updateProgress(mid, url, (data) => {
        if (data === '100') {
          clearInterval(i);
          let $image = $('#xvmp_row_' + mid + ' .xvmp_image_container img.xvmp_preview_image');
          $image.attr("src", $image.attr('data-url'));
          $('#xvmp_progress_ui_' + mid).hide();
          $('#xvmp_row_' + mid + ' .xvmp_status_text').text(VimpProgressBar.lng.transcoded);
          $('#xvmp_row_' + mid + ' .xvmp_play_overlay').show();
          let $a = $('#xvmp_row_' + mid + ' a');
          $a.removeClass('xvmp_default_cursor');
          $a.on('click', () => VimpContent.playVideo(mid));
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
