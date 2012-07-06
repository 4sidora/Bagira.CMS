
var current_ObjectLink = '';


// Для работы с полем типа ObjectLink
function addToLinkList(field_id, obj_id) {

    var found = false;

    $("#objectsLinkList_"+field_id+" > li").each(function(){
    	if ($(this).attr('name') == obj_id)
    		found = true;
    });

  
	if (!found)
		$.get($("#admin_url").val() + '/getObjectLinks/'+obj_id+'/'+$("#objectsLinkList_"+field_id).attr('rel') + '/' +field_id, function (data) {
        	$("#objectsLinkList_"+field_id).append(data);
		});

}

function onSelectldObjectLinks() {

	var url = $('#ldObjectLinks').val() + '/getObjectLinksTree/'+current_ObjectLink;

    $("#objectLinkTree").attr('src', url);
}

jQuery(document).ready(function() {

    $("input#file_image, input#file_file").change(function() {
        if (!$("input#name").val()){
            var str = $(this).val();
            str = str.replace(/^.*\\/, '');
            $("input#name").val(str);
            $("input#h1").val(str);
            $("input#title").val(str);
            $("input#pseudo_url").val(translite(str));
        }
    });

    if (typeof textlist != "undefined")
        $('#mainlist_change textarea').each(function(){
            $(this).text(textlist[$(this).attr('id') + '_vvv']);
        });
    
	$(document).click(function(e){
 		if ($(e.target).attr('class') != 'btn_list' && $(e.target).attr('class') != 'stre')
	    	$(".btn_list").hide();
 	});

	if ($('div#tabs').text() != '')
		$("#tabs").tabs({cookie:{expires:1}});

	if ($('#baseMessageBox').attr("title") != '')
	    if ($("#selectField").val() != '') {
		  	$($("#selectField").val()).addClass('errorField');
		  	ShowMessage($('#baseMessageBox').attr("title"), $('#baseMessage').html(), null, "#"+$("#focusField").val());
		} else if ($('#baseMessage').html() != '')
			ShowMessage($('#baseMessageBox').attr("title"), $('#baseMessage').html());
		else
			ShowAlert($('#baseMessageBox').attr("title"));

	$("#lightBox a[rel^='prettyPhoto']").prettyPhoto({
	  	theme: 'dark_rounded'
	});


    // Для работы с полем типа ObjectLink
	$(".addObjectLink").click(function(){

		current_ObjectLink = $(this).attr('id');

        $("#objectLinkTree").attr('src', $("#admin_url").val() + '/getObjectLinksTree/'+$(this).attr('id'));

        $("#objectLinkDiv").dialog({
				autoOpen: false,
				width: 500,
				modal: true,
				buttons: {
					'Закрыть': function() {
						$(this).dialog('close');
					}
				}
		});
		$('#objectLinkDiv').dialog('open');

		return false;

	});
    // Для работы с полем типа ObjectLink
    $("body").on("click", ".delObjectLink", function() {
		$("#objectsLinkList_"+this.id).remove();
		return false;
	});


    $('.input_min_1').focus(function(){
                   if ($(this).val() == 'Поиск в дереве'){
                       $(this).val('');
                       $(this).attr('class', 'input_min_1');
                   }
    }).blur(function(){
                       if ($(this).val() == ''){
                           $(this).val('Поиск в дереве');
                           $(this).attr('class', 'input_min_1 font_gray');
                       }
    });
    
    $('.findObjectLinks').each(function(){
		$(this).autocomplete({
		    serviceUrl: $("#admin_url").val() + '/findObjectLinks/'+this.id,
		    minChars: 2,
		    delimiter: /(,|;)\s*/,
		    maxHeight: 400,
		    width: 300,
		    zIndex: 9999,
		    deferRequestBy: 300,
		    onSelect: function(data, obj_id){
	     		addToLinkList(obj_id[1], obj_id[0]);
		    	$('.findObjectLinks').val('');
		    },
		});
	});


    // Форматируем поля типа время для удобства пользователя 
    $(".check_time").keyup( function(){
           var time_ = $(this).val().replace(/[^\d\:]/g, "");
           if ( time_.substr(0,2) < -1  ||  time_.substr(0,2) > 23 ) time_ = '00';
           if ( time_.length == 2 ) time_ = time_+':';
           if ( time_.substr(3,1) == ':' ) time_ = time_.substr(0,2)+time_.substr(3,2);
           if ( time_.substr(3,2) < -1  ||  time_.substr(3,2) > 59 ) time_ = time_.substr(0,3)+'00';
           $(this).val(time_);
    });

    // Выводим календарь для всех полей типа дата
    $(".check_date").datepicker({ dateFormat: 'dd.mm.yy' }).datepicker($.datepicker.regional["ru"]);

});

