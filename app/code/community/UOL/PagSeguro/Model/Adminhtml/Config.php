<?php
class UOL_PagSeguro_Model_Adminhtml_Config
{

	private $skin;
	private $jquery;
	private $css;
	private $logo;
	private $version;
	private $background;

	public function __construct()
	{
		//Set skin path
		$this->skin = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);

		//Set Base URL/
		$baseurl = $this->skin . 'adminhtml/default/default/uol/pagseguro/';
		
		//Set headers
		$this->jquery = $baseurl . 'js/jquery-1.11.1.js';
		$this->buildCSS();

		//Set images
    	$this->logo = $baseurl . 'images/logo.png';
    	$this->background = $baseurl . 'images/background.png';

    	//Set version
    	$this->version = Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version;
    	
	}

	private function buildCSS()
	{
		$this->css = '<style type="text/css">
						/* header */
						#pagseguro-module-header{
						    border-bottom: 5px solid #90e265;
						    margin-bottom: 10px;
						    overflow: hidden;
						    padding: 1.5em 0;
						    background: #FFF;
						}
						#pagseguro-logo{
						    float: left;
						}
						#pagseguro-registration-button{
						    float: right;
						    margin-top: 0.4em;
						}
						#pagseguro-module{
						    background: none repeat scroll 0 0 #F2F2F2;
						    box-shadow: 0 0 7px 1px rgba(0, 0, 0, 0.2);
						    margin-bottom:5em;
						    color: #666;
						    font-family: Arial!important;
						    font-size: 12px;
						    overflow: hidden;
						    position: relative;
						}
						#pagseguro-module .wrapper {
						    margin: 0 auto;
						    max-width: 96%;
						    position: relative;
						}
						#pagseguro-module-content{
						    padding:1em 0 2em 0;
						    position: relative;
						    background: url("../images/background.png") no-repeat scroll center 0 #FFF;
						    background-size: contain;
						    overflow: hidden;
						    clear: both;
						    min-height: 500px;
						}

						#pagseguro-module p{
						    margin: 1em 0;
						    line-height: 1.4em;
						}
						/* buttons */
						.pagseguro-button {
						    -webkit-box-sizing : border-box; 
						    -moz-box-sizing : border-box; 
						    -ms-box-sizing : border-box; 
						    -o-box-sizing : border-box; 
						    box-sizing : border-box;
						}
						.pagseguro-button {
						    font-family : "Arial";
						    font-size:1.4em!important;
						    cursor : pointer;
						    position : relative;
						    display : inline-block;
						    color : #FFF!important;
						    height : auto !important;
						    line-height : 1.1em !important;
						    border:none;
						    border-top: 0.1em solid rgba(0, 0, 0, 0.05);
						    border-left: 0.1em solid rgba(0, 0, 0, 0.1);
						    border-bottom: 0.1em solid rgba(0, 0, 0, 0.2);
						    border-right: 0.1em solid rgba(0, 0, 0, 0.1);
						    border-radius : 0.2em;
						    padding : 0.3em 0.55em !important;
						    background-image: -webkit-linear-gradient(transparent 20%, rgba(0, 0, 0, 0.1));
						    background-image: -moz-linear-gradient(transparent 20%, rgba(0, 0, 0, 0.1));
						    background-image: -o-linear-gradient(transparent 20%, rgba(0, 0, 0, 0.1));
						    background-image: linear-gradient(transparent 20%, rgba(0, 0, 0, 0.1));
						    -webkit-user-select : none;
						    -moz-user-select : none;
						    -ms-user-select : none;
						    -o-user-select : none;
						    user-select : none;
						    text-decoration:none !important;
						    appearance:none;
						    -moz-appearance:none;
						    -webkit-appearance:none;
						    font-weight: bold;
						    -webkit-transition:box-shadow 200ms;
						    -o-transition:box-shadow 200ms;
						    transition:box-shadow 200ms;
						}
						.pagseguro-button:active {
						    box-shadow : 0 0.1125em 0.4125em rgba(0, 0, 0, 0.3) inset !important;
						}
						.pagseguro-button:hover {
						    box-shadow : 0 2em 2em rgba(255, 255, 255, 0.125) inset;
						}
						.pagseguro-button.theme-gray,
						.pagseguro-button.gray-theme {
						    background-color : #f6f6f6;
						    color : #41b320!important;
						    text-shadow : none;
						}
						.pagseguro-button.theme-gray:hover,
						.pagseguro-button.gray-theme:hover {
						    background-color : #f9f9f9;
						}
					  </style>';
	}

    public function getCommentText()
    {
    	$redirect = Mage::getBaseUrl() . 'checkout/onepage/success/';

		$interface = '<div class="pagseguro-comment">
						   '.$this->css.'
						   <div id="pagseguro-module-header">
								<div class="wrapper">
									
									<div id="pagseguro-logo">
										<img class="pagseguro_logo" src="'.$this->logo.'" />
										<div id="pagseguro-module-version">Versão '.$this->version.'</div>
									</div>
								    
								    <a id="pagseguro-registration-button" class="pagseguro-button gray-theme" href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank">Faça seu cadastro</a>

								</div>
							</div>
						</div>
					 ';
    	$comment .= '<script src="' . $this->jquery . '"></script>';
		$comment .= '<script type="text/javascript">
						var jQuery = jQuery.noConflict();
						jQuery(document).ready(function(){
							var content = jQuery(".pagseguro-comment").html();
							jQuery("#payment_pagseguro").prepend(content);
							if (!jQuery("#payment_pagseguro_redirect").val()) {
								jQuery("#payment_pagseguro_redirect").attr("value","' . $redirect . '");
							}
							jQuery("#row_payment_pagseguro_comment").remove();

							jQuery("#payment_pagseguro").css("background", " #fff url('.$this->background.') no-repeat scroll center 45%");
						});
					 </script>';
		$comment .= $interface;
        return $comment;
    }
}