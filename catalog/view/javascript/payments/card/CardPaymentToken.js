class CardPaymentToken {
	constructor(config) {
		this.config = config;
		this.account = config.accountCode;
		this.environment = config.environment ? 'sandbox' : 'production';
		this.reuse = false;
		this.formId = 'efi-card-form';

		this.fields = this.extractFields();
	}

	extractFields() {
		const getValue = (id) => document.getElementById(id)?.value || '';
		const [month, yearSuffix] = getValue(this.config.cardExpirationInputId).split('/');
		const clean = (value) => value.replace(/\D/g, '');

		return {
			number: clean(getValue(this.config.cardNumberInputId)),
			cvv: getValue(this.config.cardCvvInputId),
			expirationMonth: month,
			expirationYear: `20${yearSuffix}`,
			holderName: getValue(this.config.customerNameInputId),
			holderDocument: clean(getValue(this.config.customerDocumentInputId))
		};
	}

	async generateToken() {
		try {
			const brand = await this.identifyBrand();

			const result = await EfiPay.CreditCard
				.setAccount(this.account)
				.setEnvironment(this.environment)
				.setCreditCardData({
					brand: brand,
					number: this.fields.number,
					cvv: this.fields.cvv,
					expirationMonth: this.fields.expirationMonth,
					expirationYear: this.fields.expirationYear,
					holderName: this.fields.holderName,
					holderDocument: this.fields.holderDocument,
					reuse: this.reuse
				})
				.getPaymentToken();

			this.insertTokenHiddenInput(result.payment_token);
			

			

			return result;
		} catch (error) {
			console.error("Erro ao gerar token de pagamento:", error);
			throw error;
		}
	}

	insertTokenHiddenInput(token) {
		const form = document.getElementById(this.formId);
		if (form) {
			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'payment_token';
			input.value = token;
			form.appendChild(input);

			this.clearCardFields();
		}
	}

	clearCardFields() {
		const ids = [
			this.config.cardNumberInputId,
			this.config.cardCvvInputId,
			this.config.cardExpirationInputId,
		];

		ids.forEach(id => {
			const el = document.getElementById(id);
			if (el) el.value = '';
		});
	}

	async identifyBrand() {
		try {
			const brand = await EfiPay.CreditCard
				.setCardNumber(this.fields.number)
				.verifyCardBrand();

			return brand;
		} catch (error) {
			console.error("Erro ao identificar a bandeira:", error);
			throw error;
		}
	}
}
