/**
 * 2015 Mediafinanz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@easymarketing.de so we can send you a copy immediately.
 *
 * @author    silbersaiten www.silbersaiten.de <info@silbersaiten.de>
 * @copyright 2015 Mediafinanz
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
$(document).ready(function() {
    if ($.trim($("#new_messages_value").html()) == '0')
        $("#new_messages_value").parent().hide();

    $("#show_new_messages").click(function() {

        $("#new_messages_block").toggle();

        if ($("#new_messages_block").is(':visible'))
        {
            $.post(
                mediafinanz_ajax,
                {
                    updateNewMessages : "1",
                    iem: iem,
                    iemp: iemp,
                    id_shop: mediafinanz_id_shop
                },
                function(data) {
                    if (data) {
                        $("#new_messages_value").html(0);
                        $("#new_messages_value").parent().hide();
                    }
                }
            );
        }
    });
});