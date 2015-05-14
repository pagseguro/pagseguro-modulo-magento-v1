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
     * @return object - Returns current session
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Construct layout of payment
     */
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
        $helper = Mage::helper('pagseguro');

        $paymentMethod = $helper->requestPaymentMethod();
        $feedback = 'checkout/onepage';

        $order = Mage::getModel('sales/order')->load($this->getCheckout()->getLastOrderId());
        $method = $order->getPayment()->getMethod();
        $code = $paymentMethod->getCode();

        if (($order->getState() == Mage_Sales_Model_Order::STATE_NEW) && ($method == $code) && ($order->getId())) {
            $orderId = $order->getEntityId();
            include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
            $environment = PagSeguroConfig::getEnvironment();

            if ($environment == 'production') {
                $environment = $helper->__("Produção");
            } else {
                $environment = $helper->__("Sandbox ");
            }

            $tp = (string) Mage::getConfig()->getTablePrefix();
            $table = $tp . 'pagseguro_orders';
            $read= Mage::getSingleton('core/resource')->getConnection('core_read');
            $value = $read->query("SELECT `order_id` FROM `" . $table . "` WHERE `order_id` = " . $orderId);
            $row = $value->fetch();

            if ($row == false) {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $sql = "INSERT INTO `" . $table . "` (`order_id`, `environment`) VALUES ('$orderId','$environment')";
                $connection->query($sql);
            }

            try {
                $paymentMethod->setOrder($order);
                $this->_redirectUrl($paymentMethod->getRedirectPaymentHtml($order));

                //after verify if the order was created, instantiates the sendEmail() method
                $this->sendEmail();

            } catch (Exception $ex) {
                Mage::log($ex->getMessage());
                Mage::getSingleton('core/session')->addError($helper->__(self::MENSAGEM));
                $this->_redirectUrl(Mage::getUrl('checkout/cart'));

                if ($checkout == 'PADRAO') {
                    $this->_redirectUrl(Mage::getUrl() . $feedback);
                }

                $this->canceledStatus($order);
            }
        } else {
            Mage::getSingleton('core/session/canceled')->addError($helper->__(self::MENSAGEM));
            $this->_redirectUrl(Mage::getUrl('checkout/cart'));

            if ($checkout == 'PADRAO') {
                $this->_redirectUrl(Mage::getUrl() . $feedback);
            }

            $this->canceledStatus($order);
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
     * The order pass to the status canceled
     */
    private function canceledStatus($order)
    {
        $order->cancel();
        $order->save();
    }
}
