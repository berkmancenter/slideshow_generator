$(function() {
    $('#finder-tabs').tabs();
    $('#slideshow-tabs').tabs();
    var catalogs = $('#finder_catalogs').hide(), input = $('#finder_search_keyword');
    $(input).prev().remove();
    $(catalogs).addClass('catalog-box').css({
        width: $(input).outerWidth() + 'px',
    }).offset({
        top: $(input).position().top + $(input).outerHeight() + 10,
        left: $(input).position().left
    });
    $(input).bind({
        focusin: function() {
            $(catalogs).show();
            $(this).data('focused', true);
        },
        focusout: function() {
           $(catalogs).mouseleave(function() {
               $(this).hide();
               $(this).unbind('mouseleave');
           });
        }
    }).attr('placeholder', 'Enter Keyword...');
});
