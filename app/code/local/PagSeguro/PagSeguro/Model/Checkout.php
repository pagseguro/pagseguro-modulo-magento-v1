<?php

require_once MAGENTO_ROOT . '/app/code/local/PagSeguro/PagSeguro/Model/PagSeguroLibrary/config/PagSeguroConfig.class.php';

class PagSeguro_PagSeguro_Model_Checkout
{

    public function toOptionArray()
    {
        return array(
            array("value" => "PADRAO", "label" => "Padrão"),
            array("value" => "LIGHTBOX", "label" => "Lightbox")
        );
    }
}