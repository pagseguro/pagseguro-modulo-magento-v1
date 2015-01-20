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

class UOL_PagSeguro_Helper_Requirements extends HelperData
{
	// It is used to store the array of abandoned
	private $arrayRequirements = array();
	
	// It active/disable abandoned for notification
	private $access = '';
	
	// It the code of admin
	private $admLocaleCode = '';
	
	/*
	 * Checks that is active query requirements
	 * Checks if email was filled and token
	 * Checks if email and token are valid
	 * If not completed one or both, is directed and notified so it can be filled
	 */	
	public function checkRequirementsAccess()
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
	
	/***
     * Validate if the requirements are enable for use correct of the PagSeguro
     * @return array
     */
    public function validateRequirements()
    {

        $requirements = array(
            'version' => '',
            'spl' => '',
            'curl' => '',
            'dom' => '',
            'currency' => ''
        );

        $version = str_replace('.', '', phpversion());

        if ($version < 533) {
            $requirements['version'] = $this->__('PagSeguroLibrary: É necessária a versão 5.3.3 do PHP ou maior.');
        } else {
        	$requirements['version'] = $this->__('Versão do PHP superior à 5.3.3.');
        }

        if (!function_exists('spl_autoload_register')) {
            $requirements['spl'] = $this->__('PagSeguroLibrary: Biblioteca padrão do PHP (SPL) é necessária.');
        } else {
        	$requirements['spl'] = $this->__('Biblioteca padrão do PHP (SPL) instalada.');
        }

        if (!function_exists('curl_init')) {
            $requirements['curl'] = $this->__('PagSeguroLibrary: A biblioteca cURL é necessária.');
        } else {
        	$requirements['curl'] = $this->__('Biblioteca cURL instalada.');
        }

        if (!class_exists('DOMDocument')) {
            $requirements['dom'] = $this->__('PagSeguroLibrary: A extensão DOM XML é necessária.');
        } else {
        	$requirements['dom'] = $this->__('DOM XML instalado.');
        }

        $CurrencyCode = Mage::getStoreConfig('currency/options/allow');
        if ($CurrencyCode != "BRL"){
        	$requirements['currency'] = $this->__('Moeda REAL não instalada.');
        } else {
        	$requirements['currency'] = $this->__('Moeda REAL instalada.');
        }

        return $requirements;
    }
	
	/**
	 * Set the log when searched records
	 * @method setLog - Set log in file
	 */
	public function setRequirementsLog()
	{
		$config = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

		// Set title
		$module = ' [Info] PagSeguroRequirements.';	
		
		// Sentence of log
		$phrase = "get()";
				   
		// Creating the update log order
		$this->setLog($phrase, $module);			
	}
}