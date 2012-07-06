var laMontera = true;
var move_parent_id;

function setTreeEvents(node, tree_obj){

           // Убираем все прикрепленные до этого события
           $("#delButton, .activate, .active_div, .main_link, .addit_line, .addit_line1, .tree_list").unbind();

           // Удаление страницы
           $("#delButton").click(function() {

           		var url = $(this).attr("href");
             	var id = $("#edits").attr("name");
             	var parent_id = $("#edits").attr("rel");
                var obj_name = $('#aline_'+id+parent_id).text();

              	if (id != $("#root_id").text()) {

                     ShowMessage(
				    	$("#del_title").val(),
			            str_replace('&name&', obj_name, $("#del_text").val()),
			            {
			            	'Отмена': function() {
			       				$(this).dialog('close');
			       			},
			       			'Удалить': function() {
					          	$.get(url, function(data){
                					if (data == 'delete') {
                						$.tree.focused().remove("#phtml_"+id+"_"+parent_id);
                						if(typeof reloadTable == 'function')
                							reloadTable();
                					}
                				});
                    			$(this).dialog('close');
			       			}
				       	}
				     );
         		}
           		return false;
           });


      if ($("#isChangeActive").text() == 1)
           // Кнопка активности страницы
           $(".activate").click(function() {

                    var id = $(this).parent().parent().attr("name");

                    if (id != $("#root_id").text()) {
                        var img = this;
                        $.get($("#act_link").text() + '/' + id, {}, function(data){
                         	  if (data == 'active' || data == 'no_active') {

                         	  	  active = (data == 'active') ? '1' : '0';
                                  if (active == 0)
                                      img.src = '/css_mpanel/tree/images/file0.gif';
                                  else
                                      img.src = '/css_mpanel/tree/images/classes/' + $(img).attr("data-ico") + '.png';

                                  $(img).attr("name", active);

                                  // Изменяем активность в таблице
                                  if(typeof reloadTable == 'function') {
		                              knopa = $("#pimpochka_"+id);
		                              if (data == 'active') {
							             	knopa.removeClass('activ_elem_0');
							             	knopa.addClass('activ_elem_1');
							          } else if (data == 'no_active') {
							                knopa.removeClass('activ_elem_1');
							             	knopa.addClass('activ_elem_0');
							          }
                                  }

                              }
                        });
                    }
                    return false;

           });

        if ($("#isShowRight").text() == 1)
           // Обрабатываем события при наведении на строку
           $(".active_div, .main_link, .addit_line, .addit_line1").mouseover(function() {

                   var cur_id = $(this).parent().attr("name");
                   var parent_id = $(this).parent().attr("rel");

               if (laMontera) {


                    var cord = getOffsetRect(this);

                    var addit = ($.browser.version == "7.0") ? 2 : 1;
                    if ($.browser.version == "8.0") addit = 1;



                    $("#edits").show();
                    $("#edits").css("top", cord.top - 122 + addit);

                    $("#edits").attr("name", cur_id);
                    $("#edits").attr("rel", parent_id);
                    //    $("#hint").text($.browser.version);
                    $("#edits > a.ledit").each(function(){
                        if (this.rel == 'getUrl')
                            this.href = $("#get_url_"+cur_id+"_"+parent_id).val();
                        else
                            this.href = this.name + $("#edits").attr("name");
                    });



                    //Если это корень меню - прячем кнопку удаления, и другие не нужные права
                    if (cur_id == $("#root_id").text()) {
                      $(".hide_in_root").hide();
                      $("#delZagl").show();
                    } else {
                      $(".hide_in_root").show();
                      $("#delZagl").hide();
                    }


                 }

                 $("#aline_"+cur_id+parent_id).addClass("active");
                    $("#aline2_"+cur_id+parent_id).addClass("active");

                  return false;

           }).mouseout(function() {


               var cur_id = $(this).parent().attr("name");
               var parent_id = $(this).parent().attr("rel");
               if (laMontera) {

                    $("#edits").hide();

                  //  $(".popdiv_tree_list").hide();
               }

                	$("#aline_"+cur_id+parent_id).removeClass("active");
                    $("#aline2_"+cur_id+parent_id).removeClass("active");
                    return false;

           });

           // Выпадающий список
           $(".tree_list").click(function() {

                var id = $(this).attr("name");

                if ($(".popdiv_tree_list:visible").attr('id') != id) {
                	$(".popdiv_tree_list").hide();
                	$(".tree_list").css('background', 'none');
                }

                if ($("#"+id).css('display') == 'none') {

                    $(this).addClass('tree_list_active');
                    $("#"+id).show();
                    $("#"+id+" a").each(function(){
                        var link = str_replace('%obj_id%', $("#edits").attr("name"), $(this).attr('name'));
                    	$(this).attr('href', link);
                    });

                    laMontera = false;

                } else {


                	$("#"+id).hide();
                	$(this).removeClass('tree_list_active');
                    laMontera = true;
                }

                return false;

           });
}

