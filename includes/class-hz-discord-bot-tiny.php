<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://heizhu.dev/author
 * @since      1.0.0
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 * @author     Hei Zhu <admin@heizhu.dev>
 */
class Hz_Discord_Bot_Tiny
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Hz_Discord_Bot_Tiny_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;


    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('HZ_DISCORD_BOT_TINY_VERSION')) {
            $this->version = HZ_DISCORD_BOT_TINY_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'hz-discord-bot-tiny';


        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Hz_Discord_Bot_Tiny_Loader. Orchestrates the hooks of the plugin.
     * - Hz_Discord_Bot_Tiny_i18n. Defines internationalization functionality.
     * - Hz_Discord_Bot_Tiny_Admin. Defines all hooks for the admin area.
     * - Hz_Discord_Bot_Tiny_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once HZ_DISCORD_BOT_TINY_PATH . 'admin/class-hz-discord-bot-tiny-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once HZ_DISCORD_BOT_TINY_PATH . 'public/class-hz-discord-bot-tiny-public.php';


        require_once HZ_DISCORD_BOT_TINY_PATH . 'admin/class_hz_discord_bot_details.php';


        require_once HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny-global.php';


        $this->loader = new Hz_Discord_Bot_Tiny_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Hz_Discord_Bot_Tiny_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Hz_Discord_Bot_Tiny_i18n();

        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Hz_Discord_Bot_Tiny_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_init', $plugin_admin, 'register_hz_discord_bot_tiny_settings');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 10, 1);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 10, 1);
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_hz_discord_bot_tiny_admin_menu', 9999);

        $this->loader->add_action('woocommerce_new_order', $plugin_admin, 'send_order_to_discord_webhook', 10, 1);
        $this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'send_order_status_to_discord_webhook', 10, 3);
        $this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'send_order_status_to_discord_bot', 10, 3);
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices');


        $this->loader->add_action('update_option_hz_discord_bot_form_blocks_status', $plugin_admin, 'update_option_hz_discord_bot_form_command', 10, 3);
        $this->loader->add_action('update_option_hz_discord_bot_form_status_ctrl', $plugin_admin, 'update_option_hz_discord_bot_form_command', 10, 3);
        $this->loader->add_action('update_option_hz_discord_bot_form_status_phone', $plugin_admin, 'update_option_hz_discord_bot_form_command', 10, 3);
        $this->loader->add_action('update_option_hz_discord_bot_form_payment_phone', $plugin_admin, 'update_option_hz_discord_bot_form_command', 10, 3);


        $this->loader->add_action('user_register', $plugin_admin, 'send_user_register_to_discord_webhook', 10, 1);
        $this->loader->add_filter('plugin_action_links_' . HZ_DISCORD_BOT_TINY_BASENAME, $plugin_admin, 'plugin_action_links');


    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Hz_Discord_Bot_Tiny_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('rest_api_init', $plugin_public, 'register_rest_routes');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Hz_Discord_Bot_Tiny_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

}
