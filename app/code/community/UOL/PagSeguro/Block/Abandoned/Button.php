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

class UOL_PagSeguro_Block_Abandoned_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
 	/**
	 * Creates the layout and action button to access the page conciliation
	 * @return html $html
	 */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
		
		// Get access url conciliation already with the key
        $url = $this->getUrl('pagseguro/adminhtml_abandoned'); 
 
        $html = 'Verificar transações abandonadas ' . $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Clique aqui')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();
 
        return $html;
    }
}
?>