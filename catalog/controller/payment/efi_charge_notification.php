<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Payment;

require_once DIR_OPENCART . 'extension/efi/catalog/vendor/autoload.php';


use Efi\Exception\EfiException;
use Efi\EfiPay;
use Opencart\Extension\Efi\Library\EfiConfigHelper;

use Opencart\System\Library\Log;

/**
 * Class EfiChargeNotification
 *
 * Controlador para receber e processar as notificações de boleto e cartão de crédito.
 */
class EfiChargeNotification extends \Opencart\System\Engine\Controller
{
    private Log $log;

    /**
     * Construtor
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->log = new Log('efi_charge_notification.log');
    }

    /**
     * Método para receber e processar os webhooks da API de cobranças.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting('payment_efi');
            $options = EfiConfigHelper::getEfiConfig($settings);
            // Definir cabeçalhos para JSON
            $this->response->addHeader('Content-Type: application/json');

            // Ler e decodificar o JSON recebido
            $notificationToken = $_POST['notification'];
            $params = [
                "token" => $notificationToken // Notification token example: "00000000-0000-0000-0000-000000000000"
            ];

            // Registrar o webhook recebido
            $this->log->write('TOKEN recebido: ' . json_encode($notificationToken));


            $api = new EfiPay($options);
            $response = $api->getNotification($params);
            $this->log->write('NOTIFICATION recebido: ' . json_encode($response));


            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 200 OK');
            $this->response->setOutput('Notificação  processada com sucesso');
        } catch (\Exception $e) {
            $this->log->write("Erro inesperado no processamento da notificação: " . $e->getMessage());
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            $this->response->setOutput('Erro interno no processamento da notificação');
        }
    }
}
