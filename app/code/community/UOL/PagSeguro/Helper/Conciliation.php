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

class UOL_PagSeguro_Helper_Conciliation extends HelperData
{
	// It is used to store the array of transactions
	private $arrayPayments = array();

	private $environment;

	public function __construct()
	{
		include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');	
		$this->environment = PagSeguroConfig::getEnvironment();
	}
					
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
	} 	
		
	/**
	 * Get url of transaction search service
	 * @return string $url - Returns full url to query
	 */
	private function getUrlTransactionSearchService()
	{
		
		// Capture the url query the webservice
		$url = $PagSeguroResources['webserviceUrl'][$this->environment] . 
			   $PagSeguroResources['transactionSearchService']['servicePath'];
			   			   
		return $url;
	}

	/**
	 * Set the start date to be found on webservice, starting from the days entered
	 * @param int $days - Days preceding the date should be initiated
	 */
	public function setDateStart($days)
	{		
		$_SESSION['dateStart'] = $this->getDateSubtracted($days);		
	}	
					
	/**
	 * Get list of payment PagSeguro
	 * @return array $transactionArray - Array with transactions
	 */ 
	private function getPagSeguroPaymentList()
	{		
		include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');	
		
		try {
			$credential = $obj->getCredentialsInformation();
			$dateStart = $this->getDateStart();		
			$transactions = PagSeguroTransactionSearchService::searchByDate($credential, 1, 1000, $dateStart);
			$pages = $transactions->getTotalPages();
			
			if ($pages > 1) {
				for ($i = 1; $i < ($pages + 1); $i++){
					$transactions = PagSeguroTransactionSearchService::searchByDate($credential, $i, 1, $dateStart);
					$transactionArray .= array_push($transactions->getTransactions());
				}						
			} else {
				$transactionArray = $transactions->getTransactions();
			}			
			
			return $transactionArray;
			
		} catch (PagSeguroServiceException $e) {
	        if(trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED'){
	        	throw new Exception( $e->getMessage() );
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
		$reference = $this->getStoreReference();
		$paymentList = $this->getPagSeguroPaymentList();
		$this->arrayPayments = '';
				
		if ($paymentList) {			
			foreach ($paymentList as $info) {		
				if ($reference == $this->getReferenceDecrypt($info->getReference())) {
					$orderId = $this->getReferenceDecryptOrderID($info->getReference());
					$order = Mage::getModel('sales/order')->load($orderId);
					
					if ($_SESSION['store_id'] != '') {
						if ( $order->getStoreId() == $_SESSION['store_id']) {
							$this->createArrayPayments($orderId, $info->getCode(), $info->getStatus()->getValue());
						}
					} else {
						if ($order) {
							$this->createArrayPayments($orderId, $info->getCode(), $info->getStatus()->getValue());
						}	
					}
					$_SESSION['store_id'] == '';	
				} 	
			}			
		}
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
	 * @param int $orderId - Id of order of Magento
	 * @param string $paymentCode - Transaction code of PagSeguro
	 * @param int $paymentStatus - Status of payment of PagSeguro
	 * @method array $this->arrayPayments - Set the complete array with the necessary information for the table
	 */
	private function createArrayPayments($orderId, $paymentCode, $paymentStatus)
	{		
		// Receives the object of order that was entered the id		
		$order = Mage::getModel('sales/order')->load($orderId);
		
		// Receives the status already converted and translated of order of Magento
		$statusMagento = strtolower($this->getPaymentStatusMagento($this->__(ucfirst($order->getStatus()))));
				
		// Receives the status of the transaction PagSeguro already converted		
		$statusPagSeguro = strtolower($this->getPaymentStatusPagSeguro($paymentStatus));
		
		if ($statusMagento != $statusPagSeguro) {	
			// Receives the creation date of the application which is converted to the format d/m/Y
			$dateOrder = $this->getOrderMagetoDateConvert($order->getCreatedAt());		
			
			// Receives the number of order
			$idMagento = '#' . $order->getIncrementId();	
				
			// Receives the transaction code of PagSeguro
			$idPagSeguro = $paymentCode;
			
			// Receives the parameter used to update an order
			$config = $order->getId() .'/'. $idPagSeguro .'/'. $this->getPaymentStatusPagSeguro($paymentStatus,true);
			
			$checkbox =  "<label class='chk_email'>";
			$checkbox .= "<input type='checkbox' name='conciliation_orders[]' data-config='" . $config . "' />";
			$checkbox .= "</label>";	
					
			// Receives the full url to access the module skin
			$skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/uol/pagseguro/';				
					
			// Receives the url edit order it from your id		
			$editUrl = $this->getEditOrderUrl($orderId);		
			$textEdit = $this->__('Ver detalhes');	
				
			// Receives the full html link to edit an order
			$editOrder .= "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($orderId) . "'>";
			$editOrder .= $this->__('Ver detalhes') . "</a>";		
		
			$array = array( 'checkbox' => $checkbox,
							'date' => $dateOrder,
							'id_magento' => $idMagento,
							'id_pagseguro' => $idPagSeguro,
							'status_magento' => $statusMagento,
							'status_pagseguro' => $statusPagSeguro,
							'edit' => $editOrder);	
			$this->arrayPayments[] = $array;		
		}	
		
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

		//Get the resource model
    	$resource = Mage::getSingleton('core/resource');
		
    	//Retrieve the read connection
		$readConnection = $resource->getConnection('core_read');
		
		//Retrieve the write connection
		$writeConnection = $resource->getConnection('core_write');

		$tp    = (string)Mage::getConfig()->getTablePrefix();
		$table = $tp . 'pagseguro_orders';

		//Select sent column from pagseguro_orders to verify if exists a register
		$query = 'SELECT order_id FROM ' . $resource->getTableName($table) . ' WHERE order_id = '.$orderId;
		$result = $readConnection->fetchAll($query);

		if (!empty($result)) {	

	    	$sql = "UPDATE `" . $table . "` SET `transaction_code` = '$transactionCode' WHERE order_id = " . $orderId;
	    
		} else {

			$environment = ucfirst(Mage::getStoreConfig('payment/pagseguro/environment'));

			$sql = $query = "INSERT INTO " . $table . " (order_id, transaction_code, environment) VALUES ('$orderId', '$transactionCode', '$environment')";
		}

		$writeConnection->query($sql);
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