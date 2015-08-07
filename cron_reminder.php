<?php
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

require(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');
require_once(dirname(__FILE__).'/mediafinanz.php');

if (Tools::isPHPCLI() && isset($argc) && isset($argv))
	Tools::argvToGET($argc, $argv);

if (Tools::getValue('secure_key') != Configuration::getGlobalValue('MEDIAFINANZ_SECURE_KEY'))
	die('Secure key is wrong');
else
{
	$module = new Mediafinanz();

	$return = true;
	$return &= $module->sendReminders();


	$log_type = 'cron';
	$message = 'Return: '.print_r($return, true);
	Mediafinanz::logToFile($message, $log_type);
}