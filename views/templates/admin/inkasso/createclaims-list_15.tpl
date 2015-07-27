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
<legend>
    {l s='New claims' mod='mediafinanz'}
</legend>
<form method="post" id="createclaims_form" class="defaultForm">

<table class="table createclaims" style="width: 100%;">
<tbody>
{assign var="showCreateButton" value="false"}
{if count($createclaims_data)}
    {foreach $createclaims_data AS $index => $tr}
        <tr class="{if $tr@iteration is odd by 1}odd{/if}">
            {if $tr.accepted_for_claiming == true}
            {assign var="showCreateButton" value="true"}
            <td>
                <div style="width: 30%;">
                    {l s='Shop' mod='mediafinanz'}: {$tr.id_shop|escape:'htmlall':'UTF-8'}
                    <br>
                    {l s='Configuration completed' mod='mediafinanz'}: {$tr.configuration_completed|escape:'htmlall':'UTF-8'}
                    <br>
                    {l s='Mode' mod='mediafinanz'}: {$tr.mode_for_shop|escape:'htmlall':'UTF-8'}
                </div>
                <div class="createclaim-mediafinanz-left">
                    <label>{l s='Date of order' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.date_add|escape:'htmlall':'UTF-8'}</p></div>
                    <div class="clear"></div>

                    <label>{l s='Date of reminder' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.date_reminder|escape:'htmlall':'UTF-8'}</p></div>
                    <div class="clear"></div>

                    <label>{l s='Date of last reminder' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.date_lastreminder|escape:'htmlall':'UTF-8'}</p></div>
                    <div class="clear"></div>

                    <label>{l s='Invoice' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.invoice|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][invoice]" value="{$tr.claim.invoice|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Claim type' mod='mediafinanz'}:</label>
                    <div class="margin-form">
                        <select name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][type]">
                            {foreach $claim_types AS $claim_type_id => $claim_type}
                            <option value="{$claim_type_id|escape:'htmlall':'UTF-8'}"{if $claim_type_id == $tr.claim.type}selected{/if}>{$claim_type|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Reason' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.reason|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][reason]" value="{$tr.claim.reason|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Original value' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.display_originalvalue|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][originalvalue]" value="{$tr.claim.originalvalue|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Overdue fees' mod='mediafinanz'}:</label>
                    <div class="margin-form"><input type="text" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][overduefees]" value="{$tr.claim.overduefees|escape:'htmlall':'UTF-8'}" onkeyup="this.value = this.value.replace(/,/g, '.');"> {$currency->sign|escape:'htmlall':'UTF-8'}</div>
                    <div class="clear"></div>

                    <label>{l s='Date of origin' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.dateoforigin|escape:'htmlall':'UTF-8'}</p>
                        {*<input type="hidden" name="claim[{$tr.id_order}][dateoforigin]" value="{$tr.claim.dateoforigin}">*}
                        <div class="input-group">
                            <input class="datepicker" type="text" value="{$tr.claim.dateoforigin|escape:'htmlall':'UTF-8'}" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][dateoforigin]">
                        </div>
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Date of last reminder' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.dateoflastreminder|escape:'htmlall':'UTF-8'}</p>
                        {*<input type="hidden" name="claim[{$tr.id_order}][dateoflastreminder]" value="{$tr.claim.dateoflastreminder}">*}
                        <div class="input-group">
                            <input class="datepicker" type="text" value="{$tr.claim.dateoflastreminder|escape:'htmlall':'UTF-8'}" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][dateoflastreminder]">
                        </div>
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Note' mod='mediafinanz'}:</label>
                    <div class="margin-form"><textarea name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][note]">{$tr.claim.note|escape:'htmlall':'UTF-8'}</textarea></div>
                    <div class="clear"></div>
                </div>
                <div class="createclaim-mediafinanz-right">
                    <label>{l s='ID' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.id|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][id]" value="{$tr.debtor.id|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Salutation' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.address|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][address]" value="{$tr.debtor.address|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='First name' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.firstname|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][firstname]" value="{$tr.debtor.firstname|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Last name' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.lastname|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][lastname]" value="{$tr.debtor.lastname|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Company' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.company|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][company]" value="{$tr.debtor.company|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Street' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.street|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][street]" value="{$tr.debtor.street|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Postcode' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.postcode|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][postcode]" value="{$tr.debtor.postcode|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='City' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.city|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][city]" value="{$tr.debtor.city|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Country' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.country|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][country]" value="{$tr.debtor.country|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Telephone 1' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.telephone1|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][telephone1]" value="{$tr.debtor.telephone1|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='Telephone 2' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.telephone2|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][telephone2]" value="{$tr.debtor.telephone2|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                    <label>{l s='E-mail' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.debtor.email|escape:'htmlall':'UTF-8'}</p>
                        <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][email]" value="{$tr.debtor.email|escape:'htmlall':'UTF-8'}">
                    </div>
                    <div class="clear"></div>

                </div>
            </td>
            {else}
            <td>
                <div class="createclaim-mediafinanz-left">
                    <label>{l s='Invoice' mod='mediafinanz'}:</label>
                    <div class="margin-form"><p class="form-control-static">{$tr.claim.invoice|escape:'htmlall':'UTF-8'}</p></div>
                    <div class="clear"></div>

                    {if $tr.claim.file_number > 0}
                        <label>{l s='File number' mod='mediafinanz'}:</label>
                        <div class="margin-form"><p class="form-control-static">{$tr.claim.file_number|escape:'htmlall':'UTF-8'}</p></div>
                        <div class="clear"></div>
                    {/if}
                </div>
                <div class="createclaim-mediafinanz-right">
                        {if $tr.claim.file_number > 0}
                            {l s='Claim had been created already' mod='mediafinanz'}
                        {elseif $tr.supported_currency_by_shop == false}
                            {l s='Euro currency is not active for current shop' mod='mediafinanz'}
                        {elseif $tr.supported_currency == false }
                            {l s='Currency of order is not Euro' mod='mediafinanz'}
                        {/if}
                    <div class="clear"></div>
                        {if $tr.claim.file_number > 0}
                            <a class="button" style="float:right;" href="{$link->getAdminLink('AdminInkasso')|escape:'html':'UTF-8'}&amp;id_mf_claim={$tr.claim.id|escape:'htmlall':'UTF-8'}&amp;viewmf_claims">
                                <i class="icon-search-plus"></i>
                                {l s='View' mod='mediafinanz'}
                            </a>
                        {/if}
                    <div class="clear"></div>
                </div>
                </td>
                {/if}
        </tr>
    {/foreach}
{else}
    <tr>
        <td class="list-empty" colspan="5">
            <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No records found' mod='mediafinanz'}
            </div>
        </td>
    </tr>
{/if}
</tbody>
</table>


<hr>
<a class="button" href="{$link->getAdminLink('AdminOrders')|escape:'html':'UTF-8'}"  style="float: left;">
    {l s='Back to list' mod='mediafinanz'}
</a>
{if $showCreateButton == 'true'}
    <button id="createclaims_form_submit_btn" class="button" name="submitCreateClaims" value="1" type="submit" style="float: right;">
        {l s='Create and register' mod='mediafinanz'}
    </button>
{/if}
</form>
</fieldset>