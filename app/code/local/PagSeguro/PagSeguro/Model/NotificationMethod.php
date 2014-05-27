<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

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

include_once (dirname(__FILE__).'/PagSeguroLibrary/PagSeguroLibrary.php');

use Mage_Payment_Model_Method_Abstract as MethodAbstract;

class PagSeguro_PagSeguro_Model_NotificationMethod extends MethodAbstract
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
        
        $this->_helper = Mage::getSingleton('PagSeguro_PagSeguro_Helper_Data');
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
                $this->_updateCms();
            }
        }
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

        $this->objTransaction = PagSeguroNotificationService::checkTransaction(
            $this->objCredential,
            $this->notificationCode
        );

        $this->reference = $this->objTransaction->getReference();
    }
   
    /**
    * Update Cms
    */
    private function _updateCms()
    {
        $arrayValue =  $this->_helper->returnOrderStByStPagSeguro($this->objTransaction->getStatus()->getValue());

        if ($this->_lastStatus() != $arrayValue['status']) {
            if ($this->_helper->_existsStatus($arrayValue['status'])) {
                $this->_updateOrders($arrayValue['status']);
            } else {
                $this->_helper->saveStatusPagSeguro($arrayValue);
                $this->_updateOrders($arrayValue['status']);
            }
        }
    }
   
    /**
    * Update
    * @param type $status
    */
    private function _updateOrders($status)
    {
        $obj = Mage::getModel('sales/order')->load($this->reference);
        $obj->setStatus($status);
        
        $history = $obj->addStatusHistoryComment('', false);
        $history->setIsCustomerNotified(false);
       
        try {
            $this->_insertCode();
            $obj->save();
            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
   
    private function _insertCode()
    {
        $table_prefix = (string)Mage::getConfig()->getTablePrefix();
        $read= Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $value = $read->query(
            "SELECT `order_id` FROM `" . $table_prefix . "pagseguro_sales_code` WHERE `order_id` = $this->reference"
        );
        
        $row = $value->fetch();

        if ($row == false) {
            $transactionId = $this->objTransaction->getCode();
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "INSERT INTO " . $table_prefix . "`pagseguro_sales_code` (`order_id`,`transaction_code`)
                VALUES ('$this->reference','$transactionId')";
            $connection->query($sql);
        }
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
