<?php

namespace Opencart\Extension\Efi\Library;

class EfiShippingHelper
{
    /**
     * Retorna o array de shipping formatado a partir do $order_info.
     *
     * @param array $order_info Informações do pedido
     * @return array
     */
    public static function getShippingsFromOrder(array $order_info): array
    {
        $shipping_value = 0.00;

        if (isset($order_info['shipping_cost']) && $order_info['shipping_cost'] > 0) {
            $shipping_value = (float) $order_info['shipping_cost'];
        } elseif (isset($order_info['shipping']) && $order_info['shipping'] > 0) {
            $shipping_value = (float) $order_info['shipping'];
        } elseif (isset($order_info['shipping_method']['cost'])) {
            $shipping_value = (float) $order_info['shipping_method']['cost'];
        }

        return self::formatShippings($shipping_value);
    }

    /**
     * Formata o valor de frete no padrão esperado pela API (centavos).
     *
     * @param float $shipping Valor do frete em reais
     * @return array
     */
    public static function formatShippings(float $shipping): array
    {
        if ($shipping > 0) {
            return [
                [
                    'name'  => 'frete',
                    'value' => intval($shipping * 100)
                ]
            ];
        }

        return [];
    }
}
