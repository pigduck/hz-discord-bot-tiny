<?php


class Hz_Discord_Bot_Global
{

    /**
     * 獲取訂單詳細資訊
     *
     * @return array
     */
    public static function get_order_details($order): array
    {
        return apply_filters('hz_discord_bot_order_replacements', [
            '{{order_id}}' => $order->get_id(),
            '{{order_total}}' => $order->get_total(),
            '{{order_status}}' => $order->get_status(),
            '{{order_date}}' => $order->get_date_created()->format('Y-m-d H:i:s'),
            '{{order_billing_first_name}}' => $order->get_billing_first_name(),
            '{{order_billing_last_name}}' => $order->get_billing_last_name(),
            '{{order_billing_email}}' => $order->get_billing_email(),
            '{{order_billing_phone}}' => $order->get_billing_phone(),
            '{{order_billing_address_1}}' => $order->get_billing_address_1(),
            '{{order_billing_address_2}}' => $order->get_billing_address_2(),
            '{{order_billing_city}}' => $order->get_billing_city(),
            '{{order_billing_state}}' => $order->get_billing_state(),
            '{{order_billing_postcode}}' => $order->get_billing_postcode(),
            '{{order_billing_country}}' => $order->get_billing_country(),
            '{{order_shipping_first_name}}' => $order->get_shipping_first_name(),
            '{{order_shipping_last_name}}' => $order->get_shipping_last_name(),
            '{{order_shipping_address_1}}' => $order->get_shipping_address_1(),
            '{{order_shipping_address_2}}' => $order->get_shipping_address_2(),
            '{{order_shipping_city}}' => $order->get_shipping_city(),
            '{{order_shipping_state}}' => $order->get_shipping_state(),
            '{{order_shipping_postcode}}' => $order->get_shipping_postcode(),
            '{{order_shipping_country}}' => $order->get_shipping_country(),
            '{{order_payment_method}}' => $order->get_payment_method(),
            '{{order_note}}' => $order->get_customer_note(),
        ], $order);
    }

    public static function debugMode($error_message): bool
    {
        if (!empty(get_option('hz_discord_bot_setting_debug', ''))) {
            $wc_logger = wc_get_logger();
            $wc_logger->debug($error_message, ['source' => 'hz-discord-bot-tiny']);
            return true;
        }
        return false;
    }

    public static function get_log_file_path(): string
    {
        return WC_Log_Handler_File::get_log_file_path('hz-discord-bot-tiny');
    }

    public static function get_log_file_url(): string
    {
        return admin_url('admin.php?page=wc-status&tab=logs');
    }
}