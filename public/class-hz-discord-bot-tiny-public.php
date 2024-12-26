<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://heizhu.dev/author
 * @since      1.0.0
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/public
 * @author     Hei Zhu <admin@heizhu.dev>
 */
class Hz_Discord_Bot_Tiny_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;


    private $discord_api_url = 'https://discord.com/api/v10';

    private $discord_public_key;

    private $discord_bot_token;

    private $discord_guild_id;

    private $discord_application_id;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->init();
    }

    public function init()
    {
        $this->discord_public_key = get_option('hz_discord_bot_setting_public_key', '');
        $this->discord_bot_token = get_option('hz_discord_bot_setting_bot_token', '');
        $this->discord_guild_id = get_option('hz_discord_bot_setting_guild_id', '');
        $this->discord_application_id = get_option('hz_discord_bot_setting_application_id', '');

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/hz-discord-bot-tiny-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/hz-discord-bot-tiny-public.js', array('jquery'), $this->version, false);

    }


    public function handle_order_interactions(WP_REST_Request $request)
    {
        try {
            $signature = isset($_SERVER['HTTP_X_SIGNATURE_ED25519'])
                ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_SIGNATURE_ED25519']))
                : '';
            $timestamp = isset($_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'])
                ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_SIGNATURE_TIMESTAMP']))
                : '';

            $request_body = $request->get_body();

            if (empty($request_body)) {
                Hz_Discord_Bot_Global::debugMode('Please request the content to be empty');
                wp_send_json_error('Please request the content to be empty', 400);
            }

            if (!$this->verify_discord_signature($signature, $timestamp, $request_body, $this->discord_public_key)) {
                Hz_Discord_Bot_Global::debugMode('Signature verification failed');
                wp_send_json_error('Signature verification failed', 401);
            }

            $request_data = json_decode($request_body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Hz_Discord_Bot_Global::debugMode('JSON parsing error');
                wp_send_json_error('JSON parsing error', 400);
            }

            if (isset($request_data['type']) && $request_data['type'] === 1) {
                wp_send_json(['type' => 1]);
            }

            $member = $request_data['member'];

            if (isset($request_data['type']) && $request_data['type'] === 2) {
                $commandName = $request_data['data']['name'];
                if ($commandName == 'order') {
                    $options = $request_data['data']['options'];
                    $order_id = $status = $note = null;
                    foreach ($options as $option) {
                        switch ($option['name']) {
                            case 'order_id':
                                $order_id = $option['value'];
                                break;
                            case 'status':
                                $status = $option['value'];
                                break;
                            case 'note':
                                $note = $option['value'];
                                break;
                        }
                    }
                    if (!$order_id) {
                        wp_send_json([
                            'type' => 4,
                            'data' => [
                                'content' => esc_attr__("Please enter the order ID", 'hz-discord-bot-tiny')
                            ]
                        ]);
                    }
                    if (!empty($status) || !empty($note)) {
                        $this->handle_general_order_command($order_id, $status, $note, $member);
                    } else {
                        $this->handle_find_order_command($order_id);
                    }
                } elseif ($commandName == 'phone') {
                    $this->handle_find_order_by_phone_command($request_data);
                } elseif ($commandName == 'product') {
                    $this->handle_find_order_by_products_command($request_data);
                }
            } else if (isset($request_data['type']) && $request_data['type'] === 4) {
                $query = $request_data['data']['options'][0]['value'] ?? '';

                $args = [
                    'limit' => 10,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    's' => $query,
                    'return' => 'ids',
                    'type' => ['simple', 'variation', 'variable'],

                ];

                $products = wc_get_products($args);

                if (empty($products)) {
                    return [];
                }
                $suggestions = [];
                foreach ($products as $product_id) {
                    $product = wc_get_product($product_id);

                    $suggestions[] = [
                        'name' => $product->get_name(),
                        'value' => (string)$product_id
                    ];
                }
                $response = [
                    'type' => 8,
                    'data' => [
                        'choices' => $suggestions
                    ]
                ];

                header('Content-Type: application/json');
                echo wp_json_encode($response);

            } else if (isset($request_data['data']['component_type'])) {
                $this->handle_component_interaction($request_data);
            } else {
                Hz_Discord_Bot_Global::debugMode('Unprocessed Interaction Types');
                wp_send_json_error('Unprocessed Interaction Types', 400);
            }
        } catch (Exception $e) {
            Hz_Discord_Bot_Global::debugMode($e->getMessage());
        }
    }

    public
    function handle_register_commands(
        WP_REST_Request $request
    )
    {
        $auth_header = $request->get_header('Authorization');

        if ($auth_header !== 'Bot ' . $this->discord_bot_token) {
            Hz_Discord_Bot_Global::debugMode('Unauthorized');

            return new WP_REST_Response(['message' => 'Unauthorized'], 401);
        }


        $url = "{$this->discord_api_url}/applications/{$this->discord_application_id}/guilds/{$this->discord_guild_id}/commands";


        $order_statuses = get_option('hz_discord_bot_form_blocks_status', array());
        $choices = [];
        foreach ($order_statuses as $status_key => $status_name) {
            $choices[] = [
                "name" => wc_get_order_status_name($status_key),
                "value" => $status_key
            ];
        }


        $order_command_data = [
            'name' => 'order',
            'description' => esc_attr__("原來你也玩原神", 'hz-discord-bot-tiny'),
            "options" => [
                [
                    "name" => "order_id",
                    "description" => esc_attr__("Enter Order ID (required)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => true
                ],
                [
                    "name" => "status",
                    "description" => esc_attr__("Select the order status to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => false,
                    "choices" => $choices
                ],
                [
                    "name" => "note",
                    "description" => esc_attr__("Enter the notes you want to add (optional)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => false
                ],
            ]
        ];

        $hz_discord_bot_form_payment_phone = get_option('hz_discord_bot_form_payment_phone', array());

        $payment = [];
        foreach ($hz_discord_bot_form_payment_phone as $payment_gateway_id => $payment_gateway) {
            $payment[] = [
                "name" => $payment_gateway,
                "value" => $payment_gateway_id
            ];
        }
        $choices = [];
        $hz_discord_bot_form_status_phone = get_option('hz_discord_bot_form_status_phone', array());
        foreach ($hz_discord_bot_form_status_phone as $status_key => $status_name) {
            $choices[] = [
                "name" => wc_get_order_status_name($status_key),
                "value" => $status_key
            ];
        }

        $phone_command_data = [
            'name' => 'phone',
            'description' => esc_attr__("Enter your phone number", 'hz-discord-bot-tiny'),
            "options" => [
                [
                    "name" => "phone",
                    "description" => esc_attr__("Enter your phone number (required)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => true
                ],
                [
                    "name" => "status",
                    "description" => esc_attr__("Select the order status to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => false,
                    "choices" => $choices
                ],
                [
                    "name" => 'payment',
                    "description" => esc_attr__("Select the payment method (optional)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => false,
                    "choices" => $payment

                ],
            ]
        ];


        $products_command_data = [
            'name' => 'product',
            'description' => esc_attr__("Enter your product name", 'hz-discord-bot-tiny'),
            "options" => [
                [
                    "name" => "product",
                    "description" => esc_attr__("Enter your product name (required)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => true,
                    "autocomplete" => true
                ],
                [
                    "name" => "status",
                    "description" => esc_attr__("Select the status to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 3,
                    "required" => false,
                    "choices" => [
                        ["name" => esc_attr__("Enable", 'hz-discord-bot-tiny'), "value" => "enable"],
                        ["name" => esc_attr__("Disable", 'hz-discord-bot-tiny'), "value" => "disable"],
                    ]
                ],
                [
                    "name" => "regular_price",
                    "description" => esc_attr__("Enter the price to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 10,
                    "required" => false,
                ],
                [
                    "name" => "sale_price",
                    "description" => esc_attr__("Enter the price to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 10,
                    "required" => false,
                ],
                [
                    "name" => "stock",
                    "description" => esc_attr__("Enter the stock quantity to be changed (optional)", 'hz-discord-bot-tiny'),
                    "type" => 10,
                    "required" => false,
                ]
            ]
        ];


        $commands = [$order_command_data, $phone_command_data, $products_command_data];


        foreach ($commands as $command_data) {
            $args = [
                'headers' => [
                    'Authorization' => 'Bot ' . $this->discord_bot_token,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($command_data),
                'method' => 'POST',
            ];
            $response = wp_remote_post($url, $args);
            $http_code = wp_remote_retrieve_response_code($response);

            if (!in_array($http_code, [200, 201], true)) {
                $response_body = wp_remote_retrieve_body($response);
                Hz_Discord_Bot_Global::debugMode($response_body);

                return new WP_REST_Response(['message' => 'Directive Registration Failure：' . $response_body], 500);
            }
        }

        return new WP_REST_Response(['message' => 'Command Registration Successful.'], 200);
    }

    public function handle_register_commandsClear(WP_REST_Request $request)
    {
        $auth_header = $request->get_header('Authorization');

        if ($auth_header !== 'Bot ' . $this->discord_bot_token) {
            Hz_Discord_Bot_Global::debugMode('Unauthorized');

            return new WP_REST_Response(['message' => 'Unauthorized'], 401);
        }

        $url = "{$this->discord_api_url}/applications/{$this->discord_application_id}/guilds/{$this->discord_guild_id}/commands";

        $check_response = wp_remote_request($url, [
            'method' => 'PUT',
            'headers' => [
                'Authorization' => 'Bot ' . $this->discord_bot_token,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([]),
        ]);

        $status_code = wp_remote_retrieve_response_code($check_response);
        $body = json_decode(wp_remote_retrieve_body($check_response), true);

        if ($status_code === 200 && empty($body)) {
            return new WP_REST_Response(['message' => 'Successfully cleared all Discord Bot commands.'], 200);
        } else {
            return new WP_REST_Response([
                'message' => 'Clear command failure, command still exists',
                'commands' => $body
            ], 500);
        }
    }

    public
    function verify_discord_signature(
        $signature, $timestamp, $body, $public_key
    )
    {
        if (empty($signature) || empty($timestamp) || empty($body) || empty($public_key)) {
            return false;
        }

        try {
            $message = $timestamp . $body;
            $public_key_bytes = sodium_hex2bin($public_key);
            $signature_bytes = sodium_hex2bin($signature);

            return sodium_crypto_sign_verify_detached(
                $signature_bytes,
                $message,
                $public_key_bytes
            );
        } catch (Exception $e) {
            Hz_Discord_Bot_Global::debugMode($e->getMessage());

            return false;
        }
    }

    public function handle_general_order_command($order_id, $status, $note, $member)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => esc_attr__("Can't find the order", 'hz-discord-bot-tiny')
                ]
            ]);
        }
        if ($status) {
            $order->update_status(
                $status,
                esc_attr(
                    'The Order has been updated to ' . wc_get_order_status_name($status) .
                    ' via Discord interaction - by ' . esc_attr($member['user']['id']),
                    'hz-discord-bot-tiny'
                )
            );
        }
        if ($note) {
            $order->add_order_note($note);
        }
        $order->save();

        $hz_discord_bot_form_blocks_message = get_option('hz_discord_bot_form_blocks_message', '');

        $message = $this->_replacement_message($hz_discord_bot_form_blocks_message, $order);

        wp_send_json([
            'type' => 4,
            'data' => [
                'content' => $message,
            ]
        ]);
    }

    public function handle_find_order_command($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => esc_attr__("Can't find the order", 'hz-discord-bot-tiny')
                ]
            ]);
        }
        $order_statuses = get_option('hz_discord_bot_form_status_ctrl', array());
        $order_message = get_option('hz_discord_bot_form_find_message', '');

        $components = [];

        foreach ($order_statuses as $status_key => $status_name) {
            switch ($status_key) {
                case 'pending':
                case 'processing':
                case 'on-hold':
                    $style = 1;
                    break;
                case 'completed':
                    $style = 3;
                    break;
                case 'cancelled':
                case 'failed':
                    $style = 4;
                    break;
                default:
                    $style = 2;
            }
            $components[] = [
                'type' => 2,
                'label' => wc_get_order_status_name($status_key),
                'style' => $style,
                'custom_id' => "status_change_{$status_key}_{$order_id}",
            ];
        }
        $components[] = [
            'type' => 2,
            'label' => esc_attr__('View Order', 'hz-discord-bot-tiny'),
            'style' => 5,
            'url' => $order->get_edit_order_url(),
        ];

        $components_containers = [];
        $chunked_components = array_chunk($components, 5);
        foreach ($chunked_components as $chunk) {
            $components_containers[] = [
                'type' => 1,
                'components' => $chunk,
            ];
        }

        $order_details_embed = [
            "title" => esc_attr__('Order Details', 'hz-discord-bot-tiny'),
            "color" => 5814783,
            "fields" => [
                [
                    "name" => esc_attr__('Message', 'hz-discord-bot-tiny'),
                    "value" => $this->_replacement_message($order_message, $order),
                    "inline" => true
                ]

            ]
        ];
        wp_send_json([
            'type' => 4,
            'data' => [
                'embeds' => [$order_details_embed],
                'components' => $components_containers
            ]
        ]);
    }

    public function handle_find_order_by_products_command($request_data)
    {
        $options = $request_data['data']['options'];
        $product_id = $options[0]['value'];
        $hz_discord_bot_form_product_enable = get_option('hz_discord_bot_form_product_enable', '0');

        if ($hz_discord_bot_form_product_enable != '1') {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => esc_attr__("Product command not yet activated", 'hz-discord-bot-tiny')
                ]
            ]);
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => esc_attr__("Can't find the product", 'hz-discord-bot-tiny')
                ]
            ]);
        }

        $status = $stock = $sale_price = $regular_price = null;
        foreach ($options as $option) {
            switch ($option['name']) {
                case 'status':
                    $status = $option['value'];
                    break;
                case 'regular_price':
                    $regular_price = $option['value'];
                    break;
                case 'sale_price':
                    $sale_price = $option['value'];
                    break;
                case 'stock':
                    $stock = $option['value'];
                    break;
            }
        }
        $feedback = [];
        $color = 5814783;
        $has_error = 0;
        if ($status || $regular_price || $sale_price || isset($stock)) {
            $type = $product->get_type();
            if ($type == 'simple') {
                //開啟或關閉商品(發布或草稿)
                if ($status) {
                    if ($status == 'enable') {
                        $product->set_status('publish');
                        $feedback[] = esc_attr__('Product has been updated for release', 'hz-discord-bot-tiny');
                    } else {
                        $product->set_status('draft');
                        $feedback[] = esc_attr__('Product has been updated for draft', 'hz-discord-bot-tiny');

                    }
                }

                //修改價格
                if ($regular_price) {
                    if ($regular_price > 0) {
                        if ($sale_price) {
                            if ($regular_price > $sale_price) {
                                $product->set_regular_price($regular_price);
                                $feedback[] = esc_attr__('Product Regular price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Regular price must be greater than sale price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        } else {
                            if ($regular_price > $product->get_regular_price()) {
                                $product->set_regular_price($regular_price);
                                $feedback[] = esc_attr__('Product Regular price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Regular price must be greater than current regular price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        }
                    } else {
                        $feedback[] = esc_attr__('Regular price must be greater than 0', 'hz-discord-bot-tiny');
                        $has_error++;
                    }
                }
                if ($sale_price) {
                    if ($sale_price > 0) {
                        if ($regular_price) {
                            if ($sale_price < $regular_price) {
                                $product->set_sale_price($sale_price);
                                $feedback[] = esc_attr__('Product Sale price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Sale price must be less than the provided regular price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        } elseif ($sale_price < $product->get_regular_price()) {
                            $product->set_sale_price($sale_price);
                            $feedback[] = esc_attr__('Product Sale price has been updated using the product\'s regular price', 'hz-discord-bot-tiny');
                        } else {
                            $feedback[] = esc_attr__('Sale price must be less than the product\'s regular price', 'hz-discord-bot-tiny');
                            $has_error++;
                        }
                    } else {
                        $feedback[] = esc_attr__('Sale price must be greater than 0', 'hz-discord-bot-tiny');
                        $has_error++;
                    }
                }
                //修改庫存數量
                if (isset($stock)) {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                    $feedback[] = esc_attr__('Product stock quantity has been updated', 'hz-discord-bot-tiny');
                }
                $product->save();
            } elseif ($type == 'variable' || $product->get_parent_id() == 0) {
                //開啟或關閉商品(發布或草稿)
                if ($status) {
                    if ($status == 'enable') {
                        $product->set_status('publish');
                        $feedback[] = esc_attr__('Product has been updated for release', 'hz-discord-bot-tiny');
                    } else {
                        $product->set_status('draft');
                        $feedback[] = esc_attr__('Product has been updated for draft', 'hz-discord-bot-tiny');
                    }
                }
                if ($regular_price) {
                    $feedback[] = esc_attr__('Variable products cannot be updated with regular price', 'hz-discord-bot-tiny');
                    $has_error++;
                }
                if ($sale_price) {
                    $feedback[] = esc_attr__('Variable products cannot be updated with sale price', 'hz-discord-bot-tiny');
                    $has_error++;
                }
                if ($stock) {
                    $feedback[] = esc_attr__('Variable products cannot be updated with stock quantity', 'hz-discord-bot-tiny');
                    $has_error++;
                }
                $product->save();

            } elseif ($type == 'variation' || $product->get_parent_id() > 0) {
                //開啟或關閉商品(啟用或不啟用)
                if ($status) {
                    if ($status == 'enable') {
                        //啟用變體商品 單獨啟用變體商品
                        //change variable_enabled
                        $product->set_status('publish');
                        $feedback[] = esc_attr__('Product has been updated for release', 'hz-discord-bot-tiny');
                    } else {
                        $product->set_status('private');
                        $feedback[] = esc_attr__('Product has been updated for draft', 'hz-discord-bot-tiny');
                    }
                }
                if ($regular_price) {
                    if ($regular_price > 0) {
                        if ($sale_price) {
                            if ($regular_price > $sale_price) {
                                $product->set_regular_price($regular_price);
                                $feedback[] = esc_attr__('Product Regular price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Regular price must be greater than sale price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        } else {
                            if ($regular_price > $product->get_regular_price()) {
                                $product->set_regular_price($regular_price);
                                $feedback[] = esc_attr__('Product Regular price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Regular price must be greater than current regular price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        }
                    } else {
                        $feedback[] = esc_attr__('Regular price must be greater than 0', 'hz-discord-bot-tiny');
                        $has_error++;
                    }
                }
                if ($sale_price) {
                    if ($sale_price > 0) {
                        if ($regular_price) {
                            if ($sale_price < $regular_price) {
                                $product->set_sale_price($sale_price);
                                $feedback[] = esc_attr__('Product Sale price has been updated', 'hz-discord-bot-tiny');
                            } else {
                                $feedback[] = esc_attr__('Sale price must be less than the provided regular price', 'hz-discord-bot-tiny');
                                $has_error++;
                            }
                        } elseif ($sale_price < $product->get_regular_price()) {
                            $product->set_sale_price($sale_price);
                            $feedback[] = esc_attr__('Product Sale price has been updated using the product\'s regular price', 'hz-discord-bot-tiny');
                        } else {
                            $feedback[] = esc_attr__('Sale price must be less than the product\'s regular price', 'hz-discord-bot-tiny');
                            $has_error++;
                        }
                    }
                }

                if (isset($stock)) {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                    $feedback[] = esc_attr__('Product stock quantity has been updated', 'hz-discord-bot-tiny');
                }
                $product->save();
            }
        }

        if ($has_error > 0) {
            $color = 14548992;
        }


        wp_send_json([
                'type' => 4,
                'data' => [
                    'embeds' => [$this->_handle_product_embed($product, $color)],
                    'content' => implode("\n", $feedback),
                    'components' => [
                        [
                            'type' => 1,
                            'components' => [
                                [
                                    'type' => 2,
                                    'style' => 5,
                                    'label' => __('View Product', 'hz-discord-bot-tiny'),
                                    'url' => $product->get_permalink()
                                ],
                                [
                                    'type' => 2,
                                    'style' => 5,
                                    'label' => __('Edit Product', 'hz-discord-bot-tiny'),
                                    'url' => admin_url('post.php?post=' . $product->get_id() . '&action=edit')
                                ]
                            ]
                        ]
                    ]
                ]]
        );
    }

    public function _handle_product_embed($product, $color = 5814783)
    {

        return [
            "title" => __('Product Details', 'hz-discord-bot-tiny'),
            "color" => $color,
            "fields" => [
                [
                    "name" => __('Enable', 'hz-discord-bot-tiny'),
                    "value" => $product->get_status() === 'publish' ? __('Yes', 'hz-discord-bot-tiny') : __('No', 'hz-discord-bot-tiny'),
                    "inline" => true
                ],
                [
                    "name" => __('Product Name', 'hz-discord-bot-tiny'),
                    "value" => $product->get_name(),
                    "inline" => true
                ],
                [
                    "name" => __('Regular Price', 'hz-discord-bot-tiny'),
                    "value" => $product->get_regular_price(),
                    "inline" => true
                ],
                [
                    "name" => __('Sale Price', 'hz-discord-bot-tiny'),
                    "value" => $product->get_sale_price() ? $product->get_sale_price() : __('N/A', 'hz-discord-bot-tiny'),
                    "inline" => true
                ],
                [
                    "name" => __('Stock Quantity', 'hz-discord-bot-tiny'),
                    "value" => $product->get_stock_quantity() !== null ? $product->get_stock_quantity() : __('N/A', 'hz-discord-bot-tiny'),
                    "inline" => true
                ],
                [
                    "name" => __('Sold Quantity', 'hz-discord-bot-tiny'),
                    "value" => $product->get_total_sales(),
                    "inline" => true
                ]
            ]
        ];
    }


    public function handle_find_order_by_phone_command($request_data)
    {
        $options = $request_data['data']['options'];
        $phone_number = $status = $payment = null;
        foreach ($options as $option) {
            switch ($option['name']) {
                case 'phone':
                    $phone_number = $option['value'];
                    break;
                case 'status':
                    $status = $option['value'];
                    break;
                case 'payment':
                    $payment = $option['value'];
                    break;
            }
        }

        if (!$phone_number) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => __("Please enter the order ID", 'hz-discord-bot-tiny')
                ]
            ]);
        }


        $hz_discord_bot_form_phone_blocks = get_option('hz_discord_bot_form_phone_blocks', array());
        $hz_discord_bot_form_phoneLimit_blocks = get_option('hz_discord_bot_form_phoneLimit_blocks', 5);

        if ($hz_discord_bot_form_phoneLimit_blocks > 10 ||
            $hz_discord_bot_form_phoneLimit_blocks < 1 ||
            !is_numeric($hz_discord_bot_form_phoneLimit_blocks)) {
            $hz_discord_bot_form_phoneLimit_blocks = 5;
        }

        if (empty($hz_discord_bot_form_phone_blocks['order_billing_phone']) &&
            empty($hz_discord_bot_form_phone_blocks['order_shipping_phone'])) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => __("Can't find the order", 'hz-discord-bot-tiny')
                ]
            ]);
        }

        $orders = [];

        if (!empty($hz_discord_bot_form_phone_blocks['order_billing_phone'])) {
            $orders_billing = wc_get_orders(array(
                'limit' => $hz_discord_bot_form_phoneLimit_blocks,
                'orderby' => 'date',
                'order' => 'DESC',
                'billing_phone' => $phone_number,
                'status' => $status,
                'payment_method' => $payment,
                'return' => 'ids',
            ));
            $orders = array_merge($orders, $orders_billing);

        }
        if (!empty($hz_discord_bot_form_phone_blocks['order_shipping_phone'])) {
            $orders_shipping = wc_get_orders(array(
                'limit' => $hz_discord_bot_form_phoneLimit_blocks,
                'orderby' => 'date',
                'order' => 'DESC',
                'shipping_phone' => $phone_number,
                'status' => $status,
                'payment_method' => $payment,
                'return' => 'ids',


            ));
            $orders = array_merge($orders, $orders_shipping);
        }

        $orders = array_slice(array_unique($orders), 0, 10);

        if (empty($orders)) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => __("Can't find the order", 'hz-discord-bot-tiny')
                ]
            ]);
        }

        $order_details = [];
        foreach ($orders as $order) {
            $order = wc_get_order($order);
            $order_data = $order->get_data();
            $order_details[] = [
                'order_id' => $order_data['id'],
                'name' => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
                'order_status' => wc_get_order_status_name($order_data['status']),
                'order_total' => $order_data['total'],
            ];
        }
        $order_details_embed = [
            "title" => __('Order Details', 'hz-discord-bot-tiny'),
            "color" => 5814783,
            "fields" => []
        ];

        foreach ($order_details as $order) {
            $order_details_embed['fields'][] = [
                "name" => "Order ID: " . $order['order_id'],
                "value" => __("Billing Name: ", 'hz-discord-bot-tiny') . $order['name'] . "\n" . __("Status: ", 'hz-discord-bot-tiny') . $order['order_status'] . "\n" . __("Total: ", 'hz-discord-bot-tiny') . $order['order_total'],
                "inline" => false,
            ];
            $components[] = [
                'type' => 2,
                'label' => '#' . $order['order_id'],
                'style' => 2,
                'custom_id' => "view_order_{$order['order_id']}",
            ];
        }

        $components_containers = [];
        $chunked_components = array_chunk($components, 5);
        foreach ($chunked_components as $chunk) {
            $components_containers[] = [
                'type' => 1,
                'components' => $chunk,
            ];
        }
        wp_send_json([
            'type' => 4,
            'data' => [
                'embeds' => [$order_details_embed],
                'components' => $components_containers
            ]
        ]);
    }

    public
    function handle_component_interaction(
        $request_data
    )
    {
        if (!isset($request_data['data']['custom_id'])) {
            Hz_Discord_Bot_Global::debugMode('Missing Order ID');
            wp_send_json_error('Missing Order ID', 400);
        }
        $custom_id = explode('_', $request_data['data']['custom_id']);
        if (substr($request_data['data']['custom_id'], 0, 13) === 'status_change') {
            $status_key = $custom_id[2];
            $order_id = $custom_id[3];
            $this->handle_change_order($order_id, $status_key, $request_data);

        } elseif (substr($request_data['data']['custom_id'], 0, 10) === 'view_order') {
            $order_id = $custom_id[2];
            $this->handle_find_order_command($order_id);
        }
    }

    public
    function handle_change_order(
        $order_id, $status_key, $request_data
    )
    {
        $order = wc_get_order($order_id);
        $discord_member_user = $request_data['member']['user']['id'];
        $discord_member_username = $request_data['member']['user']['username'];


        if (!$order) {
            wp_send_json([
                'type' => 4,
                'data' => [
                    'content' => __("Can't find the order", 'hz-discord-bot-tiny')
                ]
            ]);
        }

        $order->update_status(
            $status_key,
            esc_attr__('The order has been updated to Pending through Discord interaction.', 'hz-discord-bot-tiny')
        );

        $order->add_order_note(
            esc_attr($discord_member_username) . ' (' .
            esc_attr($discord_member_user) . ') updated the order status to ' .
            wc_get_order_status_name($status_key) .
            ' through Discord interaction',
            'hz-discord-bot-tiny'
        );

        wp_send_json([
            'type' => 4,
            'data' => [
                'content' => __('Order status has been successfully updated', 'hz-discord-bot-tiny')
            ]
        ]);
    }

    function register_rest_routes()
    {
        register_rest_route('heizhu/v1', '/interactions', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_order_interactions'],
            'permission_callback' => '__return_true'
        ]);
        register_rest_route('heizhu/v1', '/commands', [
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'handle_register_commands'],
        ]);
        register_rest_route('heizhu/v1', '/commandsClear', [
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'handle_register_commandsClear'],
        ]);
    }

    public
    function _replacement_message(
        $message, $order
    )
    {
        $replacements = Hz_Discord_Bot_Global::get_order_details($order);

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

}
