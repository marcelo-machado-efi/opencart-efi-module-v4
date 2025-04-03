<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment;

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

            ]

        ];
    }
}
