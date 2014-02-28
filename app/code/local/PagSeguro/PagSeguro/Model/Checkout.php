<?php

include_once ('PagSeguroLibrary/config/PagSeguroConfig.class.php');

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