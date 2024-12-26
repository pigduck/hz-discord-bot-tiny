<?php
$saved_blocks = get_option('hz_discord_webhook_form_blocks', array());
?>
<div class="form-block hidden" id="template-block">
    <table>
        <tr>
            <th><?php esc_attr_e('Type', 'hz-discord-bot-tiny'); ?></th>
            <td>
                <select class="hz_discord_webhook_form_blocks_type"
                        name="hz_discord_webhook_form_blocks[template][type]">
                    <?php $type = Hz_Discord_Bot_Details::get_webhooks_type(); ?>
                    <?php foreach ($type as $type_key => $type_label): ?>
                        <option value="<?php echo esc_attr($type_key); ?>">
                            <?php echo esc_attr($type_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="hz_discord_webhook_form_blocks_status hidden">
            <th><?php esc_attr_e('The status changes to','hz-discord-bot-tiny');?></th>
            <td>
                <div class="checkbox-group">
                    <?php $order_statuses = wc_get_order_statuses(); ?>
                    <?php foreach ($order_statuses as $status_key => $status_label): ?>
                        <label>
                            <input type="checkbox"
                                   name="hz_discord_webhook_form_blocks[template][status][]"
                                   value="<?php echo esc_attr($status_key); ?>">
                            <?php echo esc_attr(wc_get_order_status_name($status_key)); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <tr class="hz_discord_webhook_form_blocks_payment">
            <th><?php esc_attr_e('Payment Gateways', 'hz-discord-bot-tiny'); ?></th>
            <td>
                <div class="checkbox-group">
                    <?php $all_payment_gateways = WC()->payment_gateways->payment_gateways();?>
                    <?php foreach ($all_payment_gateways as $payment_gateway): ?>
                        <label>
                            <input type="checkbox"
                                   name="hz_discord_webhook_form_blocks[template][payment][]"
                                   value="<?php echo esc_attr($payment_gateway->id); ?>">
                            <?php echo esc_attr($payment_gateway->title); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <tr>
            <th><?php esc_attr_e('Webhooks','hz-discord-bot-tiny');?></th>
            <td>
                <input type="text" name="hz_discord_webhook_form_blocks[template][webhook]">
            </td>
        </tr>
        <tr>
            <th><?php esc_attr_e('Message','hz-discord-bot-tiny');?></th>
            <td>
                    <textarea name="hz_discord_webhook_form_blocks[template][message]"></textarea>
            </td>
        </tr>
    </table>
    </table>
    <button type="button" class="button remove-block"><?php esc_attr_e('Delete', 'hz-discord-bot-tiny'); ?></button>
</div>
