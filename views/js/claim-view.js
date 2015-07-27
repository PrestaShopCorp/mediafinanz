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
var mediafinanz_claim_view = {
    init: function() {

        $('#closeClaim').on('click', function(evt){
            evt.preventDefault();

            $('.block_action_form').slideUp();
            $('#form_close').submit();
        });

        $('#bookDirectPayment').on('click', function(evt){
            evt.preventDefault();

            $('.block_action_form').not('#block_bookDirectPayment').hide();

            if ($('#block_bookDirectPayment').is(':hidden')) {
                $('#bookDirectPayment').removeClass('btn-default').addClass('btn-primary');
                $('#block_bookDirectPayment').slideDown();
            } else {
                $('#bookDirectPayment').removeClass('btn-primary').addClass('btn-default');
                $('#block_bookDirectPayment').slideUp();
            }

            return false;
        });

        $('#backToList').on('click', function(evt){
            evt.preventDefault();
            window.location.href = currentIndex + '&token=' + token;
        });


        $('.datepicker').datepicker({
            prevText: '',
            nextText: '',
            dateFormat: 'yy-mm-dd'
        });
    }

}

$(function(){
    mediafinanz_claim_view.init();
})