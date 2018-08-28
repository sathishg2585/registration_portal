(function ($) {
  Drupal.behaviors.profile_registration = {
    attach: function (context, settings) {
      if ($("input[name=page_2_hidden]").length > 0) {
        $("input[name=page_2_hidden]").val('page-2');
      }
    }
  }
}(jQuery));