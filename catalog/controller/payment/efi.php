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
		try {
			$this->load->language('extension/efi/payment/efi');
			$this->load->model('extension/efi/payment/efi_pix_inputs');

			$data['language'] = $this->config->get('config_language');
			$data['payment_efi_status'] = $this->config->get('payment_efi_status');
			$data['inputs'] = $this->model_extension_efi_payment_efi_pix_inputs->getEntryFormatted($this->language);
			$data['img_logo_url'] =  $this->getImagePath('efi_logo.png');
			$data['btn_confirm_text_pix'] =  $this->language->get('btn_confirm_text_pix');

			// Adicionando CSS e JS
			$this->document->addStyle('extension/efi/catalog/view/stylesheet/fontawesome/css/all.min.css');
			$this->document->addStyle('extension/efi/catalog/view/stylesheet/common/color-brand.css');
			$this->document->addScript('extension/efi/catalog/view/javascript/payments/pixFormHandler.js');
			$this->document->addScript('extension/efi/catalog/view/javascript/libs/imask.min.js');
			$this->document->addScript('extension/efi/catalog/view/javascript/common/masks.js');
			$this->document->addScript('extension/efi/catalog/view/javascript/validation/commonValidations.js');


			// Obtendo os scripts e estilos adicionados pelo OpenCart
			$data['styles'] = $this->document->getStyles();
			$data['scripts'] = $this->document->getScripts();

			// Renderiza a view e envia a resposta
			return $this->load->view('extension/efi/payment/efi', $data);
		} catch (\Exception $e) {
			$this->logError($e->getMessage());
			return '';
		}
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
		return $efiImagePath . $imgName;
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

		try {
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
		} catch (\Exception $e) {
			$this->logError($e->getMessage());
			$json['error'] = 'Erro ao processar o pagamento. Consulte o log.';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Registra erros no log `efi.log`
	 *
	 * @param string $message Mensagem do erro
	 */
	private function logError(string $message): void
	{
		$log = new \Opencart\System\Library\Log('efi.log');
		$log->write($message);
	}
}
