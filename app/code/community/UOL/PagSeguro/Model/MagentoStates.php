<?php

class UOL_PagSeguro_Model_MagentoStates
{
    public function toOptionArray()
    {
        $states = Mage::getSingleton('sales/order_config')->getStates();

        $options = array();

        foreach ($states as $code=>$label) {
            $options[] = array( 'value' => $code, 'label' => $label );
        }

        return $options;
    }
}