$(function() {
    $('#finder-tabs').tabs();
    $('#slideshow-tabs').tabs();
    $('#finder_catalogs').parent().hide();
    $('#finder_keyword').attr('placeholder', 'Enter Keyword...').prev().remove();
    /*var catalogs = $('#finder_catalogs').parent().detach();
    $('#finder_keyword').bind({
        focusIn: function() {
            $(this).parent().append(catalogs);
        },
        focusOut: function() {
            $('#finder_catalogs').parent().detach();
        }
    }).prev().remove();*/
});
