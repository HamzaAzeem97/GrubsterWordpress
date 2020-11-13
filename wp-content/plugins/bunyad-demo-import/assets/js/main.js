"use strict";

jQuery(function($) {
	var ajaxRun = 0;

	function ajaxCall(data) {
		
		ajaxRun++;
		
		// Been almost 15 minutes, that's enough
		if (ajaxRun > 30) {
			$('.bunyad-import .ajax-response').append('<div class="error  below-h2"><p>Could not complete the import. Please contact support.</p></div>');
			$('.bunyad-import .ajax-loader').remove();
			
			return;
		}
		
		$.ajax({
			method:     'POST',
			url:        Bunyad_Import.ajax_url,
			data:       data,
			beforeSend: function() {
				$('.bunyad-import .ajax-response').append(
					'<div class="notice ajax-loader"><p><span class="spinner"></span> Importing...</p></div>'
				);
			}
		})
		.done(function(response) {
			if ( 'undefined' !== typeof response.status && 'newAJAX' === response.status ) {
				ajaxCall(data);
			}
			else if ( 'undefined' !== typeof response.message ) {
				$('.bunyad-import .ajax-response').append( '<p>' + response.message + '</p>' );
				$('.bunyad-import .ajax-loader').remove();
			}
			else {
				$('.bunyad-import .ajax-response').append('<div class="error  below-h2"><p>' + response + '</p></div>');
				$('.bunyad-import .ajax-loader').remove();
			}

		})
		.fail(function(error) {
			
			// Server has possibly hit the execution time, restart
			ajaxCall(data);
		});
	}
	
	// Run the importer
	$('.button.import').click(function() {
		ajaxCall({
			action: 'bunyad_import_demo',
			demo_id: $(this).data('id'),
			import_type: $(this).parent().find('[name=import_type]').val(),
			security: Bunyad_Import.ajax_nonce
		});
		
		// Scroll to response area if needed
		var scrollTo = $(".bunyad-import .ajax-response").offset().top - 75
		if ($(window).scrollTop() > scrollTo) {
			$('html, body').animate({
				scrollTop: scrollTo
			}, 100);
		}
		
		// disable all
		$('.button.import').attr('disabled', true).unbind('click');
	});
	
	// Remove
	$('.bunyad-import').on('click', 'a.cleanup', function() {
		var links = ($(this).data('remove')).split(','),
			current;
		
		var recursive = function() {
			current = links.shift();
			
			if (!current) {
				return;
			}
			
			$.get(current, function() {
				recursive();
			});
		};
		
		recursive();
		
	});
	
});