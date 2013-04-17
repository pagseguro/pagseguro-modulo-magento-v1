<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/


include_once ('PagSeguroLibrary/PagSeguroLibrary.php');
include_once ('Defines.php');

/**
* PagSeguro payment model
*/
class PagSeguro_PagSeguro_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'pagseguro';
	
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

    private $Module_Version = '1.1'; 
 
    private $Order;
    private $Shipping_Data;
   
    /**
     * Get the shipping Data of the Order
     * 
     * @return type
     */
    private function getShippingData()
    {
       $isOrderVirtual = $this->Order->getIsVirtual();
       $OrderParams = NULL;
       if ( $isOrderVirtual ) {
           $OrderParams = $this->Order->getBillingAddress(); 
       } else {
           $OrderParams = $this->Order->getShippingAddress();
       }
       
    return $OrderParams->getData();
    }
    
    /**
     * Set the Order of checkout session
     * 
     * @param type $Order
     */
    public function setOrder($Order)
    { 
        if ( $Order != NULL and !empty($Order) ) {
            $this->Order = $Order;
            $this->Shipping_Data = $this->getShippingData();
        } else {
            throw new Exception( "[PagSeguroModuleException] Message: Par�metro Inv�lido para o m�todo setOrder()." );
        }
    }
    
    /**
     * Return the created payment request html with payment url and set the PagSeguroConfig's
     * 
     * @return PaymentRequestURL
     */
    public function getRedirectPaymentHtml()
    {
       $this->setPagSeguroConfig();
    return $this->createPaymentRequest();
    }
	
    /** 
     * Set Config's to PagSeguro API
     * 
     */
    private function setPagSeguroConfig()
    {
        $_activeLog = $this->getConfigData('log');       
        $_charset = $this->getConfigData('charset');
                     
        Mage::getSingleton('PagSeguro_PagSeguro_Helper_Data')->saveAllStatusPagSeguro();
		  
		//Module version
		PagSeguroLibrary::setModuleVersion('magento-v.'.$this->Module_Version);
                
                // CMS version
                PagSeguroLibrary::setCMSVersion('magento-v.'.Mage::getVersion());
        
        //Setup Charset
        if ( $_charset != NULL and !empty( $_charset ) ) {
            PagSeguroConfig::setApplicationCharset( $_charset );
        }
        
        //Setup Log
        if ( $_activeLog == 1 ) { 
            $_log_file = $this->getConfigData('log_file');
            if ( self::checkFile( $_log_file ) ) {
                PagSeguroConfig::activeLog( $_log_file );
            } else {
                PagSeguroConfig::activeLog(); //Default Log
            }
        }
    }
    
    /**
     * Create PagSeguro payment request html with payment url
     * 
     * @return string
     */
    private function createPaymentRequest(){
	   
       $PaymentRequest = new PagSeguroPaymentRequest();
        
       $PaymentRequest->setCurrency( PagSeguroCurrencies::getIsoCodeByName("REAL") );
       
       $PaymentRequest->setReference( $this->Order->getId() ); //Order ID
       
       $PaymentRequest->setShipping( $this->getShippingInformation() );  //Shipping 
       $PaymentRequest->setSender( $this->getSenderInformation() );   //Sender
       $PaymentRequest->setItems( $this->getItensInformation() );  //Itens
       
       $PaymentRequest->setShippingType( SHIPPING_TYPE ); 
       $PaymentRequest->setShippingCost( number_format($this->Order->getBaseShippingInclTax(), 2, '.', '') );
       
       $PaymentRequest->setNotificationURL( $this->getNotificationURL());
       
       //Define Redirect Url
       $redirect_url = $this->getRedirectUrl();
       if ( !empty( $redirect_url ) and $redirect_url != NULL ) {
           $PaymentRequest->setRedirectURL( $redirect_url );
       } else {
		   $PaymentRequest->setRedirectURL( Mage::getUrl().'checkout/onepage/success/' );
        }

       
       //Define Extra Amount Information
       $_discount_amount = $this->getDiscountAmount();
       if ( $_discount_amount != 0 ) {
           $PaymentRequest->setExtraAmount( $_discount_amount );
       }

       try {
           
           $payment_url = $PaymentRequest->register( $this->getCredentialsInformation() );
           $redirect_html = $this->createRedirectPaymentHtml( $payment_url );
           
       } catch ( PagSeguroServiceException $ex ) {
           Mage::log($message);
           throw new Exception( "[PagSeguroModuleException] Message: " . $ex->getMessage() , $ex->getCode() , $ex->getPrevious() );
       }
           
    return $redirect_html;
    }
    
    /**
     * 
     * @return Notification URL
     */
    private function getNotificationURL(){
        
        $notification_url = $this->getConfigData('notification');
            
            return ( $notification_url != null && $notification_url != "" ) ? $notification_url : Mage::getUrl().'pagseguro/notification/send/';
        
    }
    
    /**
     * 
     * @return PagSeguroShipping
     */
    private function getShippingInformation()
    {      
       $PagSeguroShipping = new PagSeguroShipping();
       
       $PagSeguroAddress = new PagSeguroAddress();
       $PagSeguroAddress->setCity( $this->Shipping_Data['city'] );
       $PagSeguroAddress->setPostalCode( $this->Shipping_Data['postcode'] );
       $PagSeguroAddress->setState(strtoupper($this->Shipping_Data['region']) );
       $PagSeguroAddress->setStreet( $this->Shipping_Data['street'] );
       
       $PagSeguroShipping->setAddress( $PagSeguroAddress );
       
    return $PagSeguroShipping;
    }
    
    /**
     * 
     * @return PagSeguroItem
     */
    private function getItensInformation()
    {
        $Itens = $this->Order->getAllVisibleItems();
        
        $PagSeguroItens = array();
		
        //CarShop Items
        foreach ( $Itens as $item ) {          
            $PagSeguroItem = new PagSeguroItem();
            $PagSeguroItem->setId( $item->getId() );
            $PagSeguroItem->setDescription( self::fixStringLength ( $item->getName() , 255 ) );
            $PagSeguroItem->setQuantity( self::toFloat( $item->getQtyOrdered() ) );
            $PagSeguroItem->setWeight( round( $item->getWeight() ) );
            $PagSeguroItem->setAmount( self::toFloat( $item->getPrice() ) );
            
            array_push($PagSeguroItens, $PagSeguroItem);
        }
        
        //Shipping 
  /*      $shipping_tax = self::toFloat( $this->Order->getShippingAmount() ); 
        if ( $shipping_tax > 0 ) {
            $PagSeguroSpTaxItem = new PagSeguroItem();
            $PagSeguroSpTaxItem->setDescription( $this->Order->getShippingDescription() );
            $PagSeguroSpTaxItem->setId("frete");
            $PagSeguroSpTaxItem->setAmount($shipping_tax);
            $PagSeguroSpTaxItem->setQuantity(1);
            
            array_push($PagSeguroItens, $PagSeguroSpTaxItem);
        }
        
        //Tax
        $tax_amount = self::toFloat( $this->Order->getBaseTaxAmount() );
        if( $tax_amount > 0 ) {
            $PagSeguroTaxItem = new PagSeguroItem();
            $PagSeguroTaxItem->setDescription("Taxa");
            $PagSeguroTaxItem->setId("taxa");
            $PagSeguroTaxItem->setAmount($tax_amount);
            $PagSeguroTaxItem->setQuantity(1);
            
            array_push($PagSeguroItens, $PagSeguroTaxItem);
        }*/
        
    return $PagSeguroItens;
    }
    
    /**
     * Get Order Discount Amount
     * 
     * @return discount amount
     */
    private function getDiscountAmount()
    {
        return self::toFloat( $this->Order->getBaseDiscountAmount() );
    }
    
    /**
     * 
     * @return PagSeguroAccountCredentials
     */
    public function getCredentialsInformation()
    {
        $adm_email = $this->getConfigData('email');
        $adm_token = $this->getConfigData('token');
        $credentials = new PagSeguroAccountCredentials($adm_email,$adm_token);
        
    return $credentials;
    }
    
    /**
     * 
     * @return PagSeguroSender
     */
    private function getSenderInformation()
    {     
       $PagSeguroSender = new PagSeguroSender();
       
       $PagSeguroSender->setEmail($this->Order['customer_email']);
       $PagSeguroSender->setName($this->Order['customer_firstname'].' '.$this->Order['customer_lastname']);
       
    return $PagSeguroSender;
    }
    
    /**
     * Redirect to pagseguro request controller after user click in 'PLACE ORDER'
     * 
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl($this->getCode() . '/payment/request');
    }
    
    /*
     * Get the redirect url configured by user 
     * @return string
     */
    private function getRedirectUrl()
    {
        return $this->getConfigData('url');  
    }
 
    /**
     * Create the html redirect to pagseguro gateway.
     * 
     * @param String $_url
     * @return string
     */
    private function createRedirectPaymentHtml( $_url )
    {
       $html = '<HTML>
				<HEAD>
					<TITLE>PagSeguro</TITLE>
				</HEAD>
					<BODY>
						<DIV ID = "_pagseguro_redirect">
							<h2>Redirecionando para o PagSeguro!</h2>
							<P><h3>Para redirecionar manualmente <a href="' . $_url . '">Clique aqui</a></h3></P>
							<script type="text/javascript"> 
								window.setTimeout(function(){ location.href = "' . $_url . '" },1500); 
							</script>
						 </DIV>
					 </BODY>
				</HTML>';
        
    return $html;
    }
    

    /**
     * Concat char's in string.
     * 
     * @param string $_value 
     * @param int $_legth
     * @param string $_endchars
     * @return string 
     */
      private static function fixStringLength( $_value  , $_length , $_endchars = '...' )
    {
        if ( !empty($_value) and !empty($_length) ) {
            
            $_cut_len =  (int)$_length - (int)strlen($_endchars);

            if ( strlen( $_value ) > $_length ) {
                $str_cut = substr( $_value , 0 , $_cut_len );
                $_value = $str_cut . $_endchars;
            }
            
        }
        
    return $_value;
    }
    
    /**
     * Convert value to float. 
     * 
     * @param $_value
     * @return float
     */
    private static function toFloat( $_value )
    {
        return (float)$_value;
    }
    
    /**
     * If file not exist, try create.
     * 
     * @param string $filename
     * @return boolean
     */
    private static function checkFile( $_file ) 
    {
        try {
            $f = fopen($_file, 'a');
            $_file_exist = TRUE;
            fclose($f);
        } catch ( Exception $ex ) {
            $_file_exist = FALSE;
            Mage::logException( $ex );
        }
    return $_file_exist;
    }
	 
}

