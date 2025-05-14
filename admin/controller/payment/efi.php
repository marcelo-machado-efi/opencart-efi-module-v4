<?php



namespace Opencart\Admin\Controller\Extension\Efi\Payment;

use Opencart\System\Library\Log;

/**
 * Class Efi
 *
 * @package Opencart\Admin\Controller\Extension\Efi\Payment
 */
class Efi extends \Opencart\System\Engine\Controller
{
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void
	{
		$this->load->language('extension/efi/payment/efi');
		$this->load->model('extension/efi/payment/efi_config');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/efi/payment/efi', 'user_token=' . $this->session->data['user_token'])
		];

		$data['options'] = $this->model_extension_efi_payment_efi_config->getConfig($this->language);

		$data['save'] = $this->url->link('extension/efi/payment/efi.save', 'user_token=' . $this->session->data['user_token']);
		$data['upload'] = $this->url->link('extension/efi/payment/efi.upload', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['payment_efi_order_status_id'] = $this->config->get('payment_efi_order_status_id');

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['payment_efi_status'] = $this->config->get('payment_efi_status');
		$data['payment_efi_sort_order'] = $this->config->get('payment_efi_sort_order');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/efi/payment/efi', $data));
	}

	/**
	 * Realiza o upload do arquivo do certificado .p12.
	 *
	 * @return void
	 */
	public function upload(): void
	{
		$this->response->addHeader('Content-Type: application/json');
		$result = ['success' => null, 'error' => null];

		// Obter o arquivo enviado do $_FILES (independente do nome do campo)
		$file = reset($_FILES);

		// Verificar se o arquivo foi enviado corretamente
		if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
			$result['error'] = 'Certificado (.p12) não enviado ou houve erro no upload.';
			$this->response->setOutput(json_encode($result));
			return;
		}

		// Validar o tipo do arquivo
		$allowedTypes = ['application/x-pkcs12'];
		if (!in_array($file['type'], $allowedTypes)) {
			$result['error'] = 'O arquivo enviado não é um certificado .p12 válido.';
			$this->response->setOutput(json_encode($result));
			return;
		}

		// Processar o upload
		$uploadDir = DIR_STORAGE . 'efi_certificates/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true); // Criar o diretório se não existir
		}

		$filePath = $uploadDir . basename($file['name']);

		if (move_uploaded_file($file['tmp_name'], $filePath)) {
			$this->load->model('setting/setting');

			// Carregar as configurações atuais do grupo "payment_efi"
			$currentSettings = $this->model_setting_setting->getSetting('payment_efi');

			// Atualizar ou adicionar o novo caminho do certificado
			$currentSettings['payment_efi_pix_certificate'] = $filePath;

			// Salvar todas as configurações de volta no banco de dados
			$this->model_setting_setting->editSetting('payment_efi', $currentSettings);


			$result['success'] = 'Certificado carregado com sucesso! Recarregue a página para ver o novo caminho.';
		} else {
			$result['error'] = 'Erro ao salvar o certificado (.p12).';
		}

		// Retornar o resultado
		$this->response->setOutput(json_encode($result));
	}


	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void
	{
		try {
			$this->load->language('extension/efi/payment/efi');
			$log = new Log('efi.log');

			$json = [];

			// Verifica permissões do usuário
			if (!$this->user->hasPermission('modify', 'extension/efi/payment/efi')) {
				$json['error'] = $this->language->get('error_permission');
				$this->sendJsonResponse($json);
				return;
			}

			$this->load->model('setting/setting');
			$this->load->model('extension/efi/payment/efi_pix_webhook');



			// Atualiza as configurações do plugin
			$responseUpdateSettingConfig = $this->updateSettings();
			if (isset($responseUpdateSettingConfig['error'])) {
				$json['error'] = $responseUpdateSettingConfig['error'];
				$this->sendJsonResponse($json);
				return;
			} else {
				// Registra o webhook do Pix
				$responseRegisterPixWebhook = $this->registerPixWebhook();
				if (isset($responseRegisterPixWebhook['error'])) {
					$json['error'] = $responseRegisterPixWebhook['error'];
					$this->sendJsonResponse($json);
					return;
				}
			}




			// Caso todas as operações tenham sido bem-sucedidas
			$json['success'] = $this->language->get('text_success');
			$this->sendJsonResponse($json);
		} catch (\Throwable $th) {
			$log->write(json_encode($th->getMessage()));
		}
	}

	/**
	 * Envia a resposta JSON padronizada
	 *
	 * @param array $json
	 * @return void
	 */
	private function sendJsonResponse(array $json): void
	{
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Atualiza as configurações do plugin.
	 *
	 * @return array
	 */
	private function updateSettings(): array
	{
		try {
			$this->load->model('extension/efi/payment/efi_config');
			$this->load->model('extension/efi/payment/efi_validator');
			$configArray = $this->model_extension_efi_payment_efi_config->getConfig($this->language);
			$validation = $this->model_extension_efi_payment_efi_validator->validateConfig($configArray, $this->request->post);
			if (isset($validation['error'])) {
				return  $validation;
			}

			$log = new Log('efi.log');



			// Obtém configurações atuais
			$currentSettings = $this->model_setting_setting->getSetting('payment_efi');

			// Garante que o caminho do certificado Pix seja mantido caso já tenha sido salvo antes
			if ($this->request->post['payment_efi_pix_status']) {
				$filePath = $currentSettings['payment_efi_pix_certificate'] ?? $this->request->post['payment_efi_pix_certificate'];
				$this->request->post['payment_efi_pix_certificate'] = $filePath;
			}


			// Salva as configurações
			$this->model_setting_setting->editSetting('payment_efi', $this->request->post);
			return ["success" => "Configurações alteradas com sucesso."];
		} catch (\Throwable $th) {
			$log->write(json_encode(["error" => $th->getMessage()]));


			return ["error" => $th->getMessage()];
		}
	}

	/**
	 * Registra o Webhook do Pix.
	 *
	 * @return array
	 */
	private function registerPixWebhook(): array
	{
		return $this->model_extension_efi_payment_efi_pix_webhook->registerWebhook($this->request->post);
	}
}
