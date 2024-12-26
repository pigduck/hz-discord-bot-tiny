<?php $saved_blocks = get_option('hz_discord_bot_setting_form_blocks', array()); ?>
<div id="form-container">
    <div>
        <table style="width: 100%">
            <tr>
                <th class="big-letter">Application ID</th>
                <td>
                    <input type="text" name="hz_discord_bot_setting_form_blocks[aapplicationid]" style="width: 100%" value="<?php echo esc_textarea($saved_blocks['aapplicationid'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th class="big-letter">Public Key</th>
                <td>
                    <input type="text" name="hz_discord_bot_setting_form_blocks[publickey]" style="width: 100%" value="<?php echo esc_textarea($saved_blocks['publickey'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th class="big-letter">Bot Token</th>
                <td>
                    <input type="text" name="hz_discord_bot_setting_form_blocks[bottoken]" style="width: 100%" value="<?php echo esc_textarea($saved_blocks['bottoken'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th class="big-letter">Guild Id</th>
                <td>
                    <input type="text" name="hz_discord_bot_setting_form_blocks[guildid]" style="width: 100%" value="<?php echo esc_textarea($saved_blocks['guildid'] ?? ''); ?>">
                </td>
            </tr>
        </table>
    </div>
</div>
