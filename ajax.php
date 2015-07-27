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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(_PS_MODULE_DIR_.'mediafinanz/classes/MediafinanzNewMessage.php');

if (Employee::checkPassword(Tools::getValue('iem'), Tools::getValue('iemp')))
{
	$context = Context::getContext();

	$context->employee = new Employee(Tools::getValue('iem'));
	$context->cookie->passwd = Tools::getValue('iemp');

	if (Tools::isSubmit('updateNewMessages'))
		die(MediafinanzNewMessage::updateNewMessages());
}
else
	die(Tools::displayError('Please log in first'));
