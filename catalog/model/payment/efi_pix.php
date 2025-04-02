<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment;

require_once DIR_OPENCART . 'extension/efi/catalog/vendor/autoload.php';

use Opencart\Extension\Efi\Library\EfiConfigHelper;
use Efi\EfiPay;
use Exception;

class EfiPix extends \Opencart\System\Engine\Model
{
    public function generatePix(string $customer_name, string $customer_document, float $amount, string $order_id, array $settings): array
    {
        try {
            // Configuração da API do EfiPay
            $options = EfiConfigHelper::getEfiConfig($settings);
            $pix_key = $this->config->get('payment_efi_pix_key');
            $pix_expire_at = (int) $this->config->get('payment_efi_pix_expire_at') * 3600; // Converte para segundos
            $pix_discount = $this->config->get('payment_efi_pix_discount');

            // Aplica desconto
            $amount = $this->applyDiscount($amount, $pix_discount);

            // Gera `txid` com 35 caracteres
            $txid = $this->generateTxid($order_id);

            // Obtém os dados do pagador (CPF ou CNPJ)
            $devedor = $this->getDevedor($customer_name, $customer_document);
            if (!$devedor) {
                throw new Exception('Documento inválido.');
            }

            // Dados da cobrança Pix
            $body = [
                'calendario' => ['expiracao' => $pix_expire_at],
                'devedor' => $devedor,
                'valor' => ['original' => number_format($amount, 2, '.', '')],
                'chave' => $pix_key,
                'solicitacaoPagador' => "Pagamento do Pedido #{$order_id}"
            ];

            // Inicializa a API do EfiPay
            $efiPay = new EfiPay($options);
            $params = ['txid' => $txid];

            // Cria a cobrança Pix
            $pix_charge = $efiPay->pixCreateCharge($params, $body);

            if (!isset($pix_charge['loc']['id'])) {
                throw new Exception('Erro ao gerar cobrança Pix.');
            }

            // Obtém o QR Code do Pix
            $qrcode = $efiPay->pixGenerateQRCode(['id' => $pix_charge['loc']['id']]);

            // Obtém o tempo de expiração da cobrança (retornado pela API)
            $expiration_time = $pix_charge['calendario']['expiracao'] ?? $pix_expire_at;

            return [
                'success' => true,
                'qrcode' => $qrcode['qrcode'],
                'pix_url' => $qrcode['imagemQrcode'],
                'txid' => $txid,
                'expiration_time' => $expiration_time // Tempo de expiração em segundos
            ];
        } catch (Exception $e) {
            $this->logError("Erro na geração do Pix: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao gerar Pix. Consulte o log.'
            ];
        }
    }


    private function applyDiscount(float $amount, string $discount): float
    {
        $discount = trim($discount);

        if (strpos($discount, '%') !== false) {
            $discountPercent = (float) str_replace('%', '', $discount);
            $discountValue = ($amount * ($discountPercent / 100));
        } else {
            $discountValue = (float) $discount;
        }

        return max(0, $amount - $discountValue);
    }

    private function generateTxid(string $order_id): string
    {
        $order_id_length = strlen($order_id);
        $zero_fill = str_repeat('0', max(0, 33 - $order_id_length));
        return "OC" . substr($zero_fill . $order_id, -33);
    }

    private function getDevedor(string $customer_name, string $customer_document): ?array
    {
        $document = preg_replace('/\D/', '', $customer_document);

        if (strlen($document) === 11) {
            return ['nome' => $customer_name, 'cpf' => $document];
        } elseif (strlen($document) === 14) {
            return ['nome' => $customer_name, 'cnpj' => $document];
        }

        return null;
    }

    private function logError(string $message): void
    {
        $log = new \Opencart\System\Library\Log('efi.log');
        $log->write($message);
    }
}
