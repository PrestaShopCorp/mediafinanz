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

class MediafinanzClaim extends ObjectModel
{
	public $id_order;
	public $id_shop;
	public $file_number;
	public $firstname;
	public $lastname;
	public $date_add;
	public $date_upd;
	public $date_change;
	public $status_code;
	public $status_text;
	public $status_details;
	public $sandbox;

	public static $definition = array(
		'table' => 'mf_claims',
		'primary' => 'id_mf_claim',
		'fields' => array(
			'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'file_number' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'firstname' => array('type' => self::TYPE_STRING),
			'lastname' => array('type' => self::TYPE_STRING),
			'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_change' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'status_code' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'status_text' => array('type' => self::TYPE_STRING),
			'status_details' => array('type' => self::TYPE_STRING),
			'sandbox' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId')
		),
	);

	public static function getFilenumberByOrderId($id_order, $by_mode = true)
	{
		$order = new Order((int)$id_order);
		return Db::getInstance()->getValue('SELECT `file_number` FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE `id_order` = "'.
										pSQL($id_order).'"'.(($by_mode == true)?' AND sandbox='.(int)Configuration::get('MEDIAFINANZ_SANDBOX', null, null, $order->id_shop):''));
	}

	public static function getMediafinanzClaimByOrderId($id_order, $by_mode = true)
	{
		$order = new Order((int)$id_order);
		$id_mf_claim = Db::getInstance()->getValue('SELECT `id_mf_claim` FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE `id_order` = "'.
												pSQL($id_order).'"'.(($by_mode == true)?' AND sandbox='.(int)Configuration::get('MEDIAFINANZ_SANDBOX', null, null, $order->id_shop):''));
		if ($id_mf_claim > 0)
			return new MediafinanzClaim($id_mf_claim);

		return false;
	}

	public static function getInstanceByFilenumber($file_number, $by_mode = true)
	{
		$id_mf_claim = Db::getInstance()->getValue('SELECT `id_mf_claim` FROM `'._DB_PREFIX_.self::$definition['table'].'` WHERE `file_number` = "'.
												pSQL($file_number).'"'.(($by_mode == true)?' AND sandbox='.(int)Configuration::get('MEDIAFINANZ_SANDBOX'):''));
		if ($id_mf_claim > 0)
			return new MediafinanzClaim($id_mf_claim);

		return false;
	}
}