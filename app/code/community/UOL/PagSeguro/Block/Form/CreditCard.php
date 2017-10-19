<?php
/**
 * Form block for credit card payment
 */
class UOL_PagSeguro_Block_Form_CreditCard extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->initializePagSeguro();
    }

    protected function _prepareLayout()
    {
        $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
        if ($paymentModel->getOneStepCheckoutIsEnabled()) {
            $directPaymentCss = 'uol/pagseguro/css/direct-payment-onestepcheckout.css';
        } else {
            $directPaymentCss = 'uol/pagseguro/css/direct-payment.css';
        }

        if ($this->getLayout()->getBlock('head')) {
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/direct-payment.js');
            $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/credit-card.js');
            $this->getLayout()->getBlock('head')->addItem('skin_css', $directPaymentCss);
        }
    }

    /**
     * Set variables to be used in pagseguro boleto form (boleto.phtml)
     */
    private function initializePagSeguro()
    {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals()['grand_total'];
        $this->setGrandTotal($totals->getData()['value']);

        $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        if ($paymentModel->getOneStepCheckoutIsEnabled()) {
            $this->setPagSeguroBeforeSaveJsSkinUrl($this->getSkinUrl('uol/pagseguro/js/pagseguro-onestepcheckout-before-save.js'));
        } else {
            $this->setPagSeguroBeforeSaveJsSkinUrl($this->getSkinUrl('uol/pagseguro/js/pagseguro-before-save.js'));
        }
        // if customer is loged, get his document info (taxvat)
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $document = (is_null($customer->getData()['taxvat'])) ? '' : $customer->getData()['taxvat'];
            $this->setCreditCardDocument($customer->getData()['taxvat']);
        }
        // set credit card session
        $this->setCreditCardSession($paymentModel->getSession());
        // set template
        $this->setTemplate('uol/pagseguro/form/creditcard.phtml');
    }
}