function sendForm(action){
    $("#parramForm").val(action);
    $("#changeForm").submit();
}

// Упрощенная фукция отображения сообщения через JQueryUI
function ShowMessage(title, text, buttons_list, focus_elem) {

     //  $('#baseMessageBox').dialog('destroy');

	   $('#baseMessage').html(text);
	   $('#baseMessageBox').attr("title", title);

       if (buttons_list == null)
       	buttons_list = {'ОК': function() {
       		$(this).dialog('destroy');
       		$(focus_elem).focus();
       	}};

       $('#baseMessageBox').dialog('open');
       $("#baseMessageBox").dialog({
            bgiframe: true,
            resizable: false,
            height:160,
            width:300,
            modal: true,
            overlay: {
                    backgroundColor: '#000',
                    opacity: 0.5
            },
            buttons: buttons_list
       });
       $("#baseMessageBox").focus();

}


// Показывает сообщение через JQueryUI с автоматическим фокусом на указанном элементе
function ShowAlert(title, focus_elem) {

       $('#baseMessageBox2').dialog('destroy');
	   $('#baseMessageBox2').attr("title", title);

       	buttons_list = {'ОК': function() {
       		$(this).dialog('destroy');
       		//   alert(focus_elem);
       		$(focus_elem).focus();
       	}};

       $('#baseMessageBox2').dialog('open');
       $('#baseMessageBox2').dialog({
            bgiframe: true,
            resizable: false,
            height:140,
            width:300,
            modal: true,
            overlay: {
                    backgroundColor: '#000',
                    opacity: 0.5
            },
            buttons: buttons_list
       });
       $('#baseMessageBox2').hide();

}


function ShowMessageRVS(f, errorInfo) {

    if (errorInfo.length > 0) {

	    ShowAlert(errorInfo[0][1], errorInfo[0][0]);
	    return false;

    } else return true;
}


// Вычисляет позиционирование элемента
function getOffsetRect(elem) {
    // (1)
    var box = elem.getBoundingClientRect()

    // (2)
    var body = document.body
    var docElem = document.documentElement

    // (3)
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft

    // (4)
    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0

    // (5)
    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft

    var top2=0;
    while(elem) {
        top2 = top2 + parseFloat(elem.offsetTop);
        elem = elem.offsetParent;
    }

    return { top: Math.round(top2), left: Math.round(left) }
}

// Перевод русских символов в транслит
function Translit(from, where){

	var r = new RegExp("^[A-Za-zА-Яа-я0-9_ -]*", "g");
    var RArrayL = 'абвгдеёжзийклмнопрстуфхцчшщьыъэюя -"&%#@–=,;:';
    var RArrayU = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ -"&%#@–=,;:';
    var res = "";
    var tttt = String();
    var str = String();
    var arr = new Array("a", "b", "v", "g", "d", "e", "yo", "zh", "z", "i", "y", "k", "l", "m", "n", "o", "p", "r", "s",
            "t", "u", "f", "h", "ts", "ch", "sh", "shch", "", "y", "", "e", "yu", "ya",
            "_", "_", "", "", "", "", "", "", "", "", "", "", "");

    if ($(where).val() == ""){
      tttt = $(from).val();
      tttt.replace(r, "");
        
      for (var i = 0; i < tttt.length; ++i) {
         
          var symbol = tttt.substring(i, i+1);

          if (symbol != ')' && symbol != '(' && symbol != '?' && symbol != '!' && symbol != '*' && symbol != '+') {

              p = RArrayL.search(symbol);

              if (p == -1)
                p = RArrayU.search(symbol);

              if (p != -1)
                res = res + arr[p];
              else
                res = res + symbol;
          }
      }

      $(where).val(res.replace('--', "").replace('--', '-').toLowerCase());
    }
}

function showElFinder(fname) {
    window.open('/css_mpanel/elfinder/index.html?langCode=ru&fname='+fname,'newwin','width=1000, height=420');
}


