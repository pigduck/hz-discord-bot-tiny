<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://heizhu.dev/author
 * @since             0.0.1
 * @package           Hz_Discord_Bot_Tiny
 *
 * @wordpress-plugin
 * Plugin Name:       HZ Discord Bot Tiny
 * Plugin URI:        https://discordbot.heizhu.dev
 * Description:       Your WooCommerce Management Assistant
 * Version:           0.0.1
 * Author:            Hei Zhu
 * Author URI:        https://heizhu.dev/author/
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hz-discord-bot-tiny
 * Domain Path:       /languages
 * Plugin URI:        https://discordbot.heizhu.dev
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4.3
 * WC requires at least: 8
 *
 */


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('HZ_DISCORD_BOT_TINY_VERSION', '0.0.1');
define('HZ_DISCORD_BOT_TINY_PATH', plugin_dir_path(__FILE__));
define('HZ_DISCORD_BOT_TINY_URI', plugin_dir_url(__FILE__));
define('HZ_DISCORD_BOT_TINY_BASENAME', plugin_basename(__FILE__));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-hz-discord-bot-tiny-activator.php
 */
function activate_hz_discord_bot_tiny()
{
    require_once HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny-activator.php';
    Hz_Discord_Bot_Tiny_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-hz-discord-bot-tiny-deactivator.php
 */
function deactivate_hz_discord_bot_tiny()
{
    require_once HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny-deactivator.php';
    Hz_Discord_Bot_Tiny_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_hz_discord_bot_tiny');
register_deactivation_hook(__FILE__, 'deactivate_hz_discord_bot_tiny');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require HZ_DISCORD_BOT_TINY_PATH . 'includes/class-hz-discord-bot-tiny.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (version_compare(PHP_VERSION, '7.4', '>') &&
    is_plugin_active('woocommerce/woocommerce.php')
) {
    add_action('plugins_loaded', 'run_hz_discord_bot_tiny');
}


function run_hz_discord_bot_tiny()
{
    $plugin = new Hz_Discord_Bot_Tiny();
    $plugin->run();
}

