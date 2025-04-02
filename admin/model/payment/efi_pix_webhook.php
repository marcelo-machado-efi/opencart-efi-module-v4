<?php

namespace Opencart\Admin\Model\Extension\Efi\Payment;

require_once DIR_OPENCART . 'extension/efi/admin/vendor/autoload.php';

use Efi\EfiPay;
use Efi\Exception\EfiException;
use Exception;
use Opencart\System\Library\Log;
use Opencart\Extension\Efi\Library\EfiConfigHelper;

/**
 * Classe responsável por gerenciar o cadastro do webhook do Pix no OpenCart.
 */
class EfiPixWebhook extends \Opencart\System\Engine\Model
{
    private Log $log;

    /**
     * Construtor da classe EfiPixWebhook.
     *
     * @param \Opencart\System\Engine\Registry $registry Registro do OpenCart.
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->log = new Log('efi.log');
    }
    /**
     * Gera a URL do webhook para ser usada na configuração do EfiPay.
     *
     * @return string URL do webhook.
     */
    public function getWebhookUrl(): string
    {
        if (!defined('HTTPS_CATALOG')) {
            define('HTTPS_CATALOG', '');
        }
        if (!empty(HTTPS_CATALOG)) {
            $baseUrl = HTTPS_CATALOG;
        } else {
            throw new Exception('Detectamos que sua loja não possui configuração TLS ativa. Para garantir segurança e compatibilidade, o TLS é obrigatório para utilizar as APIs do Efí.');
        }

        return rtrim($baseUrl, '/') . '/index.php?route=extension/efi/payment/efi_pix_webhook';
    }


    /**
     * Cadastra o webhook do Pix.
     *
     * @param array $data Configurações do OpenCart para o Pix.
     * @return array Resultado do cadastro do webhook.
     */
    public function registerWebhook(array $data): array
    {


        try {
            $config = EfiConfigHelper::getEfiConfig($data);
            $config["headers"] = [
                "x-skip-mtls-checking" => ($data["payment_efi_pix_mtls"] != 1)
            ];
            $api = new EfiPay($config);

            $params = [
                "chave" => $data["payment_efi_pix_key"]
            ];
            $body = [
                "webhookUrl" => $this->getWebhookUrl()
            ];

            $response = $api->pixConfigWebhook($params, $body);

            // Caso tenha sucesso, registre no log
            $this->log->write('Webhook cadastrado com sucesso: ' . json_encode($response));

            return ['success' => 'Webhook cadastrado com sucesso.'];
        } catch (EfiException $e) {
            // Registra detalhes avançados no log
            $this->log->write('EfiException Code: ' . $e->code);
            $this->log->write('EfiException Error: ' . $e->error);
            $this->log->write('EfiException Description: ' . $e->errorDescription);

            if (isset($config["responseHeaders"]) && $config["responseHeaders"] && isset($e->headers)) {
                $this->log->write('Response Headers: ' . json_encode($e->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            // Retorna apenas a mensagem de erro para o usuário
            return ['error' => 'Erro ao cadastrar webhook: ' . $e->errorDescription];
        } catch (\Exception $e) {
            // Registrar o erro no log
            $this->log->write('Exception: ' . $e->getMessage());

            // Retorna apenas a mensagem simples para o usuário
            return ['error' => 'Erro ao cadastrar webhook: ' . $e->getMessage()];
        }
    }
}
