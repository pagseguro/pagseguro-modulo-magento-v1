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
        
    private $arraySt;
    private $objStatus;
	
	// It is used to store the initial consultation date of transactions
	private $dateStart = '';

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_createArraySt();
		$this->changeEnvironment();

    }
        
    /**
     * Create Array Status PagSeguro
     */
    private function _createArraySt()
    {
        $this->arraySt = array(
            0 => array("status" => "iniciado_ps", "label" => "Iniciado"),
            1 => array("status" => "aguardando_pagamento_ps", "label" => "Aguardando Pagamento"),
            2 => array("status" => "em_analise_ps", "label" => "Em análise"),
            3 => array("status" => "paga_ps", "label" => "Paga"),
            4 => array("status" => "disponivel_ps", "label" => "Disponível"),
            5 => array("status" => "em_disputa_ps", "label" => "Em Disputa"),
            6 => array("status" => "devolvida_ps", "label" => "Devolvida"),
            7 => array("status" => "cancelada_ps", "label" => "Cancelada")
        );
    }

    /**
     * Return payment status by key PagSeguro 
     * @param type $value
     * @return type
     */
    public function returnOrderStByStPagSeguro($value)
    {
        return (array_key_exists($value, $this->arraySt) ? $this->arraySt[$value] : false);
    }

    /**
    * get array status
    * @return type
    */
    public function getArraySt()
    {
        return $this->arraySt;
    }

    /**
     * Save Status PagSeguro 
     */
    public function saveAllStatusPagSeguro()
    {
        foreach ($this->arraySt as $key => $value) {
            if (!$this->_existsStatus($value['status'])) {
                $this->objStatus->setStatus($value['status'])
                       ->setLabel($value['label']);
                $this->objStatus->save();
            }
        }
    }
    
    /**
     * Save Status PagSeguro
     * @param array $value
     */
    public function saveStatusPagSeguro(array $value)
    {
        if (!$this->_existsStatus($value['status'])) {
            $this->objStatus->setStatus($value['status'])
                 ->setLabel($value['label']);
            $this->objStatus->save();
        }
    }
    
    /**
     * Exists Status
     * @param type $status
     * @return type
     */
    public function _existsStatus($status)
    {
        $this->objStatus = Mage::getModel('sales/order_status')->load($status);

        return ($this->objStatus->getStatus()) ? true : false;
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
	 * Get reference decrypt of transactions PagSeguro
	 * @param string $reference - String complete reference
	 * @return string $refDecrypted - String of 5 characteres
	 */
	public function getReferenceDecrypt($reference)
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