class MaskHandler {
    constructor() {
        this.initMasks();
    }

    initMasks() {
        document.querySelectorAll('[data-mask]').forEach(input => {
            const maskType = input.getAttribute('data-mask');
            this.applyMask(input, maskType);
        });
    }

    applyMask(element, type) {
        let maskOptions;

        switch (type) {
            case 'telefone':
                maskOptions = { mask: '(00) 00000-0000' };
                break;

            case 'cep':
                maskOptions = { mask: '00000-000' };
                break;

            case 'data-nascimento':
                maskOptions = { mask: '00/00/0000' };
                break;

            case 'cartao':
                maskOptions = { mask: '0000 0000 0000 0000' };
                break;

            case 'documento':
                maskOptions = {
                    mask: [
                        { mask: '000.000.000-00' }, // CPF
                        { mask: '00.000.000/0000-00' } // CNPJ
                    ]
                };
                break;

            default:
                console.warn(`Máscara não reconhecida: ${type}`);
                return;
        }

        IMask(element, maskOptions);
    }
}


