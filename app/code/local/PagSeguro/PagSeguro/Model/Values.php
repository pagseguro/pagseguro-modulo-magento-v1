<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

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


/**
 * Admin Charset Options  
 */
include_once (getcwd().'/app/code/local/PagSeguro/PagSeguro/Model/PagSeguroLibrary/config/PagSeguroConfig.class.php');

class PagSeguro_PagSeguro_Model_Values
{
    
    public function toOptionArray()
    {
        self::alertMessage();
        self::_validator();
        
        return array(
                        array("value" => "UTF-8" , "label" => "UTF-8" ),
                        array("value" => "ISO-8859-1" , "label" => "ISO-8859-1" )
                    );
    }
    
    //Mensagens de alerta caso haja erro no cURL, versão do PHP, SPL e/ou DOM.
    // mas só serão lançadas caso seja efetuado o save.
    public function alertMessage()
    {
        $requirements = PagSeguroConfig::validateRequirements();
        $required = array();
        foreach ($requirements as $key => $value) {
            if ($value != '') {
                $required[] = $value;
            }
        }
        if (!empty($required)) {
            $message = "Requerimentos para o sistema funcionar:";
            foreach ($required as $value) {
                $message .= "<br>".$value;
            }

            Mage::getSingleton('core/session')->addError($message);
        }
    }
    
    private function _validator()
    {
        require_once(getcwd().'/app/code/local/PagSeguro/PagSeguro/Model/Updates.php');
        
        Updates::createTableModule();
    }
}
