{**
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
*}
{**
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
*}
<fieldset>
    <button id="show_new_messages" class="button"><span>{l s='New messages' mod='mediafinanz'}</span>
        <span class="badge">
            <span id="new_messages_value">{$new_messages_value|escape:'html':'UTF-8'}</span>
        </span>
    </button>
    <br>
    <div id="new_messages_block" class="row" style="display:none;">
        {if count($new_messages)}
            <table id="payout_history_table" class="table">
                <thead>
                <tr>
                    <th>
                        <span class="title_box ">{l s='ID' mod='mediafinanz'}</span>
                    </th>
                    <th>
                        <span class="title_box ">{l s='Date' mod='mediafinanz'}</span>
                    </th>
                    <th>
                        <span class="title_box ">{l s='Order ID' mod='mediafinanz'}</span>
                    </th>
                    <th>
                        <span class="title_box ">{l s='File number' mod='mediafinanz'}</span>
                    </th>
                    <th>
                        <span class="title_box ">{l s='Text' mod='mediafinanz'}</span>
                    </th>
                    <th>
                    </th>
                </tr>
                </thead>
                <tbody>
                {foreach $new_messages AS $index => $message}
                    <tr class="{if $message@iteration is odd by 1}odd{/if}">
                        <td>{$message.id_mf_new_message|escape:'html':'UTF-8'}</td>
                        <td>{dateFormat date=$message.time full=1}</td>
                        <td>{$message.id_order|escape:'html':'UTF-8'}</td>
                        <td>{$message.file_number|escape:'html':'UTF-8'}</td>
                        <td>{$message.text|escape:'html':'UTF-8'}</td>
                        <td><a class="button" href="{$link->getAdminLink('AdminInkasso')|escape:'html':'UTF-8'}&amp;id_mf_claim={$message.id_mf_claim|escape:'html':'UTF-8'}&amp;viewmf_claims">{l s='View claim' mod='mediafinanz'}</a></td>
                    </tr>
                {/foreach}
                <tr class="{if $message@iteration is odd by 1}odd{/if}">
                    <td colspan="6" class="text-center">
                        <a class="button" href="{$link->getAdminLink('AdminInkasso')|escape:'html':'UTF-8'}&amp;viewMessages=true">
                            {l s='Show all messages' mod='mediafinanz'}</a>
                    </td>
                </tr>
                </tbody>
            </table>
        {/if}
    </div>
</fieldset>