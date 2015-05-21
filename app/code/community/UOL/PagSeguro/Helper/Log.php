<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Log extends HelperData
{
    /**
     * Creating log for helpers
     * @param string $phrase - It's the phrase that completes the log
     * @param string $module - It's the title that completes the log
     */
    public function setLog($phrase, $module)
    {
        $paymentMethod = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        // value 0/1
        $log = $paymentMethod->getConfigData('log');

        if ($log == 1) {
            if ($paymentMethod->getConfigData('log_file') != '') {
                $directoryLog = Mage::getBaseDir() . '/' . $paymentMethod->getConfigData('log_file');
            } else {
                $directoryLog = Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguro.log';
            }

            $date = '{' . Mage::app()->getLocale()->date() . '}';
            $return = $date . $module . $phrase . "\r\n";
            file_put_contents($directoryLog, $return, FILE_APPEND);
        }
    }
    
    /**
     * Creating log for search transations
     * @param string $class - Represents a transaction service type
     * @param int $days - Range of days to search
     */
    public function setSearchTransactionLog($class, $days)
    {
        $option = end(explode('_', $class));
        $module = ' [Info] PagSeguro' . $option . '.';

        // Sentence of log
        $phrase  = "Searched( '" . $days . " days - Range of dates ";
        $phrase .= $this->getInitialDate() . " until " . $this->getFinalDate() . "' )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Creating log for search transations
     * @param string $class - Represents a transaction service type
     * @param int $orderId - Id of magento order
     * @param mixed $transactionCode - Code of transaction PagSeguro
     * @param int $orderStatus - Status of magento order
     */
    public function setUpdateOrderLog($class, $orderId, $transactionCode, $orderStatus)
    {
        $option = end(explode('_', $class));
        $module = ' [Info] PagSeguro' . $option . '.';

        // Sentence of log
        $phrase  = "Update( OrderStatusMagento: array (\n 'orderId' => " . $orderId . ",\n ";
        $phrase .= "'transactionCode' => '" . $transactionCode . "',\n ";
        $phrase .= "'orderStatus' => '" . $orderStatus . "'\n ) )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Set the log records when sent
     * @param int $orderId - Id of order Magento
     * @param string $recoveryCode - Recovery code of transaction PagSeguro
     * @method setLog - Set log in file
     */
    public function setAbandonedSendEmailLog($orderId, $recoveryCode)
    {
        // Title of Log
        $module = ' [Info] PagSeguroAbandoned.';

        // Sentence of log
        $phrase = "Mail( SendEmailAbandoned: array (\n 'orderId' => " . $orderId . ",\n ";
        $phrase .= "'recoveryCode' => '" . $recoveryCode . "'\n) )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Set the log records when update a sent e-mail
     * @param int $orderId - Id of order Magento
     * @param int $sent - Quantity of e-mails sent
     * @method setLog - Set log in file
     */
    public function setAbandonedSentEmailUpdateLog($orderId, $sent)
    {
        // Title of Log
        $module = ' [Info] PagSeguroAbandoned.';

        // Sentence of log
        $phrase  = "SentEmailUpdate( Has been updated to " . $sent . " the number of emails sent,";
        $phrase .= " belonging to order " . $order_id . " )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Set the log when searched records
     * @method setLog - Set log in file
     */
    public function setRequirementsLog()
    {
        // Set title
        $module = ' [Info] PagSeguroRequirements.';

        // Sentence of log
        $phrase = "Verification ( Checked requirements )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }
}
