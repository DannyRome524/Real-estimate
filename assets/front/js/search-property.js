jQuery(document).ready(function($) {
	if (typeof $('.rem-select2-field').select2 === "function") { 
		$('.rem-select2-field').select2({placeholder: $(this).data('placeholder')});
	    $('.select2-container').width('100%');
	}
	$('.ich-settings-main-wrap .image-fill').each(function(index, el) {
		jQuery(this).imagefill();
	});

	$('.search-options .wcp-eq-height > div').matchHeight({byRow: false});

	$('ul.page-numbers').addClass('pagination');
	$('.page-numbers.current').closest('li').addClass('active');
	
	$('.search-property-form').submit(function(event) {
		event.preventDefault();
		$(this).data('offset', parseInt(rem_ob.offset));
		var s_wrap = $(this).closest('.ich-settings-main-wrap');
		var results_cont = '';
		if ($(this).data('resselector') != '') {
			selectorTest = $(this).data('resselector');
			if ( selectorTest.indexOf('.') != 0 && selectorTest.indexOf('#') != 0 ){
				if ( $("." + selectorTest).length )
				{
					results_cont = $("." + selectorTest);
				} else if ( $("#" + selectorTest).length ) {
					results_cont = $("#" + selectorTest);
				}
			} else {
				if ( $(selectorTest).length ){
					results_cont = $(selectorTest);
				}
			}
		}

		if ( results_cont == '' || typeof results_cont === "undefined" ){
			results_cont = s_wrap.find('.searched-properties');
		}
		s_wrap.find('.searched-properties').html('');
		s_wrap.find('.loader').show();

	    var ajaxurl = $(this).data('ajaxurl');
	    var formData = $(this).serializeArray();

		 
	    function arrayClean(thisArray, thisName) {
	    	
		    $.each(thisArray, function(index, item) {
		        if (item != undefined ) {

			        if (item.name == thisName) {
			            formData.splice( index, 1);      
			        }
		        };
		    });
		}
	    var fields = $(".rem-field-hide select").map(function() {
        	return $(this).attr('name');
       	}).get();
	    $.each( fields, function(index, key){
	    	arrayClean( formData, key);
	    });

	    $.post(ajaxurl, formData, function(resp) {
			s_wrap.find('.loader').hide();
	    	results_cont.html(resp);
	    	if (s_wrap.data('autoscroll') == 'enable') {
			    $('html, body').animate({
			        scrollTop: results_cont.offset().top
			    }, 2000);
	    	}
			$('.ich-settings-main-wrap .image-fill').each(function(index, el) {
				jQuery(this).imagefill();
			});
			
			if ($('.search-property-form').data('offset') == '-1') {
				$('.rem-load-more-wrap').hide();
			}
			if ($('.masonry-enabled').length) {
				$('.searched-properties').imagesLoaded( function() {
					$('.searched-properties > .row').masonry();
				});
			}
	    });
	}); 

	if ($('.search-property-form').length > 0 && $('.search-property-form').data('offset') != '-1') {
		
		$('body').on('click', '#load-more-properties', function(event) {
			event.preventDefault();
			$(".rem-load-more-wrap").remove();
			var form = $('.search-property-form');
			var s_wrap = $(form).closest('.ich-settings-main-wrap');
			var results_cont = '';
			if ($(form).data('resselector') != '') {
				selectorTest = $(form).data('resselector');
				if ( selectorTest.indexOf('.') != 0 && selectorTest.indexOf('#') != 0 ){
					if ( $("." + selectorTest).length )
					{
						results_cont = $("." + selectorTest);
					} else if ( $("#" + selectorTest).length ) {
						results_cont = $("#" + selectorTest);
					}
				} else {
					if ( $(selectorTest).length ){
						results_cont = $(selectorTest);
					}
				}
			}

			if ( results_cont == '' || typeof results_cont === "undefined" ){
				results_cont = s_wrap.find('.searched-properties');
			}
			s_wrap.find('.more-property-loader').show();

		    var ajaxurl = $(form).data('ajaxurl');
		    var formData = $(form).serializeArray();

			 
		    function arrayClean(thisArray, thisName) {
		    	
			    $.each(thisArray, function(index, item) {
			        if (item != undefined ) {

				        if (item.name == thisName) {
				            formData.splice( index, 1);      
				        }
			        };
			    });
			}
		    var fields = $(".rem-field-hide select").map(function() {
	        	return $(form).attr('name');
	       	}).get();
		    $.each( fields, function(index, key){
		    	arrayClean( formData, key);
		    });
		    var offset = $(form).data('offset');
		    var offset_obj = {
		    	name : 'offset',
		    	value : parseInt(offset)
		    }
		    formData.push(offset_obj);
		    
		    $.post(ajaxurl, formData, function(resp) {
				$('.more-property-loader').remove();
				$(resp).remove('.filter-title');
		    	results_cont.append(resp);
				$('.ich-settings-main-wrap .image-fill').each(function(index, el) {
					jQuery(this).imagefill();
				});
				$(form).data('offset', parseInt(offset)+parseInt(rem_ob.offset) );
				console.log($('.rem-results-box').length);

				$('.rem-results-count').html('');
				$('.rem-results-count').html($('.rem-results-box').length);
		    });
		});
	}

	if (jQuery('.labelauty-unchecked-image').length == 0) {
		jQuery(".labelauty").labelauty();
	}

	var $filter = jQuery('.filter', '#rem-search-box');

	jQuery('.botton-options', '#rem-search-box').on('click', function(){
		hideSearcher();
	});

	function hideSearcher(navigatorMap){

		if(navigatorMap==true){
			$searcher.slideUp(500);
		} else {
			$searcher.slideToggle(500);
		}
		return false;
	}

	jQuery(".set-searcher", '#rem-search-box').on('click', hideSearcher);

	jQuery(".more-button", '#rem-search-box').on('click', function(){
		$filter.toggleClass('hide-filter');
		return false;
	});

	if ($('.search-container.auto-complete').length > 0) {
		$('.search-container.auto-complete input[type=text]').autocomplete({
		    source: function (request, response) {
		    	var element_id = $(this.element).attr('id');
		    	var ajax_url = $('#search-property').data('ajaxurl');
		    	var data = {
		    		action: 'rem_search_autocomplete',
		    		field: element_id,
		    		search: request.term
		    	}
		    	$.post(ajax_url, data, function(resp) {
		    		if (resp != '') {
		    			response( JSON.parse(resp) );	
		    		}
		    	});
		    }
		});		
	}

	if ($('.rem-widget-search.auto-complete').length > 0) {
		$('.rem-widget-search.auto-complete input[type=text]').autocomplete({
		    source: function (request, response) {
		    	var element_id = $(this.element).attr('id');
		    	var ajax_url = $('.rem-ajax-url').val();
		    	var data = {
		    		action: 'rem_search_autocomplete',
		    		field: element_id,
		    		search: request.term
		    	}
		    	$.post(ajax_url, data, function(resp) {
		    		if (resp != '') {
		    			response( JSON.parse(resp) );	
		    		}
		    	});
		    }
		});		
	}

	$('.p-slide-wrap').each(function(index, el) {
		$(this).find('.price-range').noUiSlider({
			start: [ parseInt(rem_ob.price_min_default), parseInt(rem_ob.price_max_default) ],
			behaviour: 'drag',
			step: parseInt(rem_ob.price_step),
			connect: true,
			change: function(){
				alert('works');
			},
			range: {
				'min': parseInt(rem_ob.price_min),
				'max': parseInt(rem_ob.price_max)
			},
			format: wNumb({
				decimals: parseInt(rem_ob.decimal_points),
				mark: rem_ob.decimal_separator,
				thousand: rem_ob.thousand_separator,
			}),
		});

		$(this).find('.price-range').Link('lower').to( $(this).find('#price-value-min') );
		$(this).find('.price-range').Link('lower').to( $(this).find('#min-value') );
		$(this).find('.price-range').Link('upper').to( $(this).find('#price-value-max') );
		$(this).find('.price-range').Link('upper').to( $(this).find('#max-value') );
	});

	$('.slider-range-input').each(function(index, el) {
	    
	    $(this).noUiSlider({
	        start: [ parseInt($(this).data('default_min')), parseInt($(this).data('default_max')) ],
	        behaviour: 'drag',
	        step: 1,
	        connect: true,
	        range: {
	            'min': parseInt($(this).data('min')),
	            'max': parseInt($(this).data('max'))
	        },
			format: wNumb({
				decimals: parseInt(rem_ob.range_decimal_points
					),
				mark: rem_ob.range_decimal_separator,
				thousand: rem_ob.range_thousand_separator,
			}),
	    });
	    $(this).Link('lower').to( $(this).siblings('.price-slider').find('.price-value-min') );
	    $(this).Link('lower').to( $(this).siblings('.min-value') );
	    $(this).Link('upper').to( $(this).siblings('.price-slider').find('.price-value-max') );
	    $(this).Link('upper').to( $(this).siblings('.max-value') );
	});

	$('.price-slider').on('change', '.any-check', function(event) {
		event.preventDefault();
		var rangeWrapper = $(this).closest('.p-slide-wrap');
		if ($(this).find('input').is(":checked")){
			rangeWrapper.find('.noUi-base, span').css({
				'opacity': '0.5',
				'pointer-events': 'none'
			});
			rangeWrapper.find('.min-value').val('');
			rangeWrapper.find('.max-value').val('');
		} else {
			rangeWrapper.find('.noUi-base, span').css({
				'opacity': '1',
				'pointer-events': 'inherit'
			});
			var min_val = rangeWrapper.find('.price-value-min').text();
			var max_val = rangeWrapper.find('.price-value-max').text();
			rangeWrapper.find('.min-value').val(min_val);
			rangeWrapper.find('.max-value').val(max_val);
		}
	});
	
	$('.price-slider .any-check').each(function(index, checkbox) {
		var rangeWrapper = $(this).closest('.p-slide-wrap');
		if ($(checkbox).find('input').is(':checked')) {
			rangeWrapper.find('.noUi-base, span').css({
				'opacity': '0.5',
				'pointer-events': 'none'
			});
			rangeWrapper.find('.min-value').val('');
			rangeWrapper.find('.max-value').val('');
		};
	});
});