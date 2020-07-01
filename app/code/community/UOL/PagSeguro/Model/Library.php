<?php

/**
 * Class UOL_PagSeguro_Model_Library
 */
class UOL_PagSeguro_Model_Library
{
    /**
     * UOL_PagSeguro_Model_Library constructor.
     */
    public function __construct()
    {
        defined("SHIPPING_TYPE") or define("SHIPPING_TYPE", 3);
        defined("SHIPPING_COST") or define("SHIPPING_COST", 0.00);
        defined("CURRENCY") or define("CURRENCY", "BRL");
        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName('Magento')->setRelease(Mage::getVersion());
        \PagSeguro\Library::moduleVersion()->setName('PagSeguro')->setRelease(Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version);
        \PagSeguro\Configuration\Configure::setCharset(Mage::getStoreConfig('payment/pagseguro/charset'));
        $this->setCharset();
        $this->setEnvironment();
        $this->setLog();
    }

    /**
     *
     */
    private function setCharset()
    {
        \PagSeguro\Configuration\Configure::setCharset(Mage::getStoreConfig('payment/pagseguro/charset'));
    }

    /**
     *
     */
    private function setEnvironment()
    {
        \PagSeguro\Configuration\Configure::setEnvironment(Mage::getStoreConfig('payment/pagseguro/environment'));
    }

    /**
     *
     */
    private function setLog()
    {
        if (Mage::getStoreConfig('payment/pagseguro/log')) {
            \PagSeguro\Configuration\Configure::setLog(true,
                Mage::getBaseDir().Mage::getStoreConfig('payment/pagseguro/log_file'));
        } else {
            \PagSeguro\Configuration\Configure::setLog(false, null);
        }
    }

    /**
     * @return \PagSeguro\Domains\AccountCredentials
     */
    public function getAccountCredentials()
    {
        \PagSeguro\Configuration\Configure::setAccountCredentials(
            Mage::getStoreConfig('payment/pagseguro/email'),
            Mage::getStoreConfig('payment/pagseguro/token')
        );

        return \PagSeguro\Configuration\Configure::getAccountCredentials();
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return Mage::getStoreConfig('payment/pagseguro/charset');
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return Mage::getStoreConfig('payment/pagseguro/environment');
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return Mage::getStoreConfig('payment/pagseguro/log');
    }

    /**
     * @return mixed
     */
    public function getPaymentCheckoutType()
    {
        return Mage::getStoreConfig('payment/pagseguro_default_lightbox/checkout');
    }
}