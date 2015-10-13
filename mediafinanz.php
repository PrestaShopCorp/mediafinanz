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

if (!defined('_PS_VERSION_'))
    exit;

require_once(_PS_MODULE_DIR_ . 'mediafinanz/classes/MediafinanzClaim.php');
require_once(_PS_MODULE_DIR_ . 'mediafinanz/classes/MediafinanzNewMessage.php');

class Mediafinanz extends Module
{

    public static $api_url = 'https://soap.mediafinanz.de/encashment204.wsdl';
    public static $application_key = 'bbc6017ee65c439e92487563312c23f6';

    public static $partner_api_url = 'https://soap.mediafinanz.de/partner201.wsdl';
    public static $partner_application_id = '477';
    public static $partner_service_key = '2267eb2efdab1d97d3a23aacc1bf2a62';

    public static $soap_client;

    public function __construct()
    {
        $this->name = 'mediafinanz';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'Mediafinanz';
        $this->module_key = 'aa6b12d31113b93a26208c220115f7e9';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mediafinanz');
        $this->description = $this->l('Mediafinanz debt collection service');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    }

    public function install()
    {
        if (!extension_loaded('soap')) {
            $this->_errors[] = $this->l('You need to enable SOAP extension in PHP.');
            return false;
        }

        $return = true;

        $return &= parent::install();

        $return &= $this->createNewOrderStates();
        $return &= $this->createDbTables();

        $return &= $this->registerHook('displayBackOfficeHeader');
        $return &= $this->registerHook('displayAdminOrder');
        $return &= $this->registerHook('actionOrderStatusPostUpdate');
        $return &= $this->installTab('AdminInkasso', 'Inkasso', 'AdminOrders', true);
        $return &= $this->generateSecureKey();
        return (bool)$return;
    }


    public function uninstall()
    {
        $return = true;
        $return &= parent::uninstall();

        $this->uninstallTab('AdminInkasso');
        return (bool)$return;
    }

    public function reset()
    {
        $return = true;
        return (bool)$return;
    }

    private function generateSecureKey()
    {
        return Configuration::updateGlobalValue('MEDIAFINANZ_SECURE_KEY', Tools::passwdGen(10));
    }

