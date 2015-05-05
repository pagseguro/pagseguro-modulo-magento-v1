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

		//Set Base URL/
		$baseurl = $this->skin . 'adminhtml/default/default/uol/pagseguro/';
		
		//Set headers
		$this->jquery = $baseurl . 'js/jquery-1.11.1.js';
		$this->js = $baseurl . 'js/pagseguro-module.js';
		$this->jsColorbox = $baseurl . 'js/jquery.colorbox-min.js';
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
						/*
						    ColorBox Core Style:
						    The following CSS is consistent between example themes and should not be altered.
						*/
						#colorbox, #cboxOverlay, #cboxWrapper{position:absolute; top:0; left:0; z-index:9999;}
						#cboxOverlay{position:fixed; width:100%; height:100%;}
						#cboxMiddleLeft, #cboxBottomLeft{clear:left;}
						#cboxContent{position:relative;}
						#cboxLoadedContent{overflow:auto;}
						#cboxTitle{margin:0;}
						#cboxLoadingOverlay, #cboxLoadingGraphic{position:absolute; top:0; left:0; width:100%; height:100%;}
						#cboxPrevious, #cboxNext, #cboxClose, #cboxSlideshow{cursor:pointer;}
						.cboxPhoto{float:left; margin:auto; border:0; display:block; max-width:none;}
						.cboxIframe{width:100%; height:100%; display:block; border:0;}
						#colorbox, #cboxContent, #cboxLoadedContent{box-sizing:content-box;}
						/* 
						    User Style:
						    Change the following styles to modify the appearance of ColorBox.  They are
						    ordered & tabbed in a way that represents the nesting of the generated HTML.
						*/
						.cboxIframe{
							background:#fff;
						}
						#cboxOverlay{
							background:#000;
						}
						#cboxContent{
							background:#fff;
							overflow:visible;
							border-radius:5px;
						}
						#cboxLoadedContent{
							margin:15px 10px 0;
							background:#fff;
						}
						#cboxLoadingOverlay{
							background:#fff;
						}
						#cboxClose {
							display:block;
							position:absolute;
							right : -10px;
							top : -10px;
							padding : 12px;
							cursor : pointer;
							border:0;
							border-radius : 50%;
							z-index : 10;
							background: center no-repeat #FFF url(data:image/gif;base64,R0lGODlhCgAKALMAANXV1dTU1O/v7/Dw8Ovr6+zs7Lu7u8DAwPX19QAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAKAAoAAAQqEKEhJZXgFFkOQMJhaN0oIIR4qESVGmNblePGqeo2sO9BBQdZKlC5WCQRADs=);
							box-shadow : -1px 1px 13px rgba(0, 0, 0, 0.4);
							overflow:hidden;
							width:1.5em;
							height:1.5em;
							text-indent:-9999px;
						}
						#cboxTitle{
							display:none!important;
						}
						#cboxLoadedContent .title{
							font-size:1.8em;
							margin:0 0 20px 0;
							padding:0.1em 0;
						}
						/* Messages */
						.pagseguro-msg h3 > a { color:#035EC7!important }
						.pagseguro-msg.pagseguro-msg-micro h3,
						.pagseguro-msg.pagseguro-msg-micro dt,
						.pagseguro-msg.pagseguro-msg-micro dd,
						.pagseguro-msg.pagseguro-msg-micro li,
						.pagseguro-msg.pagseguro-msg-micro p {
						    font-size:1em !important;
						}
						.pagseguro-msg.pagseguro-msg-small h3,
						.pagseguro-msg.pagseguro-msg-small dt,
						.pagseguro-msg.pagseguro-msg-small dd,
						.pagseguro-msg.pagseguro-msg-small li,
						.pagseguro-msg.pagseguro-msg-small p {
						    font-size:1.2em !important;
						}
						.pagseguro-msg.pagseguro-msg-medium h3,
						.pagseguro-msg.pagseguro-msg-medium dt {
						    font-size:1.4em !important;
						}
						.pagseguro-msg.pagseguro-msg-medium dd,
						.pagseguro-msg.pagseguro-msg-medium li,
						.pagseguro-msg.pagseguro-msg-medium p {
						    font-size:1.2em !important;
						}
						.pagseguro-msg {
						    display : table;
						    overflow : hidden;
						    zoom : 1;
						    font-size : 1em;
						    width : 100%;
						    font-family : Arial;
						    padding : 0.7em;
						    margin-bottom : 1em; /* IEs */
						    margin-bottom : 0.4rem;
						    -webkit-box-sizing : border-box;
						    -moz-box-sizing : border-box;
						    -ms-box-sizing : border-box;
						    -o-box-sizing : border-box;
						    box-sizing : border-box;
						    text-align: left;
						}
						.pagseguro-msg h3,
						.pagseguro-msg p {
						    display:table-cell;
						    vertical-align:middle;
						    width:100%;
						}
						.pagseguro-msg dl,
						.pagseguro-msg ul {
						    display:table-cell;
						    vertical-align:top;
						    width:100%;
						}
						.pagseguro-msg h3,
						.pagseguro-msg p,
						.pagseguro-msg li,
						.pagseguro-msg dt,
						.pagseguro-msg dd{
						    color: #4f4f4f !important;
						}
						.pagseguro-msg h3,
						.pagseguro-msg dt {font-weight: bold;}
						.pagseguro-msg dd {margin: 0; padding: 0;}
						.pagseguro-msg li {
						    padding:0;
						    list-style-type: none;
						}
						.pagseguro-msg dd,
						.pagseguro-msg li + li {margin-top: 0.4em;}

						i.icon-pagseguro-msg,
						.pagseguro-msg:before {
						    display : inline-block;
						    vertical-align : middle;
						    overflow : hidden;
						    background-image : url("../images/messages.png");
						    background-repeat : no-repeat;
						    background-size : cover;
						}

						i.icon-pagseguro-msg.icon-loading,
						.pagseguro-msg.pagseguro-msg-loading:before {
						    background-image : url("../images/loading.gif");
						    background-position: 0 0;
						}

						
						.pagseguro-msg.no-icon:before{
						    display:none !important;
						}

						i.icon-pagseguro-msg {}
						i.icon-pagseguro-msg.icon-alert,
						.pagseguro-msg.pagseguro-msg-alert:before   { background-position : 0 0em; }
						i.icon-pagseguro-msg.icon-wait,
						.pagseguro-msg.pagseguro-msg-wait:before    { background-position : 0 -1.28em; }
						i.icon-pagseguro-msg.icon-error,
						.pagseguro-msg.pagseguro-msg-error:before   { background-position : 0 -2.56em; }
						i.icon-pagseguro-msg.icon-block,
						.pagseguro-msg.pagseguro-msg-block:before   { background-position : 0 -3.84em; }
						i.icon-pagseguro-msg.icon-info,
						.pagseguro-msg.pagseguro-msg-info:before    { background-position : 0 -5.12em; }
						i.icon-pagseguro-msg.icon-success,
						.pagseguro-msg.pagseguro-msg-success:before { background-position : 0 -6.35em; }
						i.icon-pagseguro-msg.icon-loading,
						.pagseguro-msg.pagseguro-msg-loading:before {
						    -webkit-animation : pagseguro-fx-loading 1.5s linear infinite;
						    -moz-animation : pagseguro-fx-loading 1.5s linear infinite;
						    -ms-animation : pagseguro-fx-loading 1.5s linear infinite;
						    -o-animation : pagseguro-fx-loading 1.5s linear infinite;
						    animation : pagseguro-fx-loading 1.5s linear infinite;
						}

						i.icon-pagseguro-msg,
						.pagseguro-msg:before{
						    width : 1em;
						    height : 1em;
						}
						i.icon-pagseguro-msg.micro,
						.pagseguro-msg.pagseguro-msg-micro:before {
						    font-size: 2em;
						    
						}
						i.icon-pagseguro-msg.small,
						.pagseguro-msg.pagseguro-msg-small:before {
						    font-size: 3em;
						    
						}
						i.icon-pagseguro-msg.medium,
						.pagseguro-msg.pagseguro-msg-medium:before {
						    font-size: 5em;
						    
						}
						i.icon-pagseguro-msg.large,
						.pagseguro-msg.pagseguro-msg-large:before {
						    font-size: 7em;
						    
						}
						#pagseguro-module-contents .pagseguro-msg{
						    background:#F3F3F3;
						    border:solid 1px #E3E3E3;
						    border-radius:2px 2px 2px;
						}
						.system-tooltip-box {
							width: 37%!important;
							height: 150px!important;
							text-align: justify;
							border: none;
							padding-left: 3%;
							padding-top: 12%;
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

							jQuery("#payment_pagseguro").css("background", " #fff url('.$this->background.') no-repeat scroll center 19%");

							jQuery("#payment_pagseguro_environment").change(function(){
								
								if (jQuery(this).val() == "sandbox") {
								 Modal.message("success", "Suas transações serão feitas em um ambiente de testes. Nenhuma das transações realizadas nesse ambiente tem valor monetário.");
								}
							});

							jQuery("#payment_pagseguro .discount-value").attr("maxlength", "5");
							jQuery("#payment_pagseguro .discount-value").attr("onkeyup", "maskConfig(this, maskDiscount)");

							var discountMsg = "O desconto será aplicado com base no subtotal do checkout PagSeguro. ";
							discountMsg += "Eventuais valores de frete não serão levados em consideração para a aplicação do desconto." + "<br />";
							discountMsg += "É recomendável que você simule o funcionamento desta feature no ambiente do Sandbox.";

							jQuery("#payment_pagseguro .discount-confirm").change(function(){
								if (jQuery(this).val() == 1) {
									Modal.message("alert", discountMsg);
								}
							});

						});
					 </script>';
		$comment .= $interface;
        return $comment;
    }
}