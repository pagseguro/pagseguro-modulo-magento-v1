<?php

/**
 * @property Mage_Sales_Model_Order order
 */
class UOL_PagSeguro_Model_PagSeguroDeprecated extends Mage_Payment_Model_Method_Abstract
{

    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;
    protected $_canUseInternal = false;
    protected $_canVoid = true;
    protected $_code = 'pagseguro_deprecated';
    protected $_isGateway = true;
}
