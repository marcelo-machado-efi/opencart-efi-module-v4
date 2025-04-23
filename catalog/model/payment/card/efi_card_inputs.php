<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment\Card;

use Opencart\System\Library\Language;

class EfiCardInputs extends \Opencart\System\Engine\Model
{
    /**
     * Retorna os campos de entrada formatados para pagamento de  Cartão.
     *
     * @param Language $language Objeto de linguagem para carregar os labels.
     * @return array Dados formatados contendo os campos de entrada.
     */
    public function getEntryFormatted(Language $language): array
    {
        return [

            [
                'name' => 'payment_efi_customer_name',
                'required' => true,
                'label' => $language->get('text_label_customer_name'),
                'type' => 'text',
                'data-mask' => 'nome'
            ],
            [
                'name' => 'payment_efi_customer_document',
                'required' => true,
                'label' => $language->get('text_label_customer_document'),
                'type' => 'text',
                'data-mask' => 'documento'

            ],
            [
                'name' => 'payment_efi_customer_email',
                'required' => true,
                'label' => $language->get('text_label_customer_email'),
                'type' => 'email',
                'data-mask' => 'email'

            ],
            [
                'name' => 'payment_efi_customer_phone',
                'required' => true,
                'label' => $language->get('text_label_customer_phone'),
                'type' => 'text',
                'data-mask' => 'telefone'

            ],
            [
                'name' => 'payment_efi_customer_card_number',
                'required' => true,
                'label' => $language->get('text_label_customer_card_number'),
                'type' => 'text',
                'data-mask' => 'cartao'


            ],
            [
                'name' => 'payment_efi_customer_card_expire',
                'required' => true,
                'label' => $language->get('text_label_customer_card_expire'),
                'type' => 'text',
                'data-mask' => 'cartao-vencimento'

            ],
            [
                'name' => 'payment_efi_customer_card_cvv',
                'required' => true,
                'label' => $language->get('text_label_customer_card_cvv'),
                'type' => 'text',
                'data-mask' => 'cartao-cvv'

            ],
            [
                'name' => 'payment_efi_customer_card_installments',
                'required' => true,
                'label' => $language->get('text_label_customer_card_installments'),
                'type' => 'select',
                'data-mask' => 'cartao-cvv',
                'options' => [
                    [
                        'label' => 'Insira os dados do seu cartão',
                        'value' => ''
                    ]
                ]

            ],

        ];
    }
}
