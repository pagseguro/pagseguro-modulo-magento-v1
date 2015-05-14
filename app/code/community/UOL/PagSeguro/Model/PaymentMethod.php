<?php

/**
 * ***********************************************************************
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
 * ***********************************************************************
 */

use Mage_Payment_Model_Method_Abstract as MethodAbstract;

/**
 * PagSeguro payment model
 */
class UOL_PagSeguro_Model_PaymentMethod extends MethodAbstract
{
    protected $_code = 'pagseguro';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    private $order;
    private $shippingData;

    const REAL = 'REAL';

    /**
     * Construct
     */
    public function __construct()
    {
        include_once(Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
        include_once(Mage::getBaseDir('code') . '/community/UOL/PagSeguro/Model/Defines.php');
    }

    /**
     * Get the shipping Data of the Order
     * @return object $orderParams - Return parameters, of shipping of order
     */
    private function getShippingData()
    {
        $isOrderVirtual = $this->order->getIsVirtual();
        $orderParams = null;

        if ($isOrderVirtual) {
            $orderParams = $this->order->getBillingAddress();
        } else {
            $orderParams = $this->order->getShippingAddress();
        }

        return $orderParams->getData();
    }

    /**
     * Set the Order of checkout session
     * @param type $order
     */
    public function setOrder($order)
    {
        if ($order != null and !empty($order)) {
                $this->order = $order;
                $this->shippingData = $this->getShippingData();
        } else {
                $msg = "[PagSeguroModuleException] Message: ParÃ¢metro InvÃ¡lido para o mÃ©todo setOrder().";
                throw new Exception(Mage::helper('pagseguro')->__($msg));
        }
    }

    /**
     * Return the created payment request html with payment url and set the PagSeguroConfig's
     * @param object $order
     * @return PaymentRequestURL
     */
    public function getRedirectPaymentHtml($order)
    {
        $this->setPagSeguroConfig();
        $paymentUrl =  $this->createPaymentRequest();

        // empty the cart
        $cart = Mage::getSingleton('checkout/cart');

        foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
            $cart->removeItem($item->getId());
        }

        $cart->save();
        $order->save();
        $checkout = $this->getRedirectCheckout();

        if ($checkout == 'LIGHTBOX') {
            $code = $this->base64UrlEncode($paymentUrl);
            $array = array('_secure' => true, 'type' => 'geral', 'code' => $code);
            $payment = Mage::getUrl('pagseguro/payment/payment', $array);

            return $payment;
        } else {
            return $paymentUrl;
        }
    }

    /**
     * Encoding a data
     * @param string $text
     * @return string
     */
    private function base64UrlEncode($text)
    {
        return strtr(base64_encode($text), '+/=', '-_,');
    }

    /**
     * Set Config's to PagSeguro API
     */
    private function setPagSeguroConfig()
    {
        $activeLog = $this->getConfigData('log');
        $charset = $this->getConfigData('charset');

        //Module version
        PagSeguroLibrary::setModuleVersion('magento' . ':' . Mage::helper('pagseguro')->getVersion());

        //CMS version
        PagSeguroLibrary::setCMSVersion('magento' . ':' . Mage::getVersion());

        //Setup Charset
        if ($charset != null and !empty($charset)) {
                PagSeguroConfig::setApplicationCharset($charset);
        }

        //Setup Log
        if ($activeLog == 1) {
            $logFile = $this->getConfigData('log_file');

            if (self::checkFile(Mage::getBaseDir() . '/' . $logFile)) {
                PagSeguroConfig::activeLog(Mage::getBaseDir() . '/' . $logFile);
            } else {
                PagSeguroConfig::activeLog(); //Default Log
            }
        }
    }

    /**
     * Create PagSeguro payment request html with payment url
     * @return string
     */
    private function createPaymentRequest()
    {
        $helper = Mage::helper('pagseguro');

        // Get references that stored in the database
        $reference = $helper->getStoreReference();

        $paymentRequest = new PagSeguroPaymentRequest();
        $paymentRequest->setCurrency(PagSeguroCurrencies::getIsoCodeByName(self::REAL));
        $paymentRequest->setReference($reference . $this->order->getId()); //Order ID
        $paymentRequest->setShipping($this->getShippingInformation()); //Shipping
        $paymentRequest->setSender($this->getSenderInformation()); //Sender
        $paymentRequest->setItems($this->getItensInformation()); //Itens
        $paymentRequest->setShippingType(SHIPPING_TYPE);
        $paymentRequest->setShippingCost(number_format($this->order->getShippingAmount(), 2, '.', ''));
        $paymentRequest->setNotificationURL($this->getNotificationURL());
        $helper->getDiscount($paymentRequest);

        //Define Redirect Url
        $redirectUrl = $this->getRedirectUrl();

        if (!empty($redirectUrl) and $redirectUrl != null) {
                $paymentRequest->setRedirectURL($redirectUrl);
        } else {
                $paymentRequest->setRedirectURL(Mage::getUrl() . 'checkout/onepage/success/');
        }

        //Define Extra Amount Information
        $paymentRequest->setExtraAmount($this->extraAmount());

        try {
            $paymentUrl = $paymentRequest->register($this->getCredentialsInformation());
        } catch (PagSeguroServiceException $ex) {
            Mage::log($ex->getMessage());
            $this->redirectUrl(Mage::getUrl() . 'checkout/onepage');
        }

        return $paymentUrl;
    }

