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

include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');

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
    private $_helper;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();        
        $this->_helper = Mage::helper('pagseguro');
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
        $this->_createNotification();
        $this->_initializeObjects();
                
        if ($this->objNotificationType->getValue() == $this->notificationType) {
            $this->_createTransaction();
            
            if ($this->objTransaction) {
                $this->_updateOrders($this->_helper->getPaymentStatusPagSeguro($this->objTransaction->getStatus()->getValue(), true));
            }
        }
		$this->objNotificationType->getValue();
    }
    
    /**
     * Create Notification
     */
    private function _createNotification()
    {
       $this->notificationType = (
           isset($this->post['notificationType']) &&
           trim($this->post['notificationType']) != "") ? $this->post['notificationType'] : null;
       $this->notificationCode = (
           isset($this->post['notificationCode']) &&
           trim($this->post['notificationCode']) != "") ? $this->post['notificationCode'] : null;
    }
    
    /**
     * Initialize Objects
     */
    private function _initializeObjects()
    {
        $this->_createNotificationType();
    }
    
    /**
     * Create Notification Type
     */
    private function _createNotificationType()
    {
        $this->objNotificationType = new PagSeguroNotificationType();
        $this->objNotificationType->setByType('TRANSACTION');
    }
    
    /**
    * Create Transaction
    */
    private function _createTransaction()
    {
        $this->objTransaction = PagSeguroNotificationService::checkTransaction($this->objCredential,$this->notificationCode);
		
		$reference = $this->objTransaction->getReference();
		$orderId = $this->_helper->getReferenceDecryptOrderID($reference);
        $this->reference = $orderId;
    }
	
    /**
    * Update
    * @param type $status
    */
    private function _updateOrders($status)
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
            $this->_insertCode();
            $order->save();            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
   
    private function _insertCode()
    {
        $tp = (string)Mage::getConfig()->getTablePrefix();
        $table = $tp . 'pagseguro_orders';
        $ref = $this->reference;

        $read= Mage::getSingleton('core/resource')->getConnection('core_read');
        $transactionId = $this->objTransaction->getCode();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE `" . $table . "` SET `transaction_code` = '" .$transactionId. "' WHERE order_id = " . $ref;
        $connection->query($sql);
    }
   
   
    /**
    * 
    * @param type $value
    * @return type
    */
    private function _lastStatus()
    {
        $obj = Mage::getModel('sales/order')->load($this->reference);
		
        return $obj['status'];
    }
}