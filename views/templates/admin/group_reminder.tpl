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
*}{**
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
<div class="panel col-lg-12">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {l s='Number of days for reminder' mod='mediafinanz'}
    </div>
    <div class="table-responsive clearfix">
        <span id="helpBlock" class="help-block">{l s='Please specify payment due for different customer groups' mod='mediafinanz'}</span>
        {if isset($groupreminder_data)}
        {foreach $groupreminder_data AS $index => $groupreminder}
        <div class="form-group">
           <label class="control-label col-lg-3">{$groupreminder.name|escape:'html':'UTF-8'} ({l s='Days' mod='mediafinanz'})</label>
           <div class="col-lg-3 ">
               <input type="text" size="3" name="groupReminderDays[{$groupreminder.id_group|escape:'html':'UTF-8'}]" value="{$groupreminder.value|escape:'html':'UTF-8'}" />
           </div>
        </div>
        {/foreach}
        {/if}
    </div>
</div>