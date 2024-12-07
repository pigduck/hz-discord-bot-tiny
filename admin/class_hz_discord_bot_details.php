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
            'order_id' => __('Order ID', 'hz-discord-bot-tiny'),
            'order_total' => __('Order Total', 'hz-discord-bot-tiny'),
            'order_status' => __('Order Status', 'hz-discord-bot-tiny'),
            'order_date' => __('Order Date', 'hz-discord-bot-tiny'),
            'order_billing_first_name' => __('Billing First Name', 'hz-discord-bot-tiny'),
            'order_billing_last_name' => __('Billing Last Name', 'hz-discord-bot-tiny'),
            'order_billing_email' => __('Billing Email', 'hz-discord-bot-tiny'),
            'order_billing_phone' => __('Billing Phone', 'hz-discord-bot-tiny'),
            'order_billing_address_1' => __('Billing Address 1', 'hz-discord-bot-tiny'),
            'order_billing_address_2' => __('Billing Address 2', 'hz-discord-bot-tiny'),
            'order_billing_city' => __('Billing City', 'hz-discord-bot-tiny'),
            'order_billing_state' => __('Billing State', 'hz-discord-bot-tiny'),
            'order_billing_postcode' => __('Billing Postcode', 'hz-discord-bot-tiny'),
            'order_billing_country' => __('Billing Country', 'hz-discord-bot-tiny'),
            'order_shipping_first_name' => __('Shipping First Name', 'hz-discord-bot-tiny'),
            'order_shipping_last_name' => __('Shipping Last Name', 'hz-discord-bot-tiny'),
            'order_shipping_address_1' => __('Shipping Address 1', 'hz-discord-bot-tiny'),
            'order_shipping_address_2' => __('Shipping Address 2', 'hz-discord-bot-tiny'),
            'order_shipping_city' => __('Shipping City', 'hz-discord-bot-tiny'),
            'order_shipping_state' => __('Shipping State', 'hz-discord-bot-tiny'),
            'order_shipping_postcode' => __('Shipping Postcode', 'hz-discord-bot-tiny'),
            'order_shipping_country' => __('Shipping Country', 'hz-discord-bot-tiny'),
            'order_payment_method' => __('Payment Method', 'hz-discord-bot-tiny'),
            'order_note' => __('Order Note', 'hz-discord-bot-tiny'),
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
            'user_id' => __('User ID', 'hz-discord-bot-tiny'),
            'user_login' => __('User Login', 'hz-discord-bot-tiny'),
            'user_email' => __('User Email', 'hz-discord-bot-tiny'),
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
            'new' => __('New Order Notification', 'hz-discord-bot-tiny'),
            'change' => __('Order Status Change Notification', 'hz-discord-bot-tiny'),
            'register' => __('New User Registration Notification', 'hz-discord-bot-tiny'),
        ];
    }
}