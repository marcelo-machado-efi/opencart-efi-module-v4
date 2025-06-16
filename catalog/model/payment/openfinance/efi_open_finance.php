<?php

namespace Opencart\Catalog\Model\Extension\Efi\Payment\OpenFinance;

require_once DIR_EXTENSION . 'efi/library/vendor/autoload.php';


use Opencart\Extension\Efi\Library\EfiConfigHelper;
use Efi\EfiPay;
use Exception;

class EfiOpenFinance extends \Opencart\System\Engine\Model
{
    public function generatePayment(string $customer_document_cpf, string $customer_document_cnpj, string $customer_bank, float $amount, string $order_id, array $settings): array
    {
        try {
            // Configurações Efí
            $options = EfiConfigHelper::getEfiConfig($settings);
            $options["headers"] = [
                "x-idempotency-key" => bin2hex(random_bytes(18)) // ID único
            ];

            // Participante pagador
            $pagador = [
                "idParticipante" => $customer_bank
            ];

            // Adiciona CPF (sempre enviado)
            $cpf = preg_replace('/\D/', '', $customer_document_cpf);
            if (strlen($cpf) === 11) {
                $pagador["cpf"] = $cpf;
            } else {
                throw new Exception("CPF inválido.");
            }

            // Adiciona CNPJ (se fornecido e válido)
            $cnpj = preg_replace('/\D/', '', $customer_document_cnpj);
            if (!empty($cnpj)) {
                if (strlen($cnpj) === 14) {
                    $pagador["cnpj"] = $cnpj;
                } else {
                    throw new Exception("CNPJ inválido.");
                }
            }

            // Dados do favorecido
            $favorecido = [
                "chave" => $settings['payment_efi_open_finance_key']
            ];

            // Corpo da requisição
            $body = [
                "pagador" => $pagador,
                "favorecido" => $favorecido,
                "pagamento" => [
                    "valor" => number_format($amount, 2, '.', ''),
                    "infoPagador" => "Pagamento do Pedido #{$order_id}",
                    "idProprio" => "OC-Order-{$order_id}"
                ]
            ];

            // Envia para a API
            $efiPay = new EfiPay($options);
            $response = $efiPay->ofStartPixPayment([], $body);

            if (!isset($response['redirectURI']) || !isset($response['identificadorPagamento'])) {
                throw new Exception('Resposta inesperada da API Efí.');
            }

            return [
                'success' => true,
                'redirect_url' => $response['redirectURI'],
                'identificadorPagamento' => $response['identificadorPagamento']
            ];
        } catch (Exception $e) {
            $this->logError("Erro na geração de pagamento Open Finance: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao iniciar pagamento via Open Finance. Consulte o log.'
            ];
        }
    }

    public function getDetailPayment(string $identificadorPagamento, array $settings): array
    {
        try {
            $options = EfiConfigHelper::getEfiConfig($settings);
            $efiPay = new EfiPay($options);
            $response = $efiPay->ofListPixPayment(['identificadorPagamento' => $identificadorPagamento]);

            $pagamento = $response['pagamentos'][0] ?? null;

            return [
                'status' => $pagamento['status'] ?? 'desconhecido'
            ];
        } catch (Exception $e) {
            $this->logError("Erro ao consultar status do pagamento Open Finance: " . $e->getMessage());
            return [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
    }

    private function logError(string $message): void
    {
        $log = new \Opencart\System\Library\Log('efi_open_finance.log');
        $log->write($message);
    }
}
