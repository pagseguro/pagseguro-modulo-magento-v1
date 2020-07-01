<?php

/**
 * @property Mage_Sales_Model_Order order
 */
class UOL_PagSeguro_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseInternal = true;
    protected $_canVoid = true;
    protected $_code = 'pagseguro_default_lightbox';
    protected $_isGateway = true;
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;
    /**
     * @var UOL_PagSeguro_Helper_Data
     */
    private $helper;
    /**
     * @var UOL_PagSeguro_Model_Library
     */
    private $library;
    
    protected $_session;

    /**
     * UOL_PagSeguro_Model_PaymentMethod constructor.
     */
    public function __construct()
    {
        $this->library = new UOL_PagSeguro_Model_Library();
        $this->helper = new UOL_PagSeguro_Helper_Data();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function addPagseguroOrders(Mage_Sales_Model_Order $order)
    {
        $orderId = $order->getEntityId();
        $enviroment = $this->library->getEnvironment();
        $table = Mage::getConfig()->getTablePrefix().'pagseguro_orders';
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $value = $read->query("SELECT `order_id` FROM `$table` WHERE `order_id` = $orderId");
        if (!$value->fetch()) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "INSERT INTO `$table` (`order_id`, `environment`) VALUES ('$orderId', '$enviroment')";
            $connection->query($sql);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function clearCheckoutSession(Mage_Sales_Model_Order $order)
    {
        $cart = Mage::getSingleton('checkout/cart');
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
            $cart->removeItem($item->getId());
        }
        $cart->save();
        $order->save();
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pagseguro/payment/request');
    }

    /**
     * Retrieve checkout type from system.xml
     *
     * @return mixed
     */
    public function getPaymentCheckoutType()
    {
        return $this->library->getPaymentCheckoutType();
    }

    /**
     * @return mixed
     */
    public function getPaymentSession()
    {
        return \PagSeguro\Services\Session::create($this->library->getAccountCredentials());
    }

    /**
     * @return \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     */
    public function paymentDefault()
    {
        $payment = new \PagSeguro\Domains\Requests\Payment();

        return $this->payment($payment);
    }

    /**
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit|\PagSeguro\Domains\Requests\Payment $payment
     *
     * @return \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     */
    private function payment($payment)
    {
        $this->setShoppingCartRecovery($payment);
        $payment->setReference(Mage::getStoreConfig('uol_pagseguro/store/reference').$this->order->getId());
        $payment->setCurrency('BRL');
        $this->setItems($payment);
        $payment->setSender()->setName($this->GetCustomerName($this->order->getCustomerName()));
        $payment->setSender()->setEmail($this->order->getCustomerEmail());
        $this->setSenderPhone($payment);

        if ($this->order->getShippingAddress() !== false) {
            $orderAddress = new UOL_PagSeguro_Model_OrderAddress($this->order);
            $payment->setShipping()->setAddress()->instance($orderAddress->getShippingAddress());
            $payment->setShipping()->setType()->withParameters(SHIPPING_TYPE);
            $payment->setShipping()->setCost()->withParameters(number_format($this->order->getShippingAmount(), 2, '.', ''));
        }

        $payment->setExtraAmount($this->order->getBaseDiscountAmount() + $this->order->getTaxAmount());
        $payment->setNotificationUrl($this->getNotificationURL());

        return $payment;
    }

    /**
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit $payment
     */
    private function setItems($payment)
    {
        $payment->setShipping()->setAddressRequired()->withParameters('false');

        foreach ($this->order->getAllVisibleItems() as $product) {
            // check shipping necessity according with each product in the cart
            $this->setShippingIsRequired($payment, $product->getData()['product_type']);

            $payment->addItems()->withParameters(
                $product->getProduct()->getId(),
                substr($product->getName(), 0, 254),
                (float)$product->getQtyOrdered(),
                number_format((float)$product->getPrice(), 2, '.', ''),
                round($product->getWeight())
            );
        }
    }

    private function getNotificationURL()
    {
        $notificationPath = Mage::getStoreConfig('payment/pagseguro/notification');
        
        if ($notificationPath) {
            $notificationUrl = $notificationPath;
        } else {
            $notificationUrl = Mage::app()->getStore(0)->getBaseUrl().'pagseguro/notification/send/';
        }

        return $notificationUrl;
    }

   /**
    * Get the direct payment method (boleto, onlibe debit or credit card) 
    * and instantiate the respective payment object
    * @param string $paymentMethod
    * @param array $paymentData
    * @return \PagSeguro\Domains\Requests\DirectPayment\Boleto 
    *           || \PagSeguro\Domains\Requests\DirectPayment\CreditCard
    *           || \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit $payment
    */
    public function paymentDirect($paymentMethod, $paymentData)
    {   
        $payment = null;

        switch ($paymentMethod) {
            case 'pagseguro_boleto':
                $formatedDocument = $this->helper->formatDocument($paymentData['boletoDocument']);
                $payment = new \PagSeguro\Domains\Requests\DirectPayment\Boleto();
                $payment->setSender()->setDocument()->withParameters(
                    $formatedDocument['type'],
                    $formatedDocument['number']
                );
                $payment->setSender()->setHash($paymentData['boletoHash']);
                break;

            case 'pagseguro_credit_card':
                $formatedDocument = $this->helper->formatDocument($paymentData['creditCardDocument']);

                $payment = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
                $payment->setToken($paymentData['creditCardToken']);
                $payment->setInstallment()->withParameters($paymentData['creditCardInstallment'],
                    number_format($paymentData['creditCardInstallmentValue'], 2, '.', ''));
                $payment->setHolder()->setBirthdate($paymentData['creditCardBirthdate']);
                $payment->setHolder()->setName($paymentData['creditCardHolder']);

                $phone = $this->helper->formatPhone($this->order->getBillingAddress()->getTelephone());
                $payment->setHolder()->setPhone()->withParameters($phone['areaCode'], $phone['number']);

                $payment->setHolder()->setDocument()->withParameters(
                    $formatedDocument['type'],
                    $formatedDocument['number']
                );
                $payment->setSender()->setDocument()->withParameters(
                    $formatedDocument['type'],
                    $formatedDocument['number']
                );
                $orderAddress = new UOL_PagSeguro_Model_OrderAddress($this->order);
                $payment->setBilling()->setAddress()->instance($orderAddress->getBillingAddress());
                $payment->setSender()->setHash($paymentData['creditCardHash']);
                break;
            case 'pagseguro_online_debit':
                $formatedDocument = $this->helper->formatDocument($paymentData['onlineDebitDocument']);
                $payment = new \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit();
                $payment->setBankName($paymentData['onlineDebitBankName']);
                $payment->setSender()->setDocument()->withParameters(
                    $formatedDocument['type'],
                    $formatedDocument['number']
                );
                $payment->setSender()->setHash($paymentData['onlineDebitHash']);
                break;
        }

        /** @var \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit $payment */
        return $this->payment($payment);
    }

    /**
     * @return \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     */
    public function paymentLightbox()
    {
        $payment = new \PagSeguro\Domains\Requests\Payment();

        return $this->payment($payment);
    }

    /**
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit|\PagSeguro\Domains\Requests\Payment $payment
     *
     * @param bool $code
     *
     * @return bool|\PagSeguro\Domains\Requests\DirectPayment\Boleto $response
     */
    public function paymentRegister($payment, $code = false)
    {
        $response = false;
        try {
            if ($code) {
                /** @var \PagSeguro\Domains\Requests\Payment $response */
                $response = $payment->register($this->library->getAccountCredentials(), true)->getCode();
            } else {
                /** @var \PagSeguro\Domains\Requests\DirectPayment\Boleto $payment */
                $response = $payment->register($this->library->getAccountCredentials());
            }
        } catch (Exception $exception) {
            \PagSeguro\Resources\Log\Logger::error($exception); //TODO add log function in helpers
            Mage::logException($exception);
        }

        return $response;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        return $this->order = $order;
    }
    
    /**
     * getter for $_session (must be public to be instatiated in blocks)
     * @return type
     */
    public function getSession()
    {
        if (is_null($this->_session) || empty($this->_session)) {
            try {
                $this->_session = $this->getPaymentSession()->getResult();
            } catch (Exception $exc) {
                // the error will be in the pagseguro log set in admin configuration
            }
        }
        return $this->_session;
    }
    
    /**
     * Return status (enabled or disabled) from the Inovarti One Step Checkout module
     * @return boolean
     */
    public function getOneStepCheckoutIsEnabled()
    {
        return (Mage::getStoreConfig("onestepcheckout/general/is_enabled") == 1) ? true : false;
    }

    /**
     * Checks if the product type requires shipping and, if it is required, set
     * the 'addressRequired' pagseguro api parameter to true
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto
     *      | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *      | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *      | \PagSeguro\Domains\Requests\Payment
     *      $payment
     * @param string $productType
     * @return void
     */
    private function setShippingIsRequired($payment, $productType)
    {
        if (! in_array($productType, $this->productTypesWithoutShipping())) {
            $payment->setShipping()->setAddressRequired()->withParameters('true');
        }
    }

    /**
     * Return an array of magento product types that do not require shipping
     *
     * @return array
     */
    private function productTypesWithoutShipping()
    {
        return array('virtual', 'downloadable');
    }

    /**
     * Set sender phone with magento phone from billing address or, in second case, from shipping address
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto
     *      | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *      | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *      | \PagSeguro\Domains\Requests\Payment
     *      $payment
     * @return void
     */
    private function setSenderPhone($payment)
    {
        $phone = null;
        if ($this->order->getBillingAddress() && $this->order->getBillingAddress()->getTelephone()) {
            $phone = $this->helper->formatPhone($this->order->getBillingAddress()->getTelephone());
        } else if ($this->order->getShippingAddress() && $this->order->getShippingAddress()->getTelephone()) {
            $phone = $this->helper->formatPhone($this->order->getShippingAddress()->getTelephone());
        }
        if ($phone) {
            $payment->setSender()->setPhone()->withParameters($phone['areaCode'], $phone['number']);
        }
    }

    /**
     * Set PagSeguro recovery shopping cart value
     *
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto
     *      | \PagSeguro\Domains\Requests\DirectPayment\CreditCard
     *      | \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     *      | \PagSeguro\Domains\Requests\Payment
     *      $payment
     * @return void
     */
    private function setShoppingCartRecovery($payment)
    {
        $recoveryValue = Mage::getStoreConfig('payment/pagseguro/shopping_cart_recovery');
        if (Mage::getStoreConfig('payment/pagseguro/shopping_cart_recovery') == true) {
            $payment->addParameter()->withParameters('enableRecovery', 'true');
        } else {
            $payment->addParameter()->withParameters('enableRecovery', 'false');
        }
    }
    
    /**
     * Get Customer name removing extra spaces
     *
     * @param string $name
     * @return string
     */
    private function GetCustomerName($name) {
        $parts = explode(" ", $name);
        $result = array();
        foreach($parts as $part) {
            if($part != "")
                $result[] = $part;
        }
        return join($result, " ");
    }
}
