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

class UOL_PagSeguro_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Creates the layout of the administration
	 * Receives the post and comes back	 
	 */
    public function indexAction()
    {    	
		$origin = $this->getRequest()->getPost('origin');
		$sendmail = $this->getRequest()->getPost('sendmail');
		if ($origin == 'conciliation')
			echo $this->getConciliationGrid();
		elseif ($origin == 'abandoned') {
			$json = $this->getRequest()->getPost('json');
			if ($json) {
				echo $this->sendAbandonedMail($json);
			} else {
				echo $this->getAbandonedGrid();	
			}						
		} elseif ($origin == 'requirements') {
			echo $this->getRequirements();
		}
	}
	
	/**
	 * Generates the data conciliation to populate the table
	 * @return array $dataSet - Array of data for table
	 */
	private function getConciliationGrid()
	{
		$helper = Mage::helper('pagseguro/conciliation');				
		$days = $this->getRequest()->getPost('days');
			
		// Saves the day searching for the global variable that receives the array
		if ($days) {			
			$_SESSION['days'] = $days;
			Mage::helper('pagseguro/conciliation')->setDateStart($days);			
		}		
		
		// Upgrade from Magento order status
		if ($json = $this->getRequest()->getPost('json')) {
			foreach ($json as $value) {	
				$helper->updateOrderStatusMagento($value['id'], $value['code'], $value['status']);
			}
			Mage::helper('pagseguro/conciliation')->setDateStart($_SESSION['days']);
		} else {
			if ($_SESSION['days'] != 0) {
				$helper->setConciliationListLog($days);	
			}
		}

		try {
			// Rides array that returns the query transactions
			if ($conciliationArray = $helper->getArrayPayments()) {				
				$dataSet = '[';
				$j = 1;				
				
				foreach ($conciliationArray as $info) {
					$i = 1;
					$dataSet .= ($j > 1) ? ',[' : '[';								
					foreach ($info as $item) {	
						$dataSet .= (count($info) != $i) ? '"' . $item . '",' : '"' . $item . '"';			
						$i++;				
					}
					$dataSet .= ']';
					$j++;
				}
				$dataSet .= ']';	
					
				return $dataSet;
			} else {
				return 'run';
			}		
			
		} catch (Exception $e) {
			return trim($e->getMessage());
		}
	}

	/**
	 * Generates the data abandoned to populate the table
	 * @return array $dataSet - Array of data for table
	 */
	private function getAbandonedGrid()
	{		
		$days = $this->getRequest()->getPost('days');

		$helper = Mage::helper('pagseguro/abandoned');
		$helper->setAbandonedListLog($days);	
		$helper->checkAbandonedAccess($days);

		try {
			if ($abandonedArray = $helper->getArrayAbandoned()) {
				$dataSet = '[';
				$j = 1;				
				foreach ($abandonedArray as $info) {
					$i = 1;
					$dataSet .= ($j > 1) ? ',[' : '[';								
					foreach ($info as $item) {	
						$dataSet .= (count($info) != $i) ? '"' . $item . '",' : '"' . $item . '"';			
						$i++;				
					}
					$dataSet .= ']';
					$j++;
				}
				$dataSet .= ']';	
				
				return $dataSet;	
			}
		} catch (Exception $e) {
			return trim($e->getMessage());
		}
	}
	
	/**
	 * Generates emailing abandoned customer
	 * @param array $json - Records to send
	 * @return string $run - String to guide it displays the notification message
	 */
	private function sendAbandonedMail($json)
	{
		$helper = Mage::helper('pagseguro/abandoned');		
		$helper->setAdminLocaleCode(Mage::app()->getLocale()->getLocaleCode());
		
		foreach ($json as $value) {				
			$helper->sendAbandonedEmail($value['id'], $value['recovery']);
		}
		
		return 'run';
	}

	/**
	 * Generates the data abandoned to populate the table
	 * @return array $dataSet - Array of data for table
	 */
	private function getRequirements()
	{		

		$helper = Mage::helper('pagseguro/requirements');
		$helper->setRequirementsLog();	
		$helper->checkRequirementsAccess();

		return json_encode($helper->validateRequirements());
	}
}