// Функции для работы с элементами формы для загрузки файлов
// Показывает панель замены загруженого файла
function setFileField (url, id) {
    $("#"+id).val(url);

    var ext = url.substr(url.lastIndexOf('.') + 1);
    var extImgList = ['png', 'fpng', 'gif', 'jpg', 'jpeg'];
    var isImage = (("#" + extImgList.join("#,#") + "#").search("#"+ext+"#") != -1);
    var extList = ['png', 'fpng', 'gif', 'jpg', 'jpeg', 'doc', 'docx', 'rar', 'pdf', 'xls', 'xlsx'];
    var classExt = (("#" + extList.join("#,#") + "#").search("#"+ext+"#") != -1) ? ext : 'na';

    $("#filelist_"+id).removeClass().addClass("filelist "+classExt);


    // Логика работы для элемента с нестандартным оформлением, например для использующегося в мультиформах
    if ($("#filelist_"+id+" > a.link").attr('rel') == 1) {

        if (isImage) {
            $("#filelist_"+id+" > a.link").text(LangArray['LOADFILE_VIEW_PHOTO']);
            $("#filelist_"+id+" > a.dmini").show();
        } else {
            $("#filelist_"+id+" > a.link").text(LangArray['LOADFILE_DOWNL_FILE']);
            $("#filelist_"+id+" > a.dmini").hide();
        }

        $("#filelist_"+id+" > a.link").attr("title", url);

    } else
        $("#filelist_"+id+" > a.link").text(url);


    
    // Настройка действия по нажатию на ссылку посмотреть / скачать
    if (isImage) {

        $("#filelist_"+id+" > a.link").attr("href", "#").click(function() {
            $.prettyPhoto.open(url);
            return false;
        });

    } else {
        $("#filelist_"+id+" > a.link").attr("target", '_blank').attr("href", url).attr("onClick", '').unbind();
    }
    

	$("#filelist_"+id+" > a.down").attr("href", url);
    $("#filelist_"+id+" > span").text(LangArray['LOADFILE_CHANGE']);

	$("#selectfile_"+id).hide();
	$("#filelist_"+id).show();
}

// Очищает поле с прикрепленным файлом
function clearFileField(id) {
	$("#"+id).val('');

	$("#selectfile_"+id).show();
	$("#filelist_"+id).hide();
}


// Функции для работы со справочниками

// Показать форму добавления
function AddNewHandbook(elem, id) {

   	if ($("#"+id).css("display") == "none") {

   		$("#"+id).show();

   		$(elem).parent().find(".ok_value").hide();
    	$("#"+id+"_new_val").hide().val('');
    	$(elem).removeClass('del_value').addClass('add_value');

   	} else {

    	$("#"+id+"_new_val").unbind().keypress(function (e) {
			if (e.which == 13) { doAddNewHandbook(id); return false;}
		});

    	$("#"+id).hide();
    	$(elem).parent().find(".ok_value").show();
    	$("#"+id+"_new_val").show().focus();
    	$(elem).removeClass('add_value').addClass('del_value');
    }

    return false;
}

function doAddNewHandbook(id) {

    if ($("#"+id+"_new_val").val() != "") {

    	$("#"+id).append('<option value="'+$("#"+id+"_new_val").val()+'" selected="selected">► '+$("#"+id+"_new_val").val()+'</option>');
    	$("#add_new_"+id).click();

    } else
    	ShowAlert('Значения справочника не может быть пустым!', "#"+id+"_new_val");

    return false;

};



function isValidEmail(str) {
    var s = $.trim(str);
    var at = "@";
    var dot = ".";
    var lat = s.indexOf(at);
    var lstr = s.length;
    var ldot = s.indexOf(dot);

    if (s.indexOf(at)==-1 ||
       (s.indexOf(at)==-1 || s.indexOf(at)==0 || s.indexOf(at)==lstr) ||
       (s.indexOf(dot)==-1 || s.indexOf(dot)==0 || s.indexOf(dot)==lstr) ||
       (s.indexOf(at,(lat+1))!=-1) ||
       (s.substring(lat-1,lat)==dot || s.substring(lat+1,lat+2)==dot) ||
       (s.indexOf(dot,(lat+2))==-1) ||
       (s.indexOf(" ")!=-1))
    {
      return false;
    }

    return true;
}


function isValidFloat(value) {
	var reg_exp = /^-?\d+[\.|\,]?\d+$/i;
    return reg_exp.test(value) || value == 0;
}

function isValidPrice(value) {
	var reg_exp = /^\d+[\.|\,]?\d+$/i;
    return reg_exp.test(value) || value == 0;
}

function str_replace(search, replace, subject) {
	return subject.split(search).join(replace);
}

