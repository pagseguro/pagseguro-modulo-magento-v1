<?php
/**
 * Form block for boleto payment
 */
class UOL_PagSeguro_Block_Form_Boleto extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $paymentModel = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
    $this->setBoletoSession($paymentModel->getPaymentSession()->getResult());
    $this->setTemplate('uol/pagseguro/form/boleto.phtml');
  }
  
  protected function _prepareLayout()
  { 
    if ($this->getLayout()->getBlock('head')) {
        $this->getLayout()->getBlock('head')->addItem('skin_js', 'uol/pagseguro/js/boleto.js');
    }
  }
}