<?php

namespace Opencart\Admin\Model\Extension\Efi\Payment;

use \Opencart\System\Library\Language;

/**
 * Classe responsável por gerenciar configurações específicas do plugin.
 */
class EfiConfig extends \Opencart\System\Engine\Model
{
    /**
     * Retorna todas as configurações formatadas com os valores salvos no banco de dados.
     *
     * @param Language $language Instância do objeto Language.
     * @return array Todas as configurações formatadas.
     */
    public function getConfig(Language $language): array
    {
        // Carregar e formatar configurações obrigatórias
        $this->load->model('extension/efi/payment/efi_config_required');
        $requiredConfig = $this->model_extension_efi_payment_efi_config_required->getEntryFormatted($language);
        $requiredConfig = $this->populateConfigValues($requiredConfig);

        // Carregar e formatar configurações Pix
        $this->load->model('extension/efi/payment/efi_config_pix');
        $pixConfig = $this->model_extension_efi_payment_efi_config_pix->getEntryFormatted($language);
        $pixConfig = $this->populateConfigValues($pixConfig);

        // Retornar todas as configurações agrupadas
        return [
            'required' => $requiredConfig,
            'pix' => $pixConfig
        ];
    }

    /**
     * Popula as configurações com os valores salvos no banco de dados.
     *
     * @param array $config Configurações formatadas com inputs.
     * @return array Configurações com valores preenchidos.
     */
    private function populateConfigValues(array $config): array
    {
        foreach ($config['inputs'] as &$input) {
            $savedValue = $this->config->get($input['name']);
            if ($savedValue !== null) {
                $input['value'] = $savedValue;
            }
        }
        return $config;
    }
}
