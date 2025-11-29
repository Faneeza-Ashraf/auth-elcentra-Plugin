M.local_eliscore = M.local_eliscore || {};

M.local_eliscore.init_filter_date_fix = function (Y) {
  // Find all calendar buttons within the form.
  var calendarIcons = Y.all('.fdate_time_selector .yui3-skin-sam .yui3-button');

  // Attach a click event listener to each icon.
  calendarIcons.on('click', function (e) {
    // Stop the event from bubbling up to the form.
    e.preventDefault();
    e.stopPropagation();
  });
};