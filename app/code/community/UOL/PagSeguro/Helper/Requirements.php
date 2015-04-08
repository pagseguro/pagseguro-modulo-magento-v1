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
	/***
     * Validate if the requirements are enable for use correct of the PagSeguro
     * @return array
     */
    public function validateRequirements()
    {
        $requirements = array('version' => '', 'spl' => '', 'curl' => '', 'dom' => '', 'currency' => '');

        $version = str_replace('.', '', phpversion());

        if ($version < 533) {
        	$msg = $this->__('PagSeguroLibrary: É necessária a versão 5.3.3 do PHP ou maior.');
            $requirements['version']['text'] = $msg;
            $requirements['version']['status'] = false;
        } else {
        	$requirements['version']['text'] = $this->__('Versão do PHP superior à 5.3.3.');
        	$requirements['version']['status'] = true;
        }

        if (!function_exists('spl_autoload_register')) {
            $requirements['spl']['text'] = $this->__('PagSeguroLibrary: Biblioteca padrão do PHP (SPL) é necessária.');
            $requirements['spl']['status'] = false;
        } else {
        	$requirements['spl']['text'] = $this->__('Biblioteca padrão do PHP (SPL) instalada.');
        	$requirements['spl']['status'] = true;
        }

        if (!function_exists('curl_init')) {
            $requirements['curl']['text'] = $this->__('PagSeguroLibrary: A biblioteca cURL é necessária.');
            $requirements['curl']['status'] = false;
        } else {
        	$requirements['curl']['text'] = $this->__('Biblioteca cURL instalada.');
        	$requirements['curl']['status'] = true;
        }

        if (!class_exists('DOMDocument')) {
            $requirements['dom']['text'] = $this->__('PagSeguroLibrary: A extensão DOM XML é necessária.');
            $requirements['dom']['status'] = false;
        } else {
        	$requirements['dom']['text'] = $this->__('DOM XML instalado.');
        	$requirements['dom']['status'] = true;
        }

        $CurrencyCode = Mage::getStoreConfig('currency/options/allow');
		
        if ($CurrencyCode != "BRL"){
        	$requirements['currency']['text'] = $this->__('Moeda REAL não instalada ou desativada.');
        	$requirements['currency']['status'] = false;
        } else {
        	$requirements['currency']['text'] = $this->__('Moeda REAL instalada e ativa');
        	$requirements['currency']['status'] = true;
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