    /**
     * Extra Amount
     * @return extra amount
     */
    private function extraAmount()
    {
        $discountAmount = self::toFloat($this->order->getBaseDiscountAmount());
        $taxAmount = self::toFloat($this->order->getTaxAmount());

        return PagSeguroHelper::decimalFormat($discountAmount + $taxAmount);
    }

    /**
     * Get the notification url
     * @return Notification URL
     */
    private function getNotificationURL()
    {
        if ($this->getConfigData('notification')) {
            $notificationUrl = $this->getConfigData('notification');
        } else {
            //default installation
            $notificationUrl = Mage::app()->getStore(0)->getBaseUrl() . 'pagseguro/notification/send/';
        }

        return $notificationUrl;
    }

    /**
     * Configure the address before sending
     * @param string $fullAddress - Address complet
     * @return array - Returns the treated address
     */
    private function addressConfig($fullAddress)
    {
        require_once(Mage::getBaseDir('code') . '/community/UOL/PagSeguro/Model/AddressConfig.php');
        return AddressConfig::treatmentAddress($fullAddress);
    }

    /**
     * Get the shipping information
     * @return PagSeguroShipping
     */
    private function getShippingInformation()
    {
        $street = "";
        $number = "";
        $complement = "";
        $district = "";

        $fullAddress = $this->addressConfig($this->shippingData['street']);
        $street = $fullAddress[0] != '' ? $fullAddress[0] : $this->addressConfig($this->shippingData['street']);
        $number = is_null($fullAddress[1]) ? '' : $fullAddress[1];
        $complement = is_null($fullAddress[2]) ? '' : $fullAddress[2];
        $district = is_null($fullAddress[3]) ? '' : $fullAddress[3];

        $PagSeguroShipping = new PagSeguroShipping();
        $PagSeguroAddress = new PagSeguroAddress();
        $PagSeguroAddress->setCity($this->shippingData['city']);
        $PagSeguroAddress->setPostalCode(self::fixPostalCode($this->shippingData['postcode']));
        $PagSeguroAddress->setState($this->shippingData['region']);
        $PagSeguroAddress->setStreet($street);
        $PagSeguroAddress->setNumber($number);
        $PagSeguroAddress->setComplement($complement);
        $PagSeguroAddress->setDistrict($district);
        $PagSeguroShipping->setAddress($PagSeguroAddress);

        return $PagSeguroShipping;
    }

    /**
     * Get information of purchased items
     * @return PagSeguroItem
     */
    private function getItensInformation()
    {
        $Itens = $this->order->getAllVisibleItems();
        $PagSeguroItens = array();

        //CarShop Items
        foreach ($Itens as $item) {
                $PagSeguroItem = new PagSeguroItem();
                $PagSeguroItem->setId($item->getId());
                $PagSeguroItem->setDescription(self::fixStringLength($item->getName(), 255));
                $PagSeguroItem->setQuantity(self::toFloat($item->getQtyOrdered()));
                $PagSeguroItem->setWeight(round($item->getWeight()));
                $PagSeguroItem->setAmount(self::toFloat($item->getPrice()));

                array_push($PagSeguroItens, $PagSeguroItem);
        }

        return $PagSeguroItens;
    }

    /**
     * Get the access credential
     * @return PagSeguroAccountCredentials
     */
    public function getCredentialsInformation()
    {
        $email = $this->getConfigData('email');
        $token = $this->getConfigData('token');
        $credentials = new PagSeguroAccountCredentials($email, $token);

        return $credentials;
    }

    /**
     * Customer information that are sent
     * @return PagSeguroSender
     */
    private function getSenderInformation()
    {
        $PagSeguroSender = new PagSeguroSender();
        $PagSeguroSender->setEmail($this->order['customer_email']);
        $PagSeguroSender->setName($this->order['customer_firstname'] . ' ' . $this->order['customer_lastname']);

        return $PagSeguroSender;
    }

    /**
     * Redirect to pagseguro request controller after user click in 'PLACE ORDER'
     * @return string - Returns the url of payment request
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pagseguro/payment/request');
    }

    /**
     * Get the redirect url configured by user
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->getConfigData('url');
    }

    /**
     * Get the checkout configured by user default/lightbox
     * @return string - Returns checkout selected by the user
     */
    private function getRedirectCheckout()
    {
        return $this->getConfigData('checkout');
    }

    /**
     * Concat char's in string.     *
     * @param string $value
     * @param int $_legth
     * @param string $endChars
     * @return string $value
     */
    private static function fixStringLength($value, $length, $endChars = '...')
    {
        if (!empty($value) and !empty($length)) {
            $cutLen = (int) $length - (int) strlen($endChars);
            if (strlen($value) > $length) {
                $strCut = substr($value, 0, $cutLen);
                $value = $strCut . $endChars;
            }
        }

        return $value;
    }

    /**
     * Convert value to float.
     * @param int $value
     * @return float $value
     */
    private static function toFloat($value)
    {
        return (float) $value;
    }

    /**
     * If file not exist, try create.
     * @param string $filename
     * @return boolean $fileExist
     */
    private static function checkFile($file)
    {
        try {
            $f = fopen($file, 'a');
            $fileExist = true;
            fclose($f);
        } catch (Exception $ex) {
            $fileExist = false;
            Mage::logException($ex);
        }

        return $fileExist;
    }

    /**
     * Remove all non-numeric characters from Postal Code.
     * @return fixedPostalCode
     */
    public static function fixPostalCode($postalCode)
    {
        return preg_replace("/[^0-9]/", "", $postalCode);
    }

    /**
     * Enables multi shipping
     * @return bollean
     */
    public function canUseForMultishipping()
    {
        return $this->_canUseForMultishipping;
    }
}
