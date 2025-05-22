<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment\Billet;

require_once DIR_OPENCART . 'extension/efi/catalog/vendor/autoload.php';

use Opencart\Extension\Efi\Library\EfiConfigHelper;
use Efi\EfiPay;
use Exception;

class EfiBillet extends \Opencart\System\Engine\Model
{
    /**
     * Gera uma cobrança via boleto utilizando a API do EfiPay.
     *
     * @param array $customer Dados do cliente (nome, documento, email).
     * @param float $amount Valor da cobrança.
     * @param string $order_id ID do pedido.
     * @param array $settings Configurações da conta EfiPay.
     * @return array Resultado da operação.
     */
    public function generateBilletCharge(array $customer, float $amount, string $order_id, array $settings): array
    {
        try {
            $options = EfiConfigHelper::getEfiConfig($settings);
            $customer_data = $this->getFormattedCustomer($customer);
            $expireAt = $this->getExpireAt();
            $metadata = $this->getMetadata($order_id);
            $configurations = $this->getConfigurations();
            $message = $this->getMessage();

            // Prepara os dados de pagamento dinamicamente
            $paymentData = [
                'customer' => $customer_data,
                'expire_at' => $expireAt
            ];

            if (!empty($configurations)) {
                $paymentData['configurations'] = $configurations;
            }

            if (!empty($message)) {
                $paymentData['message'] = $message;
            }

            // Corpo da requisição
            $body = [
                'items' => [
                    [
                        'name' => "Pedido #{$order_id}",
                        'value' => intval($amount * 100),
                        'amount' => 1
                    ]
                ],
                'metadata' => $metadata,
                'payment' => [
                    'banking_billet' => $paymentData
                ]
            ];

            $efiPay = new EfiPay($options);
            $charge = $efiPay->createOneStepCharge([], $body);
            $chargeData = $charge['data'];

            $response = [
                'success' => true,
                'charge_id' => $chargeData['charge_id'] ?? null,
                'status' => $chargeData['status'] ?? null,
                'link' => $chargeData['pdf']['charge'] ?? null,
                'message' => 'Pagamento aguardando confirmação.'
            ];

            $this->logError(json_encode($chargeData));
            return $response;
        } catch (Exception $e) {
            $this->logError("Erro na geração da cobrança via boleto: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao gerar cobrança via boleto.'
            ];
        }
    }

    /**
     * Formata os dados do cliente para pessoa física ou jurídica.
     *
     * @param array $customer Dados do cliente.
     * @return array Dados formatados para envio à API.
     */
    private function getFormattedCustomer(array $customer): array
    {
        $document = preg_replace('/\D/', '', $customer['document']);

        $base = [];

        if (strlen($document) === 11) {
            $base['name'] = $customer['name'];
            $base['cpf'] = $document;
        } elseif (strlen($document) === 14) {
            $base['juridical_person'] = [
                'corporate_name' => $customer['name'],
                'cnpj' => $document
            ];
        }

        if ($this->config->get('payment_efi_billet_email')) {
            $base['email'] = $customer['email'];
        }

        return $base;
    }

    /**
     * Registra erros no log do OpenCart.
     *
     * @param string $message Mensagem de erro.
     * @return void
     */
    private function logError(string $message): void
    {
        $log = new \Opencart\System\Library\Log('efi.log');
        $log->write($message);
    }

    /**
     * Gera a URL de notificação para ser usada na configuração de cobrança.
     *
     * @return string URL de notificação.
     */
    private function getNotificationUrl(): string
    {
        $baseUrl = '';

        if (defined('HTTPS_CATALOG') && !empty(HTTPS_CATALOG)) {
            $baseUrl = HTTPS_CATALOG;
        } elseif (defined('HTTP_CATALOG') && !empty(HTTP_CATALOG)) {
            $baseUrl = HTTP_CATALOG;
        } else {
            $baseUrl = $this->config->get('config_url') ?: '';
        }

        // return rtrim($baseUrl, '/') . '/index.php?route=extension/efi/payment/efi_charge_notification';
        return 'https://webhook.site/de4a223c-c8c1-4088-8128-6b7fc74b583c';
    }

    /**
     * Calcula a data de vencimento com base no número de dias configurado.
     *
     * @return string Data de vencimento formatada no padrão 'Y-m-d'.
     */
    private function getExpireAt(): string
    {
        $expireAtDays = (int) $this->config->get('payment_efi_billet_expire_at') ?? 0;

        $dueDate = new \DateTime(); // data atual
        $dueDate->modify("+{$expireAtDays} days");

        return $dueDate->format('Y-m-d');
    }




    /**
     * Retorna os metadados da cobrança.
     *
     * @param string $order_id ID do pedido.
     * @return array Metadados da cobrança.
     */
    private function getMetadata(string $order_id): array
    {
        $notificationUrl = $this->getNotificationUrl();

        return [
            'custom_id' => $order_id,
            'notification_url' => $notificationUrl
        ];
    }

    /**
     * Retorna a mensagem personalizada configurada para boletos.
     *
     * @return string Mensagem do boleto.
     */
    private function getMessage(): string
    {
        return $this->config->get('payment_efi_billet_message') ?? '';
    }

    /**
     * Retorna as configurações de juros e multa, se definidas.
     *
     * @return array Configurações de cobrança.
     */
    private function getConfigurations(): array
    {
        $fine = $this->config->get('payment_efi_billet_fine');
        $interest = $this->config->get('payment_efi_billet_interest');
        $configurations = [];

        if ($fine !== '' && $fine !== null) {
            $configurations['fine'] = (int) $fine;
        }

        if ($interest !== '' && $interest !== null) {
            $configurations['interest'] = (int) $interest;
        }

        return $configurations;
    }
}
