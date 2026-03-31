/* AI SEO Content Plugin — Admin JS
   Author: Aliyan Faisal
*/
(function ($) {
	'use strict';

	// =====================
	// Utility: Show Notice
	// =====================
	function showNotice(selector, message, type) {
		var $notice = $(selector);
		$notice
			.removeClass('notice-success notice-error')
			.addClass('notice-' + type)
			.text(message)
			.hide()
			.slideDown(200);

		setTimeout(function () {
			$notice.slideUp(300);
		}, 4000);
	}

	// =====================
	// Settings Form Submit
	// =====================
	$('#aiscp-settings-form').on('submit', function (e) {
		e.preventDefault();

		var $btn     = $('#aiscp-save-btn');
		var $text    = $btn.find('.btn-text');
		var $spinner = $btn.find('.btn-spinner');

		$text.hide();
		$spinner.show();
		$btn.prop('disabled', true);

		var formData = new FormData(this);
		formData.append('action', 'aiscp_save_settings');
		formData.append('nonce', AISCP.nonce);

		$('#aiscp-settings-form input[type="checkbox"]').each(function () {
			if (!$(this).is(':checked')) {
				formData.set($(this).attr('name'), '0');
			}
		});

		$.ajax({
			url:         AISCP.ajax_url,
			type:        'POST',
			data:        formData,
			processData: false,
			contentType: false,
			success: function (res) {
				$text.show();
				$spinner.hide();
				$btn.prop('disabled', false);
				if (res.success) {
					showNotice('#aiscp-save-notice', res.data.message || AISCP.strings.saved, 'success');
				} else {
					showNotice('#aiscp-save-notice', res.data.message || AISCP.strings.error, 'error');
				}
			},
			error: function () {
				$text.show();
				$spinner.hide();
				$btn.prop('disabled', false);
				showNotice('#aiscp-save-notice', AISCP.strings.error, 'error');
			}
		});
	});

	// =====================
	// Activate License
	// =====================
	$('#aiscp-activate-btn').on('click', function () {
		var $btn     = $(this);
		var $text    = $btn.find('.btn-text');
		var $spinner = $btn.find('.btn-spinner');

		$text.hide();
		$spinner.show();
		$btn.prop('disabled', true);

		$.post(AISCP.ajax_url, {
			action: 'aiscp_validate_license',
			nonce:  AISCP.nonce,
		}, function (res) {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			if (res.success) {
				showNotice('#aiscp-license-notice', res.message, 'success');
				setTimeout(function () { location.reload(); }, 1500);
			} else {
				showNotice('#aiscp-license-notice', res.message, 'error');
			}
		}).fail(function () {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			showNotice('#aiscp-license-notice', AISCP.strings.error, 'error');
		});
	});

	// =====================
	// Re-validate License
	// =====================
	$('#aiscp-revalidate-btn').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Validating...');

		$.post(AISCP.ajax_url, {
			action: 'aiscp_validate_license',
			nonce:  AISCP.nonce,
		}, function (res) {
			$btn.prop('disabled', false).text('Re-validate');
			showNotice('#aiscp-license-notice', res.message, res.success ? 'success' : 'error');
		}).fail(function () {
			$btn.prop('disabled', false).text('Re-validate');
			showNotice('#aiscp-license-notice', AISCP.strings.error, 'error');
		});
	});

	// =====================
	// Deactivate License
	// =====================
	$('#aiscp-deactivate-btn').on('click', function () {
		if (!confirm('Are you sure you want to deactivate your license on this site?')) return;

		var $btn = $(this);
		$btn.prop('disabled', true).text('Deactivating...');

		$.post(AISCP.ajax_url, {
			action: 'aiscp_deactivate_license',
			nonce:  AISCP.nonce,
		}, function (res) {
			if (res.success) {
				showNotice('#aiscp-license-notice', res.message, 'success');
				setTimeout(function () { location.reload(); }, 1200);
			} else {
				$btn.prop('disabled', false).text('Deactivate License');
				showNotice('#aiscp-license-notice', res.message, 'error');
			}
		});
	});

	// =====================
	// Interval Settings Save
	// =====================
	$('#aiscp-interval-save-btn').on('click', function () {
		var $btn     = $(this);
		var $text    = $btn.find('.btn-text');
		var $spinner = $btn.find('.btn-spinner');

		$text.hide();
		$spinner.show();
		$btn.prop('disabled', true);

		$.post(AISCP.ajax_url, {
			action:             'aiscp_save_interval_settings',
			nonce:              AISCP.nonce,
			cron_interval:      $('#cron_interval').val(),
			cron_start_time:    $('#cron_start_time').val(),
			cron_posts_per_run: $('#cron_posts_per_run').val(),
			cron_enabled:       $('#cron_enabled').is(':checked') ? '1' : '0',
		}, function (res) {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			if (res.success) {
				showNotice('#aiscp-interval-notice', res.data.message + ' Next run: ' + res.data.next_run, 'success');
			} else {
				showNotice('#aiscp-interval-notice', res.data.message || AISCP.strings.error, 'error');
			}
		}).fail(function () {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			showNotice('#aiscp-interval-notice', AISCP.strings.error, 'error');
		});
	});

	// =====================
	// Run Full Cycle Now
	// =====================
	$('#aiscp-run-now-btn').on('click', function () {
		if (!confirm('This will immediately queue posts for generation. Continue?')) return;

		var $btn     = $(this);
		var $text    = $btn.find('.btn-text');
		var $spinner = $btn.find('.btn-spinner');

		$text.hide();
		$spinner.show();
		$btn.prop('disabled', true);

		$.post(AISCP.ajax_url, {
			action: 'aiscp_run_now',
			nonce:  AISCP.nonce,
		}, function (res) {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			showNotice('#aiscp-interval-notice', res.data.message || AISCP.strings.error, res.success ? 'success' : 'error');
			if (res.success) {
				setTimeout(function () { location.reload(); }, 2000);
			}
		}).fail(function () {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			showNotice('#aiscp-interval-notice', AISCP.strings.error, 'error');
		});
	});

	// =====================
	// Generate Test Post
	// =====================
	$('#aiscp-test-post-btn').on('click', function () {
		var $btn     = $(this);
		var $text    = $btn.find('.btn-text');
		var $spinner = $btn.find('.btn-spinner');
		var $result  = $('#aiscp-test-post-result');

		$text.hide();
		$spinner.show();
		$btn.prop('disabled', true);
		$result.hide();

		$.post(AISCP.ajax_url, {
			action: 'aiscp_test_post',
			nonce:  AISCP.nonce,
		}, function (res) {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			if (res.success) {
				$result.removeClass('notice-error').addClass('notice-success aiscp-notice').html(res.data.message).show();
			} else {
				$result.removeClass('notice-success').addClass('notice-error aiscp-notice').text(res.data.message || AISCP.strings.error).show();
			}
		}).fail(function () {
			$text.show();
			$spinner.hide();
			$btn.prop('disabled', false);
			$result.removeClass('notice-success').addClass('notice-error aiscp-notice').text(AISCP.strings.error).show();
		});
	});

	// =====================
	// Clear Log
	// =====================
	$('#aiscp-clear-log-btn').on('click', function () {
		if (!confirm('Clear all log entries? This cannot be undone.')) return;

		var $btn = $(this);
		$btn.prop('disabled', true).text('Clearing...');

		$.post(AISCP.ajax_url, {
			action: 'aiscp_clear_log',
			nonce:  AISCP.nonce,
		}, function (res) {
			if (res.success) {
				location.reload();
			} else {
				$btn.prop('disabled', false).text('Clear Log');
				showNotice('#aiscp-interval-notice', res.data.message || AISCP.strings.error, 'error');
			}
		});
	});

})(jQuery);
