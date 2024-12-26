<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://heizhu.dev/author
 * @since      1.0.0
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/admin
 */


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/admin
 * @author     Hei Zhu <admin@heizhu.dev>
 */
class Hz_Discord_Bot_Tiny_Admin
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
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($hook)
    {
        if ($hook === 'woocommerce_page_hz_discord_bot_tiny') {
            wp_enqueue_style($this->plugin_name, HZ_DISCORD_BOT_TINY_URI . 'admin/assets/css/hz-discord-bot-tiny-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook)
    {
        if ($hook === 'woocommerce_page_hz_discord_bot_tiny') {
            wp_enqueue_script($this->plugin_name, HZ_DISCORD_BOT_TINY_URI . 'admin/assets/js/hz-discord-bot-tiny-admin.js', array('jquery'), $this->version, false);
            wp_localize_script($this->plugin_name, 'hz_discord_bot_tiny', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hz_discord_bot_tiny_nonce'),
                'commands' => get_rest_url(null, 'heizhu/v1/commands'),
                'commands_clear_url' => get_rest_url(null, 'heizhu/v1/commandsClear'),
            ));
            wp_localize_script($this->plugin_name, 'hz_discord_bot_tiny_language', array(
                'clear' => esc_attr__('Clear', 'hz-discord-bot-tiny'),
                'clearing' => esc_attr__('Clearing...', 'hz-discord-bot-tiny'),
                'clear_success' => esc_attr__('All commands cleared successfully', 'hz-discord-bot-tiny'),
                'clear_failed' => esc_attr__('Clear Error', 'hz-discord-bot-tiny'),
                'syncing' => esc_attr__('Syncing...', 'hz-discord-bot-tiny'),
                'sync_success' => esc_attr__('Command synchronization successful', 'hz-discord-bot-tiny'),
                'sync_failed' => esc_attr__('Command synchronization failed', 'hz-discord-bot-tiny'),
                'sync' => esc_attr__('Sync Command', 'hz-discord-bot-tiny'),
            ));

        }
    }

    public function admin_notices()
    {
        if ($error_message = get_transient('hz_discord_bot_settings_error')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_attr__('Error in sending POST request: ', 'hz-discord-bot-tiny') . esc_attr($error_message); ?></p>
            </div>
            <?php
            delete_transient('hz_discord_bot_settings_error');
        }

        settings_errors('hz_discord_bot_messages');
    }

    public function add_hz_discord_bot_tiny_admin_menu()
    {
        add_submenu_page(
            'woocommerce',
            'Hz Discord Bot Tiny',
            'Hz Discord Bot',
            'manage_options',
            'hz_discord_bot_tiny',
            function () {
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/hz-discord-bot-tiny-admin-display.php';
            },
        );
    }

    private function display_general_page()
    {
        include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/hz-discord-bot-tiny-general-display.php';
    }

    public function display_webhook_page()
    {
        settings_fields('hz_discord_webhook_options');
        do_settings_sections('hz_discord_bot_tiny_webhook');
        submit_button();
    }

    public function display_bot_commands_page()
    {
        settings_fields('hz_discord_bot_options');
        do_settings_sections('hz_discord_bot_tiny_bot');
        submit_button();
    }

    public function display_bot_notify_page()
    {
        settings_fields('hz_discord_bot_notice_options');
        do_settings_sections('hz_discord_bot_tiny_notice');
        submit_button();
    }

    public function display_bot_settings_page()
    {
        settings_fields('hz_discord_bot_setting_options');
        do_settings_sections('hz_discord_bot_tiny_setting');
        submit_button();
    }

    public function register_hz_discord_bot_tiny_settings()
    {
        $this->_register_setting_commands();
        $this->_register_setting_notify();
        $this->_register_setting_webhook();
        $this->_register_setting_setting();
    }

    public function send_order_to_discord_webhook($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $payment_method = $order->get_payment_method();
        $webHook = get_option('hz_discord_webhook_form_blocks', array());
        foreach ($webHook as $config) {
            if (in_array('new', $config['type'], true) && in_array($payment_method, $config['payment'], true)) {
                $payload = wp_json_encode(['username' => get_option('blogname'), 'content' => $this->_replacement_message($config['message'], $order)]);
                $webhooks = $config['webhook'];
                if (filter_var($webhooks, FILTER_VALIDATE_URL)) {
                    wp_remote_post($webhooks, [
                        'body' => $payload,
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'timeout' => 30,
                    ]);
                }
            }
        }
    }

    public function send_order_status_to_discord_webhook($order_id, $old_status, $new_status)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        $webHook = get_option('hz_discord_webhook_form_blocks', array());
        if (!is_array($webHook)) {
            return;
        }
        $payment_method = $order->get_payment_method();
        foreach ($webHook as $config) {
            if (!in_array('change', $config['type'], true) ||
                !in_array('wc-' . $new_status, $config['status'], true) ||
                !in_array($payment_method, $config['payment'], true)
            ) {
                continue;
            }
            $payload = wp_json_encode(['username' => get_option('blogname'), 'content' => $this->_replacement_message($config['message'], $order)]);
            $webhooks = $config['webhook'];
            if (filter_var($webhooks, FILTER_VALIDATE_URL)) {
                wp_remote_post($webhooks, [
                    'body' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 30,
                ]);
            }
        }
    }

    public function send_user_register_to_discord_webhook($user_id)
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        $webHook = get_option('hz_discord_webhook_form_blocks', array());
        foreach ($webHook as $config) {
            if (in_array('register', $config['type'], true)) {
                $payload = wp_json_encode(['username' => get_option('blogname'), 'content' => $this->_replacement_new_user_message($config['message'], $user)]);
                $webhooks = $config['webhook'];
                if (filter_var($webhooks, FILTER_VALIDATE_URL)) {
                    wp_remote_post($webhooks, [
                        'body' => $payload,
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'timeout' => 30,
                    ]);
                }
            }
        }
    }

    public function send_order_status_to_discord_bot($order_id, $old_status, $new_status)
    {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $saved_blocks = get_option('hz_discord_bot_notice_form_blocks', array());
        $payment_method = $order->get_payment_method();
        foreach ($saved_blocks as $index => $block) {
            if (in_array('wc-' . $new_status, $block['status'], true) && in_array($payment_method, $block['payment'], true)) {
                $message = $block['message'];

                $order_statuses = get_option('hz_discord_bot_notify_status', array());
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
                            "name" => esc_attr__('Order Number', 'hz-discord-bot-tiny'),
                            "value" => $order->get_id(),
                            "inline" => true
                        ],
                        [
                            "name" => esc_attr__('Order Status', 'hz-discord-bot-tiny'),
                            "value" => wc_get_order_status_name($new_status),
                            "inline" => true
                        ],
                        [
                            "name" => esc_attr__('Payment Amount', 'hz-discord-bot-tiny'),
                            "value" => $order->get_total(),
                            "inline" => true
                        ],
                        [
                            "name" => esc_attr__('Message', 'hz-discord-bot-tiny'),
                            "value" => $this->_replacement_message($message, $order),
                            "inline" => true
                        ]

                    ]
                ];

                $data = [
                    'embeds' => [$order_details_embed],
                    'components' => $components_containers,
                ];
                $channels = explode("\n", $block['channel']);
                foreach ($channels as $channel) {
                    $url = "{$this->discord_api_url}/channels/{$channel}/messages";

                    $response = wp_remote_post(
                        $url,
                        [
                            'headers' => [
                                'Authorization' => 'Bot ' . $this->discord_bot_token,
                                'Content-Type' => 'application/json',
                            ],
                            'body' => wp_json_encode($data),
                            'method' => 'POST',
                        ]
                    );
                }
            }
        }
    }

    public function plugin_action_links($links)
    {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=hz_discord_bot_tiny') . '">' . esc_attr__('Settings', 'hz-discord-bot-tiny') . '</a>',
        ), $links);
    }

    private function _register_setting_commands()
    {
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_blocks_status',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $v = wc_get_order_statuses();
                    $new_input = array();
                    foreach ($input as $key => $block) {
                        if (!empty($block) && array_key_exists('wc-' . $key, $v)) {
                            $new_input[$key] = sanitize_text_field($block);
                        }
                    }
                    return $new_input;
                },
                'default' => array()
            )
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_status_ctrl',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $v = wc_get_order_statuses();
                    $new_input = array();
                    foreach ($input as $key => $block) {
                        if (!empty($block) && array_key_exists('wc-' . $key, $v)) {
                            $new_input[$key] = sanitize_text_field($block);
                        }
                    }

                    return $new_input;
                },
                'default' => array()
            )
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_blocks_message',
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_find_message',
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_product_enable',
        );
        add_settings_section(
            'hz_discord_bot_form_section',
            esc_attr__('Order Application Commands', 'hz-discord-bot-tiny'),
            function () {
                esc_attr_e('You can select the state to be controlled by the ', 'hz-discord-bot-tiny');
                echo '<i class="command">/order</i>';
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/template/base_message_handler.php';
            },
            'hz_discord_bot_tiny_bot'
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_phone_blocks',
        );
        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_phoneLimit_blocks',
            array(
                'type' => 'integer',
                'default' => 5,
                'sanitize_callback' => function ($value) {
                    return min($value, 10);
                }
            )
        );

        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_status_phone',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $v = wc_get_order_statuses();
                    $new_input = array();
                    foreach ($input as $key => $block) {
                        if (!empty($block) && array_key_exists('wc-' . $key, $v)) {
                            $new_input[$key] = sanitize_text_field($block);
                        }
                    }

                    return $new_input;
                },
                'default' => array()
            )
        );

        register_setting(
            'hz_discord_bot_options',
            'hz_discord_bot_form_payment_phone',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $v = WC()->payment_gateways->payment_gateways();
                    $new_input = array();
                    foreach ($input as $key => $block) {
                        if (!empty($block) && array_key_exists($key, $v)) {
                            $new_input[$key] = sanitize_text_field($block);
                        }
                    }
                    return $new_input;
                },
                'default' => array()
            )
        );

        add_settings_field(
            'hz_discord_bot_form_blocks_status',
            esc_attr__('Order Status', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_blocks_status = get_option('hz_discord_bot_form_blocks_status', array());
                $status = wc_get_order_statuses();
                foreach ($status as $status_key => $status_label) :
                    $status_key_clean = str_replace('wc-', '', $status_key);
                    $is_checked = $hz_discord_bot_form_blocks_status[$status_key_clean] ?? '';
                    ?>
                    <label class="status-label">
                        <input type="checkbox"
                               name="hz_discord_bot_form_blocks_status[<?php echo esc_attr($status_key_clean); ?>]"
                               value="1"
                            <?php checked($is_checked, '1'); ?>
                        />
                        <?php echo esc_attr($status_label); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description"><?php esc_attr_e('Selection of command-controllable states', 'hz-discord-bot-tiny'); ?></p>
                <?php
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_section'
        );
        add_settings_field(
            'hz_discord_bot_form_blocks_message',
            esc_attr__('Message', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_blocks_message = get_option('hz_discord_bot_form_blocks_message', '');
                echo '<textarea name="hz_discord_bot_form_blocks_message" style="width: 100%; height: 200px;">' . esc_textarea($hz_discord_bot_form_blocks_message) . '</textarea>';
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_section'
        );
        add_settings_field(
            'hz_discord_bot_form_status_ctrl',
            esc_attr__('Order Status', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_status_ctrl = get_option('hz_discord_bot_form_status_ctrl', array());
                $status = wc_get_order_statuses();
                foreach ($status as $status_key => $status_label) :
                    $status_key_clean = str_replace('wc-', '', $status_key);
                    $is_checked = isset($hz_discord_bot_form_status_ctrl[$status_key_clean]) ?? '';
                    ?>
                    <label class="status-label">
                        <input type="checkbox"
                               name="hz_discord_bot_form_status_ctrl[<?php echo esc_attr($status_key_clean); ?>]"
                               value="1"
                            <?php checked($is_checked, '1'); ?>
                        />
                        <?php echo esc_attr($status_label); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description"><?php esc_attr_e('Status options of the controls that can be selected when an order is queried.', 'hz-discord-bot-tiny'); ?></p>
                <?php
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_section'
        );
        add_settings_field(
            'hz_discord_bot_form_find_message',
            esc_attr__('Message', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_find_message = get_option('hz_discord_bot_form_find_message', '');
                echo '<textarea name="hz_discord_bot_form_find_message" style="width: 100%; height: 200px;">' . esc_textarea($hz_discord_bot_form_find_message) . '</textarea>';
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_section'
        );
        add_settings_section(
            'hz_discord_bot_form_phone_section',
            esc_attr__('Phone Application Commands', 'hz-discord-bot-tiny'),
            function () {
                esc_attr__('You can check your order by /phone', 'hz-discord-bot-tiny');
            },
            'hz_discord_bot_tiny_bot'
        );
        add_settings_field(
            'hz_discord_bot_form_phone_blocks_status',
            esc_attr__('Phone Type', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_phone_blocks = get_option('hz_discord_bot_form_phone_blocks', array());
                $fields = [
                    'order_billing_phone' => esc_attr__('Billing Phone', 'hz-discord-bot-tiny'),
                    'order_shipping_phone' => esc_attr__('Shipping Phone', 'hz-discord-bot-tiny'),
                ];

                foreach ($fields as $field_key => $label) {
                    $checked = !empty($hz_discord_bot_form_phone_blocks[$field_key]) ? '1' : '0';
                    echo sprintf(
                        '<label><input type="checkbox" name="hz_discord_bot_form_phone_blocks[%s]" value="1" %s />%s</label>&nbsp;&nbsp;',
                        esc_attr($field_key),
                        checked($checked, '1', false),
                        esc_html($label)
                    );
                }
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_phone_section'
        );
        add_settings_field(
            'hz_discord_bot_form_phoneLimit_section',
            esc_attr__('Query limit', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_phoneLimit_blocks = get_option('hz_discord_bot_form_phoneLimit_blocks', 5);
                echo '<label><input type="number" min="1" max="10" name="hz_discord_bot_form_phoneLimit_blocks" value="' . esc_attr($hz_discord_bot_form_phoneLimit_blocks) . '" /></label>';
                echo '<p class="description">' . esc_attr__('Maximum query limit: 10', 'hz-discord-bot-tiny') . '</p>';
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_phone_section'
        );
        add_settings_field(
            'hz_discord_bot_form_status_phone',
            esc_attr__('Order Status', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_status_phone = get_option('hz_discord_bot_form_status_phone', array());
                $status = wc_get_order_statuses();
                foreach ($status as $status_key => $status_label) :
                    $status_key_clean = str_replace('wc-', '', $status_key);
                    $is_checked = $hz_discord_bot_form_status_phone[$status_key_clean] ?? '';
                    ?>
                    <label class="status-label">
                        <input type="checkbox"
                               name="hz_discord_bot_form_status_phone[<?php echo esc_attr($status_key_clean); ?>]"
                               value="1"
                            <?php checked($is_checked, '1'); ?>
                        />
                        <?php echo esc_attr($status_label); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description"><?php esc_attr_e('Selection of command-controllable states', 'hz-discord-bot-tiny'); ?></p>
                <?php
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_phone_section'
        );

        add_settings_field(
            'hz_discord_bot_form_payment_phone',
            esc_attr__('Payment Gateways', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_payment_phone = get_option('hz_discord_bot_form_payment_phone', array());
                $all_payment_gateways = WC()->payment_gateways->payment_gateways();
                foreach ($all_payment_gateways as $payment_gateway) :
                    if (isset($hz_discord_bot_form_payment_phone[$payment_gateway->id])) {
                        $is_checked = $hz_discord_bot_form_payment_phone[$payment_gateway->id] ? '1' : '';
                    } else {
                        $is_checked = '';
                    }

                    ?>
                    <label>
                        <input type="checkbox"
                               name="hz_discord_bot_form_payment_phone[<?php echo esc_attr($payment_gateway->id); ?>]"
                               value="<?php echo esc_attr($payment_gateway->title); ?>"
                            <?php checked($is_checked, '1'); ?>
                        />
                        <?php echo esc_attr($payment_gateway->title); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description"><?php esc_attr_e('Selection of command-controllable payment gateways', 'hz-discord-bot-tiny'); ?></p>
                <?php
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_phone_section'
        );

        add_settings_section(
            'hz_discord_bot_form_product_section',
            esc_attr__('Product Enquiry Control Instruction (Beta)', 'hz-discord-bot-tiny'),
            function () {
                esc_attr_e("You can check your product by /product", 'hz-discord-bot-tiny');
            },
            'hz_discord_bot_tiny_bot'
        );
        add_settings_field(
            'hz_discord_bot_form_product_enable',
            esc_attr__('Enable', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_form_product_enable = get_option('hz_discord_bot_form_product_enable', '0');
                echo '<label><input type="checkbox" name="hz_discord_bot_form_product_enable" value="1" ' . checked($hz_discord_bot_form_product_enable, '1', false) . ' /></label>';
            },
            'hz_discord_bot_tiny_bot',
            'hz_discord_bot_form_product_section'
        );

        add_settings_section(
            'hz_discord_bot_form_sync_section',
            __('Manual sync', 'hz-discord-bot-tiny'),
            function () {
                esc_attr_e('When the settings page is fully filled out, and after clicking save, if the command does not synchronize, please click manual sync.', 'hz-discord-bot-tiny');

                $settings = [
                    'bot_token' => get_option('hz_discord_bot_setting_bot_token', ''),
                    'guild_id' => get_option('hz_discord_bot_setting_guild_id', ''),
                    'public_key' => get_option('hz_discord_bot_setting_public_key', ''),
                    'application_id' => get_option('hz_discord_bot_setting_application_id', '')
                ];
                foreach ($settings as $key => $value) {
                    if (!empty($value) && $key === 'bot_token') {
                        echo sprintf(
                            '<input type="hidden" name="hz_discord_bot_setting_%s" value="%s">',
                            esc_attr($key),
                            esc_attr($value)
                        );
                    }
                }
                $disable = (in_array('', $settings, true)) ? 'disabled' : '';
                echo sprintf(
                    '<br><br><button type="button" class="button" id="sync_commands" %s>%s</button>',
                    esc_attr($disable),
                    esc_attr__('Sync command', 'hz-discord-bot-tiny')
                );
            },
            'hz_discord_bot_tiny_bot'
        );
    }

    private function _register_setting_webhook()
    {
        register_setting(
            'hz_discord_webhook_options',
            'hz_discord_webhook_form_blocks',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $new_input = array();
                    if (!empty($input) && is_array($input)) {
                        foreach ($input as $block) {
                            if (!empty($block)) {
                                $new_block = array(
                                    'type' => array_map('sanitize_text_field', (array)($block['type'] ?? array())),
                                    'webhook' => sanitize_text_field($block['webhook']),
                                    'status' => array_map('sanitize_text_field', (array)($block['status'] ?? array())),
                                    'message' => sanitize_textarea_field($block['message']),
                                    'payment' => array_map('sanitize_text_field', (array)($block['payment'] ?? array()))
                                );
                                $new_input[] = $new_block;
                            }
                        }
                    }

                    return $new_input;
                },
                'default' => array()
            )
        );
        add_settings_section(
            'hz_discord_webhook_form_section',
            __('Webhooks', 'hz-discord-bot-tiny'),
            function () {
                esc_attr_e('You can use the Discord Webhooks feature to get notifications quickly and easily.', 'hz-discord-bot-tiny');
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/template/base_message_handler.php';
            },
            'hz_discord_bot_tiny_webhook'
        );

        add_settings_field(
            'hz_discord_webhook_form_blocks',
            __('Content', 'hz-discord-bot-tiny'),
            function () {
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/hz-discord-bot-tiny-webhook-display.php';
            },
            'hz_discord_bot_tiny_webhook',
            'hz_discord_webhook_form_section'
        );
    }

    private function _register_setting_notify()
    {
        register_setting(
            'hz_discord_bot_notice_options',
            'hz_discord_bot_notify_status',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $v = wc_get_order_statuses();
                    $new_input = array();
                    foreach ($input as $key => $block) {
                        if (!empty($block) && array_key_exists('wc-' . $key, $v)) {

                            $new_input[$key] = sanitize_text_field($block);
                        }
                    }

                    return $new_input;
                },
                'default' => array()
            )
        );
        register_setting(
            'hz_discord_bot_notice_options',
            'hz_discord_bot_notice_form_blocks',
            array(
                'type' => 'array',
                'sanitize_callback' => function ($input) {
                    $new_input = array();
                    if (!empty($input) && is_array($input)) {
                        foreach ($input as $block) {
                            if (!empty($block)) {
                                $new_block = array(
                                    'status' => array_map('sanitize_text_field', (array)($block['status'] ?? array())),
                                    'channel' => sanitize_textarea_field($block['channel']),
                                    'message' => sanitize_textarea_field($block['message']),
                                    'payment' => array_map('sanitize_text_field', (array)($block['payment'] ?? array()))
                                );
                                $new_input[] = $new_block;
                            }
                        }
                    }

                    return $new_input;
                },
                'default' => array()
            )
        );
        add_settings_section(
            'hz_discord_bot_notice_form_section',
            __('Application Message', 'hz-discord-bot-tiny'),
            function () {
                esc_attr_e('You can use this function to send messages when an order changes to a specified status and control', 'hz-discord-bot-tiny');
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/template/base_message_handler.php';
            },
            'hz_discord_bot_tiny_notice'
        );
        add_settings_field(
            'hz_discord_bot_notify_status',
            __('Order Status', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_notify_status = get_option('hz_discord_bot_notify_status', array());
                $status = wc_get_order_statuses();
                foreach ($status as $status_key => $status_label) :
                    $status_key_clean = str_replace('wc-', '', $status_key);
                    $is_checked = $hz_discord_bot_notify_status[$status_key_clean] ?? '';
                    ?>
                    <label class="status-label">
                        <input type="checkbox"
                               name="hz_discord_bot_notify_status[<?php echo esc_attr($status_key_clean); ?>]"
                               value="1"
                            <?php checked($is_checked, '1'); ?>
                        />
                        <?php echo esc_attr($status_label); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description"><?php esc_attr_e('Selection of controllable states', 'hz-discord-bot-tiny'); ?></p>
                <?php
            },
            'hz_discord_bot_tiny_notice',
            'hz_discord_bot_notice_form_section'
        );
        add_settings_field(
            'hz_discord_bot_notice_form_blocks',
            __('Content', 'hz-discord-bot-tiny'),
            function () {
                include_once HZ_DISCORD_BOT_TINY_PATH . 'admin/partials/hz-discord-bot-tiny-notify-display.php';
            },
            'hz_discord_bot_tiny_notice',
            'hz_discord_bot_notice_form_section'
        );
    }

    private function _register_setting_setting()
    {

        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_application_id',
        );
        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_public_key',
        );
        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_bot_token',
        );
        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_guild_id',
        );
        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_debug',
        );
        register_setting(
            'hz_discord_bot_setting_options',
            'hz_discord_bot_setting_clear',
        );

        add_settings_section(
            'hz_discord_bot_setting_form_section',
            esc_attr__('Form Section Settings', 'hz-discord-bot-tiny'),
            function () {
                printf(
                    '<p>%s <a href="https://discord.com/developers/applications" target="_blank">Discord Developer Portal</a> %s</p>',
                    esc_attr__('Please go to', 'hz-discord-bot-tiny'),
                    esc_attr__('to create an application and obtain the APPLICATION ID.', 'hz-discord-bot-tiny')
                );
                $interactions_url = get_rest_url(null, 'heizhu/v1/interactions');
                echo esc_attr__('Your Interactions Endpoint URL', 'hz-discord-bot-tiny') . ': ';
                echo sprintf(
                    '<span class="interactions-url">%s</span>',
                    esc_url($interactions_url)
                );
            },
            'hz_discord_bot_tiny_setting'
        );
        add_settings_field(
            'hz_discord_bot_setting_application_id',
            __('APPLICATION ID', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_setting_pplication_id = get_option('hz_discord_bot_setting_application_id', '');
                echo sprintf(
                    '<input type="text" style="width:50%%" name="hz_discord_bot_setting_application_id" value="%s">',
                    esc_attr($hz_discord_bot_setting_pplication_id)
                );
            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_form_section'
        );
        add_settings_field(
            'hz_discord_bot_setting_public_key',
            esc_attr__('PUBLIC KEY', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_setting_public_key = get_option('hz_discord_bot_setting_public_key', '');
                echo sprintf(
                    '<input type="text" style="width:50%%" name="hz_discord_bot_setting_public_key" value="%s">',
                    esc_attr($hz_discord_bot_setting_public_key)
                );
            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_form_section'
        );
        add_settings_field(
            'hz_discord_bot_setting_bot_token',
            esc_attr__('BOT TOKEN', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_setting_bot_token = get_option('hz_discord_bot_setting_bot_token', '');
                echo sprintf(
                    '<input type="text" style="width:50%%" name="hz_discord_bot_setting_bot_token" value="%s">',
                    esc_attr($hz_discord_bot_setting_bot_token)
                );
            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_form_section'
        );
        add_settings_field(
            'hz_discord_bot_setting_guild_id',
            esc_attr__('GUILD ID', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_setting_guild_id = get_option('hz_discord_bot_setting_guild_id', '');
                echo sprintf(
                    '<input type="text" style="width:50%%" name="hz_discord_bot_setting_guild_id" value="%s">',
                    esc_attr($hz_discord_bot_setting_guild_id)
                );
                echo '<p class="description">' . esc_attr__('The GUILD ID is the ID of the server where the bot is located.', 'hz-discord-bot-tiny') . '</p>';
            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_form_section'
        );
        add_settings_section(
            'hz_discord_bot_setting_debug',
            esc_attr__('Debug', 'hz-discord-bot-tiny'),
            function () {
                printf(
                    '<p>You can see the debug log from <sapn class="debug">%s</span></p>',
                    esc_attr(Hz_Discord_Bot_Global::get_log_file_path())
                );
                printf(
                    '<a href="%s">%s</a>',
                    esc_url(Hz_Discord_Bot_Global::get_log_file_url()),
                    esc_attr__('View Logs', 'hz-discord-bot-tiny')
                );
            },
            'hz_discord_bot_tiny_setting'
        );
        add_settings_field(
            'hz_discord_bot_setting_debug',
            esc_attr__('Error Log', 'hz-discord-bot-tiny'),
            function () {
                $hz_discord_bot_setting_debug = get_option('hz_discord_bot_setting_debug', '');
                echo '<label><input type="checkbox" name="hz_discord_bot_setting_debug" value="1" ' . checked($hz_discord_bot_setting_debug, '1', false) . ' />' . esc_attr__('Enable', 'hz-discord-bot-tiny') . '</label>';

            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_debug'
        );
        add_settings_field(
            'hz_discord_bot_setting_clear',
            esc_attr__('Clear the command', 'hz-discord-bot-tiny'),
            function () {
                echo '<button type="button" id="clear_commands" class="button">Clear</button>';
            },
            'hz_discord_bot_tiny_setting',
            'hz_discord_bot_setting_debug'
        );

    }

    public function update_option_hz_discord_bot_form_command()
    {
        if (isset($this->discord_bot_token)) {
            static $executed = false;
            if (!$executed) {
                $executed = true;
                $commands_url = get_rest_url(null, 'heizhu/v1/commands');
                $response = wp_remote_post(
                    $commands_url,
                    array(
                        'method' => 'POST',
                        'headers' => array(
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bot ' . $this->discord_bot_token,
                        ),
                    )
                );
                if (wp_remote_retrieve_response_code($response) !== 200) {
                    $error_message = wp_remote_retrieve_response_message($response);
                    Hz_Discord_Bot_Global::debugMode($error_message);
                    set_transient('hz_discord_bot_settings_error', $error_message, 60);
                }
            }
        }
    }

    public function _replacement_message($message, $order)
    {
        $replacements = Hz_Discord_Bot_Global::get_order_details($order);
        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public function _replacement_new_user_message($message, $user)
    {
        $replacements = apply_filters('hz_discord_bot_user_replacements', [
            '{{user_id}}' => $user->ID,
            '{{user_login}}' => $user->user_login,
            '{{user_email}}' => $user->user_email,
        ], $user);
        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

}
