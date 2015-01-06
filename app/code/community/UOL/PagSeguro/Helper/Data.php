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

use Mage_Payment_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Data extends HelperData
{	
	// It is used to store the initial consultation date of transactions
	private $dateStart = '';

    /**
     * Construct
     */
    public function __construct()
    {
		$this->changeEnvironment();

    }
        
	/**
	 * Get status of PagSeguro or string required to change the order status Magento
	 * @param int $status - Number that contains the status of PagSeguro
	 * @param boolean $orderMagento - If the return will be to change order status Magento
	 * @return string $status - String that will be displayed in the table or used to change the order status Magento
	 */
	public function getPaymentStatusPagSeguro($status,$orderMagento)
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
				case 8:	$status = 'chargeback_debitado_ps'; break;
				case 9:	$status = 'em_contestacao_ps'; break;			
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
				case 8:	$status = 'Chargeback Debitado'; break;
				case 9:	$status = 'Em Contestação'; break;		
			}
		}
			
		return $status;
	}
	
	/**
	 * Get status of order of magento
	 * @param string $status - Strin that contains the status of PagSeguro in order Magento
	 * @return string $status - Returns the correct status queried the current status
	 */
	public function getPaymentStatusMagento($status)
	{
		switch ($status) {			
			case 'Aguardando_pagamento_ps': $status = 'Aguardando pagamento'; break;
			case 'Em_analise_ps': $status = 'Em an&aacute;lise'; break;
			case 'Paga_ps': $status = 'Paga'; break; 
			case 'Disponivel_ps': $status = 'Dispon&iacute;vel'; break;
			case 'Em_disputa_ps': $status = 'Em disputa'; break;
			case 'Devolvida_ps': $status = 'Devolvida'; break;
			case 'Cancelada_ps': $status = 'Cancelada';	break;
			case 'Chargeback_debitado_ps': $status = 'Chargeback debitado';	break;
			case 'Em_contestacao_ps': $status = 'Em contestação';	break;
		}
				
		return $status;
	}

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
	
	/**
	 * Returns the registered references in the database
	 * @return string $reference - String encrypted of 5 characters of database
	 */ 
	public function getStoreReference()
	{
		$reference = Mage::getStoreConfig('uol_pagseguro/store/reference');
				
		return $reference;
	}
	
	/**
	 * Get reference decrypt of transactions PagSeguro
	 * @param string $reference - String complete reference
	 * @return string $refDecrypted - String of 5 characteres
	 */
	public function getReferenceDecrypt($reference)
	{
		$refDecrypted = substr($reference, 0, 5);
				
		return $refDecrypted;
	}
	
	/**
	 * Get id of order, of returned of reference of the transaction PagSeguro
	 * @param string $reference - String complete reference
	 * @return int $orderIdDecrypted - Id of order
	 */
	public function getReferenceDecryptOrderID($reference)
	{
		$orderIdDecrypted = str_replace(substr($reference, 0, 5), '', $reference);
			
		return $orderIdDecrypted;
	}
	
	/**
	 * Get date start
	 * @return date $dateStart - Example Y-m-dT00:00
	 */
	public function getDateStart()
	{
		$this->dateStart = $_SESSION['dateStart'];
				
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
	 * Verifies that the correct date, starting a certain number of days
	 * @param int $days - Number of days to be checked the date
	 * @return date $correctDate - Returns the correct date
	 */
	public function getDateSubtracted($days)
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
	 * Get url request editing Magento
	 * @param int $idOrder - Id of order of Magento
	 * @return string $url - url full of the application for editing
	 */
	public function getEditOrderUrl($idOrder)
	{
		$obj = Mage::getSingleton('adminhtml/url');
		$url = $obj->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));
			   		
		return $url;
	}
	
	/**
	 * Creating log for conciliation and abandoned
	 * @param string $phrase - It's the phrase that completes the log
	 * @param string $module - It's the title that completes the log
	 */
	public function setLog($phrase, $module)
	{
		$obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
				
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
	
	/**
	 * Change the environment if necessary.
	 */
	private function changeEnvironment()
	{
		// Get the check of environment of backend.
		$environment = '"' . Mage::getStoreConfig('payment/pagseguro/environment') . '"';
		
		// File to be changed
		$archive = Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php';
		
		// Search the current environment of library.
		$search = "PagSeguroConfig['environment']";
		
		// Save the file in an array in variable $arrayArchive.
		$arrayArchive = file($archive);
		$position = 0;
		
		for ($i = 0; $i < sizeof($arrayArchive); $i++) {
			
			// Checks the position of environmental on array, and stores the environment on variable $libEnvironment.
			if (strpos($arrayArchive[$i],$search) && 
			   (strpos($arrayArchive[$i],'production') || strpos($arrayArchive[$i],'sandbox'))) {
					
				$fullLine = $arrayArchive[$i];	
				$position = $i;		
				
				if (strpos($fullLine, '"production"') == true) {
					$libEnvironment = '"production"';
				} else if (strpos($fullLine, '"sandbox"') == true) {
					$libEnvironment = '"sandbox"';
				}
			}
		}
		
		// Make changes the environment, if  the environments are different.
		if ($environment != '""' && $environment != $libEnvironment) {				
			$arrayArchive[$position] = str_replace($libEnvironment, $environment, $fullLine);
	    	file_put_contents($archive, implode("", $arrayArchive));
		}
	}
}