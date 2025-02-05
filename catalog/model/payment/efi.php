<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment;

class Efi extends \Opencart\System\Engine\Model
{
	/**
	 * Obtém os métodos de pagamento disponíveis
	 *
	 * @param array<string, mixed> $address Dados do endereço do cliente
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address = []): array
	{
		$this->load->language('extension/efi/payment/efi');

		$status = $this->config->get('payment_efi_pix_status');
		$option_data = [];
		$method_data = [];

		if ($status) {
			$option_data['efi_pix'] = [
				'code' => 'efi.pix',
				'name' => $this->language->get('text_title_pix')
			];

			$method_data = [
				'code'       => 'efi',
				'name'       => $this->language->get('text_title'),
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_efi_sort_order')
			];
		}

		return $method_data;
	}
}
