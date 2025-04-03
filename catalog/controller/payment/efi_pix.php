<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Payment;

class EfiPix extends \Opencart\System\Engine\Controller
{
    public function confirm(): void
    {
        try {
            if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Requisição inválida.');
            }

            $customer_name = $this->request->post['payment_efi_customer_name'] ?? '';
            $customer_document = $this->request->post['payment_efi_customer_document'] ?? '';
            $order_id = $this->session->data['order_id'] ?? 0;

            $this->logError("Processando pedido ID: " . $order_id);

            if (empty($customer_name) || empty($customer_document)) {
                throw new \Exception('Nome e documento são obrigatórios.');
            }

            if (!$order_id) {
                throw new \Exception('ID do pedido não encontrado.');
            }

            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if (!$order_info) {
                throw new \Exception('Pedido não encontrado.');
            }

            $amount = (float) $order_info['total'];

            $this->load->model('extension/efi/payment/efi_pix');
            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting('payment_efi');

            $pix_data = $this->model_extension_efi_payment_efi_pix->generatePix(
                $customer_name,
                $customer_document,
                $amount,
                $order_id,
                $settings
            );

            if (!$pix_data['success']) {
                throw new \Exception($pix_data['error']);
            }

            $order_status_id = 2;
            $this->model_checkout_order->addHistory($order_id, $order_status_id, 'Pagamento Pix aguardando confirmação: ' . $pix_data['txid'], false);

            $this->cart->clear();
            unset($this->session->data['order_id']);

            $data = [
                'success' => true,
                'qrcode' => $pix_data['qrcode'],
                'pix_url' => $pix_data['pix_url'],
                'txid' => $pix_data['txid'],
                'expiration_time' => $pix_data['expiration_time']
            ];

            $view = $this->load->view('extension/efi/payment/efi_pix_modal', $data);


            $this->response->addHeader('Content-Type: text/html; charset=UTF-8');
            $this->response->setOutput($view);
        } catch (\Exception $e) {
            $this->logError("Erro no processamento: " . $e->getMessage());
            $data['error'] = $e->getMessage();
            $view = $this->load->view('extension/efi/payment/efi_error_ajax', $data);



            $this->response->addHeader('Content-Type: text/html; charset=UTF-8');
            $this->response->setOutput($view);
        }
    }

    private function logError(string $message): void
    {
        $log = new \Opencart\System\Library\Log('efi.log');
        $log->write($message);
    }
}
