<br><br>
<?php _e('You can use these tags in the order message template:(click to copy)', 'hz-discord-bot'); ?>
<?php $order_details = Hz_Discord_Bot_Details::get_order_details(); ?>
<ul class="hz-discord-bot-tiny-message-ctrl">
    <?php foreach ($order_details as $key => $label) : ?>
        <li>
            <span>{{<?php echo $key; ?>}}</span>
            <div><?php _e('Copy', 'hz-discord-bot'); ?></div>
        </li>
    <?php endforeach; ?>
</ul>
<?php if ($_GET['page'] == 'hz_discord_bot_tiny' && (isset($_GET['tab']) && $_GET['tab'] && $_GET['tab'] == 'webhook')): ?>
    <hr>
    <?php _e('You can use these tags when you have new users:(click to copy)', 'hz-discord-bot'); ?>
    <?php $user_details = Hz_Discord_Bot_Details::get_user_details(); ?>
    <ul class="hz-discord-bot-tiny-message-ctrl">
        <?php foreach ($user_details as $key => $label) : ?>
            <li>
                <span>{{<?php echo $key; ?>}}</span>
                <div><?php _e('Copy', 'hz-discord-bot'); ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<div class="alert-container">
    <div>
        <?php _e('Copied successfully', 'hz-discord-bot'); ?>
    </div>
</div>