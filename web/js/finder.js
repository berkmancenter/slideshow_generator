$(function() {
    $('.metadata dd').dotdotdot();
    $('.images .buttons input').each(function() {
        $(this).prev('label').remove();
        $(this).replaceWith('<a href="#" class="buttoned addImage" value="' + $(this).val() + '">Add</a>');
    });
    $('.image-groups .buttons input').each(function() {
        $(this).prev('label').remove();
        $(this).replaceWith('<a href="#" class="buttoned addImageGroup" value="' + $(this).find('input').val() + '">Add Group</a>');
    });

    function addClickHandler(buttonClass, valueId) {
        $(buttonClass).click(function(e) {
            e.preventDefault();
            var id = $(e.target).attr('value');
            $(e.target).html(loadingImage);
            $.ajax({
                url: ajaxUrl,
                data: valueId + '[' + id + ']=' + id,
                error: function(jqXHR) {
                    $(e.target).html('Error');
                },
                success: function(data) {
                    $('#image-cart').show();
                    $('#image-count').html(data);
                    $(e.target).css({
                        'backgroundColor':'#8BBF71',
                        'boxShadow':'0px 0px 7px #6587A8',
                        'borderColor':'#6587A8'
                    }).html('Added');
                },
                type: 'POST'
            });
        });
    }
    addClickHandler('.addImageGroup', 'imageGroups');
    addClickHandler('.addImage', 'images');
});
