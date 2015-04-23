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

class UOL_PagSeguro_Model_Adminhtml_Config
{
    private $skin;
    private $jquery;
    private $js;
    private $jsColorbox;
    private $css;
    private $logo;
    private $version;
    private $background;

    public function __construct()
    {
        //Set skin path
        $this->skin = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);

        //Set Skin URL/
        $skinUrl = $this->skin . 'adminhtml/default/default/uol/pagseguro/';
        $configCss = $skinUrl . 'css/pagseguro-module-config.css';

        //Set headers
        $this->css = '<link rel="stylesheet" type="text/css" href="' . $configCss . '" />';
        $this->jquery = $skinUrl . 'js/jquery-1.11.1.js';
        $this->js = $skinUrl . 'js/pagseguro-module.js';
        $this->jsColorbox = $skinUrl . 'js/jquery.colorbox-min.js';

        //Set images
        $this->logo = $skinUrl . 'images/logo.png';
        $this->background = $skinUrl . 'images/background.png';

        //Set version
        $this->version = Mage::helper('pagseguro')->getVersion();
    }

    /**
     * Generates the layout of content of settings screen
     * @return string $comment - Contains the comment field in layout format
     */
    public function getCommentText()
    {
        $helper = Mage::helper('pagseguro');
        $redirect = Mage::getBaseUrl() . 'checkout/onepage/success/';
        $pgUrl = 'https://pagseguro.uol.com.br/registration/registration.jhtml?ep=7&tipo=cadastro#!vendedor';
        $id = 'pagseguro-registration-button';
        $class = 'pagseguro-button gray-theme';
        $version = $helper->__('Versão %s', $this->version);
        $backgroundCss = '#fff url(' . $this->background . ') no-repeat scroll center 45%';

        $alert  = $helper->__('Suas transações serão feitas em um ambiente de testes.') . '<br />';
        $alert .= $helper->__('Nenhuma das transações realizadas nesse ambiente tem valor monetário.');

        $notification .= $helper->__('Email ou token inválidos para o ambiente selecionado.');

        $interface = '<div class="pagseguro-comment">
                        ' . $this->css . '
                        ' . $helper->getHeader($this->logo). '
                     </div>';
        $email = Mage::getStoreConfig('payment/pagseguro/email');
        $token = Mage::getStoreConfig('payment/pagseguro/token');
        $credentials = Mage::getStoreConfig('uol_pagseguro/store/credentials');

        $comment  = '<script src="' . $this->jquery . '"></script>';
        $comment .= '<script src="' . $this->js . '"></script>';
        $comment .= '<script src="' . $this->jsColorbox . '"></script>';
        $comment .= '<script type="text/javascript">
                        var jQuery = jQuery.noConflict();
                        jQuery(document).ready(function(){
                            var content = jQuery(".pagseguro-comment").html();
                            jQuery("#payment_pagseguro").prepend(content);

                            if (!jQuery("#payment_pagseguro_redirect").val()) {
                                jQuery("#payment_pagseguro_redirect").attr("value","' . $redirect . '");
                            }

                            jQuery("#row_payment_pagseguro_comment").remove();
                            jQuery("#payment_pagseguro").css("background", "' . $backgroundCss . '");

                            jQuery("#payment_pagseguro_environment").change(function(){
                                if (jQuery("#payment_pagseguro_environment").val() == "sandbox") {
                                 Modal.message("success", "' . $alert . '");
                                }
                            });

                            var email  = "' . $email . '";
                            var token  = "' . $token . '";
                            var credentials  = "' . $credentials . '";

                            if (email && token && credentials == 0) {
                               Modal.message("error", "' . $notification . '");
                            }
                        });
                     </script>';
        $comment .= $interface;

        return $comment;
    }
}
