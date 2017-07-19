<?php

/**
 * Class UOL_PagSeguro_Helper_Log
 */
class UOL_PagSeguro_Helper_Log
{
    /**
     * @param $orderId
     * @param $recoveryCode
     */
    public function setAbandonedSendEmailLog($orderId, $recoveryCode)
    {
        $module = ' [Info] PagSeguroAbandoned.';
        $phrase = "Mail( SendEmailAbandoned: array (\n 'orderId' => ".$orderId.",\n ";
        $phrase .= "'recoveryCode' => '".$recoveryCode."'\n) )";
        $this->setLog($phrase, $module);
    }

    /**
     * @param $phrase
     * @param $module
     */
    public function setLog($phrase, $module)
    {
        if (Mage::getStoreConfig('payment/pagseguro/log')) {
            if (Mage::getStoreConfig('payment/pagseguro/log_file')) {
                $directoryLog = Mage::getBaseDir().'/'.Mage::getStoreConfig('payment/pagseguro/log_file');
            } else {
                $directoryLog = Mage::getBaseDir('lib').'/PagSeguroLibrary/PagSeguro.log';
            }
            $date = '{'.Mage::app()->getLocale()->date().'}';
            $return = $date.$module.$phrase."\r\n";
            file_put_contents($directoryLog, $return, FILE_APPEND);
        }
    }

    /**
     * @param $orderId
     * @param $sent
     */
    public function setAbandonedSentEmailUpdateLog($orderId, $sent)
    {
        $module = ' [Info] PagSeguroAbandoned.';
        $phrase = "SentEmailUpdate( Has been updated to ".$sent." the number of emails sent,";
        $phrase .= " belonging to order ".$orderId." )";
        $this->setLog($phrase, $module);
    }

    public function setRequirementsLog()
    {
        $module = ' [Info] PagSeguroRequirements.';
        $phrase = "Verification ( Checked requirements )";
        $this->setLog($phrase, $module);
    }

    /**
     * @param $class
     * @param $days
     */
    public function setSearchTransactionLog($class, $days)
    {
        $initialDate = date('Y-m-d', strtotime($days.'- days'));
        $phrase = "Search( '".$days." days - Range of dates ";
        $phrase .= $initialDate." until ".date("d/m/Y H:i:s")."' )";
        $this->setLog($phrase, $this->setModule($class));
    }

    /**
     * @param $class
     *
     * @return null|string
     */
    private function setModule($class)
    {
        $module = null;
        $option = explode('_', $class);
        $module = ' [Info] PagSeguro'.end($option).'.';

        return $module;
    }

    /**
     * @param $class
     * @param $orderId
     * @param $transactionCode
     * @param $orderStatus
     */
    public function setUpdateOrderLog($class, $orderId, $transactionCode, $orderStatus)
    {
        $phrase = "Update( OrderStatusMagento: array (\n 'orderId' => ".$orderId.",\n ";
        $phrase .= "'transactionCode' => '".$transactionCode."',\n ";
        $phrase .= "'orderStatus' => '".$orderStatus."'\n ) )";
        $this->setLog($phrase, $this->setModule($class));
    }
}
