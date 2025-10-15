/* --------------------------------------------------------------------------
 * File        : rating-config.js
 * Version     : 1.0
 * Author      : IMediapixel
 * Author URI  : http://themeforest.net/user/imediapixel
 *
 * IMediapixel Copyright 2019 All Rights Reserved.
 * -------------------------------------------------------------------------- */

/* --------------------------------------------------------------------------
 * javascript handle initialization
		1. Rating
 *
 * -------------------------------------------------------------------------- */
(function($){

	"use strict";
	
	var TemplateApp = {
		//---------- 1. Parallax -----------
	    template_rating:function() {
	    	$('#review-rating1,#review-rating4').barrating({
	    		theme: 'fontawesome-stars-o',
	    		initialRating: 4,
	    		hoverState: false
	    	});
	    	$('#review-rating2,#review-rating3').barrating({
	    		theme: 'fontawesome-stars-o',
	    		initialRating: 5,
	    		hoverState: false
	    	});
	    	$('#review-rating-opt').barrating({
	    		theme: 'fontawesome-stars-o',
	    		emptyValue: 0
	    	});
	    },

	  // theme init
      theme_init:function(){
      	 TemplateApp.template_rating();
      }
		
	}//end themeApp
	
	
	jQuery(document).ready(function($){
	   	   
		TemplateApp.theme_init();

    });
	
})(jQuery);