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

class UOL_PagSeguro_Helper_Abandoned extends UOL_PagSeguro_Helper_Data
{

    /**
     * @var int
     */
    private $days;
    
    /**
     * @var Array
     */
    private $magentoPaymentList;
    
    /**
     * @var Array
     */
    private $PagSeguroAbandonedPaymentList;
    
    /**
     * @var Array
     */
    protected $arrayPayments;

    /**
     * @var int
     */
    const VALID_RANGE_DAYS = 10;

    /**
     * Executes the essentials functions for this helper
     * @param $days
     */
    public function initialize($days)
    {
            $this->days = $days;
            $this->getPagSeguroAbandonedPayments();
            $this->requestAbandonedTransactions();
    }

    /**
     * Returns payment array
     * @return mixed $this->arrayPayment
     */
    public function getPaymentsArray()
    {
        return $this->arrayPayments;
    }

    /**
     * Build a array with abandoned PagSeguroTransaction
     */
    private function requestAbandonedTransactions()
    {

        foreach ($this->PagSeguroAbandonedPaymentList->getTransactions() as $payment) {
            $orderId = $this->getReferenceDecryptOrderID($payment->getReference());
            $orderHandler = Mage::getModel('sales/order')->load($orderId);

            if ($this->getStoreReference() == $this->getReferenceDecrypt($payment->getReference())) {
                if (Mage::getStoreConfig('payment/pagseguro/environment')
                        == strtolower(trim($this->getOrderEnvironment($orderId)))) {
                    if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                        if (Mage::getSingleton('core/session')->getData("store_id") == $orderHandler->getStoreId()) {
                            $this->arrayPayments[] = $this->build($payment, $orderHandler);
                        }
                    } elseif ($orderHandler) {
                        $this->arrayPayments[] = $this->build($payment, $orderHandler);
                    }
                }
            }
        }
        Mage::getSingleton('core/session')->unsetData('store_id');
    }

    /**
     * @param PagSeguroTransaction $payment
     * @param Mage_Sales_Model_Order $order
     * @return multitype:string date Ambigous <number, mixed> NULL
     */
    public function build($payment, $order)
    {

        $config = $order->getEntityId() . '/' . $payment->getRecoveryCode();

        // Checkbox of selection for send e-mail
        $checkbox  = "<label class='chk_email'>";
        $checkbox .= "<input type='checkbox' name='send_emails[]' class='checkbox' data-config='" . $config . "' />";
        $checkbox .= "</label>";

        //$dateOrder = Mage::app()->getLocale()->date($order->getCreatedAt(), null, null, true);

        // Receives the full html link to edit an order
        $editOrder = "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($order->getEntityId()) . "'>";
        $editOrder .= $this->__('Ver detalhes') . "</a>";

        $sent = $this->getSentEmailsById($order->getEntityId());
        $sent = current($sent);

        if (empty($sent)) {
            $sent = 0;
        }

        return array('checkbox' => $checkbox,
            'date' => $this->getOrderMagetoDateConvert($order->getCreatedAt()),
            'id_magento' => "#".$order->getIncrementId(),
            'validity_link' => $this->convertAbandonedDayIntervalToDate($order->getCreatedAt()),
            'email' => $sent,
            'visualize' => $editOrder);
    }

    /**
     * Get abandoned PagSeguroTransaction from webservice in a date interval.
     * @param string $page
     */
    private function getPagSeguroAbandonedPayments($page = null)
    {
        if (is_null($page)) {
            $page = 1;
        }

        $date = new DateTime ( "now" );
        $date->setTimezone ( new DateTimeZone ( "America/Sao_Paulo" ) );
        $dateInterval = "P" . ( string ) $this->days . "D";
        $date->sub ( new DateInterval ( $dateInterval ) );
        $date->setTime ( 00, 00, 00 );
        $date = $date->format ( "Y-m-d\TH:i:s" );

        try {

            if (is_null($this->PagSeguroAbandonedPaymentList)) {
                $this->PagSeguroAbandonedPaymentList = Mage::helper('pagseguro/webservice')->abandonedRequest($date);
            } else {
                $PagSeguroPaymentList = Mage::helper('pagseguro/webservice')->abandonedRequest($date, $page);

                $this->PagSeguroAbandonedPaymentList->setDate($PagSeguroPaymentList->getDate());
                $this->PagSeguroAbandonedPaymentList->setCurrentPage($PagSeguroPaymentList->getCurrentPage());
                $this->PagSeguroAbandonedPaymentList->setTotalPages($PagSeguroPaymentList->getTotalPages());
                $totalResults = $PagSeguroPaymentList->getResultsInThisPage()
                    + $this->PagSeguroAbandonedPaymentList->getResultsInThisPage;
                $this->PagSeguroAbandonedPaymentList->setResultsInThisPage($totalResults);

                $this->PagSeguroAbandonedPaymentList->setTransactions(
                    array_merge(
                        $this->PagSeguroAbandonedPaymentList->getTransactions(),
                        $PagSeguroPaymentList->getTransactions()
                    )
                );
            }

            if ($this->PagSeguroAbandonedPaymentList->getTotalPages() > $page) {
                $this->getPagSeguroAbandonedPayments(++$page);
            }
        } catch (Exception $pse) {
            throw $pse;
        }
    }

    /**
     * Get order environment
     * @param int $orderId
     * @return string Order environment
     */
    private function getOrderEnvironment($orderId)
    {
        $reader = Mage::getSingleton("core/resource")->getConnection('core_read');
        $table = Mage::getConfig()->getTablePrefix() . 'pagseguro_orders';

        $query = "SELECT environment FROM ".$table." WHERE order_id = ".$orderId;
        
        if ($reader->fetchOne($query) == 'Produção')
        {
            return "production";
        } else {
            return $reader->fetchOne($query);
        }
    }

    /**
     * Get quantity of sent e-mails by identifier
     * @param int $orderId
     */
    private function getSentEmailsById($orderId)
    {
        //Get the resource model
        $resource = Mage::getSingleton('core/resource');

        $table = $resource->getTableName('pagseguro_orders');
        $query = 'SELECT sent FROM ' . $table . ' WHERE order_id = ' . $orderId;

        return $resource->getConnection('core_read')->fetchCol($query);
    }

    /**
     * Send a e-mail with a recovery link for a abandoned PagSeguroTransaction
     * @param int $orderId
     * @param string $recoveryCode
     */
    public function sendAbandonedEmail($orderId, $recoveryCode)
    {
        // set log when sending email
        Mage::helper('pagseguro/log')->setAbandonedSendEmailLog($orderId, $recoveryCode);

        // update statusetAbandonedUpdateOrders
        $this->updateAbandonedOrder($orderId);

        // update or insert sent information into pagseguro_orders
        $this->setTransactionRecord($orderId, false, true);

        // get order
        $order = Mage::getModel('sales/order')->load($orderId);

        // set store according to product
        $this->setCurrentStore($orderId);

        //Set template de email default of module or save in database
        $emailTemplate = Mage::getModel('core/email_template');

        // Verify the theme selected of configuration of module
        if ($this->paymentModel()->getConfigData('template') == 'payment_pagseguro_template') {
            $emailTemplate->loadDefault($this->paymentModel()->getConfigData('template'));
        } else {
            $emailTemplate->load($this->paymentModel()->getConfigData('template'));
        }

        // Get sales
        $email = Mage::getStoreConfig('trans_email/ident_sales/email');
        $name = Mage::getStoreConfig('trans_email/ident_sales/name');

        // Get object of stores
        $store = Mage::app()->getStore();

        // Set sales
        $emailTemplate->setSenderName($name, $store->getId());
        $emailTemplate->setSenderEmail($email, $store->getId());

        // Variables of template
        $emailTemplateVariables['store'] = $store;
        $emailTemplateVariables['order'] = $order;
        $emailTemplateVariables['pagseguro_transaction_url'] = $this->buildAbandonedRecoveryUrl($recoveryCode);
        $emailTemplateVariables['comment'] = '';

        // Set variables values of template
        $emailTemplate->getProcessedTemplate($emailTemplateVariables);

        // Get customer of order
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        // Send template of email with variables for customer email and name
        $emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables);
    }

    /**
     * Update a order history
     * @param int $orderId
     */
    private function updateAbandonedOrder($orderId)
    {
        $comment = ($this->admLocaleCode == 'pt_BR') ? 'Transação abandonada' : 'Abandoned transaction';

        $order = Mage::getModel('sales/order')->load($orderId);
        $order->addStatusToHistory($order->getStatus(), $comment, true);

        Mage::app()->getLocale()->date();

        $order->save();
    }

    /**
     * Build a URL for recovery a PagSeguroTransaction
     * @param string $recoveryCode
     * @return URI
     */
    private function buildAbandonedRecoveryUrl($recoveryCode)
    {
        include_once(Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php');

        if (strtolower(Mage::getStoreConfig('payment/pagseguro/environment')) == "sandbox") {
            return 'https://sandbox.pagseguro.uol.com.br/checkout/v2/resume.html?r=' . $recoveryCode;
        }

        return 'https://pagseguro.uol.com.br/checkout/v2/resume.html?r=' . $recoveryCode;
    }

    /**
     * Sets the current store
     * @param int $orderId
     */
    private function setCurrentStore($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::app()->setCurrentStore($order->getStoreId());
        Mage::getSingleton('core/translate')->setLocale(
            Mage::getStoreConfig('general/locale/code')
        )->init('frontend', true);
    }

    /**
     * Converts a day interval to date.
     * @param DateTime $orderCreatedAt
     * @return string
     */
    private function convertAbandonedDayIntervalToDate($orderCreatedAt)
    {
        $date = new DateTime($orderCreatedAt);
        $date->setTimezone(new DateTimeZone("America/Sao_Paulo"));
        $dateInterval = "P".(String)self::VALID_RANGE_DAYS."D";
        $date->add(new DateInterval($dateInterval));

        return date("d/m/Y H:i:s", $date->getTimestamp());
    }

    
    /**
     * Check config for access
     * @return multitype:string boolean
     */
    private function checkAccess()
    {
        $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        if ($paymentModel->getConfigData('abandoned') == 0) {
            return array(
                'message' => "Consulta de transações abandonadas está desativada.",
                'status' => false
            );

        }

        return array(
            'message' => "",
            'status' => true
        );

    }

    /**
     * Check for access and set errors if exists.
     */
    public function checkViewAccess()
    {
        $access = $this->checkAccess();
        if (!$access['status']) {
            Mage::getSingleton('core/session')->addError($access['message']);
            Mage::app()->getResponse()->setRedirect(
                Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/')
            );
        }
    }
}
