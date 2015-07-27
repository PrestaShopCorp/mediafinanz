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

<div class="registration-mediafinanz-left">
    <div class="info">
        <h4>{l s='As a new customer, I want to create a mediafinanz account and activate the service in my prestashop store' mod='mediafinanz'}</h4>
        <p>{l s='Activate your account in just three steps now and benefit of the collection services inside your store' mod='mediafinanz'}</p>
        <p><strong>{l s='Step 1' mod='mediafinanz'}</strong></p>
        <p>{l s='Please fill in the form below. After a successfull registration you will recieve your new access key by email' mod='mediafinanz'}.</p>

        <p><strong>{l s='Step 2' mod='mediafinanz'}</strong></p>
        <p>{l s='To connect your mediafinanz account with your prestashop store please log in to your mediafinanz customer panel:' mod='mediafinanz'} <a href="https://mandos.mediafinanz.de/"><i class="icon-external-link"></i> {l s='mediafinanz cPanel login' mod='mediafinanz'}</a> </p>

		<p>&nbsp;</p>
        <p>{l s='Please fill in the client ID and licence you find there in your prestashop settings.' mod='mediafinanz'} <a href="https://mandos.mediafinanz.de/"><i class="icon-external-link"></i> {l s='Insert client ID and licence' mod='mediafinanz'}</a>. {l s='Thereby your mediafinanz configuration is completed.' mod='mediafinanz'} </p>

    </div>
    {$registration_form_html}
</div>
<div class="registration-mediafinanz-left">
    <div class="info">
        <h4>{l s='I am already a mediafinanz customer and I want to activate the module in my store.' mod='mediafinanz'}</h4>
        <p>{l s='By following these steps you can activate our collection services in your store' mod='mediafinanz'}</p>
        <p><strong>{l s='Step 1' mod='mediafinanz'}</strong></p>
        <p>{l s='You are already a mediafinanz customer, but your store is still not connected with your mediafinanz account? Please ask for a' mod='mediafinanz'} <a href="https://mandos.mediafinanz.de/api" target="_blank"><i class="icon-external-link"></i> {l s='registration key' mod='mediafinanz'}</a></p>

        <p>{if isset($registration_key) && $registration_key}{l s='Your registration key' mod='mediafinanz'}: <strong>{$registration_key|escape:'html':'UTF-8'}</strong>{/if}
            <form id="generate_key_form" class="defaultForm form-inline" method="post" action="{$action_generate_key_for|escape:'html':'UTF-8'}">
                <button name="submitGenerateRegistrationKey" class="button">{if isset($registration_key) && $registration_key}{l s='Regenerate registration key' mod='mediafinanz'}{else}{l s='Generate registration key' mod='mediafinanz'}{/if}</button>
            </form>
        </p>

        <p><strong>{l s='Step 2' mod='mediafinanz'}</strong></p>
        <p>{l s='Please leave your key in the mediafinanz account and save your changes.' mod='mediafinanz'} <a href="https://mandos.mediafinanz.de/api"><i class="icon-external-link"></i> {l s='Enter the key' mod='mediafinanz'}</a> </p>

		<p>&nbsp;</p>
        <p>{l s='After the key is successfully saved, you will be shown your clinet ID and licence, that you should insert into your prestashop configuration.' mod='mediafinanz'} <a href="https://mandos.mediafinanz.de/"><i class="icon-external-link"></i> {l s='Insert client ID and licence' mod='mediafinanz'}</a>. {l s='Thereby your mediafinanz configuration is completed.' mod='mediafinanz'} </p>

    </div>
</div>
