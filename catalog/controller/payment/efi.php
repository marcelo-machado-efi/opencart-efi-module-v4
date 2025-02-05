<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Payment;

/**
 * Classe responsável pelo processamento do pagamento via EfiPay.
 */
class Efi extends \Opencart\System\Engine\Controller
{
	/**
	 * Exibe a interface de pagamento no checkout.
	 *
	 * @return string
	 */
	public function index(): string
	{
		$this->load->language('extension/efi/payment/efi');

		$data['language'] = $this->config->get('config_language');
		$data['payment_efi_status'] = $this->config->get('payment_efi_status');

		return $this->load->view('extension/efi/payment/efi', $data);
	}

	/**
	 * Confirma o pagamento e atualiza o status do pedido.
	 *
	 * @return void
	 */
	public function confirm(): void
	{
		$this->load->language('extension/efi/payment/efi');

		$json = [];

		if (isset($this->session->data['order_id'])) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if (!$order_info) {
				$json['redirect'] = $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true);
				unset($this->session->data['order_id']);
			}
		} else {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method']['code'] != 'efi.efi') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$this->load->model('checkout/order');

			// Define o status do pedido conforme a configuração do plugin
			$order_status_id = $this->config->get('payment_efi_order_status_id');
			$this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id);

			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
