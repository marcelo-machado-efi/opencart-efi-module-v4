<?php

namespace Opencart\Admin\Model\Extension\Efi\Payment;

use Opencart\System\Library\Language;

class EfiConfigRequired extends \Opencart\System\Engine\Model
{
    /**
     * Retorna os campos de entrada formatados para configurações obrigatórias.
     *
     * @param Language $language Objeto de linguagem para carregar os rótulos.
     * @return array Dados formatados contendo os campos de entrada.
     */
    public function getEntryFormatted(Language $language): array
    {
        return [
            'name' => 'Configurações gerais',
            'inputs' => [
                [
                    'name' => 'payment_efi_client_id_production',
                    'required' => true,
                    'label' => $language->get('entry_required_client_id_production'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_client_secret_production',
                    'required' => true,
                    'label' => $language->get('entry_required_client_secret_production'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_client_id_sandbox',
                    'required' => true,
                    'label' => $language->get('entry_required_client_id_sandbox'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_client_secret_sandbox',
                    'required' => true,
                    'label' => $language->get('entry_required_client_secret_sandbox'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_account_code',
                    'required' => true,
                    'label' => $language->get('entry_required_account_code'),
                    'type' => 'text',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_sort_order',
                    'required' => true,
                    'label' => $language->get('entry_required_sort_order'),
                    'type' => 'number',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_enviroment',
                    'required' => false,
                    'label' => $language->get('entry_required_enviroment'),
                    'type' => 'checkbox',
                    'value' => ''
                ],
                [
                    'name' => 'payment_efi_status',
                    'required' => false,
                    'label' => $language->get('entry_required_status'),
                    'type' => 'checkbox',
                    'value' => ''
                ]
            ]
        ];
    }
}
