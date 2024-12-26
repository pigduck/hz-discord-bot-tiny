<?php
// Simple tab switching check - no need for strict validation since it's just UI
$tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general'; ?>
<?php if ($tab === 'general'): ?>
    <?php
    // Simple tab switching check - no need for strict validation since it's just UI
    $sub_page = isset($_GET['sub']) ? sanitize_text_field(wp_unslash($_GET['sub'])) : 'commands'; ?>
    <ul class="subsubsub">
        <li>
            <a href="?page=hz_discord_bot_tiny"
               class="<?php echo $sub_page === 'commands' ? 'current' : ''; ?>">
                <?php esc_attr_e('Commands', 'hz-discord-bot-tiny'); ?>
            </a>
        </li>
        |
        <li>
            <a href="?page=hz_discord_bot_tiny&sub=order_note"
               class="<?php echo $sub_page === 'order_note' ? 'current' : ''; ?>">
                <?php esc_attr_e('Notify', 'hz-discord-bot-tiny'); ?>
            </a>
        </li>
        |
        <li>
            <a href="?page=hz_discord_bot_tiny&sub=settings"
               class="<?php echo $sub_page === 'settings' ? 'current' : ''; ?>">
                <?php esc_attr_e('Settings', 'hz-discord-bot-tiny'); ?>
            </a>
        </li>
    </ul>
    <div class="clear"></div>
    <?php
    if ($sub_page === 'commands') :?>
        <form action="options.php" method="post">
            <?php $this->display_bot_commands_page(); ?>
        </form>
    <?php elseif ($sub_page === 'settings') : ?>
        <form action="options.php" method="post">
            <?php $this->display_bot_settings_page(); ?>
        </form>
    <?php else : ?>
        <?php include_once 'template/notify_form_handler.php'; ?>
        <form action="options.php" method="post">
            <?php $this->display_bot_notify_page(); ?>
        </form>
    <?php endif; ?>
<?php else : ?>
    <?php include_once 'template/webhook_form_handler.php'; ?>
    <form action="options.php" method="post">
        <?php $this->display_webhook_page(); ?>
    </form>
<?php endif; ?>
