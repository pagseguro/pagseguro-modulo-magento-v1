<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "PagSeguroLibrary" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "PagSeguroConfig.class.php";

class PagSeguro_PagSeguro_Model_Checkout
{

	public function toOptionArray()
	{
		return array(
				array("value" => "PADRAO" , "label" =>  utf8_encode("Padrão")),
				array("value" => "LIGHTBOX" , "label" => "Lightbox" )
		);
	}
}