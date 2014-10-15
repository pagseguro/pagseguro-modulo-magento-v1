<?php

/*
************************************************************************
Copyright [2014] [PagSeguro Internet Ltda.]

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

class Mage_PagSeguro_Helper_Data extends Mage_Core_Helper_Abstract {	
	// It is used to store the array of transactions
	private $arrayPayments = array();
	// It is used to store the array of abandoned
	private $arrayAbandoned = array();
	// It is used to access the pages generated in webservice 
	private $page; 	
	// It is used to store the initial consultation date of transactions
	private $dateStart = '';
	// It active/disable abandoned for notification
	private $access = '';
	// It the code of admin
	private $admLocaleCode = '';
	/**
	 * Return reference of 5 digits
	 * @param number $size - String length
	 * @param boolean $uppercase - Active uppercase words in string
	 * @param boolen $number - Active number in string
     * @return string  $string - String encrypted of 5 characters
	 */
	public function createReference($size, $uppercase, $number)
	{
		$lmin = 'abcdefghijklmnopqrstuvwxyz';
		$lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$num = '1234567890';	
		$string = '';
		$character = '';	
		$character .= $lmin;
		if ($uppercase) $character .= $lmai;
		if ($number) $character .= $num;	
		$len = strlen($character);		
		for ($n = 1; $n <= $size; $n++) {
			$rand = mt_rand(1, $len);
			$string .= $character[$rand-1];
		}		
		return $string;
	}	
	/*
	 * Checks if email was filled and token
	 * Checks if email and token are valid
	 * If not completed one or both, is directed and notified so it can be filled
	 */
	public function checkConciliationAccess()
	{
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');			
		// Displays this error in title	
		$module = 'PagSeguro - ';			
		// Receive url editing methods ja payment with key	
		$configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');	
		$email = $obj->getConfigData('email');
		$token = $obj->getConfigData('token');
		if ($email) {		
			if (!$token) {	
				$message =  $module . $this->__('Preencha o token.');
				Mage::getSingleton('core/session')->addError($message);	
				Mage::app()->getResponse()->setRedirect($configUrl);	
			}
		} else {
			$message = $module . $this->__('Preencha o e-mail do vendedor.');
			Mage::getSingleton('core/session')->addError($message);
			if (!$token) {				
				$message = $module . $this->__('Preencha o token.');
				Mage::getSingleton('core/session')->addError($message);	
			}
			Mage::app()->getResponse()->setRedirect($configUrl);		
		}		
		if ($this->getPagSeguroPaymentList() == 'unauthorized' && $email && $token) {
			$message = $module . $this->__('Usuário não autorizado, verifique o e-mail e token se estão corretos.');
			Mage::getSingleton('core/session')->addError($message);
			Mage::app()->getResponse()->setRedirect($configUrl);
		}		
	} 	
	/**
	 * Returns the registered references in the database
	 * @return string $reference - String encrypted of 5 characters of database
	 */ 
	public function getReadReferenceBank()
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = 'SELECT reference FROM ' . $resource->getTableName('pagseguro_conciliation');
		$results = $readConnection->fetchAll($query);
		$reference = current(current($results));		
		return $reference;
	}	
	/**
	 * Get url of transaction search service
	 * @return string $url - Returns full url to query
	 */
	private function getUrlTransactionSearchService()
	{
		include (Mage::getBaseDir('lib') . '/PagSeguroLibrary/resources/PagSeguroResources.php');
		include (Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php');		
		$environment = $PagSeguroConfig['environment'];		
		// Capture the url query the webservice
		$url = $PagSeguroResources['webserviceUrl'][$environment] . 
			   $PagSeguroResources['transactionSearchService']['servicePath'];			   
		return $url;
	}	
	/**
	 * Get date start
	 * @return date $dateStart - Example Y-m-dT00:00
	 */
	public function getDateStart()
	{
		if ($this->dateStart != '') {
			$dateStart = $this->dateStart . 'T00:00';	
		} else {	
			$dateStart = date('Y-m-d') . 'T00:00';
		}	
		return $dateStart;
	}	
	/**
	 * Get date finally for query
	 * @return date $date - Returns the end date (Y-m-dTH:i)
	 */
	public function getDateFinally()
	{
		// set date and time by time zone selected by merchant
		date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone'));
		$date = date('Y-m-d') . 'T' . date('H:i');
		return $date;
	}	
	/**
	 * Get url parameters with the transaction have to consult websevice
	 * @return string $url - Full url to query the webservice
	 */	 
	private function getUrlTransactionConsultWebService()
	{		
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');			
		$dateStart = $this->getDateStart();	
		// Receives the base url of the webservice query to get the parameters
		$url = $this->getUrlTransactionSearchService();
		$url .= '/?initialDate=' . $dateStart;	
		// Set which page will be consulted
		if ($this->page) {
			$url .= '&page=' . $this->page;
		}			
		// Maximum number of rows returned by pages (1 ~ 1000)
		$url .= '&maxPageResults=1000';		
		$url .= '&email=' . $obj->getConfigData('email');
		$url .= '&token=' . $obj->getConfigData('token');		
		return $url;
	}		
	/**
	 * Get list of payment PagSeguro
	 * @return array $transactionArray - Array with transactions
	 */ 
	private function getPagSeguroPaymentList()
	{
		$curl = curl_init($this->getUrlTransactionConsultWebService());
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$transaction = curl_exec($curl);
		curl_close($curl);		
		if ($transaction == 'Unauthorized'){
			return 'unauthorized';	
		} else {					
			$objects = json_decode(json_encode((array) simplexml_load_string($transaction)), 1);				
			// If you have more than one page in the webservice, is the query of all pages
			if ($objects['totalPages'] > 1) {
				for ($i = 1; $i < ($objects['totalPages'] + 1); $i++){
					$this->page = $i;
					$curl = curl_init($this->getUrlTransactionConsultWebService());
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$transaction = curl_exec($curl);
					curl_close($curl);					
					$obj = json_decode(json_encode((array) simplexml_load_string($transaction)), 1);
					$array[] = $obj['transactions'];
				}				
				$this->page = '';				
				for ($i = 0; $i< (count($array)); $i++) {
					foreach ($array[$i]['transaction'] as $item) {
						$info['transaction'][] = $item;
					}
				}				
				$transactionArray = $info['transaction'];
			} else {
				$transactionArray = ($objects['resultsInThisPage'] == 1) ? 
									 $objects['transactions'] : current($objects['transactions']);
			}			
			if ($transactionArray) {				
				return $transactionArray;				
			}
		} 
	}	
	/**
	 * Get reference decrypt of transactions PagSeguro
	 * @param string $reference - String complete reference
	 * @return string $refDecrypted - String of 5 characteres
	 */
	private function getReferenceDecrypt($reference)
	{
		$refDecrypted = substr($reference, 0,5);		
		return $refDecrypted;
	}	
	/**
	 * Get id of order, of returned of reference of the transaction PagSeguro
	 * @param string $reference - String complete reference
	 * @return int $orderIdDecrypted - Id of order
	 */
	public function getReferenceDecryptOrderID($reference)
	{
		$orderIdDecrypted = str_replace(substr($reference, 0,5),'',$reference);		
		return $orderIdDecrypted;
	}
	/**
	 * Filters by payments PagSeguro containing the same request Store	 
	 * @var int $orderId - Id of order
	 * @var string $info['code'] - Transaction code of PagSeguro
	 * @var string $info['status'] - Status of payment of PagSeguro
	 * @method array $this->createArrayPayments - Stores the array that contains only the payments that were made in the store at PagSeguro
	 */
	private function getMagentoPayments()
	{		
		$reference = $this->getReadReferenceBank();
		$paymentList = $this->getPagSeguroPaymentList();
		$this->arrayPayments = '';			
		if ($paymentList) {			
			foreach ($paymentList as $info) {		
				if ($reference == $this->getReferenceDecrypt($info['reference'])) {
					$orderId = $this->getReferenceDecryptOrderID($info['reference']);
					$order = Mage::getModel('sales/order')->load($orderId);	
					if ($_SESSION['store_id'] != '') {
						if ( $order->getStoreId() == $_SESSION['store_id']) {
							$this->createArrayPayments($orderId, $info['code'], $info['status']);
						}
					} else {
						if ($order) {
							$this->createArrayPayments($orderId, $info['code'], $info['status']);
						}	
					}
					$_SESSION['store_id'] == '';	
				} 	
			}			
		}
	}	
	/**
	 * Get status of PagSeguro or string required to change the order status Magento
	 * @param int $status - Number that contains the status of PagSeguro
	 * @param boolean $orderMagento - If the return will be to change order status Magento
	 * @return string $status - String that will be displayed in the table or used to change the order status Magento
	 */
	private function getPaymentStatusPagSeguro($status,$orderMagento)
	{
		if ($orderMagento == true) {			
			switch ($status) {				
				case 1: $status = 'aguardando_pagamento_ps'; break;
				case 2: $status = 'em_analise_ps'; break; 
				case 3: $status = 'paga_ps'; break;
				case 4: $status = 'disponivel_ps'; break;
				case 5: $status = 'em_disputa_ps'; break;
				case 6: $status = 'devolvida_ps'; break;
				case 7:	$status = 'cancelada_ps'; break;			
			}			
		} else {		
			switch ($status) {				
				case 1: $status = 'Aguardando pagamento'; break;
				case 2: $status = 'Em an&aacute;lise'; break; 
				case 3: $status = 'Paga'; break;
				case 4: $status = 'Dispon&iacute;vel'; break;
				case 5: $status = 'Em disputa'; break;
				case 6: $status = 'Devolvida'; break;
				case 7:	$status = 'Cancelada'; break;			
			}
		}		
		return $status;
	}	
	/**
	 * Get status of order of magento
	 * @param string $status - Strin that contains the status of PagSeguro in order Magento
	 * @return string $status - Returns the correct status queried the current status
	 */
	private function getPaymentStatusMagento($status)
	{
		switch ($status) {			
			case 'Aguardando_pagamento_ps': $status = 'Aguardando pagamento'; break;
			case 'Em_analise_ps': $status = 'Em an&aacute;lise'; break;
			case 'Paga_ps': $status = 'Paga'; break; 
			case 'Disponivel_ps': $status = 'Dispon&iacute;vel'; break;
			case 'Em_disputa_ps': $status = 'Em disputa'; break;
			case 'Devolvida_ps': $status = 'Devolvida'; break;
			case 'Cancelada_ps': $status = 'Cancelada';	break;
		}		
		return $status;
	}
	/**
	 * Get the date of the request from Magento and convert to the format (d/m/Y)
	 * @param date $date - Initial date of order, in default format of Magento
	 * @return date $dateConverted - Returns the date converted
	 */
	private function getOrderMagetoDateConvert($date)
	{
		$dateConverted = date('d/m/Y', strtotime($date));		
		return $dateConverted;
	}		
	/**
	 * Get url request editing Magento
	 * @param int $idOrder - Id of order of Magento
	 * @return string $url - url full of the application for editing
	 */
	private function getEditOrderUrl($idOrder)
	{
		$url = Mage::getSingleton('adminhtml/url')
			   ->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));		
		return $url;
	}	
	/**
	 * Creates the complete array with the necessary information for the table
	 * @var int $orderId - Id of order of Magento
	 * @var string $paymentCode - Transaction code of PagSeguro
	 * @var int $paymentStatus - Status of payment of PagSeguro
	 * @method array $this->arrayPayments - Set the complete array with the necessary information for the table
	 */
	private function createArrayPayments($orderId, $paymentCode, $paymentStatus)
	{
		// Receives the object of order that was entered the id		
		$order = Mage::getModel('sales/order')->load($orderId);	
		// Receives the creation date of the application which is converted to the format d/m/Y
		$dateOrder = $this->getOrderMagetoDateConvert($order->getCreatedAt());		
		// Receives the number of order
		$idMagento = '#' . $order->getIncrementId();		
		// Receives the transaction code of PagSeguro
		$idPagSeguro = $paymentCode;		
		// Receives the status already converted and translated of order of Magento
		$statusMagento = $this->getPaymentStatusMagento($this->__(ucfirst($order->getStatus())));		
		// Receives the status of the transaction PagSeguro already converted		
		$statusPagSeguro = $this->getPaymentStatusPagSeguro($paymentStatus);		
		// Receives the full url to access the module skin
		$skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/pagseguro/';		
		// Receives the parameter used to update an order
		$config = $order->getId() . '/' . $idPagSeguro . '/' . $this->getPaymentStatusPagSeguro($paymentStatus,true);		
		// Receives the url edit order it from your id		
		$editUrl = $this->getEditOrderUrl($orderId);		
		$textEdit = $this->__('Editar');
		$textUpdate = $this->__('Atualizar');		
		// Receives the full html link to edit an order
		$editOrder .= "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($orderId) . "'>";
		$editImage = $skinUrl . "images/edit.gif";
		$editOrder .= "<img title='" . $textEdit . "' alt='" . $textEdit . "' src='" . $editImage ."' />";
		$editOrder .= "</a>";		
		// Receives the full html link to update an application		
		if ($statusMagento == $statusPagSeguro) {
			$updateImage = $skinUrl . 'images/refresh_deactived.png';
			$class = ' deactived';
			$event = "";
		} else {
			$updateImage = $skinUrl . 'images/refresh_.png';
			$event = "onclick='updateOrder(this)' data-config='" . $config . "'";
		}
		$updateOrder .= "<a class='update{$class}' {$event} href='javascript:void(0)'>";
		$updateOrder .= "<img title='" . $textUpdate . "' alt='" . $textUpdate . "' src='" . $updateImage . "' />";
		$updateOrder .= "</a>";	
		$array = array( 'date' => $dateOrder,
						'id_magento' => $idMagento,
						'id_pagseguro' => $idPagSeguro,
						'status_magento' => $statusMagento,
						'status_pagseguro' => $statusPagSeguro,
						'edit' => $editOrder,
						'update' => $updateOrder);		
		$this->arrayPayments[] = $array;		
	}	
	/**
	 * Get the full array with only the requests made ​​in Magento with PagSeguro
	 * @return array $this->arrayPayments - Returns an array with the necessary information to fill the table
	 */
	public function getArrayPayments()
	{
		$this->getMagentoPayments();			
		return $this->arrayPayments;
	}	
	/**
	 * Verifies that the correct date, starting a certain number of days
	 * @param int $days - Number of days to be checked the date
	 * @return date $correctDate - Returns the correct date
	 */
	private function getDateSubtracted($days)
	{
		$days = ($days > 30) ? 30 : $days;
        $thisyear = date('Y');
	    $thismonth = date('m');
	    $thisday = date('d');
        $nextdate = mktime(0, 0, 0, $thismonth, $thisday - $days, $thisyear);
        $correctDate = strftime("%Y-%m-%d", $nextdate);
        return $correctDate;
    }	
	/**
	 * Set the start date to be found on webservice, starting from the days entered
	 * @param int $days - Days preceding the date should be initiated
	 * @method date $this->dateStart - Receives the start date to be consulted
	 */
	public function setDateStart($days)
	{		
		$this->dateStart = $this->getDateSubtracted($days);		
	}	
	/**
	 * Get the latest status of your order before your upgrade request
	 * @param int $orderId - Id of order of Magento
	 * @return string $obj->getStatus() - Returns the status of order of Magento
	 */
	private function getLastStatusOrder($orderId)
	{
		$obj = Mage::getModel('sales/order')->load($orderId);		
        return $obj->getStatus();
	}	
	/**
	 * Updates the order status of Magento
	 * Creates notification in the historical in order of Magento and sends email to the customer
	 * Insert the transaction code of PagSeguro in order of Magento
	 * @param int $orderId - Id of order of Magento
	 * @param string $transactionCode - Transaction code of PagSeguro
	 * @param string $orderStatus - Status of transaction of PagSeguro
	 */
	public function updateOrderStatusMagento($orderId, $transactionCode, $orderStatus)
	{
		$this->setConciliationUpdateOrderLog($orderId, $transactionCode, $orderStatus);			
		if($this->getLastStatusOrder($orderId) != $orderStatus){				
			$status = $orderStatus;
			$comment = null;
			$notify = true;						
			$order = Mage::getModel('sales/order')->load($orderId);
			$order->addStatusToHistory($status, $comment, $notify);
			$order->sendOrderUpdateEmail($notify, $comment);			
			// Makes the notification of the order of historic displays the correct date and time
			Mage::app()->getLocale()->date();
			$order->save();				
		}					
		$table_prefix = (string)Mage::getConfig()->getTablePrefix();
		$read= Mage::getSingleton('core/resource')->getConnection('core_read');
		$value = $read->query("SELECT `order_id` FROM `" . $table_prefix . "pagseguro_sales_code` 
							   WHERE `order_id` = " . $orderId);
		$row = $value->fetch();			
		if ($row == false) {
		    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		    $sql = "INSERT INTO " . $table_prefix . "`pagseguro_sales_code` (`order_id`,`transaction_code`) 
		    		VALUES ('$orderId','$transactionCode')";
		    $connection->query($sql);
		}
	}
	/**
	 * Set log records listed
	 * @method setLog - Set log in file
	 */	
	public function setConciliationListLog($days)
	{
		$module = ' [Info] PagSeguroConciliation.';
		// Sentence of log
		$phrase = "Searched( '" . $days . " days - Range of dates ";
		$phrase .= $this->getDateStart() . " until " . 
				   $this->getDateFinally() . "' )";
		// Creating the update log order
		$this->setLog($phrase, $module);
	}
	/**
	 * Set log of update order
	 * @method setLog - Set log in file
	 */	
	private function setConciliationUpdateOrderLog($orderId, $transactionCode, $orderStatus)	
	{
		$module = ' [Info] PagSeguroConciliation.';
		// Sentence of log
		$phrase = 'Update(';
		$phrase .= "OrderStatusMagento: array (\n  'orderId' => " . $orderId . ",\n  ";
		$phrase .= "'transactionCode' => '" . $transactionCode . "',\n  ";
		$phrase .= "'orderStatus' => '" . $orderStatus . "'\n))";		
		// Creating the update log order
		$this->setLog($phrase, $module);
	}
	/**
	 * Creating log update or search
	 * @param string $phrase - It's the phrase that completes the log
	 * @param string $module - It's the title that completes the log
	 */
	public function setLog($phrase, $module)
	{
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');		
		// value 0/1
		$log = $obj->getConfigData('log');		
		if ($log == 1) {
			if ($obj->getConfigData('log_file') != '') {
				$directoryLog = Mage::getBaseDir() . '/' . $obj->getConfigData('log_file');					
			} else {
				$directoryLog = Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguro.log';
			}	
			$date = '{' . Mage::app()->getLocale()->date() . '}';
			$return = $date . $module . $phrase . "\r\n";				
			file_put_contents($directoryLog, $return, FILE_APPEND);
		}
	}
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
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');			
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
	 * Get list of abandoned PagSeguro
	 * @return array $listAbandoned - Array with transactions
	 */ 
	public function getPagSeguroAbandonedList()
	{
		if ($this->access == 1) {
			include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
			$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');	
			$this->dateStart = $this->getDateSubtracted($obj->getConfigData('abandoned_link'));
			try {
				$listAbandoned = PagSeguroTransactionSearchService::searchAbandoned($obj->getCredentialsInformation(),
																					1, 1000, $this->getDateStart());
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
		$reference = $this->getReadReferenceBank();
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
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');	
		$validity_link = $this->getAbandonedDateAddDays(10, $order->getCreatedAt());
		// Receives the full url to access the module skin
		$skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/pagseguro/';
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
		$obj = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');
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
		$config = Mage::getSingleton('PagSeguro_PagSeguro_Model_PaymentMethod');
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