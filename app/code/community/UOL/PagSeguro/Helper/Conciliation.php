<?php

/**
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

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Conciliation extends HelperData
{
	// It is used to store the array of transactions
	private $arrayPayments = array();
	
	// It is used to access the pages generated in webservice 
	private $page;
				
	/*
	 * Checks if email was filled and token
	 * Checks if email and token are valid
	 * If not completed one or both, is directed and notified so it can be filled
	 */
	public function checkConciliationAccess()
	{
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
				
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
	 * Set the start date to be found on webservice, starting from the days entered
	 * @param int $days - Days preceding the date should be initiated
	 */
	public function setDateStart($days)
	{		
		$_SESSION['dateStart'] = Mage::helper('pagseguro')->getDateSubtracted($days);		
	}	
		
	/**
	 * Get url parameters with the transaction have to consult websevice
	 * @return string $url - Full url to query the webservice
	 */	 
	private function getUrlTransactionConsultWebService()
	{		
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');			
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
		$skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/uol/pagseguro/';	
			
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
		    $sql = "INSERT INTO `" . $table_prefix . "pagseguro_sales_code` (`order_id`,`transaction_code`) 
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
}