
jQuery(document).ready(function ($) {

	$('.select2').select2();

	// If element exists
	jQuery.fn.isExists = function () {
		return this.length;
	};

	// If value exists
	jQuery.fn.isValueExists = function () {
		if (!this.isExists()) {
			return false;
		}
		return this.val().length;
	};

	// If select option is select
	jQuery.fn.isSelected = function () {

		if (!this.isExists()) {
			return false;
		}

		this.find(":selected").parent().removeClass('digitsol-error');
		this.find(":selected").parent().siblings('.error-message').remove();

		if (this.val() === "") {
			this.find(":selected").parent().after('<span class="error-message"> * Required</span>');
			this.find(":selected").parent().addClass('digitsol-error');
			return false;
		}

		return true;
	};

	// Add/Remove error css-class
	jQuery.fn.isEmpty = function () {

		this.removeClass('digitsol-error');
		this.siblings('.error-message').remove();

		if (!this.isValueExists()) {
			this.after('<span class="error-message"> * Required</span>');
			this.addClass('digitsol-error');
			return true;
		}
		return false;
	};
	jQuery.fn.isEmail = function () {

		this.removeClass('digitsol-error');
		this.siblings('.error-message').remove();

		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if (!regex.test($.trim(this.val()))) { // not valid email
			this.after('<span class="error-message"> Enter Valid Email!</span>');
			this.addClass('digitsol-error');
			return false;
		}
		return true;
	};
	jQuery.fn.isPhone = function () {
		this.removeClass('digitsol-error');
		this.siblings('.error-message').remove();

		var regex = /^\(?(\d{3})\)?[- ](\d{3})[- ](\d{4})$/;

		if (!regex.test($.trim(this.val()))) { // not valid email
			this.after('<span class="error-message"> Valid Phone: (800)-640-0599</span>');
			this.addClass('digitsol-error');
			return false;
		}
		return true;
	};

	class DSM_Send {
		constructor() {
			this.form = $('#dsm-type-form');
			this.submitBtn = $('#submit');
			this.submitBtnHtml = this.submitBtn.val();
			this.resetListBtn = $('.dsm-all-sent__reset-list');
			this.events();
		}

		events() {
			this.submitBtn.on("click", this.validateSubmit.bind(this));
			this.resetListBtn.on("click", this.resetCurrentForm.bind(this));
		}

		// Reset Current Form
		resetCurrentForm(e) {
			e.preventDefault();
			this.form.find('textarea[name=sms]').prop('disabled', false);
			this.form.find('textarea[name=sms]').val('');

			this.submitBtn.prop("disabled", false);
			this.submitBtn.val(this.submitBtnHtml);

			$(".dsm-all-sent").addClass('dsm-none');
			$("#customers-list li").removeClass('dsm-sent');

		}

		// Submit Validate
		validateSubmit(e) {
			e.preventDefault();
			this.form = $(e.currentTarget).closest('form');
			let $form = this.form;
			this.submitBtn = $(e.currentTarget);
			var validated = false;
			var required_fields = {
				sms: $form.find('textarea[name=sms]')
			};
			$.each(required_fields, function (key, value) {
				if (this.isEmpty()) {
					validated = false;
					return false;
				}
				validated = true;

			});

			if (validated) {
				this.submitData();
			}
		}

		// Send Data
		submitData() {

			var required_data = {};

			var self = this;

			required_data = {
				self: this,
				type: 'POST',
				dataType: 'json',
				url: dsm_ajax_object.ajax_url,
				action: 'send_sms',
				data: this.form.serialize(),
			};

			let customers = this.form.find('input[name=customers]').val();
			let message = this.form.find('textarea[name=sms]').val();
			customers = customers.split(",");

			$.when.apply($, customers.map(function (customer) {
				return $.ajax({
					type: required_data.type,
					dataType: required_data.dataType,
					url: required_data.url,
					data: {
						action: required_data.action,
						form: required_data.data,
						customer: customer,
						message: message,
					},
					beforeSend: function () {
						self.form.find('textarea[name=sms]').prop('disabled', true);
						self.submitBtn.prop("disabled", true);
						self.submitBtn.val('Sending...');
						$("#customers-list li").addClass('dsm-sending');
					},
					success: function (response) {
						if (response.success) {
							if (response.redirect) {
								window.location.reload();
								// window.location.href = "";
							}
							setTimeout(function () {
								$("#customers-list").find("#" + customer).removeClass('dsm-sending');
								$("#customers-list").find("#" + customer).addClass('dsm-sent');
							 }, 2000);

						} else {
							alert(response.msg);
							self.submitBtn.prop("disabled", false);
							self.submitBtn.html(self.submitBtnHtml);
						}
					},
					error: function () {
						alert('Something went wrong. Try after page reload.');
						self.submitBtn.prop("disabled", false);
						self.submitBtn.html(self.submitBtnHtml);
					}
				}).then(function (data) {
					console.log('Done AJAX Then');
				});
			})).then(function () {
				setTimeout(function () {
					$(".dsm-all-sent").removeClass('dsm-none');
				}, 3000);


			});
		}
	}
	new DSM_Send();
})
