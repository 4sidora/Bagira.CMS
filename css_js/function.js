var noHide = false;
var selectedGoods = 1;
var goodsTime = 9;/*скорость смены слоёв*/
var goodsTimer = null;
var effectTime = 700;
window.reEmail = /^([a-z0-9\.\-\_])+\@(([a-zA-Z0-9\-\_])+\.)+([a-zA-Z0-9]{2,6})+$/i;

$(window).load(function(){

    var hideFunc = function(){
        if (!noHide){
            $('#autorisation:visible').fadeOut('fast');
            $('#feedback:visible').fadeOut('fast');
            $('#question:visible').fadeOut('fast');
            $('#shader').remove();
        } else {
            noHide = false;
        }
    }
    $('.cross').click(hideFunc);
    $('body').click(hideFunc);

    /*Поиск*/
    $('#search').focus(function(){
        if ($(this).val() == 'Поиск'){
            $(this).val('');
        }
    }).blur(function(){
        if ($(this).val() == ''){
            $(this).val('Поиск');
        }
    });

    /*меню на главной*/
    $('#about ul li a').click(function(){
        if ($(this).parent().hasClass('selected')){
            return false;
        }
        selectedGoods = $(this).parent().index() + 1;
        selectGoods();
        clearTimeout(goodsTimer);
        goodsTimer = setTimeout('nextGoods()', goodsTime * 3000);/*время просмотра товара по клику*/
        return false;
    });

    goodsTimer = setTimeout('nextGoods()', goodsTime * 1000);




    /*белый фон к всплывающим формам*/
    $('.whiteshader').click(function(){

        var shader = document.createElement('div');
        $(shader).attr('id', 'shader').css('background-color', '#726f6f').css('opacity', '0.45').css('position', 'fixed').css('left', '0').css('right', '0').css('top', '0').css('bottom', '0').css('z-index', '3999');

        if ($(this).attr('rel')){

            var displayed = $('#' + $(this).attr('rel'));
            $(displayed).css('left', Math.round( (parseInt($('body').outerWidth()) - parseInt($(displayed).outerWidth()) ) / 2));
            $(displayed).show();

        } else if ($(this).attr('href') && $(this).attr('href') != '#'){

            var shader2 = document.createElement('div');
            $(shader2).attr('id', 'shadercontainer').css('position', 'absolute').css('left', '0').css('right', '0').css('top', '0').css('bottom', '0');
            $('body').append(shader2);

            var box = document.createElement('div');
            $(box).attr('id', 'shaderbox').css('width', '1000px').css('margin', '0 auto').css('position', 'relative');
            $(shader2).append(box);

            var img = document.createElement('img');
            $(img).attr('src', $(this).attr('href')).css('display', 'none');

            $(img).load(function(){
                $(img).css('position', 'absolute').css('left', Math.round( (parseInt($(box).outerWidth()) - parseInt($(img).outerWidth()) ) / 2)).css('top', '20px').css('z-index', '4000');
                $(img).fadeIn('fast');
            });

            $(img).click(function(){
                noHide = true;
                return true;
            });

            $(box).append(img);
        }
        
        $('body').append(shader);
        $(shader).click(hideFunc);
        return false;
    });


    /*форма авторизации*/
    $('.enter').click(function(){
        $('#autorisation').css('left', Math.round( (parseInt($('body').outerWidth()) - parseInt($('#autorisation').outerWidth()) ) / 2));
        $('#autorisation').fadeIn('fast');
        return false;
    });

    $('#autorisation').click(function(event){
        noHide = true;
        return !(event.target.nodeName == 'DIV');
    });

    /*форма оставить отзыв*/
    $('.feedback').click(function(){
        $('#feedback').css('left', Math.round( (parseInt($('body').outerWidth()) - parseInt($('#feedback').outerWidth()) ) / 2));
        $('#feedback').fadeIn('fast');
        return false;
    });

    $('#feedback').click(function(event){
        noHide = true;
        return !(event.target.nodeName == 'DIV');
    });

    /*форма задать вопрос*/
    $('#showFaq').click(function(){
        
        $('#question').css('left', Math.round( (parseInt($('body').outerWidth()) - parseInt($('#question').outerWidth()) ) / 2));
        $('#question').fadeIn('fast');

        return false;
    });

    $('#question').click(function(event){
        noHide = true;
        return !(event.target.nodeName == 'DIV');
    });

    /*добавление товара в корзину*/
    /*$('.addtocart').click(function(){
     var summ =0;
     if ($(this).parent().children('span.summa').length > 0){
     summ = parseInt($(this).parent().children('span.summa').html());
     }
     else{
     summ = parseInt($(this).parent().children('a').children('span.summa').html());
     }
     var existsCount = parseInt($('.count').html());
     var existsSumm = parseInt($('.summ').html());
     $('.count').html(existsCount + 1);
     $('.summ').html(existsSumm + summ);
     });	*/

    /*добавление к сравнению*/
    $('.compare').click(function(){
        var existsGoods = parseInt($('#compare span').html());
        $('#compare span').html(existsGoods + 1);
    });

    /*переключение по вкладкам*/
    $('#column2 .viewsmenu li a').click(function(){
        $('#column2 .viewsmenu li.selected').removeClass('selected');
        $(this).parent().addClass('selected');
        $('.contentwrapper').addClass('hidedcontent');
        $('#content_'+$(this).attr('rel')).removeClass('hidedcontent');
        document.location.hash = '#' + $(this).attr('rel');
        return false;
    });

    if (getCurAnchor() == 'propert')
        $('#column2 .viewsmenu li a:eq(1)').click();

    if (getCurAnchor() == 'reviews')
        $('#column2 .viewsmenu li a:eq(2)').click();


    $('.cartwrapper .viewsmenu li input').change(function(){
        switch ($(this).parent().index()){
            case 0:
                $('#contentwrapper1').show();
                $('#contentwrapper2').hide();
                break;
            case 1:
                $('#contentwrapper2').show();
                $('#contentwrapper1').hide();
                break;
            case 2:
                $('#contentwrapper1').hide();
                $('#contentwrapper2').hide();
                break;
        }
    });


    /*просмотр товара*/
    $('#gallery a').click(function(){
        $('#aboutmore .image').html('<span class="newtabcell"><img src="' + $(this).attr('href') + '" width="230" alt=""/></div>');
        return false;
    });


    /*страница вопрос-ответ*/
    $('.questionanswer a.title').click(function(){
        var flag = $(this).parent().parent().hasClass('viewed');
        $('.questionanswer li').removeClass('viewed');
        if (!flag){
            $(this).parent().parent().addClass('viewed');
        }
        return false;
    });

    /*сравнение товаров*/
    function calculateULWrapper(){
        var newWidth = 0;
        $('.ulwrapper ul').each(function(){
            newWidth += parseInt($(this).width());
        });
        $('.ulwrapper').css('width', newWidth);
    }
    calculateULWrapper();

    $('.rightscroll').click(function(event){
        clearSelection();
        $('.ulwrappercover').scrollLeft(parseInt($('.ulwrappercover').scrollLeft()) + 50);
        return false;
    });

    $('.leftscroll').click(function(event){
        clearSelection();
        $('.ulwrappercover').scrollLeft(parseInt($('.ulwrappercover').scrollLeft()) - 50);
        return false;
    });

    $('.ulwrapper table').css('border-collapse', 'collapse');





    //выравнивание по низу в ие7	
    if ($.browser.msie){
        $('.albumwrapper img').each(function(){
            $(this).css('padding-top', 150 - parseInt($(this).height()));
        });
    }

    /*удаление товара из списка сравнения*/
    $('#righttable ul .cross').click(function(){
        $(this).parent().parent().parent().parent().fadeOut('slow', function(){
            $(this).remove();
            calculateULWrapper();
            $('.ulwrappercover').scrollLeft(parseInt($('.ulwrappercover').scrollLeft()));
        });
        return false;
    });

    /*выделить различающиеся параметры*/
    $('.find').click(function(){
        if ($(this).html() == 'Выделить различающиеся параметры'){
            $(this).html('Снять выделение параметров');
            $('.different').css('background', '#ffe07b');
        }
        else{
            $(this).html('Выделить различающиеся параметры');
            $('.different').css('background', 'transparent');
        }
        return false;
    });

    /* корзина - шоппинг лист*/
    $('.quantity input').keyup(function(){
        var inp = $(this).val().replace(/[^\d\:]/g, "");
        $(this).val(inp);
    });

    function calculateTotalSumm(){
        var totalSumm = 0;
        $('.summ span').each(function(){
            if (!$(this).parent().hasClass('gray')){
                totalSumm += parseInt($(this).html());
            }
        });
        $('.totalsumma span').html(totalSumm);
    }

    $('.quantity input').keyup(function(){
        var count = $(this).val();
        /*count = (/^\d+$/g.test(count)) ? parseInt(count) : 0;*/
        var price = parseInt($(this).parent().parent().children('.price').val());
        var summ = count * price;
        var existsSumm = parseInt($(this).parent().parent().children('.summ').children('span').html());
        var existsSumma = parseInt($('.totalsumma span').html());
        $(this).parent().parent().children('.summ').children('span').html(summ);
        calculateTotalSumm();
    });

    $('.number input').change(function(){
        var inputObj = $(this).parent().parent().children('.quantity').children('input');
        var numberObj = $(this).parent().parent().children('.number');
        var summObj = $(this).parent().parent().children('.summ');
        var quantityObj = $(this).parent().parent().children('.quantity').children('input');
        var nameObj = $(this).parent().parent().children('.name');
        if ($(this).prop('checked')){
            $(inputObj).removeAttr('disabled', 'disabled');
            $(numberObj).removeClass('gray');
            $(summObj).removeClass('gray');
            $(quantityObj).removeClass('gray');
            $(nameObj).removeClass('gray');
        } else {
            $(inputObj).attr('disabled', 'disabled');
            $(numberObj).addClass('gray');
            $(summObj).addClass('gray');
            $(quantityObj).addClass('gray');
            $(nameObj).addClass('gray');
        }
        calculateTotalSumm();
    });



    /*рейтинг*/
    $('.readonly').each(function() {
        $(this).raty({
            readOnly:  true,
            path: '/images/img/',
            start: $(this).attr('rel')
        });
    });

    $('.rating').each(function() {
        var raty_elem = $(this);
        $(this).raty({
            path: '/images/img/',
            start: $(this).attr('rel'),
            click: function(score, evt) {

                $.get('/structure/change-rate/' + raty_elem.attr('name') + '/' + score, function(data) {

                    if (data.error == 0)
                        $(raty_elem).html('').raty({
                            readOnly:  true,
                            path: '/images/img/',
                            start: data.new_rate
                        });

                }, 'json');
            }
        });
    });

    // выбор буквы
    $('.filterABC span').click(function(){

        if ($(this).attr('rel') == 'all')
            $('#filter_abc').val('');
        else
            $('#filter_abc').val($(this).text());

        $('#filterForm').submit();

        return false;
    });

});

function getCurAnchor() {
    var url = location + '';
    var start = url.indexOf("#");
    return url.substring(start + 1, start + 8);
}

function clearSelection() {
    if(document.selection && document.selection.empty) {
        document.selection.empty();
    } else if(window.getSelection) {
        var sel = window.getSelection();
        sel.removeAllRanges();
    }
}

function selectGoods(){
    $('.goods:visible').fadeOut(effectTime / 2, function(){
        $('.goods:nth-child(' + (selectedGoods + 1) + ')').fadeIn(effectTime);
    });
    $('#about ul li').removeClass('selected');
    $('#about ul li:nth-child(' + selectedGoods + ')').addClass('selected');
}

function nextGoods(){
    if (selectedGoods < $('#about ul li').length){
        selectedGoods++;
    } else {
        selectedGoods = 1;
    }
    selectGoods();
    goodsTimer = setTimeout('nextGoods()', goodsTime * 1000);
}