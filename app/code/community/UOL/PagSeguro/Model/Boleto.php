<?php

/**
 * @property Mage_Sales_Model_Order order
 */
class UOL_PagSeguro_Model_Boleto extends Mage_Payment_Model_Method_Abstract
{

    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseInternal = true;
    protected $_canVoid = true;
    protected $_code = 'pagseguro_boleto';
    protected $_isGateway = true;
    protected $_formBlockType = 'uol_pagseguro/form_boleto';

    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        if ($data->getBoletoCpf()) {
            $info->setBoletoCpf($data->getBoletoCpf());
        }

        if ($data->getBoletoHash()) {
            $info->setBoletoHash($data->getBoletoHash());
        }

        Mage::getSingleton('customer/session')
            ->setData('boletoHash', $info->getBoletoHash())
            ->setData('boletoDocument', $info->getBoletoCpf());

        return $this;
    }

    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pagseguro/payment/request');
    }

    /**
     * If pagseguro credentials are invalid, disable payment method
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null) 
    {
        $enablePaymentMethod = (Mage::getStoreConfig("uol_pagseguro/store/credentials") == 1) ? true : false;
        return parent::isAvailable($quote) && $enablePaymentMethod;
    }
}
