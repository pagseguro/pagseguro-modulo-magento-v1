<?php
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
		
		//Set headers
		$this->css = '<link rel="stylesheet" type="text/css" href="' . $skinUrl . 'css/pagseguro-module-config.css" />';
		$this->jquery = $skinUrl . 'js/jquery-1.11.1.js';
		$this->js = $skinUrl . 'js/pagseguro-module.js';
		$this->jsColorbox = $skinUrl . 'js/jquery.colorbox-min.js';

		//Set images
    	$this->logo = $skinUrl . 'images/logo.png';
    	$this->background = $skinUrl . 'images/background.png';

    	//Set version
    	$this->version = Mage::helper('pagseguro')->getVersion();
    	
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
								    
								    <a id="pagseguro-registration-button" class="pagseguro-button gray-theme" 
								    	href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=7&tipo=cadastro#!vendedor" target="_blank">Faça seu cadastro</a>

								</div>
							</div>
						</div>
					 ';					 
    	$comment .= '<script src="' . $this->jquery . '"></script>';
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
							jQuery("#payment_pagseguro").css("background", " #fff url('.$this->background.') no-repeat scroll center 45%");

							jQuery("#payment_pagseguro_environment").change(function(){								
								if (jQuery("#payment_pagseguro_environment").val() == "sandbox") {
								 Modal.message("success", "Suas transações serão feitas em um ambiente de testes. Nenhuma das transações realizadas nesse ambiente tem valor monetário.");
								}
							});						});
					 </script>';
		$comment .= $interface;
        return $comment;
    }
}