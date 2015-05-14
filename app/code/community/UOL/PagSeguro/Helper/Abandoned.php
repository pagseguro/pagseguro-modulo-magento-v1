<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Abandoned extends HelperData
{
    // It is used to store the array of abandoned
    private $arrayAbandoned = array();

    // It active/disable abandoned for notification
    private $access = '';

    // It the code of admin
    private $admLocaleCode = '';

    /*
     * Checks that is active query abandoned
     * Checks if email was filled and token
     * Checks if email and token are valid
     * If not completed one or both, is directed and notified so it can be filled
     */
    public function checkAbandonedAccess($days)
    {
        // Abandoned access
        $this->access = 1;

        if (!is_null($days)) {
            $this->days = $days;
        }

        // Displays this error in title
        $module = 'PagSeguro - ';

        // Receive url editing methods ja payment with key
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');

        if ($this->requestPaymentMethod()->getConfigData('abandoned') == 0) {
            $this->access = 0;
            $message =  $module . $this->__('Consulta de transações abandonadas está desativado.');
            Mage::getSingleton('core/session')->addError($message);
            Mage::app()->getResponse()->setRedirect($configUrl);
        } else {
            // Check if email and token was filled
            $this->checkTransactionAccess();
        }
    }

    /**
     * Filters by payments PagSeguro containing the same request Store
     * @var int $orderId - Id of order
     * @var string $info->getRecoveryCode() - Abandoned code for recovery transaction
     * @method array $this->createArrayAbandoned - Stores the array that contains transactions abandoned at PagSeguro
     */
    private function getMagentoAbandoned()
    {
        $reference = $this->getStoreReference();
        $abandonedtList = $this->requestWebservice()->getPagSeguroAbandonedList();
        $this->arrayAbandoned = '';

        if ($abandonedtList) {
            foreach ($abandonedtList as $info) {
                if ($reference == $this->getReferenceDecrypt($info->getReference())) {
                    $orderId = $this->getReferenceDecryptOrderID($info->getReference());
                    $order = Mage::getModel('sales/order')->load($orderId);

                    if ($_SESSION['store_id'] != '') {
                        if ($order->getStoreId() == $_SESSION['store_id']) {
                            $this->createArrayAbandoned($orderId, $info->getRecoveryCode());
                        }
                    } elseif ($order) {
                        $this->createArrayAbandoned($orderId, $info->getRecoveryCode());
                    }

                    $_SESSION['store_id'] == '';
                }
            }
        }
    }

    /**
     * Creates the complete array with the necessary information for the table
     * @param int $orderId - Id of order of Magento
     * @param string $recoveryCode - Code of recovery transaction in PagSeguro
     * @method array $this->arrayAbandoned - Set the complete array with the necessary information for the table
     */
    private function createArrayAbandoned($orderId, $recoveryCode)
    {
        // force default time zone
        date_default_timezone_set(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

        // Receives the parameter used to send e-mail
        $config = $orderId . '/' . $recoveryCode;

        // Checkbox of selection for send e-mail
        $checkbox  = "<label class='chk_email'>";
        $checkbox .= "<input type='checkbox' name='send_emails[]' data-config='" . $config . "' />";
        $checkbox .= "</label>";

        // Receives the object of order that was entered the id
        $order = Mage::getModel('sales/order')->load($orderId);

        // Receives the creation date of the application which is converted to the format d/m/Y
        $dateOrder = Mage::app()->getLocale()->date($order->getCreatedAt(), null, null, true);

        // Receives the number of order
        $idMagento = '#' . $order->getIncrementId();

        $validity_link = $this->getAbandonedDateAddDays(10, $order->getCreatedAt());

        // Receives the url edit order it from your id
        $editUrl = $this->getEditOrderUrl($orderId);
        $editText = $this->__('Ver detalhes');

        // Receives the full html link to edit an order
        $editOrder .= "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($orderId) . "'>";
        $editOrder .= $this->__('Ver detalhes') . "</a>";

        $sent = $this->getSentEmailsById($orderId);
        $sent = current($sent);

        if (empty($sent)) {
            $sent = 0;
        }

        $array = array('checkbox' => $checkbox,
                       'date' => $dateOrder,
                       'id_magento' => $idMagento,
                       'validity_link' => $validity_link,
                       'email' => $sent,
                       'visualize' => $editOrder);

        $this->arrayAbandoned[] = $array;
    }

    /**
     * Get the full array with only the requests made ​​in Magento with PagSeguro
     * @return array $this->arrayAbandoned - Returns an array with the necessary information to fill the table
     */
    public function getArrayAbandoned()
    {
        $this->getMagentoAbandoned();

        return $this->arrayAbandoned;
    }

    /**
     * Get quantity of times this e-mail was been sent
     * @param int $order_id - Id of order of Magento
     * @return array $sent qty.
     */
    private function getSentEmailsById($orderId)
    {
        //Get the resource model
        $resource = Mage::getSingleton('core/resource');

        //Retrieve the read connection
        $readConnection = $resource->getConnection('core_read');

        //Get table name
        $table = $resource->getTableName('pagseguro_orders');

        //Select sent column from pagseguro_orders to verify if exists a register
        $query = 'SELECT sent FROM ' . $resource->getTableName($table) . ' WHERE order_id = ' . $orderId;

        return $readConnection->fetchCol($query);
    }

    /**
     * Adds days in the given date
     * @param int $days - Number of days
     * @param date $initialDate - Informed start date
     * @return date $correctDate - Returns the date with the days added
     */
    private function getAbandonedDateAddDays($days, $initialDate)
    {
        $date = date('m/d/Y', strtotime($initialDate));
        $days = ($days > 10) ? 10 : $days;
        $thisyear = date('Y', strtotime($date));
        $thismonth = date('m', strtotime($date));
        $thisday = date('d', strtotime($date));
        $nextdate = mktime(0, 0, 0, $thismonth, $thisday + $days, $thisyear);
        $correctDate = strftime("%d/%m/%Y", $nextdate);

        return $correctDate;
    }

    /**
     * Send email of abandoned transactions for customers
     * @param int $orderId - Id of order of Magento
     * @param string $recoveryCode - Code of recovery transaction
     */
    public function sendAbandonedEmail($orderId, $recoveryCode)
    {
        // set log when sending email
        Mage::helper('pagseguro/log')->setAbandonedSendEmailLog($orderId, $recoveryCode);

        // update status
        $this->setAbandonedUpdateOrder($orderId);

        // update or insert sent information into pagseguro_orders
        $this->setTransactionRecord($orderId, false, true);

        // get order
        $order = Mage::getModel('sales/order')->load($orderId);

        // set store according to product
        $this->setCurrentStore($orderId);

        //Set template de email default of module or save in database
        $emailTemplate = Mage::getModel('core/email_template');

        // Verify the theme selected of configuration of module
        if ($this->requestPaymentMethod()->getConfigData('template') == 'payment_pagseguro_template') {
            $emailTemplate->loadDefault($this->requestPaymentMethod()->getConfigData('template'));
        } else {
            $emailTemplate->load($this->requestPaymentMethod()->getConfigData('template'));
        }

        // Get email of Sales
        $email = Mage::getStoreConfig('trans_email/ident_sales/email');

        // Get name of Sales
        $name = Mage::getStoreConfig('trans_email/ident_sales/name');

        // Get object of stores
        $store = Mage::app()->getStore();

        // Set name of Sales of store
        $emailTemplate->setSenderName($name, $store->getId());

        // Set email of Sales of store
        $emailTemplate->setSenderEmail($email, $store->getId());

        // Variables of template
        $emailTemplateVariables['store'] = $store;
        $emailTemplateVariables['order'] = $order;
        $emailTemplateVariables['pagseguro_transaction_url'] = $this->getUrlAbandonedRecovery($recoveryCode);
        $emailTemplateVariables['comment'] = '';

        // Set variables values of template
        $emailTemplate->getProcessedTemplate($emailTemplateVariables);

        // Get customer of order
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        // Send template of email with variables for customer email and name
        $emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables);
    }

    /**
     * Get url to access the abandoned transaction
     * @param string $recoveryCode - Code of recovery transaction
     * @return string $url - Url of abandoned transaction
     */
    private function getUrlAbandonedRecovery($recoveryCode)
    {
        include_once(Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php');

        // Get environment
        $sandbox = ($PagSeguroConfig['environment'] == 'sandbox') ? 'sandbox.' : '';
        $url = 'https://' . $sandbox . 'pagseguro.uol.com.br/checkout/v2/resume.html?r=' . $recoveryCode;

        return $url;
    }

    /**
     * Set admin locale code
     * @param string $code - Current code
     * @var string $this->admLocaleCode- Current admin code
     */
    public function setAdminLocaleCode($code)
    {
        $this->admLocaleCode = $code;
    }

    /**
     * Set current store for send correct abandoned email
     * @param int $orderId - Id of order Magento
     */
    private function setCurrentStore($orderId)
    {
        // get order
        $order = Mage::getModel('sales/order')->load($orderId);

        // Set store of Sets the store where it was purchased
        Mage::app()->setCurrentStore($order->getStoreId());

        // Get local of language examples en_US, pt_BR
        $localeCode = Mage::getStoreConfig('general/locale/code');

        // Set local for send correct email language
        Mage::getSingleton('core/translate')->setLocale($localeCode)->init('frontend', true);
    }

    /**
     * Set history in order and change if necessary the status
     * @param int $orderId - Id of order Magento
     * @method addStatusToHistory - Set history in order
     */
    private function setAbandonedUpdateOrder($orderId)
    {
        // get order
        $order = Mage::getModel('sales/order')->load($orderId);

        // get stats of order
        $status = $order->getStatus();

        // Comment of history order
        $comment = ($this->admLocaleCode == 'pt_BR') ? 'Transação abandonada' : 'Abandoned transaction';

        // if show icone notify in history order
        $notify = true;

        // Update history order and status order
        $order->addStatusToHistory($status, $comment, $notify);

        // Set correct time zone of store
        Mage::app()->getLocale()->date();

        // Save and recorded in history order
        $order->save();
    }
}
