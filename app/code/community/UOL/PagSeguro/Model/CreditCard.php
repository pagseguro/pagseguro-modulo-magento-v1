<?php

/**
 * @property Mage_Sales_Model_Order order
 */
class UOL_PagSeguro_Model_CreditCard extends Mage_Payment_Model_Method_Abstract
{
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseInternal = true;
    protected $_canVoid = true;
    protected $_code = 'pagseguro_credit_card';
    protected $_isGateway = true;
    /**
     * @var string, path to the template form block
     */
    protected $_formBlockType = 'uol_pagseguro/form_creditCard';
/**
     * Assign block data
     * @param type $data
     * @return $this
     */
    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        if ($data->getCreditCardCpf()) {
            $info->setCreditCardCpf($data->getCreditCardCpf());
        }

        if ($data->getCreditCardHash()) {
            $info->setCreditCardHash($data->getCreditCardHash());
        }
        
        if ($data->getCreditCardHolder()) {
            $info->setCreditCardHolder($data->getCreditCardHolder());
        }
        
        if ($data->getCreditCardHolderBirthdate()) {
            $info->setCreditCardHolderBirthdate($data->getCreditCardHolderBirthdate());
        }
        
        if ($data->getCreditCardToken()) {
            $info->setCreditCardToken($data->getCreditCardToken());
        }
        
        if ($data->getCreditCardInstallment()) {
            $info->setCreditCardInstallment($data->getCreditCardInstallment());
        }
        
       if ($data->getCreditCardInstallmentValue()) {
            $info->setCreditCardInstallmentValue($data->getCreditCardInstallmentValue());
        }

        Mage::getSingleton('customer/session')
            ->setData('creditCardHash', $info->getCreditCardHash())
            ->setData('creditCardDocument', $info->getCreditCardCpf())
            ->setData('creditCardHolder', $info->getCreditCardHolder())
            ->setData('creditCardBirthdate', $info->getCreditCardHolderBirthdate())
            ->setData('creditCardToken', $info->getCreditCardToken())
            ->setData('creditCardInstallment', $info->getCreditCardInstallment())
            ->setData('creditCardInstallmentValue', $info->getCreditCardInstallmentValue());

        return $this;
    }

    /**
     * Validate the payment before request
     * @return $this
     */
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