$(function () {

           $(document).click(function(e){
                if ($(e.target).attr('class') != 'popdiv_tree_list' && $(e.target).attr('class') != 'tree_list') {
	           		$(".popdiv_tree_list").hide();
	           		$(".tree_list").removeClass('tree_list_active');
	           		//$("#edits").hide();
	                laMontera = true;
                }
           });

           $("#basic_html").tree({
                     callback: {

                       // Открытие страницы
                       onselect: function (node) {

                            if ($("#isEditable").text() == 1)
			                	document.location.href($("#"+node.id+" > a").attr("href"));
			           		else
			           			window.parent.addToLinkList($("#parram").val(), $("#"+node.id).attr("name"));


			           },

                       beforemove: function (NODE, REF_NODE, TYPE, TREE_OBJ) {
                         if ($("#isDragged").text() == 1) {
	                         move_parent_id = $(NODE).parent().parent().attr("name");
	                         return true;
                         }
                       },

                       check_move: function (NODE,REF_NODE,TYPE,TREE_OBJ) {
                         return ($("#isDragged").text() == 1);
                       },

                       // Перемещение страниц
                       onmove: function (node, ref_node, type) {

                         if ($("#isDragged").text() == 1) {
	                         node_id = $(node).attr("name");
	                         dest_id = $(ref_node).attr("name");
                             parent_id = $(node).parent().parent().attr("name");

                             var url = $("#remove_link").text() + '/' + dest_id + '/' + node_id + '/' + type + '/' + move_parent_id + '/' + parent_id;

	                         $.get(url, function(data){
	                             if (data != 'ok')
	                             	alert(data);
	                         });
                         }
                       },

                       onopen: function (tree_obj) {
                          $.get($("#load_link").text() + '/open/' + $(tree_obj).attr("name"));
                          setTreeEvents();
                       },
                       onclose: function (tree_obj) {
                          $.get($("#load_link").text() + '/close/' + $(tree_obj).attr("name"));
                       },
                       onload: function (tree_obj) {
                          setTreeEvents(0, 0);
                       }

                     },

                     data: {
                        async : true,
                        opts : {
                           method : "POST",
                           url : $("#load_link").text()
                        }
                     },
                     rules : {
                        valid_children : [ "root" ]
                     },
                     types : {
                        "root" : {
                                draggable : false,
                                valid_children : [ "branch" ]
                        },
                        "branch" : {
                                valid_children : "none",
                                max_children : 0,
                                max_depth :0
                        }
                     }
           });

	if ($("#isShowRight").text() == 1)

           // Обрабатываем события при наведении на блок с кнопками
           $("#edits, .popdiv_tree_list").mouseover(function() {
                if (laMontera) {
                    $("#edits").show();
                }

                $("#aline_"+$("#edits").attr("name")+$("#edits").attr("rel")).addClass("active");
                $("#aline2_"+$("#edits").attr("name")+$("#edits").attr("rel")).addClass("active");
                return true;

           }).mouseout(function() {
                if (laMontera) {
                    $("#edits").hide();
                }

                $("#aline_"+$("#edits").attr("name")+$("#edits").attr("rel")).removeClass("active");
                $("#aline2_"+$("#edits").attr("name")+$("#edits").attr("rel")).removeClass("active");
                return true;
           });

});