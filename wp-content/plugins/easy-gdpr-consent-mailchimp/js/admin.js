/**
 * EGCF Admin Class - NOT an API
 */
var EGCF_Admin = function($) {

	var self = {

		init: function() {
			$('.egcf-code-detect').on('click', this.detectCode);

			$('.egcf-instructions h3 a').click(function() {
				$(this).parent().next().toggleClass('active');
				return false;
			});
		},

		/**
		 * The magic - use DomParser to detect checkboxes in form code and use nearby labels
		 */
		detectCode: function() {

			var code = $(this).parent().find('textarea').val();

			var dom = new DOMParser().parseFromString(code, 'text/html');
			var checkboxes = [];
			[].forEach.call(dom.body.querySelectorAll('input[type="checkbox"]'), function(node) {
				
				var label, 
					parent = node.parentNode;

				if (parent.matches('label')) {
					label = parent.textContent.trim();
				}
				else if (ele = parent.querySelector('label')) {
					label = ele.textContent;
				}
				else if (ele = parent.querySelector('span')) {
					label = ele.textContent;
				}

				checkboxes.push({
					key: node.name,
					value: node.value,
					label: label
				});
			});

			if (!checkboxes.length) {
				return;
			}

			var key = 0;
			var group = $('[data-groupid=egcf_checkboxes]');

			// Remove existing
			group.find('.cmb-repeatable-grouping')
				.find('.cmb-remove-group-row')
				.click();

			group.on('cmb2_add_row', function(e, newRow) {

				var checkbox = checkboxes[key];
				
				newRow.find('[name*="[name]"]').val(checkbox.key);
				newRow.find('[name*="[value]"]').val(checkbox.value);
				newRow.find('[name*="[label]"]').val(checkbox.label);

				key++;

				if (key < checkboxes.length) {
					$('.cmb-add-group-row', group).click();
				}
				else {
					// Cleanup - all done
					group.find('.cmb-repeatable-grouping').first()
						.find('.cmb-remove-group-row')
						.click();

					$(this).off(e);
				}
			});

			$('.cmb-add-group-row', group).click();

			alert('We detected ' + checkboxes.length + ' checkboxes and have added them below.');
		}
	};

	return self;
};

jQuery(function($) {
	(new EGCF_Admin($)).init();
});

// Polyfill
if (!Element.prototype.matches) {
	Element.prototype.matches = Element.prototype.msMatchesSelector;
}
