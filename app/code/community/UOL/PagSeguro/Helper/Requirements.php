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
    /**
     * Validate if the requirements are enable for use correct of the PagSeguro
     * @return array
     */
    public function validateRequirements()
    {
        $req = array('version' => '', 'spl' => '', 'curl' => '', 'dom' => '', 'currency' => '');

        $version = str_replace('.', '', phpversion());

        if ($version < 540) {
            $msg = $this->__('PagSeguroLibrary: É necessária a versão 5.4.27 do PHP ou maior.');
            $req['version']['text'] = $msg;
            $req['version']['status'] = false;
        } else {
            $req['version']['text'] = $this->__('Versão do PHP superior à 5.4.27.');
            $req['version']['status'] = true;
        }

        if (!function_exists('spl_autoload_register')) {
            $req['spl']['text'] = $this->__('PagSeguroLibrary: Biblioteca padrão do PHP (SPL) é necessária.');
            $req['spl']['status'] = false;
        } else {
            $req['spl']['text'] = $this->__('Biblioteca padrão do PHP (SPL) instalada.');
            $req['spl']['status'] = true;
        }

        if (!function_exists('curl_init')) {
            $req['curl']['text'] = $this->__('PagSeguroLibrary: A biblioteca cURL é necessária.');
            $req['curl']['status'] = false;
        } else {
            $req['curl']['text'] = $this->__('Biblioteca cURL instalada.');
            $req['curl']['status'] = true;
        }

        if (!class_exists('DOMDocument')) {
            $req['dom']['text'] = $this->__('PagSeguroLibrary: A extensão DOM XML é necessária.');
            $req['dom']['status'] = false;
        } else {
            $req['dom']['text'] = $this->__('DOM XML instalado.');
            $req['dom']['status'] = true;
        }

        $currencyCode = Mage::getStoreConfig('currency/options/allow');

        if ($currencyCode != "BRL") {
            $req['currency']['text'] = $this->__('Moeda REAL não instalada ou desativada.');
            $req['currency']['status'] = false;
        } else {
            $req['currency']['text'] = $this->__('Moeda REAL instalada e ativa');
            $req['currency']['status'] = true;
        }

        return $req;
    }
}