    public function createDbTables()
    {
        $return = true;

        $return &= (bool)Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mf_reminders` (
				`id_mf_reminder` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NOT NULL,
                `id_shop` int(11) UNSIGNED NOT NULL DEFAULT \'1\',
				`reminder_date` datetime,
				`lastreminder_date` datetime,
			    PRIMARY KEY (`id_mf_reminder`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mf_claims` (
				`id_mf_claim` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NOT NULL,
                `id_shop` int(11) UNSIGNED NOT NULL DEFAULT \'1\',
				`file_number` int(10) unsigned NOT NULL,
				`firstname` varchar(100) NOT NULL,
				`lastname` varchar(100) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				`date_change` datetime NOT NULL,
				`status_code` smallint(5) unsigned,
                `status_text` text,
                `status_details` text,
                `sandbox` int(1) UNSIGNED NOT NULL DEFAULT \'1\',
			    PRIMARY KEY (`id_mf_claim`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mf_new_messages` (
				`id_mf_new_message` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_order` int(10) unsigned NOT NULL,
                `id_shop` int(11) UNSIGNED NOT NULL DEFAULT \'1\',
				`file_number` int(10) unsigned NOT NULL,
				`invoice_number` int(10) unsigned NOT NULL,
				`text` text,
				`time` datetime NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
			    PRIMARY KEY (`id_mf_new_message`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mf_employee` (
				`id_mf_employee` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`id_employee` int(10) unsigned NOT NULL,
				`id_shop` int(10) unsigned NOT NULL,
                `id_last_new_message` int(11) UNSIGNED NOT NULL,
                `id_first_new_message` int(11) UNSIGNED NOT NULL,
			    PRIMARY KEY (`id_mf_employee`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
        );

        return $return;
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);

        if (Validate::isLoadedObject($order)) {
            $claim = MediafinanzClaim::getMediafinanzClaimByOrderId($order->id);
            if ($claim)
                $this->context->smarty->assign('claim', $claim);
            else
                $this->context->smarty->assign('id_order', $order->id);
        }
        return $this->display(__FILE__, 'views/templates/hook/admin_order' . ((_PS_VERSION_ < '1.6.0.0') ? '_15' : '') . '.tpl');
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        unset($params);
        $script = '';
        if ($this->active && $this->isEnabledForShopContext()) {
            if ($this->context->controller instanceof AdminOrdersController && !Tools::getIsset('id_order')) {
                $this->context->controller->addJS($this->_path . 'views/js/order-list' . ((_PS_VERSION_ < '1.6.0.0') ? '_15' : '') . '.js');

                return '<script type="text/javascript">
			var request_path = "' . Dispatcher::getInstance()->createUrl('AdminInkasso',
                    $this->context->language->id, array('token' => Tools::getAdminTokenLite('AdminInkasso'), 'createclaims' => 'true'), false) . '",
				mediafinanz_translation = {
				"inkasso": "' . $this->l('Inkasso') . '"
				}
			</script>';
            }
        }

        if ($this->context->controller instanceof AdminInkassoController) {
            $this->context->controller->addJS($this->_path . 'views/js/notifications.js');

            $script .= '
				<script type="text/javascript">
					var mediafinanz_ajax = "' . $this->_path . '/ajax.php";
                    var iem = ' . (int)$this->context->cookie->id_employee . ',
		    		iemp = "' . $this->context->cookie->passwd . '", mediafinanz_id_shop="' . Configuration::get('PS_SHOP_DEFAULT') . '";
				</script>';
        }

        if (Tools::getValue('configure') == $this->name) {
            if (_PS_VERSION_ < '1.6.0.0') {
                $this->context->controller->addCSS($this->_path . 'views/css/admin_15.css');
                $this->context->controller->addJquery();
                $this->context->controller->addJqueryPlugin(array('idTabs'));
            }
        }
        return $script;
    }

    public function installTab($tab_class, $tab_name, $parent = 'AdminModules', $active = false)
    {
        $tab = new Tab();
        $tab->active = (int)$active;
        $tab->class_name = $tab_class;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = $tab_name;

        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab($tab_class)
    {
        $id_tab = (int)Tab::getIdFromClassName($tab_class);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return false;
    }

    public static function logToFile($msg, $key = '')
    {
        if (Configuration::get('MEDIAFINANZ_LOG_ENABLED')) {
            $filename = dirname(__FILE__) . '/logs/log_' . $key . '.txt';
            $fd = fopen($filename, 'a');
            fwrite($fd, "\n" . date('Y-m-d H:i:s') . ' ' . $msg);
            fclose($fd);
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $order = new Order((int)$params['id_order']);
        $order_status = $params['newOrderStatus'];

        if ((int)Configuration::get('MEDIAFINANZ_SEND_OS_MAILS') == 1 && (Configuration::get('PS_OS_MF_REMINDER') == $order_status->id || Configuration::get('PS_OS_MF_LASTREMINDER') == $order_status->id)) {

            if (Validate::isLoadedObject($order)) {
                $customer = new Customer((int)$order->id_customer);
                $iso = Language::getIsoById((int)$order->id_lang);
                $date_reminder = '';
                if (Configuration::get('PS_OS_MF_REMINDER') == $order_status->id) {
                    $template = 'mediafinanz_reminder';
                    $subject = $this->l('Payment reminder');
                } else {
                    $history_entities = $order->getHistory((int)$order->id_lang, Configuration::get('PS_OS_MF_REMINDER'));
                    if (count($history_entities)) {
                        $last_history_entity = end($history_entities);
                        $date_reminder = Tools::displayDate($last_history_entity['date_add'], null, false);
                    }

                    $template = 'mediafinanz_lastreminder';
                    $subject = $this->l('Last payment reminder');
                }

                $total_to_pay = Tools::displayPrice((float)$order->total_paid + (float)Configuration::get('MEDIAFINANZ_OVERDUEFEES',
                        null, null, $order->id_shop), new Currency((int)$order->id_currency), false);
                $total_paid = Tools::displayPrice((float)$order->total_paid, new Currency((int)$order->id_currency), false);
                $total_overduefees = Tools::displayPrice((float)Configuration::get('MEDIAFINANZ_OVERDUEFEES',
                    null, null, $order->id_shop), new Currency((int)$order->id_currency), false);

                $data = array(
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname,
                    '{order_name}' => $order->reference,
                    '{id_order}' => $order->id,
                    '{total_to_pay}' => $total_to_pay,
                    '{total_paid}' => $total_paid,
                    '{order_date}' => Tools::displayDate($order->date_add, null, false),
                    '{reminder_date}' => $date_reminder,
                    '{total_overduefees}' => $total_overduefees,
                    '{payment_information}' => nl2br(str_replace(array('{total_to_pay}', '{total_paid}', '{total_overduefees}'),
                        array($total_to_pay, $total_paid, $total_overduefees), Configuration::get('MEDIAFINANZ_REMINDER_INFO',
                            null, null, $order->id_shop))));

                // Attach invoice if they exists and status is set to attach them
                $context = Context::getContext();
                $invoice = $order->getInvoicesCollection();
                $file_attachment = array();

                if ((int)Configuration::get('PS_INVOICE') && $order->invoice_number) {
                    $pdf = new PDF($invoice, PDF::TEMPLATE_INVOICE, $context->smarty);
                    $file_attachment['invoice']['content'] = $pdf->render(false);
                    $file_attachment['invoice']['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)$order->id_lang, null, $order->id_shop) . sprintf('%06d', $order->invoice_number) . '.pdf';
                    $file_attachment['invoice']['mime'] = 'application/pdf';
                }

                if (file_exists(dirname(__FILE__) . '/mails/' . $iso . '/' . $template . '.html')
                    && file_exists(dirname(__FILE__) . '/mails/' . $iso . '/' . $template . '.txt')
                ) {
                    Mail::Send(
                        $order->id_lang,
                        $template,
                        $subject,
                        $data,
                        $customer->email,
                        $customer->firstname . ' ' . $customer->lastname,
                        null,
                        null,
                        $file_attachment,
                        null,
                        dirname(__FILE__) . '/mails/',
                        true
                    );
                }
            }
        }
    }

    public function changeOrderState($id_order, $id_order_state)
    {
        $order_state = new OrderState((int)$id_order_state);
        $order = new Order((int)$id_order);
        $current_order_state = $order->getCurrentOrderState();

        if (!Validate::isLoadedObject($order_state))
            //throw new Exception($this->l('The new order status is invalid.'));
            return false;
        elseif (!Validate::isLoadedObject($order))
            //throw new Exception($this->l('The order is invalid.'));
            return false;
        else {
            if ($current_order_state->id != $order_state->id) {
                // Create new OrderHistory
                $history = new OrderHistory();
                $history->id_order = $order->id;

                if (isset($this->context->employee->id))
                    $history->id_employee = (int)$this->context->employee->id;

                $use_existings_payment = false;
                if (!$order->hasInvoice())
                    $use_existings_payment = true;
                $history->changeIdOrderState((int)$id_order_state, $order, $use_existings_payment);
                $history->add(true);
                return true;
            } else {
                return false;
            }
        }
    }

    public function getMediafinanzOrderStates()
    {
        return array(
            'PS_OS_MF_REMINDER' => array(
                'color' => '#D9FF00',
                'name' => $this->l('Reminder'),
                'template' => '',
                'send_email' => 0
            ),
            'PS_OS_MF_LASTREMINDER' => array(
                'color' => '#FFFB00',
                'name' => $this->l('Last reminder'),
                'template' => '',
                'send_email' => 0
            ),
            'PS_OS_MF_INKASSO' => array(
                'color' => '#FFA500',
                'name' => $this->l('Inkasso'),
                'send_email' => 0
            )
        );
    }

    public function getInstalledMediafinanzOrderStates()
    {
        $states = $this->getMediafinanzOrderStates();
        foreach ($states as $state_key => $state) {
            unset($state);
            $id_order_state = Configuration::get($state_key);
            if (!$id_order_state || !Validate::isLoadedObject($os = new OrderState((int)$id_order_state)))
                unset($states[$state_key]);
            else
                $states[$state_key]['id_order_state'] = $os->id;
        }
        return $states;
    }

    public function getNotInstalledMediafinanzOrderStates()
    {
        $states = $this->getMediafinanzOrderStates();
        foreach ($states as $state_key => $state) {
            unset($state);
            $id_order_state = Configuration::get($state_key);
            if (Validate::isLoadedObject(new OrderState((int)$id_order_state)))
                unset($states[$state_key]);
        }
        return $states;
    }

    public function createNewOrderStates()
    {
        $order_states = $this->getMediafinanzOrderStates();

        //check shop for custom order states
        foreach ($order_states as $order_state_key => $order_state) {
            $create_os = false;
            $create_os_id = 0;
            if ((int)Configuration::get($order_state_key) > 0) {
                $os = new OrderState((int)Configuration::get($order_state_key));
                if (!Validate::isLoadedObject($os)) {
                    $create_os = true;
                    $create_os_id = (int)Configuration::get($order_state_key);
                }
            } else
                $create_os = true;

            if ($create_os == true) {
                $os = new OrderState();
                if ($create_os_id > 0)
                    $os->id = $create_os_id;

                $langs = Language::getLanguages();
                foreach ($langs as $lang) {
                    $os->name[$lang['id_lang']] = $order_state['name'];
                    $os->template[$lang['id_lang']] = $order_state['template'];
                }

                $os->color = $order_state['color'];
                $os->send_email = $order_state['send_email'];
                $os->unremovable = 1;
                $os->hidden = 0;
                $os->logable = 0;
                $os->delivery = 0;
                $os->shipped = 0;
                $os->paid = 0;
                $os->pdf_invoice = 1;
                $os->module_name = $this->name;
                if ($os->add()) {
                    $source = dirname(__FILE__) . '/../../img/os/' . Configuration::get('PS_OS_CHEQUE') . '.gif';
                    $destination = dirname(__FILE__) . '/../../img/os/' . (int)$os->id . '.gif';
                    Tools::copy($source, $destination);
                }
                Configuration::updateValue($order_state_key, $os->id);
            }
        }
        return true;
    }

    public function getContent()
    {
        $html = '';
        $html .= $this->displayInfo();
        $html .= $this->postProcess();
        $html .= $this->displayForm();
        return $html;
    }

    public function displayInfo()
    {
        $this->smarty->assign(array(
            '_path' => $this->_path,
            'displayName' => $this->displayName,
            'author' => $this->author,
            'description' => $this->description,
        ));

        return $this->display(__FILE__, 'views/templates/admin/info' . ((_PS_VERSION_ < '1.6.0.0') ? '_15' : '') . '.tpl');
    }

    public function displayForm()
    {
        $html = '';

        // remove registration form if user entered client id
        if (Configuration::get('MEDIAFINANZ_CLIENT_ID'))
            $html .= $this->displayFormSettings();
        else {
            $active_registration = false;
            if (Tools::isSubmit('submitRegistrationForm') || Tools::isSubmit('submitGenerateRegistrationKey'))
                $active_registration = true;

            $this->smarty->assign(
                array(
                    'registration_form_html' => $this->displayFormRegistration(),
                    'registration_key' => Configuration::get('MEDIAFINANZ_REGISTRATIONKEY'),
                    'action_generate_key_form' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules')
                )
            );
            $registration_wrapper = $this->display(__FILE__, 'views/templates/admin/registration_wrapper' . ((_PS_VERSION_ < '1.6.0.0') ? '_15' : '') . '.tpl');

            $this->smarty->assign(
                array(
                    'active_registration' => $active_registration,
                    'form_settings_html' => $this->displayFormSettings(),
                    'registration_wrapper_html' => $registration_wrapper
                )
            );

            $html = $this->display(__FILE__, 'views/templates/admin/tabs' . ((_PS_VERSION_ < '1.6.0.0') ? '_15' : '') . '.tpl');
        }
        return $html;
    }

    protected function displayFormRegistration()
    {
        $helper = new HelperForm();

        // Helper Options
        $helper->required = false;
        $helper->id = Tab::getCurrentTabId();

        // Helper
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->table = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->identifier = null;
        $helper->toolbar_btn = null;
        $helper->ps_help_context = null;
        $helper->title = null;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = false;
        $helper->bootstrap = true;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        if (_PS_VERSION_ < '1.6.0.0') {
            $helper->show_toolbar = false;
            $helper->toolbar_btn = $this->toolbar_btn;
            $helper->title = $this->displayName;
        }

        $helper->fields_value['address'] = Tools::getValue('address');
        $helper->fields_value['firstname'] = Tools::getValue('firstname');
        $helper->fields_value['lastname'] = Tools::getValue('lastname');
        $helper->fields_value['company'] = Tools::getValue('company');
        $helper->fields_value['legalForm'] = Tools::getValue('legalForm');
        $helper->fields_value['street'] = Tools::getValue('street');
        $helper->fields_value['postcode'] = Tools::getValue('postcode');
        $helper->fields_value['city'] = Tools::getValue('city');
        $helper->fields_value['country'] = Tools::getValue('country');
        $helper->fields_value['telephone'] = Tools::getValue('telephone');
        $helper->fields_value['fax'] = Tools::getValue('fax');
        $helper->fields_value['email'] = Tools::getValue('email');
        $helper->fields_value['homepage'] = Tools::getValue('homepage');
        $helper->fields_value['estimatedClaims'] = Tools::getValue('estimatedClaims');
        $helper->fields_value['voucher'] = Tools::getValue('voucher');
        $helper->fields_value['owner'] = Tools::getValue('owner');
        $helper->fields_value['bankCode'] = Tools::getValue('bankCode');
        $helper->fields_value['bankName'] = Tools::getValue('bankName');
        $helper->fields_value['accountNumber'] = Tools::getValue('accountNumber');
        $helper->fields_value['iban'] = Tools::getValue('iban');
        $helper->fields_value['bic'] = Tools::getValue('bic');

        return $helper->generateForm($this->getFormFieldsRegistration());
    }

    protected function getFormFieldsRegistration()
    {
        $order_states = OrderState::getOrderStates($this->context->language->id);
        $order_states_for_checkboxes = array();

        foreach ($order_states as $order_state_key => $order_state) {
            $order_states_for_checkboxes[$order_state_key] = $order_state;
            $order_states_for_checkboxes[$order_state_key]['val'] = $order_state['id_order_state'];
        }

        return array(
            array(
                'form' => array(
                    'id_form' => 'registration_general',
                    'legend' => array(
                        'title' => $this->l('Registration'),
                        'icon' => 'icon-circle',
                    ),
                    'input' => array(
                        array(
                            'name' => 'address',
                            'type' => 'text',
                            'label' => $this->l('Salutation'),
                            'desc' => $this->l(''),
                            'required' => false
                        ),
                        array(
                            'name' => 'firstname',
                            'type' => 'text',
                            'label' => $this->l('First name'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'lastname',
                            'type' => 'text',
                            'label' => $this->l('Last name'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'company',
                            'type' => 'text',
                            'label' => $this->l('Company'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'legalForm',
                            'type' => 'text',
                            'label' => $this->l('Legal form of the company'),
                            'desc' => $this->l(''),
                            'required' => false
                        ),
                        array(
                            'name' => 'street',
                            'type' => 'text',
                            'label' => $this->l('Street with house number'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'postcode',
                            'type' => 'text',
                            'label' => $this->l('Postcode'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'city',
                            'type' => 'text',
                            'label' => $this->l('City'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'country',
                            'type' => 'select',
                            'label' => $this->l('Country'),
                            'desc' => $this->l(''),
                            'required' => true,
                            'options' => array(
                                'default' => array('value' => 0, 'label' => $this->l('---------')),
                                'query' => Country::getCountries($this->context->language->id),
                                'id' => 'iso_code',
                                'name' => 'country',
                            )
                        ),
                        array(
                            'name' => 'telephone',
                            'type' => 'text',
                            'label' => $this->l('Phone'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'fax',
                            'type' => 'text',
                            'label' => $this->l('Fax'),
                            'desc' => $this->l(''),
                        ),
                        array(
                            'name' => 'email',
                            'type' => 'text',
                            'label' => $this->l('E-mail'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'homepage',
                            'type' => 'text',
                            'label' => $this->l('Homepage'),
                            'desc' => $this->l(''),
                        ),
                        array(
                            'name' => 'estimatedClaims',
                            'type' => 'text',
                            'label' => $this->l('Expected number of collection per month'),
                            'desc' => $this->l(''),
                            'required' => false
                        ),
                        array(
                            'name' => 'voucher',
                            'type' => 'text',
                            'label' => $this->l('Voucher'),
                            'desc' => $this->l(''),
                        ),
                    )
                )
            ),
            array(
                'form' => array(
                    'id_form' => 'bank_account',
                    'legend' => array(
                        'title' => $this->l('Bank account'),
                        'icon' => 'icon-circle',
                    ),
                    'description' => $this->l('It is not obligatory to fill this section.'),
                    'input' => array(
                        array(
                            'name' => 'owner',
                            'type' => 'text',
                            'label' => $this->l('Owner'),
                            'desc' => $this->l(''),
                            'required' => false,
                        ),
                        array(
                            'name' => 'bankCode',
                            'type' => 'text',
                            'label' => $this->l('Bank code'),
                            'desc' => $this->l(''),
                            'required' => false,
                        ),
                        array(
                            'name' => 'bankName',
                            'type' => 'text',
                            'label' => $this->l('Bank name'),
                            'desc' => $this->l(''),
                            'required' => false,
                        ),
                        array(
                            'name' => 'accountNumber',
                            'type' => 'text',
                            'label' => $this->l('Account Number'),
                            'desc' => $this->l(''),
                            'required' => false,
                        ),
                        array(
                            'name' => 'iban',
                            'type' => 'text',
                            'label' => $this->l('IBAN Number'),
                            'desc' => $this->l(''),
                        ),
                        array(
                            'name' => 'bic',
                            'type' => 'text',
                            'label' => $this->l('BIC Number'),
                            'desc' => $this->l(''),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Register'),
                        'name' => 'submitRegistrationForm',
                    )
                ),
            ),
        );
    }

    protected function displayFormSettings()
    {
        $helper = new HelperForm();

        // Helper Options
        $helper->required = false;
        $helper->id = Tab::getCurrentTabId();

        // Helper
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->table = '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->identifier = null;
        $helper->toolbar_btn = null;
        $helper->ps_help_context = null;
        $helper->title = null;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = false;
        $helper->bootstrap = true;

        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        if (_PS_VERSION_ < '1.6.0.0') {
            $helper->show_toolbar = false;

            $helper->title = $this->displayName;
        }

        $helper->fields_value['MEDIAFINANZ_CLIENT_ID'] = Tools::getValue('MEDIAFINANZ_CLIENT_ID', Configuration::get('MEDIAFINANZ_CLIENT_ID'));
        $helper->fields_value['MEDIAFINANZ_CLIENT_KEY'] = Tools::getValue('MEDIAFINANZ_CLIENT_KEY', Configuration::get('MEDIAFINANZ_CLIENT_KEY'));
        $helper->fields_value['MEDIAFINANZ_LOG_ENABLED'] = Tools::getValue('MEDIAFINANZ_LOG_ENABLED', Configuration::get('MEDIAFINANZ_LOG_ENABLED'));
        $helper->fields_value['log_information'] = $this->displayLogInformation();
        $helper->fields_value['MEDIAFINANZ_SANDBOX'] = Tools::getValue('MEDIAFINANZ_SANDBOX', Configuration::get('MEDIAFINANZ_SANDBOX'));

        $init_os = $this->getInitOSSettings();
        if (is_array($init_os)) {
            foreach ($init_os as $os_id) {
                $helper->fields_value['MEDIAFINANZ_REMINDER_INIT_OS_' . $os_id] = Tools::getValue('MEDIAFINANZ_REMINDER_INIT_OS_' . $os_id, $os_id);
            }
        }

        $helper->fields_value['MEDIAFINANZ_REMINDER_DAYS'] = $this->displayReminderDays();
        $helper->fields_value['MEDIAFINANZ_LAST_REMINDER_DAYS'] = $this->displayLastReminderDays();
        $helper->fields_value['MEDIAFINANZ_REMINDER_INFO'] = Tools::getValue('MEDIAFINANZ_REMINDER_INFO', Configuration::get('MEDIAFINANZ_REMINDER_INFO'));
        $helper->fields_value['MEDIAFINANZ_SEND_OS_MAILS'] = Tools::getValue('MEDIAFINANZ_SEND_OS_MAILS', Configuration::get('MEDIAFINANZ_SEND_OS_MAILS'));
        $helper->fields_value['cron_information'] = $this->displayCronInformation();

        $helper->fields_value['MEDIAFINANZ_CLAIM_TYPE'] = Tools::getValue('MEDIAFINANZ_CLAIM_TYPE', Configuration::get('MEDIAFINANZ_CLAIM_TYPE'));
        $helper->fields_value['MEDIAFINANZ_OVERDUEFEES'] = Tools::getValue('MEDIAFINANZ_OVERDUEFEES', Configuration::get('MEDIAFINANZ_OVERDUEFEES'));
        $helper->fields_value['MEDIAFINANZ_NOTE'] = Tools::getValue('MEDIAFINANZ_NOTE', Configuration::get('MEDIAFINANZ_NOTE'));

        return $helper->generateForm($this->getFormFieldsSettings());
    }

    protected function getFormFieldsSettings()
    {
        $order_states = OrderState::getOrderStates($this->context->language->id);
        $order_states_for_checkboxes = array();

        //exclude module orderstates
        $excluded_order_states = array(Configuration::get('PS_OS_MF_REMINDER'),
            Configuration::get('PS_OS_MF_LASTREMINDER'),
            Configuration::get('PS_OS_MF_INKASSO'));

        foreach ($order_states as $order_state_key => $order_state) {
            if (!in_array($order_state['id_order_state'], $excluded_order_states)) {
                $order_states_for_checkboxes[$order_state_key] = $order_state;
                $order_states_for_checkboxes[$order_state_key]['val'] = $order_state['id_order_state'];
            }
        }

        return array(
            array(
                'form' => array(
                    'id_form' => 'global_settings',
                    'legend' => array(
                        'title' => $this->l('Global settings'),
                        'icon' => 'icon-circle',
                    ),
                    'description' => $this->l('Please fill in the form with Client ID and Client Licence Key. Testing purposes please enable the Sandbox mode. You also can enable the log if you want to write action logs in a file.'),
                    'input' => array(
                        array(
                            'name' => 'MEDIAFINANZ_CLIENT_ID',
                            'type' => 'text',
                            'label' => $this->l('Client ID'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_CLIENT_KEY',
                            'type' => 'text',
                            'label' => $this->l('Client licence'),
                            'desc' => $this->l(''),
                            'required' => true
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_SANDBOX',
                            'type' => (_PS_VERSION_ < '1.6.0.0') ? 'radio' : 'radio',
                            'label' => $this->l('Mode'),
                            'desc' => $this->l('Select "Sandbox" for testing'),
                            'class' => (_PS_VERSION_ < '1.6.0.0') ? 't' : '',
                            //'is_bool' => true,
                            'disabled' => false,
                            'values' => array(
                                array(
                                    'id' => 'sandbox_off',
                                    'value' => 0,
                                    'label' => $this->l('Live')
                                ),
                                array(
                                    'id' => 'sanbox_on',
                                    'value' => 1,
                                    'label' => $this->l('Sandbox')
                                ),
                            ),
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_LOG_ENABLED',
                            'type' => (_PS_VERSION_ < '1.6.0.0') ? 'radio' : 'switch',
                            'label' => $this->l('Enable Log'),
                            'desc' => $this->l('Logs of actions in') . ' ' . DIRECTORY_SEPARATOR . 'logs ' .
                                $this->l('directory. By activating you can log your claim history. Please notice: logs information can take a lot of disk space after a time.'),
                            'class' => (_PS_VERSION_ < '1.6.0.0') ? 't' : '',
                            'is_bool' => true,
                            'disabled' => false,
                            'values' => array(
                                array(
                                    'value' => 1,
                                ),
                                array(
                                    'value' => 0,
                                )
                            ),
                        ),
                        array(
                            'type' => 'free',
                            'name' => 'log_information',
                        ),
                    )
                )
            ),
            array(
                'form' => array(
                    'id_form' => 'reminder_settings',
                    'legend' => array(
                        'title' => $this->l('Reminder settings'),
                        'icon' => 'icon-circle',
                    ),
                    'description' => '<p>' . $this->l('You need to select order states which will initiate reminder after "Number of days for reminder". Days: order state will be changed to "Reminder" and mail will be sent to customer. If order current state is "Reminder", then after "Number of days for last reminder" days from "Reminder" date, script will change order state to "Last reminder" and mail will be sent to customer. You can create claim for order if 10 days had been passed after date of creating order and if 5 days had been passed after date of "Last reminder" order state. You need to set cron job for change order states automatically.') .
                        '</p><p>' . $this->l('Claim transfer to mediafinanz') .
                        '</p><p>' . $this->l('if a customer doesn\'t pay after a first remnider, legal requirements for a claim are fulfilled. You can manually transfer the invoice to mediafinanz by clicking the claim button at the bottom of the orders list') . '</p>',
                    'input' => array(
                        array(
                            'name' => 'MEDIAFINANZ_REMINDER_INIT_OS',
                            'type' => 'checkbox',
                            'label' => $this->l('Order statuses for initiating reminder'),
                            'desc' => $this->l('The order statuses of order which will be used for initiating reminder.'),
                            'values' => array(
                                'query' => $order_states_for_checkboxes,
                                'id' => 'id_order_state',
                                'name' => 'name',
                            )
                        ),
                        array(
                            'type' => 'free',
                            'name' => 'MEDIAFINANZ_REMINDER_DAYS'
                        ),
                        array(
                            'type' => 'free',
                            'name' => 'MEDIAFINANZ_LAST_REMINDER_DAYS'
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_SEND_OS_MAILS',
                            'type' => (_PS_VERSION_ < '1.6.0.0') ? 'radio' : 'switch',
                            'label' => $this->l('Enable sending mails'),
                            'desc' => $this->l('Enable sending mails to client about changing order status related to Mediafinanz module'),
                            'class' => (_PS_VERSION_ < '1.6.0.0') ? 't' : '',
                            'is_bool' => true,
                            'disabled' => false,
                            'values' => array(
                                array(
                                    'value' => 1,
                                ),
                                array(
                                    'value' => 0,
                                )
                            ),
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_REMINDER_INFO',
                            'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 5,
                            'label' => $this->l('Bank account'),
                            'desc' => $this->l('Please enter the information on your bank account'),
                        ),
                        array(
                            'type' => 'free',
                            'name' => 'cron_information',
                        ),
                    ),
                ),
            ),
            array(
                'form' => array(
                    'id_form' => 'predefined_values',
                    'legend' => array(
                        'title' => $this->l('Predefined values of claim'),
                        'icon' => 'icon-circle',
                    ),
                    'description' => $this->l('You can set predefined values for fields of "new claims" form.'),
                    'input' => array(
                        array(
                            'name' => 'MEDIAFINANZ_CLAIM_TYPE',
                            'type' => 'select',
                            'label' => $this->l('Predefined Type of Claims'),
                            'desc' => $this->l(''),
                            'options' => array(
                                'default' => array('value' => 0, 'label' => $this->l('---------')),
                                'query' => $this->getOptionsFromArray($this->getClaimTypes()),
                                'id' => 'id',
                                'name' => 'name',
                            )
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_OVERDUEFEES',
                            'type' => 'text',
                            'label' => $this->l('Predefined overdue fee'),
                            'desc' => $this->l(''),
                        ),
                        array(
                            'name' => 'MEDIAFINANZ_NOTE',
                            'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 5,
                            'label' => $this->l('Predefined note for mediafinanz clerk'),
                            'desc' => $this->l(''),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save options'),
                        'name' => 'submitSaveOptions',
                    )
                )
            )
        );
    }

    public static function getActiveEuroCurrencyID($id_shop = 0)
    {
        if ($id_shop == 0)
            $id_shop = Shop::getContextShopID();
        $currency_id = Currency::getIdByIsoCode('EUR', $id_shop);

        if ($currency_id > 0) {
            $currency = Currency::getCurrencyInstance($currency_id);

            if ($currency->active == 1)
                return $currency_id;
        }
        return false;
    }

    public function registerClient($client_registration, $bank_account)
    {
        $options = array(
            'trace' => 1,
            'compression' => true,
            'exceptions' => true
        );

        $wsdl_headers = get_headers(self::$partner_api_url);
        $http_text = explode(' ', $wsdl_headers[0]);
        $http_code = (int)$http_text[1];

        if ($http_code != 200)
            throw new Exception($this->l('Error! No access to SOAP service.'));
        else {
            $soap_client = new SoapClient(self::$partner_api_url, $options);

            $auth = array(
                'applicationId' => self::$partner_application_id,
                'licenceKey' => self::$partner_service_key,
                'sandbox' => (bool)false //Configuration::get('MEDIAFINANZ_SANDBOX')
            );

            if ($soap_client)
                return $soap_client->registerClient($auth, $client_registration, $bank_account);
            else
                return false;
        }
    }

    public function generateRegistrationKey()
    {
        $options = array(
            'trace' => 1,
            'compression' => true,
            'exceptions' => true
        );

        $wsdl_headers = get_headers(self::$partner_api_url);
        $http_text = explode(' ', $wsdl_headers[0]);
        $http_code = (int)$http_text[1];

        if ($http_code != 200)
            throw new Exception($this->l('Error! No access to SOAP service.'));
        else
            $soap_client = new SoapClient(self::$partner_api_url, $options);

        $auth = array(
            'applicationId' => self::$partner_application_id,
            'licenceKey' => self::$partner_service_key,
            'sandbox' => (bool)false
        );

        if ($soap_client)
            return $soap_client->generateRegistrationKey($auth);

        return false;
    }

    public function getSoapClient()
    {
        if (self::$soap_client)
            return self::$soap_client;

        $options = array(
            'trace' => 1,
            'compression' => true,
            'exceptions' => true
        );

        $wsdl_headers = get_headers(self::$api_url);
        $http_text = explode(' ', $wsdl_headers[0]);
        $http_code = (int)$http_text[1];

        if ($http_code != 200)
            throw new Exception($this->l('Error! No access to SOAP service.'));
        else
            return new SoapClient(self::$api_url, $options);

        return false;
    }

    public function isModuleConfigurationCompleted($id_shop = null)
    {
        return ((int)Configuration::get('MEDIAFINANZ_CLIENT_ID', null, null, $id_shop) > 0);
    }

    public function getAuth($id_shop = null)
    {
        if (!$this->isModuleConfigurationCompleted($id_shop)) {
            $text_excpetion = $this->l('Mediafinanz module has not been configured for this shop');
            throw new Exception($text_excpetion);
        }
        return array(
            'clientId' => Configuration::get('MEDIAFINANZ_CLIENT_ID', null, null, $id_shop),
            'licenceKey' => md5(Mediafinanz::$application_key . Configuration::get('MEDIAFINANZ_CLIENT_KEY', null, null, $id_shop)),
            'sandbox' => Configuration::get('MEDIAFINANZ_SANDBOX', null, null, $id_shop)
        );
    }

    public function checkClientAccount($client_id, $client_key)
    {
        $soap_client = $this->getSoapClient();

        $auth = array(
            'clientId' => $client_id,
            'licenceKey' => md5(Mediafinanz::$application_key . $client_key),
            'sandbox' => (!Configuration::get('MEDIAFINANZ_SANDBOX')) ? 'true' : Configuration::get('MEDIAFINANZ_SANDBOX')
        );

        return $soap_client->getPayoutList($auth);
    }

    public function getPayoutList($id_shop = null)
    {
        $soap_client = self::getSoapClient();
        if ($soap_client) {
            try {
                $result = $soap_client->getPayoutList($this->getAuth($id_shop));
                return $result;
            } catch (Exception $e) {
                self::logToFile($e->getMessage(), 'general');
                return false;
            }
        } else
            return false;
    }

    public function updateClaimsStatuses($id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $result = $soap_client->getClaimStatusChanges($this->getAuth($id_shop));
        $transaction_id = $result->transactionId;

        foreach ($result->changes as $order) {
            $claim = MediafinanzClaim::getInstanceByFilenumber($order->fileNumber);
            if (Validate::isLoadedObject($claim)) {
                $claim->status_code = $order->statusCode;
                $claim->status_text = $order->statusName;
                $details = '';
                if (isset($order->closingReasonExplanation))
                    $details = $order->closingReasonExplanation;

                if (isset($order->closingReasonNote))
                    $details .= "\n " . $order->closingReasonNote;

                $claim->status_details = $details;

                $claim->date_change = date('Y-m-d H:i:s', strtotime($order->time));
                $claim->save();
            }
        }

        Configuration::updateValue('MEDIAFINANZ_LASTSTATUSUPDATE', date('Y-m-d H:i:s'));
        if ($soap_client->commitTransaction($this->getAuth($id_shop), $transaction_id))
            return true;
    }

    public function getClaimStatus($file_number, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        $res = $soap_client->getClaimStatus($this->getAuth($id_shop), $claim_identifier);
        return $res;
    }

    public function getClaimAccountingSummary($file_number, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->getClaimAccountingSummary($this->getAuth($id_shop), $claim_identifier);
    }

    public function getClaimHistory($file_number, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->getClaimHistory($this->getAuth($id_shop), $claim_identifier);
    }

    public function bookDirectPayment($file_number, $direct_payment, $id_shop = null)
    {
        $soap_client = $this->getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->bookDirectPayment($this->getAuth($id_shop), $claim_identifier, $direct_payment);
    }

    public function newClaim($claim, $debtor, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        return $soap_client->newClaim($this->getAuth($id_shop), $claim, $debtor);
    }

    public function closeClaim($file_number, $id_shop = null)
    {
        $soap_client = $this->getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->closeClaim($this->getAuth($id_shop), $claim_identifier);
    }

    public function getClaimOptions($file_number, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->getClaimOptions($this->getAuth($id_shop), $claim_identifier);
    }

    public function getNewMessages($id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $result = $soap_client->getNewMessages($this->getAuth($id_shop));
        $transaction_id = $result->transactionId;

        foreach ($result->messages as $message) {
            $claim = MediafinanzClaim::getInstanceByFilenumber($message->fileNumber);
            if (Validate::isLoadedObject($claim)) {
                $new_message = new MediafinanzNewMessage();
                $new_message->id_order = $claim->id_order;
                $new_message->id_shop = $claim->id_shop;
                $new_message->file_number = (int)$message->fileNumber;
                $new_message->invoice_number = (int)$message->invoiceNumber;
                $new_message->text = $message->text;
                $new_message->time = date('Y-m-d H:i:s', strtotime($message->time));
                $new_message->add();
            }
        }

        Configuration::updateValue('MEDIAFINANZ_LASTMESSAGEUPDATE', date('Y-m-d H:i:s'), false, null, $id_shop);

        if ($soap_client->commitTransaction($this->getAuth($id_shop), $transaction_id))
            return true;
    }

    public function getMessageHistory($file_number, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->getMessageHistory($this->getAuth($id_shop), $claim_identifier);
    }

    public function sendMessage($file_number, $message, $id_shop = null)
    {
        $soap_client = self::getSoapClient();
        $claim_identifier = array('fileNumber' => $file_number);
        return $soap_client->sendMessage($this->getAuth($id_shop), $claim_identifier, $message);
    }

    public function getClaimDetails($claim_object, $id_shop = null)
    {
        $current_status = $claim_object->status_code;

        //try to get new status
        $claim_status = $this->getClaimStatus($claim_object->file_number, $id_shop);

        $accounting_summary = $this->getClaimAccountingSummary($claim_object->file_number, $id_shop);

        if (isset($claim_status) && is_object($claim_status)) {
            //update status in database, if changed:
            if ($current_status != $claim_status->statusCode) {
                $claim_object->status_code = $claim_status->statusCode;
                $claim_object->status_text = $claim_status->statusText;

                if (isset($claim_status->statusDetails))
                    $claim_object->status_details = $claim_status->statusDetails;
                else
                    $claim_object->status_details = '';

                $claim_object->date_change = date('Y-m-d H:i:s');
                $claim_object->save();
            }
        }

        $claim_object->payoutHistory = array();
        if ($accounting_summary) {
            //add payout details:
            $claim_object->totalDebts = $accounting_summary->totalDebts;
            $claim_object->paid = $accounting_summary->paid;
            $claim_object->outstanding = $accounting_summary->outstanding;
            $claim_object->currentPayout = $accounting_summary->currentPayout;
            $claim_object->sumPayout = $accounting_summary->sumPayout;

            if (isset($accounting_summary->payoutHistory)) {
                $payout_history = array();

                if (is_object($accounting_summary->payoutHistory))
                    $payout_history[] = array(
                        'date' => $accounting_summary->payoutHistory->date,
                        'total' => $accounting_summary->payoutHistory->total,
                        'payoutNumber' => $accounting_summary->payoutHistory->payoutNumber
                    );
                elseif (is_array($accounting_summary->payoutHistory)) {
                    foreach ($accounting_summary->payoutHistory as $history_entry) {
                        $payout_history[] = array(
                            'date' => $history_entry->date,
                            'total' => $history_entry->total,
                            'payoutNumber' => $history_entry->payoutNumber
                        );
                    }
                }
                $claim_object->payoutHistory = $payout_history;
            }
        }

        return $claim_object;
    }

    public function getClaimTypes()
    {
        return array(
            1 => $this->l('Goods'),
            2 => $this->l('Goods by bankwire'),
            3 => $this->l('Services'),
        );
    }

    public function getInstructionsForAssigningClaimToLawyer()
    {
        /*
        1 -  nur Mahnbescheid (und Vollstreckungsbescheid) beantragen (keine Klage)
        2 - erst Mahnbescheid (und Vollstreckungsbescheid) beantragen und bei Widerspruch Klage erheben
        3 - sofort Klage erheben (kein Mahnbescheid)
        */

        return array(
            1 => $this->l('Instruction 1'),
            2 => $this->l('Instruction 2'),
            3 => $this->l('Instruction 3'),
        );
    }

    public function getMissionDepthes()
    {
        /*
        7 - Stufe 1 + 2 (Datenbankrecherche)
        8 - Eine Einwohnermeldeamtsanfrage (ohne Stufe 1 + 2)
        15 - Stufe 1 + 2 plus max. einer Einwohnermeldeamtsanfrage
        24 - Mehrere Einwohnermeldeamtsanfragen (falls nötig) (ohne Stufe 1 + 2)
        31 - Stufe 1 + 2 plus mehreren Einwohnermeldeamtsanfragen (falls nötig)
        */

        return array(
            7 => $this->l('Level 1 + 2 (database search)'),
            8 => $this->l('A registration office request (without Stage 1 + 2)'),
            15 => $this->l('Level 1 + 2 plus max. registration office request'),
            24 => $this->l('Several requests in registration office (if necessary) (without level 1 + 2)'),
            31 => $this->l('Level 1 + 2 plus several requests in registration office (if necessary)'),
        );
    }

    public static function getOrderReason($id_order)
    {
        $reason = '';
        $order_details = OrderDetail::getList($id_order);
        foreach ($order_details as $order_detail)
            $reason .= $order_detail['product_quantity'] . ' x ' . $order_detail['product_name'] . '; ';

        return Tools::substr($reason, 0, 250);
    }


    public function getOptionsFromArray($arr)
    {
        $return_arr = array();
        foreach ($arr as $k => $v)
            $return_arr[] = array('id' => $k, 'name' => $v);

        return $return_arr;
    }

    public static function getGroupReminderSettings()
    {
        return Tools::jsonDecode(Configuration::get('MEDIAFINANZ_GROUP_REM'), true);
    }

    public static function getGroupLastReminderSettings()
    {
        return Tools::jsonDecode(Configuration::get('MEDIAFINANZ_GROUP_LASTREM'), true);
    }

    public static function getInitOSSettings()
    {
        return Tools::jsonDecode(Configuration::get('MEDIAFINANZ_REMINDER_INIT_OS'), true);
    }

    public function getCurrentModeTitle($id_shop = null)
    {
        return (Configuration::get('MEDIAFINANZ_SANDBOX', null, null, $id_shop) == 1) ? $this->l('sandbox') : $this->l('live');
    }

    private function getGroups($id_lang, $id_shop = false)
    {
        $groups = Group::getGroups($id_lang, $id_shop);
        foreach ($groups as $group_key => $group) {
            //remove visitors
            if ($group['id_group'] == Configuration::get('PS_UNIDENTIFIED_GROUP')) {
                unset($groups[$group_key]);
                break;
            }
        }
        return $groups;
    }

    private function displayReminderDays()
    {
        $groups = $this->getGroups($this->context->language->id, true);
        $group_reminder_days = Tools::getValue('groupReminderDays');

        if (count($groups)) {
            $group_values = self::getGroupReminderSettings();

            foreach ($groups as &$group) {
                $value = '';
                if (isset($group_reminder_days[$group['id_group']]))
                    $value = $group_reminder_days[$group['id_group']];
                else {
                    if (isset($group_values[$group['id_group']]))
                        $value = $group_values[$group['id_group']];
                }

                $group['value'] = $value;
            }

            $this->smarty->assign(
                array(
                    'groupreminder_data' => $groups
                )
            );
        }
        return $this->display(__FILE__, 'views/templates/admin/group_reminder.tpl');
    }

    private function displayLastReminderDays()
    {
        $groups = $this->getGroups($this->context->language->id, true);
        $group_lastreminder_days = Tools::getValue('groupLastReminderDays');

        if (count($groups)) {
            $group_values = self::getGroupLastReminderSettings();

            foreach ($groups as &$group) {
                $value = '';
                if (isset($group_lastreminder_days[$group['id_group']]))
                    $value = $group_lastreminder_days[$group['id_group']];
                else {
                    if (isset($group_values[$group['id_group']]))
                        $value = $group_values[$group['id_group']];
                }

                $group['value'] = $value;
            }

            $this->smarty->assign(
                array(
                    'groupreminder_data' => $groups
                )
            );
        }
        return $this->display(__FILE__, 'views/templates/admin/group_lastreminder.tpl');
    }

    private function displayCronInformation()
    {
        $this->smarty->assign(array(
            'cron_path' => dirname(__FILE__) . DIRECTORY_SEPARATOR,
            'secure_key' => Configuration::getGlobalValue('MEDIAFINANZ_SECURE_KEY')
        ));

        return $this->display(__FILE__, 'views/templates/admin/cron_information.tpl');
    }

    private function displayLogInformation()
    {
        $this->smarty->assign(array(
            'general_log_file_path' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&log_file=general',
            'cron_log_file_path' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&log_file=cron',
        ));

        return $this->display(__FILE__, 'views/templates/admin/log_information.tpl');
    }

    public function postProcess()
    {
        //check active Euro currency
        if ((bool)self::getActiveEuroCurrencyID() == false)
            $this->_errors[] = $this->l('Euro is not active currency for current shop. You have to add it for using this module.');

        //check not installed order states
        $not_installed_states = $this->getNotInstalledMediafinanzOrderStates();
        if (count($not_installed_states) > 0) {
            $state_names = array();
            foreach ($not_installed_states as $not_installed_state)
                $state_names[] = $not_installed_state['name'];

            $this->_errors[] = $this->l('This order statuses do not exist in shop') . ': ' . implode(', ', $state_names);
        }

        if (Tools::getIsset('log_file')) {
            if (in_array(Tools::getValue('log_file'), array('general', 'cron'))) {
                $key = Tools::getValue('log_file');
                $file_path = dirname(__FILE__) . '/logs/log_' . $key . '.txt';
                $file_content = '';
                if (file_exists($file_path)) {
                    $file_content = file_get_contents($file_path);
                }
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename=' . $key . '.txt');
                echo $file_content;
                exit;
            }
        }

        //generate registration key if it's not existed
        if (Tools::isSubmit('submitGenerateRegistrationKey')) {
            try {
                $registration_key = $this->generateRegistrationKey();
                if ($registration_key != '') {
                    Configuration::updateValue('MEDIAFINANZ_REGISTRATIONKEY', $registration_key);
                    $this->_confirmations[] = $this->l('Registration key has been created');
                } else
                    $this->_errors[] = $this->l('Registration key has not been created');
            } catch (Exception $e) {
                $this->_errors[] = $this->l('Registration key has not been created');
                $this->_errors[] = $e->getMessage();
                self::logToFile($e->getMessage(), 'general');
            }
        }

        if (Tools::isSubmit('submitRegistrationForm')) {
            $form_errors = array();
            if (Tools::getValue('address') == '')
                $form_errors[] = $this->_errors[] = $this->l('Salutation is empty');
            if (Tools::getValue('firstname') == '')
                $form_errors[] = $this->_errors[] = $this->l('Firstname is empty');
            if (Tools::getValue('lastname') == '')
                $form_errors[] = $this->_errors[] = $this->l('Lastname is empty');
            if (Tools::getValue('company') == '')
                $form_errors[] = $this->_errors[] = $this->l('Company is empty');
            if (Tools::getValue('legalForm') == '')
                $form_errors[] = $this->_errors[] = $this->l('Legal form is empty');
            if (Tools::getValue('street') == '')
                $form_errors[] = $this->_errors[] = $this->l('Street is empty');
            if (Tools::getValue('postcode') == '')
                $form_errors[] = $this->_errors[] = $this->l('Postcode is empty');
            if (Tools::getValue('city') == '')
                $form_errors[] = $this->_errors[] = $this->l('City is empty');
            if (Tools::getValue('country') == '')
                $form_errors[] = $this->_errors[] = $this->l('Country is empty');
            if (Tools::getValue('telephone') == '')
                $form_errors[] = $this->_errors[] = $this->l('Phone is empty');
            if (Tools::getValue('email') == '')
                $form_errors[] = $this->_errors[] = $this->l('E-mail is empty');
            if (Tools::getValue('estimatedClaims') == '')
                $form_errors[] = $this->_errors[] = $this->l('Estimated quantity of claims is empty');
            if (!Validate::isUnsignedInt(Tools::getValue('estimatedClaims')))
                $form_errors[] = $this->_errors[] = $this->l('Estimated quantity of claims must be number');

            $client_registration = array(
                'partnerCustomerId' => '',
                'address' => Tools::getValue('address'),
                'firstname' => Tools::getValue('firstname'),
                'lastname' => Tools::getValue('lastname'),
                'company' => Tools::getValue('company'),
                'legalForm' => Tools::getValue('legalForm'),
                'street' => Tools::getValue('street'),
                'postcode' => Tools::getValue('postcode'),
                'city' => Tools::getValue('city'),
                'country' => Tools::getValue('country'),
                'telephone' => Tools::getValue('telephone'),
                'fax' => Tools::getValue('fax'),
                'email' => Tools::getValue('email'),
                'homepage' => Tools::getValue('homepage'),
                'estimatedClaims' => Tools::getValue('estimatedClaims'),
                'voucher' => Tools::getValue('voucher'),
            );

            $bank_account = array(
                'owner' => Tools::getValue('owner'),
                'bankCode' => Tools::getValue('bankCode'),
                'bankName' => Tools::getValue('bankName'),
                'accountNumber' => Tools::getValue('accountNumber'),
                'iban' => Tools::getValue('iban'),
                'bic' => Tools::getValue('bic'),
            );

            if (count($form_errors) <= 0) {
                try {
                    if ($this->registerClient($client_registration, $bank_account))
                        $this->_confirmations[] = $this->l('Registration form has been sent');
                    else
                        $this->_errors[] = $this->l('Registration form has not been sent');
                } catch (Exception $e) {
                    $form_errors[] = $this->_errors[] = $this->l('Registration form has not been sent');
                    $form_errors[] = $this->_errors[] = $e->getMessage();
                    self::logToFile($e->getMessage(), 'general');
                }

            }
        }

        if (Tools::isSubmit('submitSaveOptions')) {
            $form_errors = array();

            $client_id = Tools::getValue('MEDIAFINANZ_CLIENT_ID');
            $client_key = Tools::getValue('MEDIAFINANZ_CLIENT_KEY');

            if ($client_id == '')
                $form_errors[] = $this->_errors[] = $this->l('Please enter cliend ID');

            if ($client_key == '')
                $form_errors[] = $this->_errors[] = $this->l('Please enter cliend key');

            try {
                $check_client = $this->checkClientAccount($client_id, $client_key);

                if (is_array($check_client)) {
                    if (!Configuration::updateValue('MEDIAFINANZ_CLIENT_ID', $client_id))
                        $form_errors[] = $this->_errors[] = $this->l('Could not update') . ': MEDIAFINANZ_CLIENT_ID';

                    if (!Configuration::updateValue('MEDIAFINANZ_CLIENT_KEY', $client_key))
                        $form_errors[] = $this->_errors[] = $this->l('Could not update') . ': MEDIAFINANZ_CLIENT_KEY';
                } else
                    $form_errors[] = $this->_errors[] = $this->l('Client ID and/or Client key are incorrect');
            } catch (Exception $e) {
                $form_errors[] = $this->_errors[] = $this->l('Client ID and/or Client key are incorrect');
                $form_errors[] = $this->_errors[] = $e->getMessage();
                self::logToFile($e->getMessage(), 'general');
            }

            $init_os = array();
            foreach ($_POST as $key => $value) {
                if (strncmp($key, 'MEDIAFINANZ_REMINDER_INIT_OS', Tools::strlen('MEDIAFINANZ_REMINDER_INIT_OS')) == 0)
                    $init_os[] = $value;
            }
            if (count($init_os) == 0)
                $form_errors[] = $this->_errors[] = $this->l('Please select at least one order state for initiating reminder');

            $group_reminder_days = Tools::getValue('groupReminderDays');
            $valid_group_reminder_days = true;
            if (is_array($group_reminder_days) && count($group_reminder_days)) {
                foreach ($group_reminder_days as $group_reminder_day) {
                    if (!Validate::isUnsignedInt($group_reminder_day))
                        $valid_group_reminder_days = false;
                }
            }
            if ($valid_group_reminder_days != true)
                $form_errors[] = $this->_errors[] = $this->l('Number of days for reminder must be number');

            $group_lastreminder_days = Tools::getValue('groupLastReminderDays');
            $valid_group_lastreminder_days = true;
            if (is_array($group_lastreminder_days) && count($group_lastreminder_days)) {
                foreach ($group_lastreminder_days as $group_lastreminder_day) {
                    if (!Validate::isUnsignedInt($group_lastreminder_day))
                        $valid_group_lastreminder_days = false;
                }
            }
            if ($valid_group_lastreminder_days != true)
                $form_errors[] = $this->_errors[] = $this->l('Number of days for last reminder must be number');

            /*
            $valid_reminder_days = true;
            foreach ($group_reminder_days as $day_key => $day)
            {
                if ($day > $group_lastreminder_days[$day_key])
                    $valid_reminder_days = false;
            }
            if ($valid_reminder_days == false)
                $form_errors[] = $this->_errors[] = $this->l('Number of days for last reminder must be more Number of days for reminder');
            */

            // predefined
            $overduefees = Tools::ps_round((float)str_replace(',', '.', Tools::getValue('MEDIAFINANZ_OVERDUEFEES')), 2);
            if (!Validate::isPrice($overduefees))
                $form_errors[] = $this->_errors[] = $this->l('Overdue fees must to be sum of money');

            $updated = true;
            $updated &= (count($form_errors) == 0);
            $updated &= Configuration::updateValue('MEDIAFINANZ_REMINDER_INFO', Tools::getValue('MEDIAFINANZ_REMINDER_INFO'));
            $updated &= Configuration::updateValue('MEDIAFINANZ_NOTE', Tools::getValue('MEDIAFINANZ_NOTE'));
            $updated &= Configuration::updateValue('MEDIAFINANZ_OVERDUEFEES', $overduefees);
            $updated &= Configuration::updateValue('MEDIAFINANZ_CLAIM_TYPE', (int)Tools::getValue('MEDIAFINANZ_CLAIM_TYPE'));
            $updated &= Configuration::updateValue('MEDIAFINANZ_GROUP_LASTREM', Tools::jsonEncode($group_lastreminder_days));
            $updated &= Configuration::updateValue('MEDIAFINANZ_GROUP_REM', Tools::jsonEncode($group_reminder_days));
            $updated &= Configuration::updateValue('MEDIAFINANZ_REMINDER_INIT_OS', Tools::jsonEncode($init_os));
            $updated &= Configuration::updateValue('MEDIAFINANZ_SEND_OS_MAILS', (int)Tools::getValue('MEDIAFINANZ_SEND_OS_MAILS'));
            $updated &= Configuration::updateValue('MEDIAFINANZ_LOG_ENABLED', (int)Tools::getValue('MEDIAFINANZ_LOG_ENABLED'));
            $updated &= Configuration::updateValue('MEDIAFINANZ_SANDBOX', (int)Tools::getValue('MEDIAFINANZ_SANDBOX'));

            if ($updated == true)
                $this->_confirmations[] = $this->l('Settings updated');
        }

        $messages = '';
        foreach ($this->_errors as $error)
            $messages .= $this->displayError($error);
        foreach ($this->_confirmations as $confirmation)
            $messages .= $this->displayConfirmation($confirmation);

        return $messages;
    }

    public function sendReminders()
    {
        $send_os_mails = (int)Configuration::get('MEDIAFINANZ_SEND_OS_MAILS');
        $id_order_state_for_reminder = (int)Configuration::get('PS_OS_MF_REMINDER');
        $id_order_state_for_lastreminder = (int)Configuration::get('PS_OS_MF_LASTREMINDER');
        if ($id_order_state_for_reminder == 0 && !Validate::isObjectLoaded(new OrderState((int)$id_order_state_for_reminder)))
            self::logToFile('cron has been stopped - "Reminder" order state has not been defined. Please check configuration.', 'cron');
        elseif ($id_order_state_for_lastreminder == 0 && !Validate::isObjectLoaded(new OrderState((int)$id_order_state_for_lastreminder)))
            self::logToFile('cron has been stopped - "Last Reminder" order state has not been defined. Please check configuration.', 'cron');
        else {
            $reminder_init_os = array();
            $reminder_days = array();
            $active_euro_shops = array();
            $orders = Db::getInstance()->executeS('SELECT a.`id_order`, a.`id_shop`, a.`current_state`, a.`id_lang`, a.`id_customer`, a.`id_currency`
			FROM ' . _DB_PREFIX_ . 'orders a LEFT JOIN ' . _DB_PREFIX_ . 'mf_claims c ON a.`id_order`=c.`id_order` WHERE c.`id_order` IS NULL');

            foreach ($orders as $order) {

                //check currency
                if (!isset($active_euro_shops[$order['id_shop']])) {
                    $active_euro_shops[$order['id_shop']] = $this->getActiveEuroCurrencyID($order['id_shop']);

                    $active_euro = $active_euro_shops[$order['id_shop']];
                } else
                    $active_euro = $active_euro_shops[$order['id_shop']];

                //$active_euro = 1; // for testing

                if ((int)$active_euro == 0)
                    self::logToFile('Order ' . $order['id_order'] . ' - euro is not active for shop', 'cron');
                elseif ($active_euro != $order['id_currency'])
                    self::logToFile('Order ' . $order['id_order'] . ' - euro is not currency of order', 'cron');
                else {
                    //get customer group
                    $id_customer_group = Customer::getDefaultGroupId($order['id_customer']);

                    // *send reminder*
                    //check current order state
                    if (!isset($reminder_init_os[$order['id_shop']])) {
                        $reminder_init_os[$order['id_shop']] = Tools::jsonDecode(Configuration::get('MEDIAFINANZ_REMINDER_INIT_OS',
                            null, null, $order['id_shop']), true);

                        $init_os = $reminder_init_os[$order['id_shop']];
                    } else
                        $init_os = $reminder_init_os[$order['id_shop']];

                    if (in_array($order['current_state'], $init_os)) {
                        // get days
                        if (!isset($reminder_days[$order['id_shop']])) {
                            $reminder_days[$order['id_shop']] = Tools::jsonDecode(Configuration::get('MEDIAFINANZ_GROUP_REM',
                                null, null, $order['id_shop']), true);

                            $days = $reminder_days[$order['id_shop']];
                        } else
                            $days = $reminder_days[$order['id_shop']];

                        $days_x = (int)$days[(string)$id_customer_group];

                        $order_obj = new Order($order['id_order']);

                        $history_entities = $order_obj->getHistory($order['id_lang'], $order['current_state']);
                        $last_history_entity = end($history_entities);
                        $date_reminder = $last_history_entity['date_add'];

                        $days_between = ceil(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($date_reminder)) / 86400);
                        if ($days_between >= $days_x) {
                            if ($this->changeOrderState($order['id_order'], $id_order_state_for_reminder))
                                self::logToFile('Order ' . $order['id_order'] . ' - reminder - successful.' . (($send_os_mails == 0) ? ' Sending mail is disabled' : ''), 'cron');
                            else
                                self::logToFile('Order ' . $order['id_order'] . ' - reminder - failed.' . (($send_os_mails == 0) ? ' Sending mail is disabled' : ''), 'cron');
                        }
                    }


                    //*send last reminder*
                    if ($order['current_state'] == $id_order_state_for_reminder) {
                        // get days
                        if (!isset($reminder_days[$order['id_shop']])) {
                            $reminder_days[$order['id_shop']] = Tools::jsonDecode(Configuration::get('MEDIAFINANZ_GROUP_LASTREM',
                                null, null, $order['id_shop']), true);

                            $days = $reminder_days[$order['id_shop']];
                        } else
                            $days = $reminder_days[$order['id_shop']];

                        $days_y = (int)$days[(string)$id_customer_group];

                        $order_obj = new Order($order['id_order']);

                        $history_entities = $order_obj->getHistory($order['id_lang'], $order['current_state']);
                        $last_history_entity = end($history_entities);
                        $date_reminder = $last_history_entity['date_add'];

                        $days_between = ceil(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($date_reminder)) / 86400);
                        if ($days_between >= $days_y) {
                            if ($this->changeOrderState($order['id_order'], $id_order_state_for_lastreminder))
                                self::logToFile('Order ' . $order['id_order'] . ' - lastreminder - successful.' . (($send_os_mails == 0) ? ' Sending mail is disabled' : ''), 'cron');
                            else
                                self::logToFile('Order ' . $order['id_order'] . ' - lastreminder - failed.' . (($send_os_mails == 0) ? ' Sending mail is disabled' : ''), 'cron');
                        }
                    }
                }
            }
        }
    }
}