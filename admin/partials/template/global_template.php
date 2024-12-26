<br><br>
<?php esc_attr_e('You can use these tags in the order message template:(click to copy)', 'hz-discord-bot-tiny'); ?>
<?php $order_details = Hz_Discord_Bot_Details::get_order_details(); ?>
<ul class="hz-discord-bot-tiny-message-ctrl">
    <?php foreach ($order_details as $key => $label) : ?>
        <li>
            <span>{{<?php echo esc_attr($key); ?>}}</span>
            <div><?php esc_attr_e('Copy', 'hz-discord-bot-tiny'); ?></div>
        </li>
    <?php endforeach; ?>
</ul>
<?php
    // Simple tab switching check - no need for strict validation since it's just UI
    $tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
    // Simple tab switching check - no need for strict validation since it's just UI
    $page = isset($_GET['page']) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    if ( $page === 'hz_discord_bot_tiny' && $tab === 'webhook' ):
?>
    <hr>
    <?php esc_attr_e('You can use these tags when you have new users:(click to copy)', 'hz-discord-bot-tiny'); ?>
    <?php $user_details = Hz_Discord_Bot_Details::get_user_details(); ?>
    <ul class="hz-discord-bot-tiny-message-ctrl">
        <?php foreach ($user_details as $key => $label) : ?>
            <li>
                <span>{{<?php echo esc_attr($key); ?>}}</span>
                <div><?php esc_attr_e('Copy', 'hz-discord-bot-tiny'); ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<div class="alert-container">
    <div>
        <?php esc_attr_e('Copied successfully', 'hz-discord-bot-tiny'); ?>
    </div>
</div>