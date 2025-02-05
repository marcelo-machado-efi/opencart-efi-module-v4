<?php

namespace Opencart\Admin\Model\Extension\Efi\Payment;

use Opencart\System\Library\Language;

class EfiConfigPix extends \Opencart\System\Engine\Model
{
    /**
     * Retorna os campos de entrada formatados para configurações Pix.
     *
     * @param Language $language Objeto de linguagem para carregar os rótulos.
     * @return array Dados formatados contendo os campos de entrada.
     */
    public function getEntryFormatted(Language $language): array
    {
        return [
            'name' => 'Configurações Pix',
            'inputs' => [
                [
                    'name' => 'payment_efi_pix_key',
                    'required' => true,
                    'label' => $language->get('entry_pix_key'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_pix_expire_at',
                    'required' => true,
                    'label' => $language->get('entry_pix_expire_at'),
                    'type' => 'number',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_pix_certificate',
                    'required' => true,
                    'label' => $language->get('entry_pix_certificate'),
                    'type' => 'file',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_pix_discount',
                    'required' => false,
                    'label' => $language->get('entry_pix_discount'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_pix_mtls',
                    'required' => false,
                    'label' => $language->get('entry_pix_mtls'),
                    'type' => 'checkbox',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_pix_status',
                    'required' => false,
                    'label' => $language->get('entry_pix_status'),
                    'type' => 'checkbox',
                    'value' => ''
                ]
            ]
        ];
    }
}
