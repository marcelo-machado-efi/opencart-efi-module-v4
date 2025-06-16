<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Payment;

use Opencart\System\Library\Log;

/**
 * Class EfiPixWebhook
 *
 * Controlador para receber e processar os Webhooks do Pix.
 */
class EfiPixWebhook extends \Opencart\System\Engine\Controller
{
    private Log $log;

    /**
     * Construtor
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->log = new Log('efi_pix_webhook.log');
    }

    /**
     * Método principal de entrada do Webhook Pix.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $this->response->addHeader('Content-Type: application/json');

            if (!$this->validateHmac()) {
                return;
            }

            $input = file_get_contents('php://input');
            $webhookData = json_decode($input, true);

            $this->log->write('Webhook recebido: ' . json_encode($webhookData));

            if (!$webhookData || (isset($webhookData['evento']) && $webhookData['evento'] === 'teste_webhook')) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 200 OK');
                $this->response->setOutput('Webhook processado com sucesso');
                return;
            }

            if (!isset($webhookData['pix'])) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 400 Bad Request');
                $this->response->setOutput('Webhook inválido');
                return;
            }

            foreach ($webhookData['pix'] as $pix) {
                try {
                    $txid = $pix['txid'] ?? null;

                    if (!$txid) {
                        $this->log->write('Erro: Webhook recebido sem txid.');
                        continue;
                    }

                    $order_id = ltrim(substr($txid, 2), '0');

                    if (!ctype_digit($order_id)) {
                        $this->log->write("Erro: TXID inválido. TXID recebido: $txid");
                        continue;
                    }

                    $this->load->model('checkout/order');
                    $order_info = $this->model_checkout_order->getOrder((int) $order_id);

                    if (!$order_info) {
                        $this->log->write("Erro: Pedido não encontrado para o número: $order_id");
                        continue;
                    }

                    $order_status_id = $this->config->get('payment_efi_order_status_paid');

                    if ($order_status_id) {
                        $this->model_checkout_order->addHistory(
                            $order_info['order_id'],
                            $order_status_id,
                            'Pagamento confirmado via Pix',
                            true
                        );
                    }
                } catch (\Exception $e) {
                    $this->log->write("Erro ao processar transação Pix: " . $e->getMessage());
                }
            }

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 200 OK');
            $this->response->setOutput('Webhook processado com sucesso');
        } catch (\Exception $e) {
            $this->log->write("Erro inesperado no processamento do webhook: " . $e->getMessage());
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            $this->response->setOutput('Erro interno no processamento do webhook');
        }
    }

    /**
     * Valida o HMAC da requisição com base na URL e no Client ID de produção.
     *
     * @return bool
     */
    private function validateHmac(): bool
    {
        $hmacRecebido = $this->request->get['hmac'] ?? '';
        if (!$hmacRecebido) {
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            $this->response->setOutput('HMAC não fornecido.');
            return false;
        }

        $clientId = $this->config->get('payment_efi_client_id_production');
        if (!$clientId) {
            $this->log->write("Erro: Client ID de produção não encontrado na configuração.");
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            $this->response->setOutput('Erro interno de configuração.');
            return false;
        }

        $httpsCatalog = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : '';
        if (!empty($httpsCatalog)) {
            $baseUrl = rtrim($httpsCatalog, '/');
        } else {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
            $host  = $_SERVER['HTTP_HOST'] ?? null;

            if ($proto === 'https' && $host) {
                $baseUrl = 'https://' . rtrim($host, '/');
            } else {
                $this->log->write("Erro: Não foi possível determinar uma URL segura para validar o HMAC.");
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
                $this->response->setOutput('Erro de validação HMAC.');
                return false;
            }
        }

        $webhookPath = '/index.php?route=extension/efi/payment/efi_pix_webhook';
        $webhookUrl  = $baseUrl . $webhookPath;

        $hmacCalculado = hash_hmac('sha256', $webhookUrl, $clientId);

        if (!hash_equals($hmacCalculado, $hmacRecebido)) {
            $this->log->write("Erro: HMAC inválido. Recebido: $hmacRecebido | Esperado: $hmacCalculado");
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');
            $this->response->setOutput('HMAC inválido.');
            return false;
        }

        return true;
    }
}
