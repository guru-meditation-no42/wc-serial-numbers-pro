/**
 * WC Serial Numbers Pro
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

(function ($) {
	'use strict';
	$.wc_serial_numbers_pro_admin = function () {
		var plugin = this;
		plugin.init = function () {
			$(document).on('change', '.serial_key_source', plugin.control_keysource_view);
			$(document).on('woocommerce_variations_loaded', plugin.control_keysource_view);
			$(document).on('woocommerce_variations_loaded', plugin.control_serial_number_settings_view);
			$(document).on('change', '.variable_is_serial', plugin.control_serial_number_settings_view);
			$('.column-generate').on('click', 'input.button', plugin.generate_serial_numbers);
		};
		plugin.control_keysource_view = function () {
			$('.serial_key_source').each(function () {
				if ($(this).is(':checked')) {
					var source = $(this).attr('value');
					$(this).closest('div').find('.wc-serial-numbers-key-source-settings').each(function () {
						var dataSource = $(this).data('source');
						if (dataSource === source) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				}
			});
		};
		plugin.control_serial_number_settings_view = function () {
			$('.variable_is_serial').each(function () {
				var $wrapper = $(this).closest('.data');
				if ($(this).is(':checked')) {
					$wrapper.find('.wc-serial-numbers-variation-settings').show();
				} else {
					$wrapper.find('.wc-serial-numbers-variation-settings').hide();
				}
			});
		};
		plugin.generate_serial_numbers = function (e) {
			e.preventDefault();
			var $button = $(this);
			var $wrap = $(this).closest('td');
			var $input = $wrap.find('.serial_count');
			var count = parseInt($wrap.find('.serial_count').val(), 10);
			var id = parseInt($button.data('id'), 10);
			var nonce = $button.data('nonce');

			$button.attr('disabled', 'disabled');
			$input.attr('disabled', 'disabled');

			if (!id || !nonce) {
				$button.removeAttr('disabled');
				alert(wc_serial_numbers_pro_i10n.i18n.something_wrong);
			}

			$button.next().css({
				visibility: 'visible'
			});

			wp.ajax.send('wc_serial_numbers_generate_serials', {
				data: {
					serial_count: count,
					generator_id: id,
					nonce: nonce
				},
				success: function (res) {
					if (res.message) {
						alert(res.message);
					}
					$button.removeAttr('disabled');
					$input.removeAttr('disabled');
					$button.next().css({
						visibility: 'hidden'
					});
				},
				error: function (error) {
					alert(error.message);
					$button.removeAttr('disabled');
					$input.removeAttr('disabled');
					$button.next().css({
						visibility: 'hidden'
					});
				}
			});
			$input.val('');
			return false;
		};
		plugin.init();
	};

	//$.fn
	$.fn.wc_serial_numbers_pro_admin = function () {
		return new $.wc_serial_numbers_pro_admin();
	};

	$.wc_serial_numbers_pro_admin();
})(jQuery, window);
