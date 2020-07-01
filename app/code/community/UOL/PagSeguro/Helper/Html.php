<?php
/**
 ************************************************************************
 * Copyright [2015] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */
use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Html extends HelperData
{
    /**
     * Get html of header of backend
     *
     * @return string $html - Html of header
     */
    public function getHeader()
    {
        $logo = Mage::getBaseUrl('skin').'adminhtml/default/default/uol/pagseguro/images/logo.png';
        $url = 'https://pagseguro.uol.com.br/registration/registration.jhtml?ep=7&tipo=cadastro#!vendedor';
        $version = $this->__('Versão %s', $this->getVersion());
        $id = 'pagseguro-registration-button';
        $html = '<div id="pagseguro-module-header">
                    <div class="wrapper">
                        <div id="pagseguro-logo">
                            <img class="pagseguro_logo" src="'.$logo.'" />
                            <div id="pagseguro-module-version">'.$version.'</div>
                        </div>
                        <a id="'.$id.'" class="pagseguro-button gray-theme" href="'.$url.'" target="_blank">
                            '.$this->__('Faça seu cadastro').'
                        </a>
                    </div>
                </div>';

        return $html;
    }

    /**
     * Get html of side menu of backend
     *
     * @return string $html - Html of side menu
     */
    public function getSideMenu()
    {
        // Set controller name of page in variable $page
        $page = str_replace('adminhtml_', 'pagseguro_', Mage::app()->getRequest()->getControllerName());
        $menu = new Mage_Adminhtml_Block_Page_Menu();
        $menuArray = $menu->getMenuArray();
        $html = '<div id="pagseguro-module-menu">'.
            '   <ul>';
        foreach ($menuArray['pagseguro_menu']['children'] as $key => $item) {
            $selected = ($page == $key) ? ' class="selected"' : '';
            $html .= '<li id="menu-item-'.$key.'"'.$selected.' data-has-form="true">';
            if (isset($item['children'])) {
                $html .= '<span class="children"><i class="icon"></i>'.$item['label'].'</span>
                          <ul>';
                foreach ($item['children'] as $key => $subItem) {
                    $selected = ($page == $key) ? ' class="selected"' : '';
                    $html .= '<li id="menu-subitem-'.$key.'"'.$selected.' data-has-form="true">
                                <a href="'.$this->getSideMenuUrl($key).'">
                                '.$subItem['label'].'
                                </a>
                              </li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<a href="'.$this->getSideMenuUrl($key).'">
                            '.$item['label'].'
                        </a>';
            }
            $html .= '</li>';
        }
        $html .= '  </ul>'.
            '</div>';

        return $html;
    }

    /**
     * Get url of access the page correct
     *
     * @param string $path - Path of the page to be returned
     *
     * @return string $url - Returns the url of page.
     */
    private function getSideMenuUrl($path)
    {
        $obj = Mage::getSingleton('adminhtml/url');
        if ($path == 'pagseguro_configuration') {
            $url = $obj->getUrl('adminhtml/system_config/edit/section/payment/key');
        } else {
            $correctPath = str_replace('pagseguro_', 'adminhtml_', $path);
            $url = $obj->getUrl('pagseguro/'.$correctPath);
        }

        return $url;
    }
}
