jQuery(document).ready(function() {

      $(":checkbox").click(function() {
          $(this).parent().find("div.right_block > input").attr("checked", $(this).attr("checked"));
      });
});
