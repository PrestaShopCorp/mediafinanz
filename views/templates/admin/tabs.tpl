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
<ul class="nav nav-tabs">
    <li class="{if !isset($active_registration) || $active_registration != true}active{/if}"><a data-toggle="tab" href="#settings_pane">{l s='Settings' mod='mediafinanz'}</a></li>
    <li class="{if isset($active_registration) && $active_registration == true}active{/if}"><a data-toggle="tab" href="#registration_pane">{l s='Registration' mod='mediafinanz'}</a></li>
</ul>
<div class="tab-content panel">
    <div id="settings_pane" class="tab-pane {if isset($active_registration) && $active_registration == true}{else}active{/if}"><div class="module_info alert alert-success"><h4>'
            {l s='Ask us!' mod='mediafinanz'}</h4>
            {l s='Do you need support on setting up the mediafinanz feature? Don\'t hesitate to contact the mediafinanz team monday till friday by phone:' mod='mediafinanz'}
            <b>+49 541/2029-110</b>
            {l s='Monday till Friday by phone' mod='mediafinanz'}
            </div>
        {$form_settings_html}</div>
    <div id="registration_pane" class="tab-pane {if isset($active_registration) && $active_registration == true}active{/if}">{$registration_wrapper_html}</div>
</div>