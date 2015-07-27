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
<div class="panel">
    <div class="panel-heading">
        <i class="icon-envelope-alt"></i>
        {l s='New claims' mod='mediafinanz'}
    </div>
    <form method="post" id="createclaims_form" class="defaultForm">
    <div class="table-responsive-row clearfix">
        <table class="table">
            <tbody>
            {assign var="showCreateButton" value="false"}
            {if count($createclaims_data)}
                {foreach $createclaims_data AS $index => $tr}
                    <tr class="{if $tr@iteration is odd by 1}odd{/if}">
                        {if $tr.accepted_for_claiming == true}
                        {assign var="showCreateButton" value="true"}
                        <td>
                            <div class="form-horizontal panel">
                                <div class="row alert-info">
                                    <div class="col-lg-4">
                                        {l s='Shop' mod='mediafinanz'}: {$tr.id_shop|escape:'htmlall':'UTF-8'}
                                    </div>
                                    <div class="col-lg-4">
                                        {l s='Configuration completed' mod='mediafinanz'}: {$tr.configuration_completed|escape:'htmlall':'UTF-8'}
                                    </div>
                                    <div class="col-lg-4">
                                        {l s='Mode' mod='mediafinanz'}: {$tr.mode_for_shop|escape:'htmlall':'UTF-8'}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Date of order' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.date_add|escape:'htmlall':'UTF-8'}</p></div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Date of reminder' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.date_reminder|escape:'htmlall':'UTF-8'}</p></div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Date of last reminder' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.date_lastreminder|escape:'htmlall':'UTF-8'}</p></div>
                                        </div>

                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Invoice' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.claim.invoice|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][invoice]" value="{$tr.claim.invoice|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Claim type' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9">
                                                <select name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][type]" class="fixed-width-xl">
                                                    {foreach $claim_types AS $claim_type_id => $claim_type}
                                                    <option value="{$claim_type_id|escape:'htmlall':'UTF-8'}"{if $claim_type_id == $tr.claim.type}selected{/if}>{$claim_type|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Reason' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.claim.reason|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][reason]" value="{$tr.claim.reason|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Original value' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.claim.display_originalvalue|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][originalvalue]" value="{$tr.claim.originalvalue|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Overdue fees' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9">
                                                <div class="input-group">
                                                <span class="input-group-addon"> {$currency->sign|escape:'htmlall':'UTF-8'}</span>
                                                <input type="text" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][overduefees]" value="{$tr.claim.overduefees|escape:'htmlall':'UTF-8'}" class="fixed-width-xl" onkeyup="this.value = this.value.replace(/,/g, '.');">
                                                </div>
                                            </div>
                                        </div>
                                            <div class="row">
                                                <label class="control-label col-lg-3">{l s='Date of origin' mod='mediafinanz'}:</label>
                                                <div class="col-lg-9"><p class="form-control-static">{$tr.claim.dateoforigin|escape:'htmlall':'UTF-8'}</p>
                                                    {*<input type="hidden" name="claim[{$tr.id_order}][dateoforigin]" value="{$tr.claim.dateoforigin}">*}
                                                    <div class="input-group fixed-width-xl">
                                                        <input class="datepicker" type="text" value="{$tr.claim.dateoforigin|escape:'htmlall':'UTF-8'}" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][dateoforigin]">
                                                        <div class="input-group-addon">
                                                            <i class="icon-calendar-o"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <label class="control-label col-lg-3">{l s='Date of last reminder' mod='mediafinanz'}:</label>
                                                <div class="col-lg-9"><p class="form-control-static">{$tr.claim.dateoflastreminder|escape:'htmlall':'UTF-8'}</p>
                                                    {*<input type="hidden" name="claim[{$tr.id_order}][dateoflastreminder]" value="{$tr.claim.dateoflastreminder}">*}
                                                    <div class="input-group fixed-width-xl">
                                                        <input class="datepicker" type="text" value="{$tr.claim.dateoflastreminder|escape:'htmlall':'UTF-8'}" name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][dateoflastreminder]">
                                                        <div class="input-group-addon">
                                                            <i class="icon-calendar-o"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Note' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><textarea name="claim[{$tr.id_order|escape:'htmlall':'UTF-8'}][note]" class="textarea-autosize">{$tr.claim.note|escape:'htmlall':'UTF-8'}</textarea></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='ID' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.id|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][id]" value="{$tr.debtor.id|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Salutation' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.address|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][address]" value="{$tr.debtor.address|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='First name' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.firstname|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][firstname]" value="{$tr.debtor.firstname|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Last name' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.lastname|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][lastname]" value="{$tr.debtor.lastname|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Company' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.company|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][company]" value="{$tr.debtor.company|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Street' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.street|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][street]" value="{$tr.debtor.street|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Postcode' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.postcode|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][postcode]" value="{$tr.debtor.postcode|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='City' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.city|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][city]" value="{$tr.debtor.city|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Country' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.country|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][country]" value="{$tr.debtor.country|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Telephone 1' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.telephone1|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][telephone1]" value="{$tr.debtor.telephone1|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Telephone 2' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.telephone2|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][telephone2]" value="{$tr.debtor.telephone2|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='E-mail' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.debtor.email|escape:'htmlall':'UTF-8'}</p>
                                                <input type="hidden" name="debtor[{$tr.id_order|escape:'htmlall':'UTF-8'}][email]" value="{$tr.debtor.email|escape:'htmlall':'UTF-8'}">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </td>
                        {else}
                        <td>
                            <div class="form-horizontal">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='Invoice' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.claim.invoice|escape:'htmlall':'UTF-8'}</p>
                                            </div>
                                        </div>
                                        {if $tr.claim.file_number > 0}
                                        <div class="row">
                                            <label class="control-label col-lg-3">{l s='File number' mod='mediafinanz'}:</label>
                                            <div class="col-lg-9"><p class="form-control-static">{$tr.claim.file_number|escape:'htmlall':'UTF-8'}</p>
                                            </div>
                                        </div>
                                        {/if}
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            {if $tr.claim.file_number > 0}
                                                {l s='Claim had been created already' mod='mediafinanz'}
                                            {elseif $tr.supported_currency_by_shop == false}
                                                {l s='Euro currency is not active for current shop' mod='mediafinanz'}
                                            {elseif $tr.supported_currency == false }
                                                {l s='Currency of order is not Euro' mod='mediafinanz'}
                                            {/if}
                                        </div>
                                        <div class="row">
                                            {if $tr.claim.file_number > 0}
                                            <a class="btn btn-default pull-right" href="{$link->getAdminLink('AdminInkasso')|escape:'html':'UTF-8'}&amp;id_mf_claim={$tr.claim.id|escape:'htmlall':'UTF-8'}&amp;viewmf_claims">
                                                <i class="icon-search-plus"></i>
                                                {l s='View' mod='mediafinanz'}
                                            </a>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    </div>
    <div class="panel-footer">
        {if $showCreateButton == 'true'}
        <button id="createclaims_form_submit_btn" class="btn btn-default pull-right" name="submitCreateClaims" value="1" type="submit">
            <i class="process-icon-save"></i>
            {l s='Create and register' mod='mediafinanz'}
        </button>
        {/if}
        <a class="btn btn-default" href="{$link->getAdminLink('AdminOrders')|escape:'html':'UTF-8'}">
            <i class="process-icon-back"></i>
            {l s='Back to list' mod='mediafinanz'}
        </a>
    </div>
    </form>
</div>