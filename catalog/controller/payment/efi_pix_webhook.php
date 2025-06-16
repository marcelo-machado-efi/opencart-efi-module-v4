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
     * Método para receber e processar os webhooks do Pix.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            // Definir cabeçalhos para JSON
            $this->response->addHeader('Content-Type: application/json');

            // Ler e decodificar o JSON recebido
            $input = file_get_contents('php://input');
            $webhookData = json_decode($input, true);

            // Registrar o webhook recebido
            $this->log->write('Webhook recebido: ' . json_encode($webhookData));

            if (!$webhookData || (isset($webhookData['evento']) &&  $webhookData['evento'] == 'teste_webhook')) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 200 OK');
                $this->response->setOutput('Webhook processado com sucesso');
                return;
            }

            // Validar se o webhook contém dados esperados
            if (!$webhookData || !isset($webhookData['pix'])) {
                $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 400 Bad Request');
                $this->response->setOutput('Webhook inválido');
                return;
            }

            // Processar cada transação Pix recebida
            foreach ($webhookData['pix'] as $pix) {
                try {
                    $txid = $pix['txid'] ?? null; // ID da transação Pix

                    if (!$txid) {
                        $this->log->write('Erro: Webhook recebido sem txid.');
                        continue;
                    }

                    // Extrair o número do pedido a partir do TXID (removendo 'OC' e zeros à esquerda)
                    $order_id = ltrim(substr($txid, 2), '0');

                    if (!ctype_digit($order_id)) {
                        $this->log->write("Erro: TXID inválido ou não contém um número de pedido válido. TXID recebido: $txid");
                        continue;
                    }

                    // Buscar o pedido pelo número extraído do TXID
                    $this->load->model('checkout/order');
                    $order_info = $this->model_checkout_order->getOrder((int) $order_id);

                    if (!$order_info) {
                        $this->log->write("Erro: Pedido não encontrado para o número: $order_id");
                        continue;
                    }


                    $order_status_id = $this->config->get('payment_efi_order_status_paid');

                    // Atualizar status do pedido no OpenCart
                    if ($order_status_id) {
                        $this->model_checkout_order->addHistory($order_info['order_id'], $order_status_id, 'Pagamento confirmado via Pix', true);
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
}
