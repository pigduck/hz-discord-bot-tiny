<?php

class Hz_Discord_Bot_Details
{
    /**
     * 獲取訂單詳細資訊
     *
     * @return array
     */
    public static function get_order_details()
    {
        return apply_filters('hz_discord_bot_order_details', [
            'order_id' => esc_attr__('Order ID', 'hz-discord-bot-tiny'),
            'order_total' => esc_attr__('Order Total', 'hz-discord-bot-tiny'),
            'order_status' => esc_attr__('Order Status', 'hz-discord-bot-tiny'),
            'order_date' => esc_attr__('Order Date', 'hz-discord-bot-tiny'),
            'order_billing_first_name' => esc_attr__('Billing First Name', 'hz-discord-bot-tiny'),
            'order_billing_last_name' => esc_attr__('Billing Last Name', 'hz-discord-bot-tiny'),
            'order_billing_email' => esc_attr__('Billing Email', 'hz-discord-bot-tiny'),
            'order_billing_phone' => esc_attr__('Billing Phone', 'hz-discord-bot-tiny'),
            'order_billing_address_1' => esc_attr__('Billing Address 1', 'hz-discord-bot-tiny'),
            'order_billing_address_2' => esc_attr__('Billing Address 2', 'hz-discord-bot-tiny'),
            'order_billing_city' => esc_attr__('Billing City', 'hz-discord-bot-tiny'),
            'order_billing_state' => esc_attr__('Billing State', 'hz-discord-bot-tiny'),
            'order_billing_postcode' => esc_attr__('Billing Postcode', 'hz-discord-bot-tiny'),
            'order_billing_country' => esc_attr__('Billing Country', 'hz-discord-bot-tiny'),
            'order_shipping_first_name' => esc_attr__('Shipping First Name', 'hz-discord-bot-tiny'),
            'order_shipping_last_name' => esc_attr__('Shipping Last Name', 'hz-discord-bot-tiny'),
            'order_shipping_address_1' => esc_attr__('Shipping Address 1', 'hz-discord-bot-tiny'),
            'order_shipping_address_2' => esc_attr__('Shipping Address 2', 'hz-discord-bot-tiny'),
            'order_shipping_city' => esc_attr__('Shipping City', 'hz-discord-bot-tiny'),
            'order_shipping_state' => esc_attr__('Shipping State', 'hz-discord-bot-tiny'),
            'order_shipping_postcode' => esc_attr__('Shipping Postcode', 'hz-discord-bot-tiny'),
            'order_shipping_country' => esc_attr__('Shipping Country', 'hz-discord-bot-tiny'),
            'order_payment_method' => esc_attr__('Payment Method', 'hz-discord-bot-tiny'),
            'order_note' => esc_attr__('Order Note', 'hz-discord-bot-tiny'),
        ]);
    }

    /**
     * 獲取用戶詳細資訊
     *
     * @return array
     */
    public static function get_user_details()
    {
        return apply_filters('hz_discord_bot_user_details', [
            'user_id' => esc_attr__('User ID', 'hz-discord-bot-tiny'),
            'user_login' => esc_attr__('User Login', 'hz-discord-bot-tiny'),
            'user_email' => esc_attr__('User Email', 'hz-discord-bot-tiny'),
        ]);
    }

    /**
     * 獲取所有詳細資訊
     *
     * @return array
     */
    public static function get_all_details()
    {
        return [
            'order' => self::get_order_details(),
            'user' => self::get_user_details()
        ];
    }

    /**
     *
     * @return array
     */
    public static function get_webhooks_type()
    {
        return [
            'new' => esc_attr__('New Order Notification', 'hz-discord-bot-tiny'),
            'change' => esc_attr__('Order Status Change Notification', 'hz-discord-bot-tiny'),
            'register' => esc_attr__('New User Registration Notification', 'hz-discord-bot-tiny'),
        ];
    }
}