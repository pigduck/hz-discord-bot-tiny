<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<?php if (current_user_can('manage_options')): ?>
    <?php $saved_blocks = get_option('hz_discord_bot_form_blocks', array()); ?>
    <?php
    $user = array_column($saved_blocks, 'user');
    $channel = array_column($saved_blocks, 'channel');
    $status = array_column($saved_blocks, 'status');
    // Simple tab switching check - no need for strict validation since it's just UI
    $tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=hz_discord_bot_tiny"
               class="nav-tab <?php echo empty($tab) ? 'nav-tab-active' : ''; ?>">
                <?php esc_attr_e('Bot', 'hz-discord-bot-tiny'); ?>
            </a>
            <a href="?page=hz_discord_bot_tiny&tab=webhook"
               class="nav-tab <?php echo isset($tab) && sanitize_text_field(wp_unslash($tab)) === 'webhook' ? 'nav-tab-active' : ''; ?>">
                <?php esc_attr_e('Webhooks', 'hz-discord-bot-tiny'); ?>
            </a>
        </h2>
        <?php $this->display_general_page(); ?>
    </div>
<?php else: ?>

<?php endif; ?>
