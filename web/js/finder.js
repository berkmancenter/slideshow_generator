$(function() {
    $('.image-buttons').each(function() {
        $(this).replaceWith('<a href="#" class="buttoned addImage" value="' + $(this).find('input').val() + '">Add</a>');
    });
    $('.collection-buttons').each(function() {
        $(this).replaceWith('<a href="#" class="buttoned addCollection" value="' + $(this).find('input').val() + '">Add</a>');
    });

    function addClickHandler(buttonClass, valueId) {
        $(buttonClass).click(function(e) {
            e.preventDefault();
            var id = $(e.target).attr('value');
            $.post(
                ajaxUrl,
                valueId + '[' + id + ']=' + id,
                function(data) {
                    $('#image-cart').show();
                    $('#image-count').html(data);
                    $(e.target).css({
                        'backgroundColor':'#8BBF71',
                        'boxShadow':'0px 0px 7px #6587A8',
                        'borderColor':'#6587A8'
                    }).html('Added');
                }
            );
        });
    }
    addClickHandler('.addCollection', 'collections');
    addClickHandler('.addImage', 'images');
});
