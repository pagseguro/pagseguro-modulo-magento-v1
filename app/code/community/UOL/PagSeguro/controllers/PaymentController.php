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

use Mage_Core_Controller_Front_Action as FrontAction;

class UOL_PagSeguro_PaymentController extends FrontAction
{
    const CANCELADO = 7;    
    const MENSAGEM = 'Desculpe, infelizmente, houve um erro durante o checkout.
    				  Entre em contato com o administrador da loja, se o problema persistir.';
					  
    /**
     * Get Checkout Session  
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get the Order of Checkout
     */
    private function getOrder()
    {
        return Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
    }

    /**
     * Get PagSeguro Model instance
     */
    private function getPagSeguroPaymentModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod'); //Model
    }

    /**
     * Get PagSeguro Model instance
     */
    private function getPagSeguroHelloWorldModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_Geral'); //Model
    }

    public function paymentAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Process the payment request and redirect to PagSeguro Gateway
     */
    public function requestAction()
    {
    
    
        $Order = $this->getOrder(); //Order Data        
        $PagSeguroPaymentModel = $this->getPagSeguroPaymentModel();        
        $enabledOSC = false;
        $fileOSC = scandir(getcwd().'/app/code/community/DeivisonArthur');
        
        if ($fileOSC) {
            $enabledOSC = Mage::helper('onepagecheckout')->isOnepageCheckoutEnabled();
        }           
            
        $feedback = ($enabledOSC == false ? 'checkout/onepage' : 'onepagecheckout');

        if (($Order->getState() == Mage_Sales_Model_Order::STATE_NEW) and
            ($Order->getPayment()->getMethod() == $PagSeguroPaymentModel->getCode()) and
            ($Order->getId())) {

            $orderId = $Order->getEntityId();
            include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');  
            $environment = PagSeguroConfig::getEnvironment();
            if ($environment == 'production') {
                $environment = "Produção";
            } else {
                $environment = "Sandbox";
            }

            $tp = (string)Mage::getConfig()->getTablePrefix();
            $table = $tp . 'pagseguro_orders';
            $read= Mage::getSingleton('core/resource')->getConnection('core_read');
            $value = $read->query("SELECT `order_id` FROM `" . $table . "` WHERE `order_id` = " . $orderId);
            $row = $value->fetch();     
                
            if ($row == false) {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sql = "INSERT INTO `" . $table . "` (`order_id`, `environment`) 
                        VALUES ('$orderId','$environment')";  
                $connection->query($sql);    
            }

            try {

                $PagSeguroPaymentModel->setOrder($Order);                
                $this->_redirectUrl($PagSeguroPaymentModel->getRedirectPaymentHtml($Order));
                
                //after verify if the order was created, instantiates the sendEmail() method
                $this->sendEmail();
                
            } catch (Exception $ex) {
                Mage::log($ex->getMessage());
                Mage::getSingleton('core/session')->addError(self::MENSAGEM);
				$this->_redirectUrl(Mage::getUrl('checkout/cart'));
				
                if ($checkout == 'PADRAO') {
                    $this->_redirectUrl(Mage::getUrl() . $feedback);
                }
                $this->_canceledStatus($Order);
            }
            
        } else {
            Mage::getSingleton('core/session/canceled')->addError(self::MENSAGEM);
			$this->_redirectUrl(Mage::getUrl('checkout/cart'));
			
            if ($checkout == 'PADRAO') {
                $this->_redirectUrl(Mage::getUrl() . $feedback);
            }
            $this->_canceledStatus($Order);
        }
    }

    /**
     * Send a e-mail with shopping order.
     */
    private function sendEmail()
    {        
        $order = new Mage_Sales_Model_Order();
        $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order->loadByIncrementId($incrementId);
		
        try {
            $order->sendNewOrderEmail();
        } catch (Exception $ex) {
            die($ex);
        }        
    }
    
    /**
     * returns PagSeguro checkout configuration
     * @return CheckoutStatus = 'LIGHTBOX' or 'PADRÃO'
     */
    private function getRedirectCheckout()
    {
        $idStore = Mage::app()->getStore()->getCode();
        Mage::log("ID_DA_LOJA:".$idStore);
		
        return Mage::getStoreConfig('payment/pagseguro/checkout', $idStore);
    }

    /**
     * cancel order status
     */
    private function _canceledStatus($Order)
    {
        $Order->cancel();
        $Order->save();
    }
}
