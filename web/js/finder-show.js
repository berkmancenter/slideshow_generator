$(function() {
    $('#finder-results-tabs').tabs({
        show: function() { $('.metadata dd').dotdotdot(); }
    });
});
