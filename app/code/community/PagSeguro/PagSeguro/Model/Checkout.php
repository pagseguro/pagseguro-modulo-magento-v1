<?php

class PagSeguro_PagSeguro_Model_Checkout
{

    public function toOptionArray()
    {
        return array(
                array("value" => "PADRAO" , "label" =>  "Padrão"),
                array("value" => "LIGHTBOX" , "label" => "Lightbox" )
        );
    }
}
