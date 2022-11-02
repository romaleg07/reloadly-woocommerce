(function( $ ) {
	'use strict';

	$( window ).load(function() {
		if ($('#country-reloadly').length) {
			get_countries($('#country-reloadly'));
		}
		$('#name-reloadly').on('input', function() {
			if($(this).val().length > 3) {
				if($(this).val().length % 2 == 0) {
					setTimeout(() => {
						get_products($(this).val());
					}, "1000")
				}
			}
		})
		$('#reloadly-save-field').on('click', function() {
			add_img_product();
			save_reloadly_data();

		})
	});

	function get_countries(wrapper){
		let data = {
			'action': 'get_countries'
		};

		let arr = new Array();
		$.post(ajaxurl, data, function(response) {
			arr = $.parseJSON(response);
			console.log(arr);
			for(let i = 0; i < arr.length; i++ ) {
				wrapper.append('<option value="' + arr[i]['isoName'] + '">' + arr[i]['name'] + '</option>')
			}
		});
	}

	function get_products(nameProd){
		let country = $('#country-reloadly').val();
		let data = {
			'action': 'get_products',
			'country': country,
			'nameProd': nameProd
		};

		let arr = [];
		$.post(ajaxurl, data, function(response) {
			arr = $.parseJSON(response);
			console.log(arr['content']);
			let wrapper = $('#help-div-1');
			let content = '';
			for(let i = 0; i < arr['content'].length; i++ ) {
				let productName = arr['content'][i]['productName'];
				let productId = arr['content'][i]['productId'];
				let senderFee = arr['content'][i]['senderFee'];
				let senderFeePercentage = arr['content'][i]['senderFeePercentage'];
				let discountPercentage = arr['content'][i]['senderFeePercentage'];
				let recipientCurrencyCode = arr['content'][i]['recipientCurrencyCode'];
				let denominationType = arr['content'][i]['denominationType'];
				let senderCurrencyCode = arr['content'][i]['senderCurrencyCode'];
				let countryIsoName = arr['content'][i]['country']['isoName'];
				let countryName = arr['content'][i]['country']['name'];
				let img_link = arr['content'][i]['logoUrls'][0];
				let redeem_instruction_verbose = arr['content'][i]['redeemInstruction']['verbose'];
				let denomination = [];
				if (denominationType == 'RANGE') {
					denomination.push(arr['content'][i]['minRecipientDenomination']);
					denomination.push(arr['content'][i]['maxRecipientDenomination'])
				} else {
					denomination = arr['content'][i]['fixedRecipientDenominations'];
				}
				content += '<div class="reloaly-product-item" ';
				content += 'data-name="' + productName + '" ';
				content += 'data-id="' + productId + '" ';
				content += 'data-sender_fee="' + senderFee + '" ';
				content += 'data-sender_fee_percentage="' + senderFeePercentage + '" ';
				content += 'data-discount_percentage="' + discountPercentage + '" ';
				content += 'data-recipient_currency_code="' + recipientCurrencyCode + '" ';
				content += 'data-sender_currency_code="' + senderCurrencyCode + '" ';
				content += 'data-denomination="' + denomination + '" ';
				content += 'data-denomination_type="' + denominationType + '" ';
				content += 'data-country="' + countryIsoName + ', ' + countryName + '" ';
				content += 'data-img="' + img_link + '" ';
				content += 'data-instruction="' + redeem_instruction_verbose + '" ';
				content += '" >' + productName + ' <span> ' + denominationType + ' (' + recipientCurrencyCode + ')</span></div>';

			}
			wrapper.html(content);
			wrapper.addClass('active');

			$('.reloaly-product-item').on('click', function() {
				chose_product($(this));
			})

		});
	}

	function change_country() {
		$('#country-reloadly').on('change', function() {

		})
	}

	function chose_product(product) {
		$('#name-reloadly').val(product.text());
		$('#name-reloadly-system').val(product.attr('data-name'));
		$('#id-reloadly').val(product.attr('data-id'));
		$('#current-country-reloadly').val(product.attr('data-country'));
		$('#sender-fee-reloadly').val(product.attr('data-sender_fee'));
		$('#sender-fee-percentage-reloadly').val(product.attr('data-sender_fee_percentage'));
		$('#discount-percentage-reloadly').val(product.attr('data-discount_percentage'));
		$('#denomination-reloadly-system').val(product.attr('data-recipient_currency_code') );
		$('#image_product_in_reloadly').val(product.attr('data-img') );
		$('#redeem_instruction_verbose').val(product.attr('data-instruction') );


		$('#help-div-1').removeClass('active');

		add_value_fields(product);
	}

	function add_value_fields(product) {
		let wrapper = $('.item-value-reloadly');
		let content = '<h3>Введите или выберите номинал:</h3>';
		let denomination = product.attr('data-denomination');
		let currency = product.attr('data-recipient_currency_code');
		denomination = denomination.split(',')
		
		content += '<div>';
		if (product.attr('data-denomination_type') == 'RANGE') {
			content += '<label>Введите номинал от ' + denomination[0] + ' до ' + denomination[1] + ' (' + currency + ')';
			content += '<input id="denomination_reloadly" type="text" value="' + denomination[0] + '">'
			content += '</label>';
		} else {
			content += '<label>Выберите номинал' + ' (' + currency + ')';
			content += '<select id="denomination_reloadly">';
			for(let i = 0; i < denomination.length; i++){
				content += '<option val="' + denomination[i] + '">' + denomination[i] + '</option>';
			}
			content += '</select>';
			content += '</label>';
		}
		content += '</div>';
		wrapper.html(content);

	}

	function save_reloadly_data() {
		let product_id_woocommerce = $('#id_product_in_woocommerce').val();

		let product_name_reloadly = $('#name-reloadly-system').val();
		let product_id_reloadly = $('#id-reloadly').val();
		let product_country_reloadly = $('#current-country-reloadly').val();
		let product_sender_fee_reloadly = $('#sender-fee-reloadly').val();
		let product_sender_fee_percentage_reloadly = $('#sender-fee-percentage-reloadly').val();
		let product_discount_percentage_reloadly = $('#discount-percentage-reloadly').val();
		let product_denomination_reloadly = $('#denomination_reloadly').val();
		let product_denomination_currency_reloadly = $('#denomination-reloadly-system').val();
		let product_redeem_instruction_verbose = $('#redeem_instruction_verbose').val();

		let valid = true;

		$('#reloadly_options .item input:not(#redeem_instruction_verbose), #denomination_reloadly').each(function() {
			if($(this).val() == '') {
				valid = false;
				$(this).addClass('error');
			} else {
				$(this).removeClass('error');
			}
		})

		if(!valid) {
			return false;
		}

		let data = {
			'action': 'save_reloadly_data',
			'product_id_woocommerce': product_id_woocommerce,
			'product_name_reloadly': product_name_reloadly,
			'product_id_reloadly': product_id_reloadly,
			'product_country_reloadly': product_country_reloadly,
			'product_sender_fee_reloadly': product_sender_fee_reloadly,
			'product_sender_fee_percentage_reloadly': product_sender_fee_percentage_reloadly,
			'product_discount_percentage_reloadly': product_discount_percentage_reloadly,
			'product_denomination_reloadly': product_denomination_reloadly,
			'product_denomination_currency_reloadly': product_denomination_currency_reloadly,
			'product_redeem_instruction_verbose': product_redeem_instruction_verbose
		};

		$.post(ajaxurl, data, function(response) {

			alert('Сохранено');
			
		});
		return false;
	}

	function add_img_product() {
		let image_product_in_reloadly = $('#image_product_in_reloadly').val();
		let id_product_in_woocommerce = $('#id_product_in_woocommerce').val();
		let data = {
			'action': 'add_img_product_from_reloadly',
			'image_product_in_reloadly': image_product_in_reloadly,
			'id_product_in_woocommerce': id_product_in_woocommerce
		};

		$.post(ajaxurl, data, function(response) {

			console.log('ответ ' + response);
			
		});

	}

})( jQuery );
