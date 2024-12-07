(($) => {
    $(document).ready(() => {
        const $formContainer = $('#form-container');
        const $templateBlock = $('#template-block');
        const $alertContainer = $('.alert-container');

        const ALERT_TIMEOUT = 3000;

        let blockCount = $formContainer.find('.form-block').length;

        const addBlock = () => {
            const $newBlock = $templateBlock
                .clone()
                .removeClass('hidden')
                .removeAttr('id');

            $newBlock.find('input, textarea, select').each((_, element) => {
                const $element = $(element);
                const name = $element.attr('name')?.replace('template', blockCount);
                $element.attr('name', name);
            });

            $formContainer.append($newBlock);
            blockCount++;
        };

        const copyWithNewAPI = async (text) => {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (err) {
                return false;
            }
        };

        const copyWithLegacyMethod = (text) => {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();

            try {
                const successful = document.execCommand('copy');
                if (!successful) {
                    console.warn('Copy Failure');
                }
            } catch (err) {
                console.error('Copy Failure:', err);
            }

            $temp.remove();
        };

        const copyToClipboard = async (text) => {
            if (navigator.clipboard) {
                const success = await copyWithNewAPI(text);
                if (success) {
                    showAlert();
                    return;
                }
            }

            copyWithLegacyMethod(text);
            showAlert();
        };

        const showAlert = () => {
            $alertContainer
                .show()
                .delay(ALERT_TIMEOUT)
                .fadeOut();
        };

        $('#add-block').on('click', addBlock);

        $formContainer.on('click', '.remove-block', function () {
            $(this).closest('.form-block').remove();
        });

        $(document).on('click', '.hz-discord-bot-tiny-message-ctrl li', function () {
            const contentToCopy = $(this).find('span').text();
            copyToClipboard(contentToCopy);
        });

        $(document).on('click', '#clear_commands', function (e) {
            e.preventDefault();
            const botToken = $('input[name="hz_discord_bot_setting_bot_token"]').val();
            let $this = $(this);
            if (!botToken) {
                alert('Bot token is required');
                return;
            }
            $.ajax({
                url: `${hz_discord_bot_tiny.commands_clear_url}`,
                type: 'POST',
                headers: {
                    'Authorization': `Bot ${botToken}`
                },
                beforeSend: function () {
                    $this.prop('disabled', true).text(hz_discord_bot_tiny_language.clearing);
                },
                success: function () {
                    alert(hz_discord_bot_tiny_language.clear_success);
                },
                complete: function () {
                    $this.prop('disabled', false).text(hz_discord_bot_tiny_language.clear);
                },
                error: function (response) {
                    alert(hz_discord_bot_tiny_language.clear_failed);
                }
            });
        });

        $(document).on('click', '#sync_commands', function (e) {
            e.preventDefault();
            const botToken = $('input[name="hz_discord_bot_setting_bot_token"]').val();
            let $this = $(this);
            if (!botToken) {
                alert('Bot token is required');
                return;
            }
            $.ajax({
                url: `${hz_discord_bot_tiny.commands}`,
                type: 'POST',
                headers: {
                    'Authorization': `Bot ${botToken}`
                },
                beforeSend: function () {
                    $this.prop('disabled', true).text(hz_discord_bot_tiny_language.syncing);
                },
                success: function () {
                    alert(hz_discord_bot_tiny_language.sync_success);
                },
                complete: function () {
                    $this.prop('disabled', false).text(hz_discord_bot_tiny_language.sync);
                },
                error: function (response) {
                    alert(hz_discord_bot_tiny_language.sync_failed);
                }
            });
        });

        $(document).on('change', '.hz_discord_webhook_form_blocks_type', function () {
            const $closestTable = $(this).closest('table');
            const value = $(this).val();
            $closestTable.find('.hz_discord_webhook_form_blocks_status').toggle(value === 'change');
            $closestTable.find('.hz_discord_webhook_form_blocks_payment').toggle(value !== 'register');
        });
    });
})(jQuery);
