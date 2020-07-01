<?php

class UOL_PagSeguro_Model_Checkout
{
    public function toOptionArray()
    {
        $helper = Mage::helper('pagseguro');

        return array(
            array('value' => 'PADRAO', 'label' => $helper->__('Padrão')),
            array('value' => 'LIGHTBOX', 'label' => $helper->__('Lightbox'))
        );
    }
}
