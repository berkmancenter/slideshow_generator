$(function() {
    $('#finder-tabs').tabs();
    $('#slideshow-tabs').tabs();
    var catalogs = $('#finder_catalogs').parent().hide();
    $('#more-button').click(function() { $(catalogs).slideToggle(); });
    $('#finder_keyword').bind({
        focusin: function() {
           /*$(catalogs).css({
                position: 'absolute',
                left: $(this).offset().left,
                top: $(this).offset().top + $(this).outerHeight(),
                backgroundColor: 'white',
                color: 'black'
            });*/
            /*$(catalogs).show();*/
        },
        focusout: function() {
           //$('#finder_catalogs').parent().hide();
        }
    }).attr('placeholder', 'Enter Keyword...').prev().remove();
});
