<?php
/**
 * Form block for online debit payment
 */
class UOL_PagSeguro_Block_Form_OnlineDebit extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->initializePagSeguro();
  }

  protected function _prepareLayout()
  { 
    if ($this->getLayout()->getBlock('head')) {
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/direct-payment.js');
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/online-debit.js');
        $this->getLayout()->getBlock('head')->addItem('skin_css', 'uol/pagseguro/css/direct-payment.css');
    }
  }
  
  /**
   * Set variables to be used in pagseguro boleto form (boleto.phtml)
   */
  private function initializePagSeguro()
  {
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
        $this->setOnlineDebitDocument($customer->getData()['taxvat']);
    }
    // set boleto session
    $this->setOnlineDebitSession($paymentModel->getSession());
    // set template
    $this->setTemplate('uol/pagseguro/form/onlinedebit.phtml');
  }
}
