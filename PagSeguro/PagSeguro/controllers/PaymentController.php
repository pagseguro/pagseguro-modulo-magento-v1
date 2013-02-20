<?php

class PagSeguro_PagSeguro_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get Checkout Session  
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Get the Order of Checkout
     */
    private function getOrder()
    {
        return Mage::getModel('sales/order')->load( $this->getCheckout()->getLastOrderId() );
    }
    
    /**
     * Get PagSeguro Model instance
     */
    private function getPagSeguroPaymentModel()
    {
        return Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod'); //Model
    }    
    
    /**
     * Process the payment request and redirect to PagSeguro Gateway
     */
    public function requestAction()
    {   
        $Order = $this->getOrder(); //Order Data
        
        $PagSeguroPaymentModel = $this->getPagSeguroPaymentModel();
	

        if ( ( $Order->getState() == Mage_Sales_Model_Order::STATE_NEW ) and 
             ( $Order->getPayment()->getMethod() == $PagSeguroPaymentModel->getCode() ) and 
             ( $Order->getId() ) 
           ) 
        {
            
            try {
                
                $PagSeguroPaymentModel->setOrder($Order);   
                echo $PagSeguroPaymentModel->getRedirectPaymentHtml();
                
                $Order->save();              
                $this->getCheckout()->clear();
                $this->getCheckout()->unsetData();  
                
            } catch ( Exception $ex ) {
                Mage::throwException( $ex->getMessage() );
            }
        
        } else {
            $this->_redirectUrl('/'); 
        }
     }
     
}
