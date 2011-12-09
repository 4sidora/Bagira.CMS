jQuery(document).ready(function() {

      $("#login").change(function (){
            if ($("#email").val() == '' && isValidEmail($(this).val()))
            	$("#email").val($(this).val());
	  });

      $("div.cheka").each(function() {
          $(this).css("background", "url(/css_mpanel/images/checka/check_"+$("#val"+this.id).val()+".gif)  no-repeat");
      });

      $("div.cheka").click(function() {

          var tmp = parseInt($("#val"+this.id).val()) + 1;
          if (tmp > 1) tmp = -1;
          $(this).css("background", "url(/css_mpanel/images/checka/check_"+tmp+".gif)  no-repeat");
          $("#val"+this.id).val(tmp);

          $(this).parent().find("div.right_block > div.cheka").each(function() {

            $(this).css("background", "url(/css_mpanel/images/checka/check_"+tmp+".gif)  no-repeat");
            $("#val"+this.id).val(tmp);

          });

          return false;
      });

});
