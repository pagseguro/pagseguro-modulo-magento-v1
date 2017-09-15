<?php
/**
 * Form block for credit card payment
 */
class UOL_PagSeguro_Block_Form_CreditCard extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals()['grand_total'];
    $this->setGrandTotal($totals->getData()['value']);
    
    $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
    $this->setCreditCardSession($paymentModel->getSession());
    $this->setTemplate('uol/pagseguro/form/creditcard.phtml');
  }

  protected function _prepareLayout()
  { 
    if ($this->getLayout()->getBlock('head')) {
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/direct-payment.js');
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/credit-card.js');
        $this->getLayout()->getBlock('head')->addItem('skin_css', 'uol/pagseguro/css/direct-payment.css');
    }
  }
}
