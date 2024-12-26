<?php

/**
 * Fired during plugin activation
 *
 * @link       https://heizhu.dev/author
 * @since      1.0.0
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 * @author     Hei Zhu <admin@heizhu.dev>
 */
class Hz_Discord_Bot_Tiny_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $options = [
            'hz_discord_bot_form_blocks_status',
            'hz_discord_bot_form_status_ctrl',
            'hz_discord_bot_form_status_phone',
            'hz_discord_bot_form_payment_phone'
        ];
        foreach ($options as $option_name) {
            $option_value = get_option($option_name);
            if (empty($option_value)) {
                update_option($option_name, []);
            }
        }
	}
}
