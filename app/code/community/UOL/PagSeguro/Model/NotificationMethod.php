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

use Mage_Payment_Model_Method_Abstract as MethodAbstract;

class UOL_PagSeguro_Model_NotificationMethod extends MethodAbstract
{
    private $notificationType;
    private $notificationCode;
    private $reference;
    private $objCredential;
    private $objNotificationType;
    private $objTransaction;
    private $post;
    private $helper;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->helper = Mage::helper('pagseguro');
        include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
    }

    /**
     * Initialize
     * @param type $objCredential
     * @param type $post
     */
    public function initialize($objCredential, $post)
    {
        $this->objCredential = $objCredential;
        $this->post = $post;
        $this->createNotification();
        $this->initializeObjects();

        if ($this->objNotificationType->getValue() == $this->notificationType) {
            $this->createTransaction();

            if ($this->objTransaction) {
                $transactionStatus = $this->objTransaction->getStatus()->getValue();
                $this->updateOrderStatus($this->helper->getPaymentStatusPagSeguro($transactionStatus, true));
            }
        }

        $this->objNotificationType->getValue();
    }

    /**
     * Create Notification
     */
    private function createNotification()
    {
        $type = $this->post['notificationType'];
        $code = $this->post['notificationCode'];

        $this->notificationType = (isset($type) && trim($type) != "") ? $type : null;
        $this->notificationCode = (isset($code) && trim($code) != "") ? $code : null;
    }

    /**
     * Initialize Objects
     */
    private function initializeObjects()
    {
        $this->createNotificationType();
    }

    /**
     * Create Notification Type
     */
    private function createNotificationType()
    {
        $this->objNotificationType = new PagSeguroNotificationType();
        $this->objNotificationType->setByType('TRANSACTION');
    }

    /**
    * Create Transaction
    */
    private function createTransaction()
    {
        $ckTransaction = PagSeguroNotificationService::checkTransaction($this->objCredential, $this->notificationCode);
        $this->objTransaction = $ckTransaction;

        $reference = $this->objTransaction->getReference();
        $orderId = $this->helper->getReferenceDecryptOrderID($reference);

        $this->reference = $orderId;
    }

    /**
    * Update
    * @param type $status
    */
    private function updateOrderStatus($status)
    {
        $comment = null;
        $notify = true;
        $order = Mage::getModel('sales/order')->load($this->reference);
        $order->addStatusToHistory($status, $comment, $notify);
        $order->sendOrderUpdateEmail($notify, $comment);

        // Makes the notification of the order of historic displays the correct date and time
        Mage::app()->getLocale()->date();
        $order->save();

        try {
            $this->insertTransactionCode();
            $order->save();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * Insert transaction code in order
     */
    private function insertTransactionCode()
    {
        $tp = (string)Mage::getConfig()->getTablePrefix();
        $table = $tp . 'pagseguro_orders';
        $ref = $this->reference;

        $read= Mage::getSingleton('core/resource')->getConnection('core_read');
        $transactionId = $this->objTransaction->getCode();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE `" . $table . "` SET `transaction_code` = '" . $transactionId . "' WHERE order_id = " . $ref;
        $connection->query($sql);
    }
}
