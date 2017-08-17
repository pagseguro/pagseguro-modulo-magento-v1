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
    protected $_code = 'pagseguro';
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

    /**
     * UOL_PagSeguro_Model_PaymentMethod constructor.
     */
    public function __construct()
    {
        $this->library = new UOL_PagSeguro_Model_Library();
        $this->helper = new UOL_PagSeguro_Helper_Data();
    }


    /**
     * @return string
     */
    public function getEnvironment(){
        return $this->library->getEnvironment();
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
        $payment->setReference(Mage::getStoreConfig('uol_pagseguro/store/reference').$this->order->getId());
        $payment->setCurrency('BRL');
        $this->setItems($payment);
        $payment->setSender()->setName($this->order->getCustomerName());
        $payment->setSender()->setEmail($this->order->getCustomerEmail());
        $phone = $this->helper->formatPhone($this->order->getShippingAddress()->getTelephone());
        $payment->setSender()->setPhone()->withParameters($phone['areaCode'], $phone['number']);
        $orderAddress = new UOL_PagSeguro_Model_OrderAddress($this->order);
        $payment->setShipping()->setAddress()->instance($orderAddress->getShippingAddress());
        $payment->setShipping()->setType()->withParameters(SHIPPING_TYPE);
        $payment->setShipping()->setCost()->withParameters(number_format($this->order->getShippingAmount(), 2, '.',
            ''));
        $payment->setExtraAmount($this->order->getBaseDiscountAmount() + $this->order->getTaxAmount());
        $payment->setNotificationUrl($this->getNotificationURL());

        return $payment;
    }

    /**
     * @param \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit $payment
     */
    private function setItems($payment)
    {
        foreach ($this->order->getAllVisibleItems() as $product) {
            $payment->addItems()->withParameters(
                $product->getId(),
                substr($product->getName(), 0, 254),
                (float)$product->getQtyOrdered(),
                number_format((float)$product->getPrice(), 2, '.', ''),
                round($product->getWeight())
            );
        }
    }

    private function getNotificationURL()
    {
        if ($this->getConfigData('notification')) {
            $notificationUrl = $this->getConfigData('notification');
        } else {
            $notificationUrl = Mage::app()->getStore(0)->getBaseUrl().'pagseguro/notification/send/';
        }

        return $notificationUrl;
    }

    /**
     * @param $params
     *
     * @return \PagSeguro\Domains\Requests\DirectPayment\Boleto|\PagSeguro\Domains\Requests\DirectPayment\CreditCard|\PagSeguro\Domains\Requests\DirectPayment\OnlineDebit
     */
    public function paymentDirect($params)
    {
        $payment = null;
        switch ($params['method']) {
            case 'boleto':
                $payment = new \PagSeguro\Domains\Requests\DirectPayment\Boleto();
                $payment->setSender()->setDocument()->withParameters(
                    'CPF',
                    $params['bilitDocument']
                );
                break;
            case 'credit-card':
                $payment = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
                $payment->setToken($params['token']);
                $payment->setInstallment()->withParameters($params['cardInstallment'],
                    number_format($params['cardInstallmentValue'], 2, '.', ''));
                $payment->setHolder()->setBirthdate($params['cardHolderBirthdate']);
                $payment->setHolder()->setName($params['cardHolderName']);
                $payment->setHolder()->setPhone()->withArray($this->helper->formatPhone($this->order->getBillingAddress()->getTelephone()));
                $payment->setHolder()->setDocument()->withParameters(
                    'CPF',
                    $params['cardHolderDocument']
                );
                $payment->setSender()->setDocument()->withParameters(
                    'CPF',
                    $params['cardHolderDocument']
                );
                $orderAddress = new UOL_PagSeguro_Model_OrderAddress($this->order);
                $payment->setBilling()->setAddress()->instance($orderAddress->getBillingAddress());
                break;
            case 'online-debit':
                $payment = new \PagSeguro\Domains\Requests\DirectPayment\OnlineDebit();
                $payment->setBankName($params['debitBankName']);
                $payment->setSender()->setDocument()->withParameters(
                    'CPF',
                    $params['debitDocument']
                );
                break;
        }
        $payment->setSender()->setHash($params['senderHash']);

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
}
