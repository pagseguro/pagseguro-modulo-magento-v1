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
class UOL_PagSeguro_Model_Adminhtml_Config
{
    private $background;
    private $css;
    private $jquery;
    private $js;
    private $jsColorbox;
    private $logo;
    private $skin;
    private $version;
    private $session;
    private $stc;

    public function __construct()
    {
        $this->library = new UOL_PagSeguro_Model_Library();
        //Set skin path
        $this->skin = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        //Set Skin URL/
        $skinUrl = $this->skin.'adminhtml/default/default/uol/pagseguro/';
        $configCss = $skinUrl.'css/pagseguro-module-config.css';
        //Set headers
        $this->css = '<link rel="stylesheet" type="text/css" href="'.$configCss.'" />';
        $this->jquery = $skinUrl.'js/jquery-1.11.1.js';
        $this->js = $skinUrl.'js/pagseguro-module.js';
        $this->jsColorbox = $skinUrl.'js/jquery.colorbox-min.js';
        //Set images
        $this->logo = $skinUrl.'images/logo.png';
        $this->background = $skinUrl.'images/background.png';
        //Set version
        $this->version = Mage::helper('pagseguro')->getVersion();
        if (Mage::getStoreConfig('payment/pagseguro/token') && Mage::getStoreConfig('payment/pagseguro/email')) {
            try {
                $this->session = \PagSeguro\Services\Session::create($this->library->getAccountCredentials())->getResult();
            } catch (Exception $exception){
                $this->session = null;
                // TODO make a default format of exception
                Mage::log('[PAGSEGURO] Error: ' . $exception->getCode() . ' - ' . $exception->getMessage());
            }
        }
        if (Mage::getStoreConfig('payment/pagseguro/environment') === 'production') {
            $this->stc = 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
        } else {
            $this->stc = 'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js?';
        }
    }

    /**
     * Generates the layout of content of settings screen
     *
     * @return string $comment - Contains the comment field in layout format
     */
    public function getCommentText()
    {
        $helper = Mage::helper('pagseguro');
        $redirect = Mage::getBaseUrl().'checkout/onepage/success/';
        $pgUrl = 'https://pagseguro.uol.com.br/registration/registration.jhtml?ep=7&tipo=cadastro#!vendedor';
        $id = 'pagseguro-registration-button';
        $class = 'pagseguro-button gray-theme';
        $version = $helper->__('Versão %s', $this->version);
        $backgroundCss = '#fff url('.$this->background.') no-repeat scroll center 19%';
        $html = Mage::helper('pagseguro/html');
        $alertEnvironment = $helper->__('Suas transações serão feitas em um ambiente de testes.').'<br />';
        $alertEnvironment .= $helper->__('Nenhuma das transações realizadas nesse ambiente tem valor monetário.');
        $alertCredentials = $helper->__('E-mail e/ou token inválido(s) para o ambiente selecionado.');
        $alertEmailToken = $helper->__('Certifique-se de que o e-mail e token foram preenchidos.');
        $alertDiscount = $helper->__('O desconto será aplicado com base no subtotal do checkout PagSeguro.');
        $alertD = 'Eventuais valores de frete não serão levados em consideração para a aplicação do desconto.';
        $alertDiscount .= $helper->__($alertD).'<br />';
        $alertD = 'É recomendável que você simule o funcionamento desta feature no ambiente do Sandbox.';
        $alertDiscount .= $helper->__($alertD);
        $interface = '<div class="pagseguro-comment">
                        '.$this->css.'
                        '.$html->getHeader($this->logo).'
                     </div>';
        $init = Mage::getStoreConfig('payment/pagseguro/init');
        $email = Mage::getStoreConfig('payment/pagseguro/email');
        $token = Mage::getStoreConfig('payment/pagseguro/token');
        //TODO javascript na model!?
        $comment = '<script src="'.$this->jquery.'"></script>';
        $comment .= '<script src="'.$this->js.'"></script>';
        $comment .= '<script src="'.$this->stc.'"></script>';
        if ($this->session) {
            $comment .= '<script>PagSeguroDirectPayment.setSessionId("' . $this->session . '")</script>';
        }
        $comment .= '<script src="'.$this->jsColorbox.'"></script>';
        $comment .= '<script type="text/javascript">
                        var jQuery = jQuery.noConflict();
                        jQuery(document).ready(function(){
                            var content = jQuery(".pagseguro-comment").html();
                            jQuery("#payment_pagseguro").prepend(content);

                            if (!jQuery("#payment_pagseguro_redirect").val()) {
                                jQuery("#payment_pagseguro_redirect").attr("value","'.$redirect.'");
                            }

                            jQuery("#row_payment_pagseguro_comment").remove();
                            jQuery("#payment_pagseguro").css("background", "'.$backgroundCss.'");

                            jQuery("#payment_pagseguro_environment").change(function(){
                                if (jQuery("#payment_pagseguro_environment").val() == "sandbox") {
                                 Modal.message("warning", "'.$alertEnvironment.'");
                                }
                            });
                            var init  = "'.$init.'";
                            var email  = "'.$email.'";
                            var token  = "'.$token.'";

                            if (init) {
                                if (!email) {
                                    Modal.message("error", "'.$alertEmailToken.'");
                                } else if (!token) {
                                    Modal.message("error", "'.$alertEmailToken.'");
                                }
                            }
                            var discountId = "#payment_pagseguro .discount-value";
                            jQuery(discountId).attr("maxlength", "5");
                            jQuery(discountId).attr("onkeyup", "maskConfig(this, maskDiscount)");
                            jQuery("#payment_pagseguro .discount-confirm").change(function(){
                                if (jQuery(this).val() == 1) {
                                    Modal.message("alert", "'.$alertDiscount.'");
                                }
                            });
                        });
                     </script>';
        $comment .= $interface;

        return $comment;
    }
}
