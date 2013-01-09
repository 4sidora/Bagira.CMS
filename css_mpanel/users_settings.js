jQuery(document).ready(function() {

    $(".sh-social-block .checkbox").click(function() {
        if (!$(this).attr('checked')){
            $(this).parent().find('a, div').slideUp(75);
            $(this).parent().parent().find('.slide').slideUp(75);
            $(this).parent().css('height', 'auto');
        } else {
            $(this).parent().find('a, div').slideDown(75);
            $(this).parent().parent().find('.slide').slideDown(75);

            if ($(this).parent().hasClass('ok'))
                $(this).parent().css('height', '110px');
            else
                $(this).parent().css('height', '50px');
        }
    });

    $(".sh-social-block .checkbox").each(function() {
        if ($(this).attr('checked')){
            $(this).parent().find('a, div').slideDown(75);
            $(this).parent().parent().find('.slide').slideDown(75);

            if ($(this).parent().hasClass('ok'))
                $(this).parent().css('height', '110px');
            else
                $(this).parent().css('height', '50px');
        }
    });

});
