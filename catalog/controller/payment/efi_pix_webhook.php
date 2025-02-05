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
                    $status = $pix['status'] ?? null; // Status do pagamento

                    if (!$txid || !$status) {
                        $this->log->write('Erro: Webhook recebido sem txid ou status válido.');
                        continue;
                    }

                    // Remover zeros à esquerda do TXID para obter o número do pedido
                    $order_id = ltrim($txid, '0');

                    if (!is_numeric($order_id)) {
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

                    // Determinar novo status com base no status do Pix
                    $order_status_id = $this->getOrderStatusIdByPixStatus($status);

                    // Atualizar status do pedido no OpenCart
                    if ($order_status_id) {
                        $this->model_checkout_order->addHistory($order_info['order_id'], $order_status_id, 'Pagamento Pix atualizado via webhook');
                        $this->log->write("Pedido {$order_info['order_id']} atualizado para status $order_status_id.");
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
     * Mapeia os status do Pix para os status do OpenCart.
     *
     * @param string $pixStatus O status recebido do webhook Pix.
     * @return int|null Retorna o ID do status correspondente no OpenCart ou null se não mapeado.
     */
    private function getOrderStatusIdByPixStatus(string $pixStatus): ?int
    {
        try {
            switch ($pixStatus) {
                case 'CONCLUIDA':
                    return $this->config->get('payment_efi_order_status_paid'); // Status configurado para pedidos pagos
                case 'DEVOLVIDA':
                    return $this->config->get('payment_efi_order_status_refunded'); // Status configurado para reembolsos
                default:
                    return null; // Outros status não alteram o pedido
            }
        } catch (\Exception $e) {
            $this->log->write("Erro ao mapear status do Pix: " . $e->getMessage());
            return null;
        }
    }
}
