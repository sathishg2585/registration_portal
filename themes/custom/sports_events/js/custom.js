/* --------------------------------------------- 
* Filename:     custom.js
* Version:      1.0.0 (2016-02-27)
* Website:      http://www.zymphonies.com
* Description:  Global Script
* Author:       Zymphonies Team
                info@zymphonies.com
-----------------------------------------------*/

jQuery(document).ready(function($){

	//Main menu
	$('#main-menu').smartmenus();
	
	//Mobile menu toggle
	$('.navbar-toggle').click(function(){
    if ($(window).width() > 400 && $('.region-primary-menu').css('display') == 'none') {
      var sidebar_height = $("body").height() - 60;console.log(sidebar_height);
      $(".sports-events-body .main-header .menu-base-theme").css('min-height', sidebar_height);
    }
		$('.region-primary-menu').toggle();
    if ($('.region-primary-menu').css('display') == 'none') {
      $(".icon-close").css('display', 'none');
      $(".icon-bar").css('display', 'block');
      $(".navbar-default .navbar-toggle").css('padding-top', '6px');
    }
    else {
      $(".icon-close").css('display', 'block');
      $(".icon-bar").css('display', 'none');
      $(".navbar-default .navbar-toggle").css('padding-top', '0px');      
    }
	});

	//Mobile dropdown menu
	if ( $(window).width() < 770) {
		$(".region-primary-menu li a:not(.has-submenu)").click(function () {
			$('.region-primary-menu').hide();
	    });
	}
	if($(".path-events-zip-code-search").length > 0) {
    //$(".geolocation-views-filter-geocoder").attr("type", "number");
  }
});