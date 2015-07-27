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

class MediafinanzNewMessage extends ObjectModel
{
	public $id_order;
	public $id_shop;
	public $file_number;
	public $invoice_number;
	public $text;
	public $time;
	public $date_add;
	public $date_upd;

	public static $definition = array(
		'table' => 'mf_new_messages',
		'primary' => 'id_mf_new_message',
		'fields' => array(
			'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'file_number' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'invoice_number' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'text' => array('type' => self::TYPE_STRING),
			'time' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		),
	);

	public static function getMessages($unread = true, $id_shop = null)
	{
		$context = Context::getContext();

		if ($id_shop == null && Shop::getContext() == Shop::CONTEXT_SHOP)
			$id_shop = Shop::getContextShopID();

		if ($id_shop > 0)
		{
			$where = array();
			$where[] = 'a.`id_shop`='.(int)$id_shop;
			$where[] = 'c.`sandbox`='.Configuration::get('MEDIAFINANZ_SANDBOX', null, null, (int)$id_shop);

			if ($unread == true)
			{
				$where[] = 'a.`id_mf_new_message` > IFNULL((SELECT IFNULL(id_last_new_message, 0) FROM `'.
							_DB_PREFIX_.'mf_employee` WHERE `id_shop`='.(int)$id_shop.' AND `id_employee` = '.(int)$context->cookie->id_employee.' ), 0)';
			}
			else
			{
				$where[] = 'a.`id_mf_new_message` > IFNULL((SELECT IFNULL(id_first_new_message, 0) FROM `'.
							_DB_PREFIX_.'mf_employee` WHERE `id_shop`='.(int)$id_shop.' AND `id_employee` = '.(int)$context->cookie->id_employee.' ), 0)';
			}

			return Db::getInstance()->executeS('SELECT a.`id_mf_new_message`, c.`id_mf_claim`, a.`id_order`, a.`file_number`, a.`text`, a.`time` FROM `'.
											_DB_PREFIX_.self::$definition['table'].'` a LEFT JOIN `'._DB_PREFIX_.
											'mf_claims` c ON (c.`id_order` = a.`id_order` AND c.`id_shop` = a.`id_shop`) '.
											(count($where)?'WHERE '.implode(' AND ', $where):'').' ORDER BY a.`id_mf_new_message` DESC');
		}
		return false;
	}

	public static function updateNewMessages($id_shop = null)
	{
		$context = Context::getContext();

		if ($id_shop == null && Shop::getContext() == Shop::CONTEXT_SHOP)
			$id_shop = Shop::getContextShopID();

		if ($id_shop > 0)
		{
			if (($mf_employee = Db::getInstance()->getRow('SELECT id_employee, id_last_new_message, id_first_new_message FROM `'.
											_DB_PREFIX_.'mf_employee` WHERE `id_shop`='.(int)$id_shop.
											' AND `id_employee` = '.(int)$context->employee->id)) != false)
			{
				$id_last_new_message = Db::getInstance()->getValue('SELECT IFNULL(MAX(`'.self::$definition['primary'].'`), 0)
						FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE `id_shop`='.(int)$id_shop);
				if ($id_last_new_message > $mf_employee['id_last_new_message'])
					$id_first_new_message = $mf_employee['id_last_new_message'];
				else
					$id_first_new_message = $mf_employee['id_first_new_message'];
				return Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'mf_employee`
					SET `id_last_new_message` = '.(int)$id_last_new_message.',
					`id_first_new_message`='.(int)$id_first_new_message.'
					WHERE `id_shop`='.(int)$id_shop.' AND `id_employee` = '.(int)$context->employee->id);
			}
			else
				return Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'mf_employee`
					(`id_last_new_message`, `id_first_new_message`, `id_employee`, `id_shop`) VALUES ((
						SELECT IFNULL(MAX(`'.self::$definition['primary'].'`), 0)
						FROM `'._DB_PREFIX_.self::$definition['table'].'`
					), 0, '.(int)$context->employee->id.', '.(int)$id_shop.')');
		}
	}
}