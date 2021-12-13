jQuery(document).ready(function($) {
	$('.vendor_enable_disable').on('change', function(e) {
		e.preventDefault();
		let that = $(this);
		let vendor_id = that.val();
		let data = {
			action: 'enable_vendor',
			vendor_id: vendor_id,
			security: wcv_vendors_table_params.nonce
		};

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: data,
			beforeSend: function() {
				console.log('Sending ajax');
			},
			success: function(response) {
				console.log(response);
			},
			error: function(err) {
				console.log(err);
			}
		});
	});

	$('.delete_vendor').each(function(i, link) {
		$(link).on('click', function(e) {
			if (!window.confirm(wcv_vendors_table_params.confirm_delete)) {
				e.preventDefault();
			}
		});
	});

	$('#wcv-vendors-table').on('submit', function(e) {
		const action = document.getElementById('bulk-action-selector-top');
		const action_value = action.value;

		if ('delete' === action_value) {
			if (!window.confirm(wcv_vendors_table_params.confirm_bulk_delete)) {
				e.preventDefault();
			}
		}
	});
});
