<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment\Card;

require_once DIR_EXTENSION . 'efi/library/vendor/autoload.php';


use Opencart\Extension\Efi\Library\EfiConfigHelper;
use Efi\EfiPay;
use Exception;

class EfiCard extends \Opencart\System\Engine\Model
{
    /**
     * Gera uma cobrança via cartão de crédito utilizando a API do EfiPay.
     *
     * @param array $customer Dados do cliente (nome, documento, email, telefone).
     * @param array $card Dados do cartão (installments, token).
     * @param float $amount Valor da cobrança.
     * @param string $order_id ID do pedido.
     * @param array $settings Configurações da conta EfiPay.
     * @return array Resultado da operação.
     */
    public function generateCardCharge(array $customer, array $card, float $amount, string $order_id, array $settings): array
    {
        try {
            // Configuração da API do EfiPay
            $options = EfiConfigHelper::getEfiConfig($settings);

            // Dados do cliente
            $customer_data = $this->getFormattedCustomer($customer);


            // Corpo da requisição
            $body = [
                'items' => [
                    [
                        'name' => "Pedido #{$order_id}",
                        'value' => intval($amount * 100),
                        'amount' => 1
                    ]
                ],
                'payment' => [
                    'credit_card' => [
                        'installments' => (int) $card['installments'],
                        'payment_token' => $card['token'],
                        'customer' => $customer_data
                    ]
                ]
            ];

            // Inicializa a API do EfiPay
            $efiPay = new EfiPay($options);

            // Cria a cobrança
            $charge = $efiPay->createOneStepCharge([], $body);
            $chargeData = $charge['data'];
            if ($chargeData['status'] == 'approved') {
                $response = [
                    'success' => true,
                    'charge_id' => $chargeData['charge_id'] ?? null,
                    'status' => $chargeData['status'] ?? null,
                    'message' => 'Pagamento confirmado via cartão de crédito.'
                ];
            } else {
                $response = [
                    'success' => true,
                    'charge_id' => $chargeData['charge_id'] ?? null,
                    'status' => $chargeData['status'] ?? null,
                    'message' => 'Não foi possível processar o pagamento. Verifique os dados do cartão e tente novamente.'
                ];
            }
            $this->logError(json_encode($chargeData));
            return $response;
        } catch (Exception $e) {
            $this->logError("Erro na geração da cobrança via cartão: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao gerar cobrança via cartão.'
            ];
        }
    }

    /**
     * Formata os dados do cliente para pessoa física ou jurídica.
     *
     * @param array $customer
     * @return array
     */
    private function getFormattedCustomer(array $customer): array
    {
        $document = preg_replace('/\D/', '', $customer['document']);

        $base = [
            'phone_number' => preg_replace('/\D/', '', $customer['phone']),
            'email' => $customer['email'],
        ];

        if (strlen($document) === 11) {

            $base['name'] = $customer['name'];
            $base['cpf'] = $document;
        } elseif (strlen($document) === 14) {

            $base['juridical_person'] = [
                'corporate_name' => $customer['name'],
                'cnpj ' => $document
            ];
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
     * Gera a URL de notificação para ser usada na configuração de cobranca.
     *
     * @return string URL de notificação.
     */
    public function getNotificationUrl(): string
    {
        if (!defined('HTTPS_CATALOG')) {
            $baseUrl = HTTPS_CATALOG;
        } else {
            $baseUrl = HTTP_CATALOG;
        }

        return rtrim($baseUrl, '/') . '/index.php?route=extension/efi/payment/efi_charge_notification';
    }
}
