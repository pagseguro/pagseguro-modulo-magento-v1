<?php

require_once (Mage::getSingleton('PagSeguro_PagSeguro_Helper_Data')->getPageSeguroUrl() . '/PagSeguroLibrary/config/PagSeguroConfig.class.php');

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