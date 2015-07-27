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
<div class="alert alert-info">
    <h4>{l s='A cron job for changing order statuses and sending reminders automatically should be added.' mod='mediafinanz'}</h4>
    <p>{l s='For this purposals, "Cron tasks manager" prestashop module or cronjob section of hosting panel or Unix crontab file can be used.' mod='mediafinanz'}</p>
	
	<ol>
		<li><em>{l s='With Prestashop Cron Task Manager' mod='mediafinanz'}</em></li>
		<li><em>{l s='With a a server Customer Panel' mod='mediafinanz'}</em></li>
		<li><em>{l s='With a Unix Crontab file on your server' mod='mediafinanz'}</em></li>
	</ol>
    <p>{l s='This cron job will changes status of orders and send reminders every night at 1:00am.' mod='mediafinanz'}</p>
    <p><code>1 * * * * php -f {$cron_path|escape:'mail':'UTF-8'}cron_reminder.php</code></p>
	<p>
    <em>{l s='Cron is a job scheduler for Unix-based systems and it\'s a very handy tool, as you can schedule some routine tasks to run automatically, no matter if you or anyone else is present on your website: as long as the server hosting your site is running, cron will do it\'s job. To activate cron for this module, add the line below to your crontab file.' mod='mediafinanz'}</em>
    <em>{l s='You can edit a crontab record with following methods:' mod='mediafinanz'}</em>
</p>
</div>