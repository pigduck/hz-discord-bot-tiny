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
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=hz_discord_bot_tiny"
               class="nav-tab <?php echo empty($_GET['tab']) ? 'nav-tab-active' : ''; ?>">
                <?php _e('Bot', 'hz-discord-bot-tiny'); ?>
            </a>
            <a href="?page=hz_discord_bot_tiny&tab=webhook"
               class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'webhook' ? 'nav-tab-active' : ''; ?>">
                <?php _e('Webhooks', 'hz-discord-bot-tiny'); ?>
            </a>
        </h2>
        <?php $this->display_general_page(); ?>
    </div>
<?php else: ?>

<?php endif; ?>
