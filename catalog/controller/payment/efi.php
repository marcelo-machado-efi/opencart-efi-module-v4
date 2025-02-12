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
		$this->load->model('extension/efi/payment/efi_pix_inputs');
		$this->document->addScript('extension/efi/catalog/view/javascript/libs/imask.min.js');
		$this->document->addScript('extension/efi/catalog/view/javascript/common/masks.js');
		$this->document->addScript('extension/efi/catalog/view/javascript/validation/commonValidations.js');
		$this->document->addStyle('extension/efi/catalog/view/stylesheet/fontawesome/css/all.min.css');
		$this->document->addStyle('extension/efi/catalog/view/stylesheet/common/color-brand.css');




		$data['language'] = $this->config->get('config_language');
		$data['payment_efi_status'] = $this->config->get('payment_efi_status');
		$data['inputs'] = $this->model_extension_efi_payment_efi_pix_inputs->getEntryFormatted($this->language);
		$data['img_logo_url'] =  $this->getImagePath('efi_logo.png');
		$data['btn_confirm_text_pix'] =  $this->language->get('btn_confirm_text_pix');



		return $this->load->view('extension/efi/payment/efi', $data);
	}

	/**
	 * Retorna o caminho completo da imagem do plugin
	 *
	 * @param string $imgName Nome do arquivo da imagem
	 * @return string Caminho completo da imagem
	 */
	private function getImagePath(string $imgName): string
	{
		$efiImagePath = 'extension/efi/catalog/view/image/';
		return $efiImagePath  . $imgName;
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

	public function logo(): void
	{
		$image_path = DIR_EXTENSION . 'efi/catalog/view/image/efi_logo.png';

		if (is_file($image_path)) {
			$mime_type = mime_content_type($image_path); // Detecta automaticamente o tipo da imagem
			header('Content-Type: ' . $mime_type);
			readfile($image_path);
			exit;
		} else {
			http_response_code(404);
			echo 'Imagem não encontrada';
		}
	}
}
