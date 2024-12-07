<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://heizhu.dev/author
 * @since      1.0.0
 *
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Hz_Discord_Bot_Tiny
 * @subpackage Hz_Discord_Bot_Tiny/includes
 * @author     Hei Zhu <admin@heizhu.dev>
 */
class Hz_Discord_Bot_Tiny_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'hz-discord-bot-tiny',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
