<?php

namespace Opencart\Catalog\Controller\Extension\Efi\Event;

use Efi\EfiPay;
use Opencart\Extension\Efi\Library\EfiConfigHelper;
use Opencart\System\Library\Log;

class EfiCancelListener extends \Opencart\System\Engine\Controller
{
    public function onOrderStatusUpdate(string &$route, array &$args, mixed &$output): void
    {
        $this->log("Disparado evento onOrderStatusUpdate. Route: {$route}, Args: " . json_encode($args));

        $order_id = (int) ($args[0] ?? 0);
        $new_status_id = (int) ($args[1] ?? 0);
        $cancel_status_id = 7;

        if ($new_status_id !== $cancel_status_id) {
            $this->log("Status diferente de cancelamento ({$new_status_id} != {$cancel_status_id}). Ignorando.");
            return;
        }

        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('payment_efi');
        $this->log("Configurações carregadas: " . json_encode($settings));

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $this->log("Dados do pedido #{$order_id}: " . json_encode($order_info));

        if (!$order_info) {
            $this->log("Pedido #{$order_id} não encontrado.");
            return;
        }

        if ($order_info['payment_code'] !== 'efi_billet') {
            $this->log("Pedido #{$order_id} não foi pago com efi_billet ({$order_info['payment_code']}).");
            return;
        }

        $charge_id = $this->getChargeIdFromEfi($order_id, $settings);

        if (!$charge_id) {
            $this->log("Charge ID não encontrado para o pedido #{$order_id}");
            return;
        }

        try {
            $this->cancelarCobrancaEfi($charge_id, $settings);
        } catch (\Exception $e) {
            $this->log("Erro ao cancelar cobrança (pedido #{$order_id}): " . $e->getMessage());
        }
    }

    private function getChargeIdFromEfi(int $order_id, array $settings): ?int
    {
        $this->log("Buscando charge_id para o pedido #{$order_id}");

        $efiPay = new EfiPay(EfiConfigHelper::getEfiConfig($settings));

        $end = new \DateTimeImmutable();
        $begin = $end->sub(new \DateInterval('P1Y'));

        $params = [
            "charge_type" => "billet",
            "begin_date" => $begin->format('Y-m-d'),
            "end_date" => $end->format('Y-m-d'),
            "custom_id" => (string) $order_id,
        ];

        $this->log("Parâmetros da consulta: " . json_encode($params));

        try {
            $response = $efiPay->listCharges($params);
            $this->log("Resposta da Efí: " . json_encode($response));

            if (!empty($response['data'][0]['charge_id'])) {
                return (int) $response['data'][0]['charge_id'];
            }

            return null;
        } catch (\Exception $e) {
            $this->log("Erro ao buscar charge_id para o pedido #{$order_id}: " . $e->getMessage());
            return null;
        }
    }

    private function cancelarCobrancaEfi(int $charge_id, array $settings): void
    {
        $this->log("Enviando requisição para cancelar charge_id: {$charge_id}");

        $efiPay = new EfiPay(EfiConfigHelper::getEfiConfig($settings));
        $efiPay->cancelCharge(['id' => $charge_id]);

        $this->log("Cobrança cancelada com sucesso (charge_id: {$charge_id})");
    }

    private function log(string $message): void
    {
        $log = new Log('efi_cancel.log');
        $log->write('[' . date('Y-m-d H:i:s') . '] ' . $message);
    }
}
