<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

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

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Abandoned extends HelperData
{
	// It is used to store the array of abandoned
	private $arrayAbandoned = array();
	
	// It is used to store the initial consultation date of transactions
	private $dateStart = '';
	
	// It active/disable abandoned for notification
	private $access = '';
	
	// It the code of admin
	private $admLocaleCode = '';
	
	/*
	 * Checks that is active query abandoned
	 * Checks if email was filled and token
	 * Checks if email and token are valid
	 * If not completed one or both, is directed and notified so it can be filled
	 */	
	public function checkAbandonedAccess()
	{
		// Abandoned access
		$this->access = 1;
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
			
		// Displays this error in title	
		$module = 'PagSeguro - ';	
				
		// Receive url editing methods ja payment with key	
		$configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');	
		$email = $obj->getConfigData('email');
		$token = $obj->getConfigData('token');	
		
		if ($obj->getConfigData('abandoned') == 0) {
			$this->access = 0;	
			$message =  $module . $this->__('Consulta de transações abandonadas está desativado.');
			Mage::getSingleton('core/session')->addError($message);	
			Mage::app()->getResponse()->setRedirect($configUrl);				
		} else {
			if ($email) {		
				if (!$token) {
					$this->access = 0;	
					$message =  $module . $this->__('Preencha o token.');
					Mage::getSingleton('core/session')->addError($message);	
					Mage::app()->getResponse()->setRedirect($configUrl);	
				}
			} else {
				$this->access = 0;
				$message = $module . $this->__('Preencha o e-mail do vendedor.');
				Mage::getSingleton('core/session')->addError($message);
				if (!$token) {				
					$message = $module . $this->__('Preencha o token.');
					Mage::getSingleton('core/session')->addError($message);	
				}
				Mage::app()->getResponse()->setRedirect($configUrl);		
			}
		}
		
		if ($this->getPagSeguroAbandonedList() == 'unauthorized' && $email && $token) {
			$message = $module . $this->__('Usuário não autorizado, verifique o e-mail e token se estão corretos.');
			Mage::getSingleton('core/session')->addError($message);
			Mage::app()->getResponse()->setRedirect($configUrl);
		}
	}
	
	/**
	 * Set the start date to be found on webservice, starting from the days entered
	 * @param int $days - Days preceding the date should be initiated
	 */
	public function setDateStart($days)
	{		
		$_SESSION['dateStart'] = Mage::helper('pagseguro')->getDateSubtracted($days);		
	}

	/**
	 * Get list of abandoned PagSeguro
	 * @return array $listAbandoned - Array with transactions
	 */ 
	public function getPagSeguroAbandonedList()
	{
		if ($this->access == 1) {
			include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
			$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');	
			$this->dateStart = $this->getDateSubtracted($obj->getConfigData('abandoned_link'));
			
			try {
				$credential = $obj->getCredentialsInformation();
				$dateStart = $this->getDateStart();
				$listAbandoned = PagSeguroTransactionSearchService::searchAbandoned($credential, 1, 1000, $dateStart);
				return $listAbandoned->getTransactions();
			} catch (PagSeguroServiceException $e) {
	            if(trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED'){
	            	return 'unauthorized';
	            }
	        }			
		}
	}
	
	/**
	 * Filters by payments PagSeguro containing the same request Store	 
	 * @var int $orderId - Id of order
	 * @var string $info->getRecoveryCode() - Abandoned code for recovery transaction
	 * @method array $this->createArrayAbandoned - Stores the array that contains only the abandoned that were made in the store at PagSeguro
	 */
	private function getMagentoAbandoned()
	{
		$reference = $this->getStoreReference();
		$abandonedtList = $this->getPagSeguroAbandonedList();
		$this->arrayAbandoned = '';	
		
		if ($abandonedtList) {
			foreach ($abandonedtList as $info) {
				if ($reference == $this->getReferenceDecrypt($info->getReference())) {
					$orderId = $this->getReferenceDecryptOrderID($info->getReference());
					$order = Mage::getModel('sales/order')->load($orderId);
					
					if ($_SESSION['store_id'] != '') {
						if ( $order->getStoreId() == $_SESSION['store_id']) {
							$this->createArrayAbandoned($orderId, $info->getRecoveryCode());
						}
					} else {
						if ($order) {
							$this->createArrayAbandoned($orderId, $info->getRecoveryCode());
						}	
					}
					$_SESSION['store_id'] == '';
				} 	
			}			
		}
	}
	
	/**
	 * Creates the complete array with the necessary information for the table
	 * @var int $orderId - Id of order of Magento
	 * @var string $recoveryCode - Code of recovery transaction in PagSeguro
	 * @method array $this->arrayAbandoned - Set the complete array with the necessary information for the table
	 */
	private function createArrayAbandoned($orderId, $recoveryCode)
	{
		// force default time zone
		date_default_timezone_set(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
		
		// Receives the parameter used to send e-mail
		$config = $orderId . '/' . $recoveryCode;
		
		// Checkbox of selection for send e-mail
		$checkbox =  "<label class='chk_email'>";
		$checkbox .= "<input type='checkbox' name='send_emails[]' data-config='" . $config . "' />";
		$checkbox .= "</label>";
		
		// Receives the object of order that was entered the id		
		$order = Mage::getModel('sales/order')->load($orderId);	
			
		// Receives the creation date of the application which is converted to the format d/m/Y
		$dateOrder = Mage::app()->getLocale()->date($order->getCreatedAt(), null, null, true);
		
		// Receives the number of order
		$idMagento = '#' . $order->getIncrementId();
		
		// Obtaining amount of days selected in settings
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');	
		$validity_link = $this->getAbandonedDateAddDays(10, $order->getCreatedAt());
		
		// Receives the full url to access the module skin
		$skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/uol/pagseguro/';
		
		// Receives the url edit order it from your id		
		$editUrl = $this->getEditOrderUrl($orderId);	
		$emailText = $this->__('Enviar e-mail');	
		$editText = $this->__('Detalhes');
		
		// Receives the full html link to email an order
		$onClick = "onclick='SendMail(this)'";
		$class = "class='send_email' " . $onClick;
		$emailOrder .= "<a " . $class . " data-config='" . $config . "' href='javascript:void(0)'>";
		$emailImage = $skinUrl . "images/email.gif";
		$emailOrder .= "<img title='" . $emailText . "' alt='" . $emailText . "' src='" . $emailImage ."' />";
		$emailOrder .= "</a>";
		
		// Receives the full html link to visualize an order
		$visualizeOrder .= "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($orderId) . "'>";
		$visualizeImage = $skinUrl . "images/details.gif";
		$visualizeOrder .= "<img title='" . $editText . "' alt='" . $editText . "' src='" . $visualizeImage ."' />";
		$visualizeOrder .= "</a>";
		$array = array( 'checkbox' => $checkbox,
						'date' => $dateOrder,
						'id_magento' => $idMagento,
						'validity_link' => $validity_link,
						'email' => $emailOrder,
						'visualize' => $visualizeOrder);		
		$this->arrayAbandoned[] = $array;
	}	

	/**
	 * Get the full array with only the requests made ​​in Magento with PagSeguro
	 * @return array $this->arrayAbandoned - Returns an array with the necessary information to fill the table
	 */
	public function getArrayAbandoned()
	{
		$this->getMagentoAbandoned();
						
		return $this->arrayAbandoned;
	}
	
	/**
	 * Adds days in the given date
	 * @param int $days - Number of days
	 * @param date $dateStart - Informed start date
	 * @return date $correctDate - Returns the date with the days added
	 */
	private function getAbandonedDateAddDays($days, $dateStart)
	{
		$date = date('m/d/Y', strtotime($dateStart));
		$days = ($days > 30) ? 30 : $days;
        $thisyear = date('Y', strtotime($date));
	    $thismonth = date('m', strtotime($date));
	    $thisday = date('d', strtotime($date));
        $nextdate = mktime(0, 0, 0, $thismonth, $thisday + $days, $thisyear);
        $correctDate = strftime("%d/%m/%Y", $nextdate);
		
        return $correctDate;
    }
	
	/**
	 * Send email of abandoned transactions for customers
	 * @param int $orderId - Id of order of Magento
	 * @param string $recoveryCode - Code of recovery transaction
	 */
	public function sendAbandonedEmail($orderId, $recoveryCode)
	{
		// set log when sending email
		$this->setAbandonedSendEmailLog($orderId, $recoveryCode);
		
		// update status
		$this->setAbandonedUpdateOrder($orderId);
		
		// get methods of payment
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
		
		// get order		
		$order = Mage::getModel('sales/order')->load($orderId);
		
		// set store according to product
		$this->setCurrentStore($orderId);
		
		//Set template de email default of module or save in database	
		$emailTemplate = Mage::getModel('core/email_template');
		
		// Verify the theme selected of configuration of module
		if ($obj->getConfigData('template') == 'payment_pagseguro_template') {
			$emailTemplate->loadDefault($obj->getConfigData('template'));
		} else {
			$emailTemplate->load($obj->getConfigData('template'));
		}
		
		// Get email of Sales
		$email = Mage::getStoreConfig('trans_email/ident_sales/email');
		
		// Get name of Sales
		$name = Mage::getStoreConfig('trans_email/ident_sales/name');
			
		// Get object of stores	
		$store = Mage::app()->getStore();
		
		// Set name of Sales of store
		$emailTemplate->setSenderName($name, $store->getId());
		
		// Set email of Sales of store
		$emailTemplate->setSenderEmail($email, $store->getId());
		
		// Variables of template
		$emailTemplateVariables['store'] = $store;
		$emailTemplateVariables['order'] = $order;
		$emailTemplateVariables['pagseguro_transaction_url'] = $this->getUrlAbandonedRecovery($recoveryCode);
		$emailTemplateVariables['comment'] = '';
		
		// Set variables values of template		
		$emailTemplate->getProcessedTemplate($emailTemplateVariables);	
				
		// Get customer of order		
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		// Send template of email with variables for customer email and name
		$emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables);
	}

	/**
	 * Get url to access the abandoned transaction
	 * @param string $recoveryCode - Code of recovery transaction
	 * @return string $url - Url of abandoned transaction
	 */
	private function getUrlAbandonedRecovery($recoveryCode)
	{		
		include (Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php');
		
		// Get environment
		$sandbox = ($PagSeguroConfig['environment'] == 'sandbox') ? 'sandbox.' : '';
		$url = 'https://' . $sandbox . 'pagseguro.uol.com.br/checkout/v2/resume.html?r=' . $recoveryCode;
		
		return $url;
	}
	
	/**
	 * Set admin locale code
	 * @param string $code - Current code
	 * @var string $this->admLocaleCode- Current admin code
	 */
	public function setAdminLocaleCode($code)
	{
		$this->admLocaleCode = $code;
	}
	
	/**
	 * Set current store for send correct abandoned email
	 * @param int $orderId - Id of order Magento
	 */
	private function setCurrentStore($orderId)
	{
		// get order
		$order = Mage::getModel('sales/order')->load($orderId);
		
		// Set store of Sets the store where it was purchased
		Mage::app()->setCurrentStore($order->getStoreId());
		
		// Get local of language examples en_US, pt_BR	
		$localeCode = Mage::getStoreConfig('general/locale/code');
		
		// Set local for send correct email language
		Mage::getSingleton('core/translate')->setLocale($localeCode)->init('frontend', true);
	}
	
	/**
	 * Set the log when searched records
	 * @method setLog - Set log in file
	 */
	public function setAbandonedListLog()
	{
		$config = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
		$this->setDateStart($config->getConfigData('abandoned_link'));
		
		// Set title
		$module = ' [Info] PagSeguroAbandoned.';	
		
		// Sentence of log
		$phrase = "Searched( '" . $config->getConfigData('abandoned_link') . " days - Range of dates ";
		$phrase .= $this->getDateStart() . " until " . 
				   $this->getDateFinally() . "' )";
				   
		// Creating the update log order
		$this->setLog($phrase, $module);			
	}
	
	/**
	 * Set the log records when sent
	 * @param int $orderId - Id of order Magento
	 * @param string $recoveryCode - Recovery code of transaction PagSeguro
	 * @method setLog - Set log in file
	 */
	private function setAbandonedSendEmailLog($orderId, $recoveryCode)
	{
		// Title of Log
		$module = ' [Info] PagSeguroAbandoned.';
		
		// Sentence of log
		$phrase = 'Mail(';
		$phrase .= "SendEmailAbandoned: array (\n  'orderId' => " . $orderId . ",\n  ";
		$phrase .= "'recoveryCode' => '" . $recoveryCode . "'\n))";
			
		// Creating the update log order
		$this->setLog($phrase, $module);			
	}
	
	/**
	 * Set history in order and change if necessary the status
	 * @param int $orderId - Id of order Magento
	 * @method addStatusToHistory - Set history in order
	 */
	private function setAbandonedUpdateOrder($orderId)
	{
		// get order
		$order = Mage::getModel('sales/order')->load($orderId);
		
		// get stats of order
		$status = $order->getStatus();
		
		// Comment of history order
		$comment = ($this->admLocaleCode == 'pt_BR') ? 'Transação abandonada' : 'Abandoned transaction';
		
		// if show icone notify in history order
		$notify = true;	
			
		// Update history order and status order				
		$order->addStatusToHistory($status, $comment, $notify);
		
		// Set correct time zone of store
		Mage::app()->getLocale()->date();
		
		// Save and recorded in history order
		$order->save();
	}
}