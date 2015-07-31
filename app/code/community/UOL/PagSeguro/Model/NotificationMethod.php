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
    private $post;
    private $helper;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->helper = Mage::helper('pagseguro');
    }

    public function initialize($post)
    {
        $this->post = $post;
        $this->getNotificationPost();

        if ($this->notificationType == 'transaction') {
            $this->setNotificationUpdateOrder();
        }
    }

    private function getNotificationPost()
    {
        $type = $this->post['notificationType'];
        $code = $this->post['notificationCode'];

        $this->notificationType = (isset($type) && trim($type) != "") ? $type : null;
        $this->notificationCode = (isset($code) && trim($code) != "") ? $code : null;
    }

    private function setNotificationUpdateOrder()
    {
        $class = get_class($this);
        $transaction = $this->helper->webserviceHelper()->requestPagSeguroService($class, $this->notificationCode);

        $orderId = $this->helper->getReferenceDecryptOrderID($transaction->getReference());
        $transactionCode = $transaction->getCode();
        $orderStatus = $this->helper->getPaymentStatusToString($transaction->getStatus()->getValue());

        $this->helper->updateOrderStatusMagento($class, $orderId, $transactionCode, $orderStatus);
    }
}
