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
<ul class="idTabs mediafinanz-tabs clearfix">
    <li><a href="#settings_pane" class="{if !isset($active_registration) || $active_registration != true}selected{/if}">{l s='Settings' mod='mediafinanz'}</a></li>
    <li><a href="#registration_pane" class="{if isset($active_registration) && $active_registration == true}selected{/if}">{l s='Registration' mod='mediafinanz'}</a></li>
</ul>
<div id="settings_pane" class="mediafinanz-tabs-div">{$form_settings_html}</div>
<div id="registration_pane" class="mediafinanz-tabs-div">{$registration_wrapper_html}</div>