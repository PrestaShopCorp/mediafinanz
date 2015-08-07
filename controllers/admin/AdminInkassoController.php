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

require_once(_PS_MODULE_DIR_.'mediafinanz/classes/MediafinanzClaim.php');
require_once(_PS_MODULE_DIR_.'mediafinanz/classes/MediafinanzNewMessage.php');

class AdminInkassoController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'mf_claims';
		$this->list_id = 'mf_claims';
		$this->identifier = 'id_mf_claim';
		$this->className = 'MediafinanzClaim';
		$this->lang = false;
		$this->addRowAction('view');

		$this->boxes = true;
		$this->bootstrap = true;
		$this->bulk_actions = array();

		$this->deleted = false;
		$this->multishop_context = Shop::CONTEXT_SHOP;
		$this->context = Context::getContext();

		$this->_select = 'IF (a.date_change between \''.date('Y-m-d').
						'\' and DATE_ADD(\''.date('Y-m-d').'\',INTERVAL 1 DAY), 1, 0) badge_success';

		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = a.`id_order` AND o.`id_shop` = a.`id_shop`) ';

		$this->_where = 'AND a.sandbox='.(int)Configuration::get('MEDIAFINANZ_SANDBOX');

		$this->_orderBy = 'id_mf_claim';
		$this->_orderWay = 'DESC';

		$this->fields_list = array(
			'id_mf_claim' => array(
				'title' => $this->l('ID'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs',
				'width' => 25
			),
			'id_order' => array(
				'title' => $this->l('Order ID'),
				'align' => 'center',
				'width' => 25
			),
			'file_number' => array(
				'title' => $this->l('File number'),
				'align' => 'center',
				'width' => 65
			),
			'firstname' => array(
				'title' => $this->l('Firstname'),
				'havingFilter' => true,
			),
			'lastname' => array(
				'title' => $this->l('Lastname'),
				'havingFilter' => true,
			),
			/*
			'status_code' => array(
				'title' => $this->l('Status code'),
				'havingFilter' => true,
			),*/
			'status_text' => array(
				'title' => $this->l('Status text'),
				'havingFilter' => true,
			),
			'date_add' => array(
				'title' => $this->l('Date'),
				'width' => 130,
				'align' => 'right',
				'type' => 'datetime',
				'filter_key' => 'date_add'
			),
			'date_change' => array(
				'title' => $this->l('Status upd. date'),
				'width' => 130,
				'align' => 'right',
				'type' => 'datetime',
				'filter_key' => 'date_change',
				'badge_success' => true
			),
			'sandbox' => array(
				'title' => $this->l('Mode'),
				'align' => 'text-center',
				'callback' => 'printSandbox',
				'orderby' => false,
				'search' => false
			),
		);

		$this->shopLinkType = 'shop';
		$this->shopShareDatas = Shop::SHARE_ORDER;

		parent::__construct();
	}

	public function printSandbox($value, $row)
	{
		unset($row);
		return ($value == 1 ? $this->l('sandbox') : $this->l('live'));
	}

	public function setMedia()
	{
		parent::setMedia();

		$this->addJqueryUI('ui.datepicker');
		$this->addJS($this->module->getPathUri().'views/js/claim-view.js');

		if (_PS_VERSION_ < '1.6.0.0')
			$this->addCSS($this->module->getPathUri().'views/css/admin_15.css');
	}

	public function init()
	{
		if (Tools::isSubmit('createclaims'))
			$this->display = 'createClaims';

		if (Tools::isSubmit('viewMessages'))
			$this->display = 'viewMessages';

		parent::init();
	}

	public function initContent()
	{
		if (!$this->viewAccess())
			$this->errors[] = Tools::displayError('You do not have permission to view this.');
		elseif (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
			$this->displayInformation($this->l('You can only display the page in a shop context.'));
		elseif (!$this->module->isModuleConfigurationCompleted())
			$this->errors[] = Tools::displayError('Mediafinanz module has not been configured for this shop');
		else
		{
			$this->getLanguages();
			$this->initToolbar();
			$this->initTabModuleList();

			$this->toolbar_title[0] = $this->l('Inkasso');
			$this->page_header_toolbar_title = $this->l('Inkasso');

			if ($this->display == 'createClaims')
				$this->content .= $this->renderCreateClaimsList();
			elseif ($this->display == 'viewMessages')
			{
				$this->toolbar_title[0] = $this->l('Messages');
				$this->page_header_toolbar_title = $this->l('Messages');
				$this->content .= $this->renderViewMessagesList();
			}
			elseif ($this->display == 'edit' || $this->display == 'add')
			{
				if (!$this->loadObject(true))
					return;

				$this->content .= $this->renderForm();
			}
			elseif ($this->display == 'view')
			{
				// Some controllers use the view action without an object
				if ($this->className)
					$this->loadObject(true);
				$this->content .= $this->renderView();
			}
			elseif ($this->display == 'details')
				$this->content .= $this->renderDetails();
			elseif (!$this->ajax)
			{
				$this->toolbar_title[] = $this->l('Claims').' '.$this->l('Last update:').' '.Configuration::get('MEDIAFINANZ_LASTSTATUSUPDATE');

				$this->content .= $this->renderModulesList();
				if (_PS_VERSION_ >= '1.6.0.0')
					$this->content .= $this->renderKpis();

				$this->content .= $this->renderNewMessagesList();
				$this->content .= $this->renderList();
				$this->content .= $this->renderOptions();

				// if we have to display the required fields form
				if ($this->required_database)
					$this->content .= $this->displayRequiredFields();
			}
		}

		$this->toolbar_title[0] = $this->l('Inkasso');

		if (_PS_VERSION_ >= '1.6.0.0')
			$this->initPageHeaderToolbar();

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'lite_display' => $this->lite_display,
			'url_post' => self::$currentIndex.'&token='.$this->token
		));

		if (_PS_VERSION_ >= '1.6.0.0')
			$this->context->smarty->assign(array(
				'show_page_header_toolbar' => $this->show_page_header_toolbar,
				'page_header_toolbar_title' => $this->page_header_toolbar_title,
				'title' => $this->page_header_toolbar_title,
				'toolbar_btn' => $this->page_header_toolbar_btn,
				'page_header_toolbar_btn' => $this->page_header_toolbar_btn
			));

	}


	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);

		/*
		if (!$this->display)
			$this->toolbar_btn['download'] = array(
				'href' => $this->context->link->getAdminLink('AdminInkasso', true).'&update_claims_statuses=true',
				'desc' => $this->l('Update status of claims'),
				'class' => (_PS_VERSION_ < '1.6.0.0')?'process-icon-refresh-cache':''
			);
		*/
	}

	public function displayViewclaimLink($token = null, $id)
	{
		if (!array_key_exists('viewclaim', self::$cache_lang))
			self::$cache_lang['viewclaim'] = $this->l('View claim');

		$this->context->smarty->assign(array(
			'href' => self::$currentIndex.'&'.$this->identifier.'='.$id.
					'&viewmf_claims&token='.($token != null ? $token : $this->token),
			'action' => self::$cache_lang['viewclaim'],
		));

		//&id_mf_claim=19&viewmf_claims&

		return $this->context->smarty->fetch('helpers/list/list_action_view.tpl');
	}

	public function renderViewMessagesList()
	{
		$this->table = 'mf_new_messages';
		$this->list_id = 'mf_new_messages';
		$this->identifier = 'id_mf_new_message';
		$this->className = 'MediafinanzNewMessage';
		$this->lang = false;
		//$this->addRowAction('view');
		$this->actions = array();
		$this->addRowAction('viewclaim');

		$this->boxes = true;
		$this->bootstrap = true;
		$this->bulk_actions = array();

		$this->deleted = false;
		$this->multishop_context = Shop::CONTEXT_SHOP;
		$this->context = Context::getContext();

		$this->_select = 'c.`sandbox`';

		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'mf_claims` c ON (c.`id_order` = a.`id_order` AND c.`id_shop` = a.`id_shop`) ';

		$this->_where = 'AND c.sandbox='.(int)Configuration::get('MEDIAFINANZ_SANDBOX');

		$this->_orderBy = 'id_mf_new_message';
		$this->_orderWay = 'DESC';

		$this->fields_list = array(
			'id_mf_new_message' => array(
				'title' => $this->l('ID'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs',
				'width' => 25
			),
			'time' => array(
				'title' => $this->l('Date'),
				'align' => 'center',
				'width' => 25
			),
			'id_order' => array(
				'title' => $this->l('Order ID'),
				'align' => 'center',
				'width' => 25
			),
			'file_number' => array(
				'title' => $this->l('File number'),
				'align' => 'center',
				'width' => 65
			),
			'text' => array(
				'title' => $this->l('Text'),
				'havingFilter' => true,
			),
			'sandbox' => array(
				'title' => $this->l('Mode'),
				'align' => 'text-center',
				'callback' => 'printSandbox',
				'orderby' => false,
				'search' => false
			),
		);

		if (!($this->fields_list && is_array($this->fields_list)))
			return false;
		$this->getList($this->context->language->id);

		// If list has 'active' field, we automatically create bulk action
		if (isset($this->fields_list) && is_array($this->fields_list) && array_key_exists('active', $this->fields_list)
			&& !empty($this->fields_list['active']))
		{
			if (!is_array($this->bulk_actions))
				$this->bulk_actions = array();

			$this->bulk_actions = array_merge(array(
				'enableSelection' => array(
					'text' => $this->l('Enable selection'),
					'icon' => 'icon-power-off text-success'
				),
				'disableSelection' => array(
					'text' => $this->l('Disable selection'),
					'icon' => 'icon-power-off text-danger'
				),
				'divider' => array(
					'text' => 'divider'
				)
			), $this->bulk_actions);
		}

		$helper = new HelperList();

		// Empty list is ok
		if (!is_array($this->_list))
		{
			$this->displayWarning($this->l('Bad SQL query', 'Helper').'<br />'.htmlspecialchars($this->_list_error));
			return false;
		}

		$this->setHelperDisplay($helper);
		$helper->_default_pagination = $this->_default_pagination;
		$helper->_pagination = $this->_pagination;
		$helper->tpl_vars = $this->getTemplateListVars();
		$helper->tpl_delete_link_vars = $this->tpl_delete_link_vars;

		$helper->currentIndex = self::$currentIndex.'&viewMessages';

		// For compatibility reasons, we have to check standard actions in class attributes
		foreach ($this->actions_available as $action)
			if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action)
				$this->actions[] = $action;

		$helper->is_cms = $this->is_cms;
		$helper->sql = $this->_listsql;
		$list = $helper->generateList($this->_list, $this->fields_list);

		return $list;
	}

	public function renderCreateClaimsList()
	{
		$order_ids = Tools::getValue('order_list');

		if (count($order_ids) > 0)
		{
			$list = Db::getInstance()->executeS('SELECT a.`id_shop`, a.`id_order`, a.`id_address_invoice`, a.`date_add`, a.`id_customer`,
												a.`total_paid_tax_incl`, a.`id_currency` FROM `'.
												_DB_PREFIX_.'orders` a LEFT JOIN `'._DB_PREFIX_.
												'orders` o ON (o.`id_order` = a.`id_order` AND o.`id_shop` = a.`id_shop`) LEFT JOIN `'.
												_DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`) WHERE a.`id_order` IN ('.
												pSQL(implode(', ', $order_ids)).') '.
												Shop::addSqlRestriction(Shop::SHARE_ORDER, 'a', 'shop'));

			$claim = Tools::getValue('claim');

			foreach ($list as &$row)
			{
				$row['accepted_for_claiming'] = true;

				$row['claim']['file_number'] = 0;
				if ($claim_object = MediafinanzClaim::getMediafinanzClaimByOrderId($row['id_order']))
				{
					$row['claim']['file_number'] = (int)$claim_object->file_number;
					$row['claim']['id'] = (int)$claim_object->id;
					$row['accepted_for_claiming'] = false;
				}

				$order = new Order($row['id_order']);

				// get Reminder date
				$row['date_reminder'] = '';
				$state_reminder = Configuration::get('PS_OS_MF_REMINDER');
				if ($state_reminder > 0)
				{
					$history_entities = $order->getHistory($this->context->language->id, $state_reminder);
					$last_history_entity = end($history_entities);
					if ($last_history_entity)
						$row['date_reminder'] = $last_history_entity['date_add'];
				}

				// get Last Reminder date
				$row['date_lastreminder'] = '';
				$state_reminder = Configuration::get('PS_OS_MF_LASTREMINDER');
				if ($state_reminder > 0)
				{
					$history_entities = $order->getHistory($this->context->language->id, $state_reminder);
					$last_history_entity = end($history_entities);
					if ($last_history_entity)
						$row['date_lastreminder'] = $last_history_entity['date_add'];
				}

				$euro_currency_id = $this->module->getActiveEuroCurrencyID($row['id_shop']);

				// check currency
				if ($euro_currency_id == 0)
				{
					$row['accepted_for_claiming'] = false;
					$row['supported_currency_by_shop'] = false;
				}
				else
				{
					$row['supported_currency_by_shop'] = true;

					if ($row['id_currency'] == $euro_currency_id)
						$row['supported_currency'] = true;
					else
					{
						$row['accepted_for_claiming'] = false;
						$row['supported_currency'] = false;
					}
				}

				$row['claim']['invoice'] = $row['id_order'];

				$row['claim']['type'] = (!isset($claim[$row['id_order']]['type'])) ?
					Configuration::get('MEDIAFINANZ_CLAIM_TYPE') : $claim[$row['id_order']]['type'];

				$row['claim']['reason'] = $this->module->getOrderReason($row['id_order']);
				$currency = new Currency($row['id_currency']);
				$row['claim']['display_originalvalue'] = Tools::displayPrice($row['total_paid_tax_incl'], $currency);
				$row['claim']['originalvalue'] = Tools::ps_round($row['total_paid_tax_incl'], 2);
				$row['claim']['overduefees'] = (!isset($claim[$row['id_order']]['overduefees'])) ?
					Configuration::get('MEDIAFINANZ_OVERDUEFEES') : $claim[$row['id_order']]['overduefees'];
				$row['claim']['dateoforigin'] = date('Y-m-d', strtotime($row['date_add'])); //'2015-5-16';
				$row['claim']['dateoflastreminder'] = date('Y-m-d', strtotime($row['date_lastreminder']));
				$row['claim']['note'] = (!isset($claim[$row['id_order']]['note']))?Configuration::get('MEDIAFINANZ_NOTE') : $claim[$row['id_order']]['note'];

				$customer = new Customer($row['id_customer']);
				$address = new Address($row['id_address_invoice']);

				$row['debtor']['id'] = $customer->id;
				if ($address->company != '')
					$row['debtor']['address'] = 'c';
				elseif ($customer->id_gender == 1)
					$row['debtor']['address'] = 'm';
				elseif ($customer->id_gender == 2)
					$row['debtor']['address'] = 'f';
				else
					$row['debtor']['address'] = '@';

				$row['debtor']['firstname'] = $address->firstname;
				$row['debtor']['lastname'] = $address->lastname;
				$row['debtor']['company'] = $address->company;
				$row['debtor']['street'] = $address->address1.($address->address2 != '' ? ' '.$address->address2 : '');
				$row['debtor']['postcode'] = $address->postcode;
				$row['debtor']['city'] = $address->city;
				$country = new Country($address->id_country);
				$row['debtor']['country'] = $country->iso_code;
				//$row['debtor']['addressstatus'] = $row['id_customer'];
				$row['debtor']['telephone1'] = $address->phone;
				$row['debtor']['telephone2'] = $address->phone_mobile;
				//$row['debtor']['fax'] = $row['id_customer'];
				$row['debtor']['email'] = $customer->email;
				//$row['debtor']['dateofbirth'] = $row['id_customer'];
				//$row['debtor']['deliveryaddress'] = $row['id_customer'];

				$row['configuration_completed'] = (int)$this->module->isModuleConfigurationCompleted($order->id_shop);
				$row['id_shop'] = (int)$order->id_shop;
				$row['mode_for_shop'] = $this->module->getCurrentModeTitle($order->id_shop);
			}
		}

		$this->context->smarty->assign('currency', new Currency($this->module->getActiveEuroCurrencyID(Context::getContext()->shop->id)));
		$this->context->smarty->assign('claim_types', $this->module->getClaimTypes());
		$this->context->smarty->assign('createclaims_data', $list);
		$this->setTemplate('createclaims-list'.((_PS_VERSION_ < '1.6.0.0')?'_15':'').'.tpl');
	}

	public function renderNewMessagesList()
	{
		try{
			$this->module->getNewMessages(Context::getContext()->shop->id);
		}
		catch(Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}

		$this->context->smarty->assign('new_messages_value', count(MediafinanzNewMessage::getMessages()));
		$this->context->smarty->assign('new_messages', MediafinanzNewMessage::getMessages(false));

		$template = $this->createTemplate('new-messages-list'.((_PS_VERSION_ < '1.6.0.0')?'_15':'').'.tpl');
		return $template->fetch();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitCloseClaim'))
		{
			$id_mf_claim = (int)Tools::getValue('id_mf_claim');
			if (!$id_mf_claim || !Validate::isUnsignedId($id_mf_claim))
				$this->errors[] = $this->l('The claim is no longer valid.');
			else
			{
				$claim = new MediafinanzClaim($id_mf_claim);
				if (!Validate::isLoadedObject($claim))
					$this->errors[] = $this->l('The Claim cannot be found');
				else
				{
					try
					{
						$res = $this->module->closeClaim($claim->file_number);
						if ($res)
							$this->confirmations[] = $this->l('The Claim has been closed');
						else
							$this->errors[] = $this->l('The Claim has not been closed');
					}
					catch (Exception $e)
					{
						$this->errors[] = $this->l('The Claim has not been closed');
						$this->errors[] = $e->getMessage();
						Mediafinanz::logToFile($e->getMessage(), 'general');
					}
				}
			}
		}

		if (Tools::isSubmit('submitBookDirectPayment'))
		{
			$id_mf_claim = (int)Tools::getValue('id_mf_claim');
			$amount = str_replace(',', '.', Tools::getValue('paidAmount'));

			if (!$id_mf_claim || !Validate::isUnsignedId($id_mf_claim))
				$this->errors[] = $this->l('The Claim is no longer valid.');
			else
			{
				$claim = new MediafinanzClaim($id_mf_claim);
				if (!Validate::isLoadedObject($claim))
					$this->errors[] = $this->l('The Claim cannot be found');
				elseif (!Validate::isDate(Tools::getValue('dateOfPayment')))
					$this->errors[] = $this->l('The date of payment is invalid');
				elseif (!Validate::isPrice($amount))
					$this->errors[] = $this->l('The paid amount is invalid.');
				else
				{
					try
					{
						$direct_payment = array('dateOfPayment' => Tools::getValue('dateOfPayment'), 'paidAmount' => $amount);
						$res = $this->module->bookDirectPayment($claim->file_number, $direct_payment);
						if ($res)
							$this->confirmations[] = $this->l('Direct payment has been booked');
						else
							$this->errors[] = $this->l('Direct payment has not been booked');
					}
					catch (Exception $e)
					{
						$this->errors[] = $this->l('Direct payment has not been booked');
						$this->errors[] = $e->getMessage();
						Mediafinanz::logToFile($e->getMessage(), 'general');
					}
				}
			}
		}

		if (Tools::isSubmit('submitMessage'))
		{
			$id_mf_claim = (int)Tools::getValue('id_mf_claim');
			$msg_text = Tools::getValue('message');

			if (!$id_mf_claim || !Validate::isUnsignedId($id_mf_claim))
				$this->errors[] = $this->l('The claim is no longer valid.');
			elseif (empty($msg_text))
				$this->errors[] = $this->l('The message cannot be blank.');
			elseif (!Validate::isMessage($msg_text))
				$this->errors[] = $this->l('This message is invalid (HTML is not allowed).');
			if (!count($this->errors))
			{
				$claim = new MediafinanzClaim($id_mf_claim);
				if (Validate::isLoadedObject($claim))
				{
					try
					{
						$res = $this->module->sendMessage($claim->file_number, $msg_text);
						if (!$res)
							$this->errors[] = $this->l('The Message has not been sent');
						else
							$this->confirmations[] = $this->l('The Message has been sent');
					}
					catch (Exception $e)
					{
						$this->errors[] = $this->l('The Message has not been sent');
						$this->errors[] = $e->getMessage();
						Mediafinanz::logToFile($e->getMessage(), 'general');
					}
				}
				else
					$this->errors[] = $this->l('The Claim not found');
			}
		}

		/*if (Tools::isSubmit('update_claims_statuses'))
		{*/
		if ($this->display == '')
		{
			try
			{
				$this->module->updateClaimsStatuses();
			}
			catch (Exception $e)
			{
				$this->_errors[] = $e->getMessage();
				Mediafinanz::logToFile($e->getMessage(), 'general');
			}
		}
		//}

		if (Tools::isSubmit('submitCreateClaims'))
		{
			$order_ids = Tools::getValue('order_list');
			$claim = Tools::getValue('claim');
			$debtor = Tools::getValue('debtor');

			$list = Db::getInstance()->executeS('SELECT a.`id_order`, a.`id_shop` FROM `'._DB_PREFIX_.'orders` a LEFT JOIN `'._DB_PREFIX_.
												'orders` o ON (o.`id_order` = a.`id_order` AND o.`id_shop` = a.`id_shop`) LEFT JOIN '._DB_PREFIX_.
												'mf_claims c ON a.`id_order`=c.`id_order` AND c.`sandbox`='.
												(int)Configuration::get('MEDIAFINANZ_SANDBOX').' WHERE c.`id_order` IS NULL AND a.`id_order` IN ('.
												pSQL(implode(', ', $order_ids)).')'.
												Shop::addSqlRestriction(Shop::SHARE_ORDER, 'a', 'shop'));

			foreach ($list as $row)
			{
				$id = $row['id_order'];

				$debtor_to = array(
					'id' => $debtor[$id]['id'],
					'address' => $debtor[$id]['address'],
					'firstname' => $debtor[$id]['firstname'],
					'lastname' => $debtor[$id]['lastname'],
					'company' => $debtor[$id]['company'],
					'co' => '',
					'street' => $debtor[$id]['street'],
					'postcode' => $debtor[$id]['postcode'],
					'city' => $debtor[$id]['city'],
					'country' => $debtor[$id]['country'],
					'telephone1' => $debtor[$id]['telephone1'],
					'telephone2' => $debtor[$id]['telephone2'],
					'email' => $debtor[$id]['email']
				);

				$claim_to = array(
					'invoice' => $claim[$id]['invoice'],
					'type' => $claim[$id]['type'],
					'reason' => $claim[$id]['reason'],
					'originalValue' => $claim[$id]['originalvalue'],
					'overdueFees' => $claim[$id]['overduefees'],
					'dateOfOrigin' => $claim[$id]['dateoforigin'],
					'dateOfLastReminder' => $claim[$id]['dateoflastreminder'],
					'note' => $claim[$id]['note'],
				);

				try
				{
					$result = $this->module->newClaim($claim_to, $debtor_to);
					if (!empty($result->fileNumber))
					{
						$mf = new MediafinanzClaim();
						$mf->id_order = $claim[$id]['invoice'];
						$mf->file_number = $result->fileNumber;
						$mf->firstname = $debtor[$id]['firstname'];
						$mf->lastname = $debtor[$id]['lastname'];
						$mf->id_shop = $row['id_shop'];
						$mf->sandbox = (int)Configuration::get('MEDIAFINANZ_SANDBOX');
						$mf->add();

						$claim_status = $this->module->getClaimStatus($result->fileNumber, $row['id_shop']);
						if ($mf->status_code != $claim_status->statusCode)
						{
							$mf->status_code = $claim_status->statusCode;
							$mf->status_text = $claim_status->statusText;

							if (isset($claim_status->statusDetails))
								$mf->status_details = $claim_status->statusDetails;
							else
								$mf->status_details = '';

							$mf->date_change = date('Y-m-d H:i:s');
							$mf->save();
						}

						//change state
						$this->module->changeOrderState($claim[$id]['invoice'], Configuration::get('PS_OS_MF_INKASSO'));
					}
					else
					{
						foreach ($result->errorList as $error_msg)
							$this->errors[] = $this->l('Order').' - '.$row['id_order'].': '.$error_msg;
					}
				}
				catch (Exception $e)
				{
					$this->errors[] = $this->l('Order').' - '.$row['id_order'].': '.$e->getMessage();
					Mediafinanz::logToFile($this->l('Order').' - '.$row['id_order'].': '.$e->getMessage(), 'general');
				}
			}
		}

		parent::postProcess();
	}

	public function renderView()
	{
		if (Tools::getValue('id_mf_new_message'))
		{
			$mf_message = new MediafinanzNewMessage((int)Tools::getValue('id_mf_new_message'));
			if (Validate::isLoadedObject($mf_message))
				$claim_object = MediafinanzClaim::getMediafinanzClaimByOrderId($mf_message->id_order);
			else
				$claim_object = new MediafinanzClaim();
		}
		else
			$claim_object = new MediafinanzClaim(Tools::getValue('id_mf_claim'));

		if (!Validate::isLoadedObject($claim_object))
			$this->errors[] = $this->l('The claim cannot be found within your database.');

		try
		{
			$claim_details = $this->module->getClaimDetails($claim_object);
			$claim_history = $this->module->getClaimHistory($claim_object->file_number);
			$claim_message_history = $this->module->getMessageHistory($claim_object->file_number);
			$claim_options = $this->module->getClaimOptions($claim_object->file_number);
		}
		catch (Exception $e)
		{
			$this->errors[] = $e->getMessage();
			Mediafinanz::logToFile($e->getMessage(), 'general');
		}

		if (in_array('close', $claim_options))
			$claim_options[] = 'bookDirectPayment'; // add form for direct payment

		//remove actions from this version of module
		$remove_options = array('lawyer', 'factoring', 'longTermObservation', 'addressIdentification');

		$claim_options = array_values(array_diff($claim_options, $remove_options));

		$this->context->smarty->assign('claim_details', $claim_details);
		$this->context->smarty->assign('claim_history', $claim_history);
		$this->context->smarty->assign('claim_options', $claim_options);
		$this->context->smarty->assign('claim_message_history', $claim_message_history);

		$this->context->smarty->assign('instructions_for_assigning', $this->module->getInstructionsForAssigningClaimToLawyer());
		$this->context->smarty->assign('mission_depthes', $this->module->getMissionDepthes());

		$this->setTemplate('claim-view'.((_PS_VERSION_ < '1.6.0.0')?'_15':'').'.tpl');
	}
}