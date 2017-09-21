<?php
/**
 * @property Mage_Sales_Model_Order order
 */
class UOL_PagSeguro_Model_OnlineDebit extends Mage_Payment_Model_Method_Abstract
{
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseInternal = true;
    protected $_canVoid = true;
    protected $_code = 'pagseguro_online_debit';
    protected $_isGateway = true;
    
    protected $_formBlockType = 'uol_pagseguro/form_onlineDebit';
    
    /**
     * Assign block data
     * @param type $data
     * @return $this
     */
    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        if ($data->getOnlineDebitCpf()) {
            $info->setOnlineDebitCpf($data->getOnlineDebitCpf());
        }

        if ($data->getOnlineDebitHash()) {
            $info->setOnlineDebitHash($data->getOnlineDebitHash());
        }
        
        if ($data->getOnlineDebitBankName()) {
            $info->setOnlineDebitBankName($data->getOnlineDebitBankName());
        }

        Mage::getSingleton('customer/session')
            ->setData('onlineDebitHash', $info->getOnlineDebitHash())
            ->setData('onlineDebitDocument', $info->getOnlineDebitCpf())
            ->setData('onlineDebitBankName', $info->getOnlineDebitBankName());

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
