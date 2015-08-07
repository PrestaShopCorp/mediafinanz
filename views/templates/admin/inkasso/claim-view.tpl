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
{if count($claim_options)}
    {foreach $claim_options AS $index => $claim_option}
<div class="row block_action_form" id="block_{$claim_option|escape:'html':'UTF-8'}" style="display:{if $claim_option=='bookDirectPayment' && isset($smarty.post.submitBookDirectPayment)}block{else}none{/if};">
    <div class="col-lg-6">
        <div class="panel">
            {if ($claim_option == 'close')}
                <form id="form_{$claim_option|escape:'html':'UTF-8'}" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to close claim?' mod='mediafinanz'}');">
                    <div class="row">
                        <div class="col-lg-12">
                            <input type="hidden" name="submitCloseClaim" value="true">
                            {*<button id="submitCloseClaim" class="btn btn-default pull-left" name="submitCloseClaim" type="submit">{l s='Close claim' mod='mediafinanz'}</button>*}
                        </div>
                     </div>
                </form>
            {elseif ($claim_option == 'lawyer')}
                <form id="form_{$claim_option|escape:'html':'UTF-8'}" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to assign claim to lawyer?' mod='mediafinanz'}');">
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Instruction' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <select name="instruction">
                                <option value="0">{l s='Select instruction' mod='mediafinanz'}</option>
                                {foreach $instructions_for_assigning AS $instruction_id => $instruction}
                                <option value="{$instruction_id|escape:'html':'UTF-8'}"{if isset($smarty.post.instruction) && ($instruction_id == $smarty.post.instruction)}selected{/if}>{$instruction|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="submitAssignClaimToLawyer" class="btn btn-default pull-left" name="submitAssignClaimToLawyer" type="submit">{l s='Assign claim to lawyer' mod='mediafinanz'}</button>
                        </div>
                    </div>
                </form>
            {elseif ($claim_option == 'bookDirectPayment')}
                <form class="form-horizontal" id="form_{$claim_option|escape:'html':'UTF-8'}" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to book direct payment?' mod='mediafinanz'}');">
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Date of payment' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <div class="input-group fixed-width-xl">
                                <input class="datepicker" type="text" value="{if isset($smarty.post.dateOfPayment)}{$smarty.post.dateOfPayment|escape:'html':'UTF-8'}{/if}" name="dateOfPayment">
                                <div class="input-group-addon">
                                    <i class="icon-calendar-o"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Paid amount' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <input class="form-control fixed-width-sm" type="text" name="paidAmount" value="{if isset($smarty.post.paidAmount)}{$smarty.post.paidAmount|escape:'html':'UTF-8'}{/if}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="submitBookDirectPayment" class="btn btn-default pull-right" name="submitBookDirectPayment" type="submit">{l s='Book direct payment' mod='mediafinanz'}</button>
                        </div>
                    </div>
                </form>
            {elseif ($claim_option == 'factoring')}
                <form id="form_{$claim_option|escape:'html':'UTF-8'}" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to request factoring?' mod='mediafinanz'}');">
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="submitRequestFactoring" class="btn btn-default pull-left" name="submitRequestFactoring" type="submit">{l s='Request factoring' mod='mediafinanz'}</button>
                        </div>
                    </div>
                </form>
            {elseif ($claim_option == 'longTermObservation')}
                <form id="form_{$claim_option|escape:'html':'UTF-8'}" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to enable long-term observation?' mod='mediafinanz'}');">
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="submitLongTermObservation" class="btn btn-default pull-left" name="submitLongTermObservation" type="submit">{l s='Enable long-term observation' mod='mediafinanz'}</button>
                        </div>
                    </div>
                </form>
            {elseif ($claim_option == 'addressIdentification')}
                <form id="form_{$claim_option|escape:'html':'UTF-8'}" class="form-horizontal" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to perform address identification?' mod='mediafinanz'}');">
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Suspect (Address)' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <input type="text" name="suspect">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Reference' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <input type="text" name="reference">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Justification' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <input type="text" name="justification">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">{l s='Mission depth' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <select name="missionDepth">
                                <option value="0">{l s='Select mission depth' mod='mediafinanz'}</option>
                                {foreach $mission_depthes AS $mission_depth_id => $mission_depth}
                                    <option value="{$mission_depth_id|escape:'html':'UTF-8'}"{if isset($smarty.post.missionDepth) && ($mission_depth_id == $smarty.post.missionDepth)}selected{/if}>{$mission_depth|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="submitAddressIdentification" class="btn btn-default pull-left" name="submitAddressIdentification" type="submit">{l s='Perform address identification' mod='mediafinanz'}</button>
                        </div>
                    </div>
                </form>
            {/if}
        </div>
    </div>
</div>
{/foreach}
{/if}

<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="row">
                <div class="col-lg-12">
                    <button id="backToList" style="margin-right:15px;" class="btn btn-default pull-left" name="backToList" type="button">
                        <i class="process-icon-back"></i>
                        {l s='Back to list' mod='mediafinanz'}
                    </button>
                    {if count($claim_options)}
                        {foreach $claim_options AS $index => $claim_option}
                            {if ($claim_option == 'close')}
                                <button id="closeClaim" style="margin-right:15px;" class="btn btn-default pull-left" name="closeClaim" type="button">
                                    <i class="process-icon-close"></i>
                                    {l s='Close claim' mod='mediafinanz'}
                                </button>
                            {elseif ($claim_option == 'bookDirectPayment')}
                                <button id="bookDirectPayment" style="margin-right:15px;" class="btn btn-default pull-left" name="bookDirectPayment" type="button">
                                    <i class="process-icon-payment"></i>
                                    {l s='Book direct payment' mod='mediafinanz'}
                                </button>
                            {/if}
                        {/foreach}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-paperclip"></i> {l s='Claim' mod='mediafinanz'} <span class="badge">{l s='Nr.' mod='mediafinanz'} {$claim_details->id_order|escape:'mail':'UTF-8'}</span>
                <span class="badge">{$claim_details->file_number|escape:'html':'UTF-8'}</span>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Order ID' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{$claim_details->id_order|escape:'html':'UTF-8'}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='File number' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {$claim_details->file_number|escape:'html':'UTF-8'}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Date of creating' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{dateFormat date=$claim_details->date_add full=1}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Firstname' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {$claim_details->firstname|escape:'html':'UTF-8'}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Lastname' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {$claim_details->lastname|escape:'html':'UTF-8'}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Date of last status change' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {dateFormat date=$claim_details->date_change full=1}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Status text' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {$claim_details->status_text|escape:'html':'UTF-8'}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Status details' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static"> {$claim_details->status_details|escape:'html':'UTF-8'}</p></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-money"> </i> {l s='Accounting summary' mod='mediafinanz'}
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Total debts' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{displayPrice price=$claim_details->totalDebts}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Paid' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{displayPrice price=$claim_details->paid}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Outstanding' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{displayPrice price=$claim_details->outstanding}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Current payout' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{displayPrice price=$claim_details->currentPayout}</p></div>
            </div>
            <div class="row">
                <label class="control-label col-lg-4">{l s='Sum of payout' mod='mediafinanz'}:</label>
                <div class="col-lg-8"><p class="form-control-static">{displayPrice price=$claim_details->sumPayout}</p></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-paper"> </i> {l s='Payout history' mod='mediafinanz'} <span class="badge">{count($claim_details->payoutHistory)|escape:'mail':'UTF-8'}</span>
            </div>
            <div class="table-responsive">
                <table id="payout_history_table" class="table">
                    <thead>
                    <tr>
                        <th>
                            <span class="title_box ">{l s='Date' mod='mediafinanz'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Total' mod='mediafinanz'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Payout number' mod='mediafinanz'}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {if count($claim_details->payoutHistory)}{foreach $claim_details->payoutHistory AS $index => $payout}
                    <tr class="{if $payout@iteration is odd by 1}odd{/if}">
                        <td>{dateFormat date=$payout.date full=0}</td>
                        <td>{displayPrice price=$payout.total}</td>
                        <td>{$payout.payoutNumber|escape:'html':'UTF-8'}</td>
                    </tr>
                    {/foreach}{/if}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-history"></i> {l s='Claim history' mod='mediafinanz'}
            </div>
            <div class="table-responsive">
                <table id="payout_history_table" class="table">
                    <thead>
                    <tr>
                        <th>
                            <span class="title_box ">{l s='Time' mod='mediafinanz'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Subject' mod='mediafinanz'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Details' mod='mediafinanz'}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {if count($claim_history)}{foreach $claim_history AS $index => $claim_history_entity}
                        <tr class="{if $claim_history_entity@iteration is odd by 1}odd{/if}">
                            <td>{dateFormat date=date('Y-m-d H:i:s', strtotime($claim_history_entity->time)) full=1}</td>
                            <td>{$claim_history_entity->subject|escape:'html':'UTF-8'}</td>
                            <td>{if isset($claim_history_entity->details)}{$claim_history_entity->details|escape:'html':'UTF-8'}{/if}</td>
                        </tr>
                    {/foreach}{/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-envelope"></i> {l s='Messages' mod='mediafinanz'} <span class="badge">{count($claim_message_history)|escape:'mail':'UTF-8'}</span>
            </div>
            <div id="messages" class="well hidden-print">
            <form method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" onsubmit="return confirm('{l s='Do you want to send this message to the mediafinanz?' mod='mediafinanz'}');">
                <div id="message" class="form-horizontal">
                    <div class="form-group">
                        <label for="txt_msg" class="control-label col-lg-3">{l s='Message' mod='mediafinanz'}</label>
                        <div class="col-lg-9">
                            <textarea id="txt_msg" class="textarea-autosize" name="message" style="overflow: hidden; word-wrap: break-word; resize: none; height: 61px;"></textarea>
                            <p id="nbchars"></p>
                        </div>
                    </div>
                    <button id="submitMessage" class="btn btn-primary pull-right" name="submitMessage" type="submit">{l s='Send message' mod='mediafinanz'}</button>
                    <br>
                </div>
            </form>
            </div>
            {if count($claim_message_history)}
                {foreach $claim_message_history AS $index => $claim_message_history_entity}
                <div id="messages" class="panel hidden-print">
                        <div class="panel-heading">
                        <span class="badge">{l s='Sender' mod='mediafinanz'}: {$claim_message_history_entity->sender|escape:'html':'UTF-8'}</span> <span class="badge">{dateFormat date=date('Y-m-d H:i:s', strtotime($claim_message_history_entity->time)) full=1}</span>
                        </div>
                        {nl2br($claim_message_history_entity->text)|escape:'mail':'UTF-8'}
                </div>
                {/foreach}
            {/if}
        </div>
    </div>
</div>