<?php $saved_blocks = get_option('hz_discord_bot_notice_form_blocks', array()); ?>
<div id="form-container">
    <?php foreach ($saved_blocks as $index => $block) : ?>
        <div class="form-block">
            <table>
                <tr class="hz_discord_webhook_form_blocks_status">
                    <th><?php esc_attr_e('The status changes to','hz-discord-bot-tiny');?></th>
                    <td>
                        <div class="checkbox-group">
                            <?php $order_statuses = wc_get_order_statuses(); ?>
                            <select name="hz_discord_bot_notice_form_blocks[<?php echo esc_attr($index); ?>][status]">
                                <?php foreach ($order_statuses as $status_key => $status_label): ?>
                                    <option value="<?php echo esc_attr($status_key); ?>"
                                        <?php echo in_array($status_key, $block['status'] ?? array()) ? 'selected' : ''; ?>>
                                        <?php echo esc_attr(wc_get_order_status_name($status_key)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_attr_e('Payment Gateways', 'hz-discord-bot-tiny'); ?></th>
                    <td>
                        <div class="checkbox-group">
                            <?php $all_payment_gateways = WC()->payment_gateways->payment_gateways();?>
                            <?php foreach ($all_payment_gateways as $payment_gateway): ?>
                                <label>
                                    <input type="checkbox"
                                           name="hz_discord_bot_notice_form_blocks[<?php echo esc_attr($index); ?>][payment][]"
                                           value="<?php echo esc_attr($payment_gateway->id); ?>"
                                        <?php echo in_array($payment_gateway->id, $block['payment'] ?? array()) ? 'checked' : ''; ?>>
                                    <?php echo esc_attr($payment_gateway->title); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_attr_e('Send to Channel','hz-discord-bot-tiny');?></th>
                    <td>
                        <textarea name="hz_discord_bot_notice_form_blocks[<?php echo esc_attr($index); ?>][channel]"><?php echo esc_textarea($block['channel'] ?? ''); ?></textarea>
                        <p class="description"><?php esc_attr_e('Enter the channel ID','hz-discord-bot-tiny');?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_attr_e('Message','hz-discord-bot-tiny');?></th>
                    <td>
                        <textarea name="hz_discord_bot_notice_form_blocks[<?php echo esc_attr($index); ?>][message]"><?php echo esc_textarea($block['message'] ?? ''); ?></textarea>
                    </td>
                </tr>
            </table>

            <button type="button" class="button remove-block"><?php esc_attr_e('Delete', 'hz-discord-bot-tiny'); ?></button>
        </div>
    <?php endforeach; ?>
</div>

<button type="button" id="add-block" class="button"><?php esc_attr_e('Add a new group', 'hz-discord-bot-tiny'); ?></button>

