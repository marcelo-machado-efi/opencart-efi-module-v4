<?php

namespace Opencart\Admin\Controller\Extension\Efi\Payment;

use Opencart\System\Library\Log;

class Save extends \Opencart\System\Engine\Controller
{
    /**
     * Salva as configurações e registra os webhooks dos meios ativos
     */
    public function index(): void
    {
        $log = new Log('efi.log');

        try {
            $this->load->language('extension/efi/payment/efi');
            $json = [];

            // checa permissão no controller principal
            if (!$this->user->hasPermission('modify', 'extension/efi/payment/efi')) {
                $this->response->addHeader('HTTP/1.1 401 Unauthorized');
                $this->sendJson(['error' => $this->language->get('error_permission')]);
                return;
            }

            $this->load->model('setting/setting');

            // registra webhooks apenas dos métodos ativos
            $webhookErrors = $this->registerWebhooks($this->request->post);
            if (!empty($webhookErrors)) {
                // retorna todos os erros de webhook num único JSON
                $this->sendJson([
                    'error' => implode(' | ', $webhookErrors)
                ]);
                return;
            }

            // atualiza todas as configurações
            $update = $this->updateSettings();
            if (isset($update['error'])) {
                $this->sendJson(['error' => $update['error']]);
                return;
            }



            $this->sendJson(['success' => $this->language->get('text_success')]);
        } catch (\Throwable $e) {
            $log->write('Save.php exception: ' . $e->getMessage());
            $this->sendJson([
                'error' => 'Ocorreu um erro inesperado. Veja o log para mais detalhes.'
            ]);
        }
    }

    /**
     * Verifica cada meio de pagamento que utiliza webhook, se ativo, aciona seu model de  cadastramento de webhook
     */
    private function registerWebhooks(array $postData): array
    {
        $errors = [];

        // mapeamento de métodos => [campo_status, model, método_de_registro]
        $methods = [
            'pix'   => [
                'status_key'     => 'payment_efi_pix_status',
                'model_route'    => 'extension/efi/payment/efi_pix_webhook',
                'register_func'  => 'registerWebhook',
            ],
            // adicione outros métodos aqui seguindo o padrão
        ];

        foreach ($methods as $name => $cfg) {
            if (!empty($postData[$cfg['status_key']])) {
                // carrega o model específico
                $this->load->model($cfg['model_route']);
                // invoca o método de registro
                $modelVar = 'model_' . str_replace('/', '_', $cfg['model_route']);
                $response = $this->$modelVar->{$cfg['register_func']}($postData);

                if (isset($response['error'])) {
                    $errors[] = ucfirst($name) . ': ' . $response['error'];
                }
            }
        }

        return $errors;
    }

    /**
     * Atualiza as configurações do plugin
     */
    private function updateSettings(): array
    {
        $this->load->model('extension/efi/payment/efi_config');
        $this->load->model('extension/efi/payment/efi_validator');

        $config   = $this->model_extension_efi_payment_efi_config->getConfig($this->language);
        $validate = $this->model_extension_efi_payment_efi_validator->validateConfig($config, $this->request->post);

        if (isset($validate['error'])) {
            return $validate;
        }

        $current = $this->model_setting_setting->getSetting('payment_efi');

        // se Pix ativo, mantém o certificado antigo
        if (!empty($this->request->post['payment_efi_pix_status'])) {
            $this->request->post['payment_efi_pix_certificate'] =
                $current['payment_efi_pix_certificate']
                ?? $this->request->post['payment_efi_pix_certificate'];
        }

        $this->model_setting_setting->editSetting('payment_efi', $this->request->post);
        return ['success' => 'Configurações salvas com sucesso.'];
    }

    /**
     * Envia resposta JSON
     */
    private function sendJson(array $json): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
