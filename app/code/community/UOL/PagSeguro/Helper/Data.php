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

use Mage_Payment_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Data extends HelperData
{
    // It is used to store the array of transactions
    protected $arrayPayments = array();

    // It is used to store the initial consultation date of transactions
    private $initialDate;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->changeEnvironment();
        $this->environmentNotification();
    }

    /**
     * Get the current version of module
     * @return string - Returns the current version of the module
     */
    public function getVersion()
    {
        $version = Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version;
        return $version;
    }

    /**
     * Get status of PagSeguro or string required to change the order status Magento
     * @param int $status - Number that contains the status of PagSeguro
     * @param boolean $orderMagento - If the return will be to change order status Magento
     * @return string $status - String that will be displayed in the table or used to change the order status Magento
     */
    public function getPaymentStatusPagSeguro($status, $orderMagento)
    {
        if ($orderMagento == true) {
            switch ($status) {
                case 1:
                    $status = 'aguardando_pagamento_ps';
                    break;
                case 2:
                    $status = 'em_analise_ps';
                    break;
                case 3:
                    $status = 'paga_ps';
                    break;
                case 4:
                    $status = 'disponivel_ps';
                    break;
                case 5:
                    $status = 'em_disputa_ps';
                    break;
                case 6:
                    $status = 'devolvida_ps';
                    break;
                case 7:
                    $status = 'cancelada_ps';
                    break;
                case 8:
                    $status = 'chargeback_debitado_ps';
                    break;
                case 9:
                    $status = 'em_contestacao_ps';
                    break;
            }
        } else {
            switch ($status) {
                case 1:
                    $status = $this->__('Aguardando pagamento');
                    break;
                case 2:
                    $status = $this->__('Em an&aacute;lise');
                    break;
                case 3:
                    $status = $this->__('Paga');
                    break;
                case 4:
                    $status = $this->__('Dispon&iacute;vel');
                    break;
                case 5:
                    $status = $this->__('Em disputa');
                    break;
                case 6:
                    $status = $this->__('Devolvida');
                    break;
                case 7:
                    $status = $this->__('Cancelada');
                    break;
                case 8:
                    $status = $this->__('Chargeback Debitado');
                    break;
                case 9:
                    $status = $this->__('Em Contestação');
                    break;
            }
        }

        return $status;
    }

    /**
     * Get status of order of magento
     * @param string $status - Strin that contains the status of PagSeguro in order Magento
     * @return string $status - Returns the correct status queried the current status
     */
    public function getPaymentStatusMagento($status)
    {
        switch ($status) {
            case 'Aguardando_pagamento_ps':
                $status = 'Aguardando pagamento';
                break;
            case 'Em_analise_ps':
                $status = 'Em an&aacute;lise';
                break;
            case 'Paga_ps':
                $status = 'Paga';
                break;
            case 'Disponivel_ps':
                $status = 'Dispon&iacute;vel';
                break;
            case 'Em_disputa_ps':
                $status = 'Em disputa';
                break;
            case 'Devolvida_ps':
                $status = 'Devolvida';
                break;
            case 'Cancelada_ps':
                $status = 'Cancelada';
                break;
            case 'Chargeback_debitado_ps':
                $status = 'Chargeback debitado';
                break;
            case 'Em_contestacao_ps':
                $status = 'Em contestação';
                break;
        }

        return $status;
    }

    /**
     * Return reference of 5 digits
     * @param number $size - String length
     * @param boolean $uppercase - Active uppercase words in string
     * @param boolen $number - Active number in string
     * @return string  $string - String encrypted of 5 characters
     */
    public function createReference($size, $uppercase, $number)
    {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $string = '';
        $character = '';
        $character .= $lmin;

        if ($uppercase) {
            $character .= $lmai;
        }

        if ($number) {
            $character .= $num;
        }

        $len = strlen($character);

        for ($n = 1; $n <= $size; $n++) {
            $rand = mt_rand(1, $len);
            $string .= $character[$rand-1];
        }

        return $string;
    }

    /**
     * Returns the registered references in the database
     * @return string $reference - String encrypted of 5 characters of database
     */
    public function getStoreReference()
    {
        $reference = Mage::getStoreConfig('uol_pagseguro/store/reference');

        return $reference;
    }

    /**
     * Get reference decrypt of transactions PagSeguro
     * @param string $reference - String complete reference
     * @return string $refDecrypted - String of 5 characteres
     */
    public function getReferenceDecrypt($reference)
    {
        $refDecrypted = substr($reference, 0, 5);

        return $refDecrypted;
    }

    /**
     * Get id of order, of returned of reference of the transaction PagSeguro
     * @param string $reference - String complete reference
     * @return int $orderIdDecrypted - Id of order
     */
    public function getReferenceDecryptOrderID($reference)
    {
        $orderIdDecrypted = str_replace(substr($reference, 0, 5), '', $reference);

        return $orderIdDecrypted;
    }

    /**
     * Get date start
     * @return date $initialDate - Example Y-m-dT00:00
     */
    public function getInitialDate()
    {
        $this->initialDate = $_SESSION['initialDate'];

        if ($this->initialDate != '') {
            $initialDate = $this->initialDate . 'T00:00';
        } else {
            $initialDate = date('Y-m-d') . 'T00:00';
        }

        return $initialDate;
    }

    /**
     * Get date finally for query
     * @return date $date - Returns the end date (Y-m-dTH:i)
     */
    public function getFinalDate()
    {
        // set date and time by time zone selected by merchant
        date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone'));
        $date = date('Y-m-d') . 'T' . date('H:i');

        return $date;
    }

    /**
     * Verifies that the correct date, starting a certain number of days
     * @param int $days - Number of days to be checked the date
     * @return date $correctDate - Returns the correct date
     */
    public function getDateSubtracted($days)
    {
        $days = ($days > 30) ? 30 : $days;
        $thisyear = date('Y');
        $thismonth = date('m');
        $thisday = date('d');
        $nextdate = mktime(0, 0, 0, $thismonth, $thisday - $days, $thisyear);
        $correctDate = strftime("%Y-%m-%d", $nextdate);

        return $correctDate;
    }

    /**
     * Get url request editing Magento
     * @param int $idOrder - Id of order of Magento
     * @return string $url - url full of the application for editing
     */
    public function getEditOrderUrl($idOrder)
    {
        $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
        $url = $adminhtmlUrl->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));

        return $url;
    }

    /**
     * Change the environment if necessary.
     */
    private function changeEnvironment()
    {
        // Get the check of environment of backend.
        $environment = '"' . Mage::getStoreConfig('payment/pagseguro/environment') . '"';

        // File to be changed
        $archive = Mage::getBaseDir('lib') . '/PagSeguroLibrary/config/PagSeguroConfig.php';

        // Search the current environment of library.
        $search = "PagSeguroConfig['environment']";

        // Save the file in an array in variable $arrayArchive.
        $arrayArchive = file($archive);
        $position = 0;

        for ($i = 0; $i < sizeof($arrayArchive); $i++) {
            // Checks the position of environmental on array, and stores the environment on variable $libEnvironment.
            if (strpos($arrayArchive[$i], $search) &&
               (strpos($arrayArchive[$i], 'production') || strpos($arrayArchive[$i], 'sandbox'))) {
                $fullLine = $arrayArchive[$i];
                $position = $i;

                if (strpos($fullLine, '"production"') == true) {
                    $libEnvironment = '"production"';
                } elseif (strpos($fullLine, '"sandbox"') == true) {
                    $libEnvironment = '"sandbox"';
                }
            }
        }

        // Make changes the environment, if  the environments are different.
        if ($environment != '""' && $environment != $libEnvironment) {
            $arrayArchive[$position] = str_replace($libEnvironment, $environment, $fullLine);
            file_put_contents($archive, implode("", $arrayArchive));
        }
    }

    /**
     * Create or destroy a notice based on a active envinroment
     */
    private function environmentNotification()
    {
        $environment = Mage::getStoreConfig('payment/pagseguro/environment');

        //Define table name with their prefix
        $tp    = (string) Mage::getConfig()->getTablePrefix();
        $table = $tp . 'adminnotification_inbox';

        $sql = "SELECT notification_id  FROM `" . $table . "` WHERE title LIKE '%[UOL_PagSeguro]%'";

        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $readConnection->fetchOne($sql);

        //Verify the environment
        if ($environment == "sandbox") {
            if (empty($results)) {
                $this->insertEnvironmentNotice($table);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());

            } elseif ($results != $this->getEnvironmentIncrement($table)) {
                $this->removeEnvironmentNotice($table, $results);
                $this->insertEnvironmentNotice($table);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
            }
        } elseif ($environment == 'production') {
            if ($results) {
                $this->removeEnvironmentNotice($table, $results);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
            }
        }
    }

    /**
     * Remove environment notice from adminnotification_inbox
     * @param string $table - Database table name.
     * @return int $id - Returns the nofitication_id value.
     */
    private function getEnvironmentIncrement($table)
    {
        $sql = "SELECT MAX(notification_id) as 'max_id' FROM `" . $table . "`";

        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $readConnection->fetchAll($sql);

        return $results[0]['max_id'];
    }

    /**
     * Insert environment notice into adminnotification_inbox
     * @param string $table - Database table name.
     */
    private function insertEnvironmentNotice($table)
    {
        // force default time zone
        Mage::app()->getLocale()->date();
        $date = date("Y-m-d H:i:s");
        $title = $this->__("[UOL_PagSeguro] Suas transações serão feitas em um ambiente de testes.");
        $description =  $this->__("Nenhuma das transações realizadas nesse ambiente tem valor monetário.");

        $sql = "INSERT INTO `" . $table . "` (severity, date_added, title, description, is_read, is_remove)
                VALUES (4, '$date', '$title', '$description', 0, 0)";

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);
        unset($connection);
    }

    /**
     * Remove environment notice from adminnotification_inbox
     * @param string $table - Database table name.
     * @param string $id - nofitication_id record.
     */
    private function removeEnvironmentNotice($table, $id)
    {
        $sql = "DELETE FROM `" . $table . "` WHERE notification_id = " . $id;

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);

        unset($connection);
    }

    /*
     * Checks if email was filled and token
     * Checks if email and token are valid
     * If not completed one or both, is directed and notified so it can be filled
     */
    public function checkTransactionAccess()
    {
        // Displays this error in title
        $module = 'PagSeguro - ';

        // Receive url editing methods ja payment with key
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');
        $email = $this->requestPaymentMethod()->getConfigData('email');
        $token = $this->requestPaymentMethod()->getConfigData('token');

        if ($email) {
            if (!$token) {
                $message =  $module . $this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
                Mage::app()->getResponse()->setRedirect($configUrl);
            }
        } else {
            $message = $module . $this->__('Preencha o e-mail do vendedor.');
            Mage::getSingleton('core/session')->addError($message);

            if (!$token) {
                $message = $module . $this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
            }
            Mage::app()->getResponse()->setRedirect($configUrl);
        }
    }

    /**
     * Set the start date to be found on webservice, starting from the days entered
     * @param int $days - Days preceding the date should be initiated
     */
    public function setInitialDate($days)
    {
        $_SESSION['initialDate'] = $this->getDateSubtracted($days);
    }

    /**
     * Get the date of the request from Magento and convert to the format (d/m/Y)
     * @param date $date - Initial date of order, in default format of Magento
     * @return date $dateConverted - Returns the date converted
     */
    protected function getOrderMagetoDateConvert($date)
    {
        $dateConverted = date('d/m/Y', strtotime($date));

        return $dateConverted;
    }

    /**
     * Get the latest status of your order before your upgrade request
     * @param int $orderId - Id of order of Magento
     * @return string $obj->getStatus() - Returns the status of order of Magento
     */
    protected function getLastStatusOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getStatus();
    }

    /**
     * Alert the shopkeeper before an action saying for conciliation first
     * @param string $action - Is the title of the action
     * @return string $msg - Returns the phrase of alert
     */
    protected function alertConciliation($action)
    {
        $msg  = $this->__('Não foi possível ' . $action . ' a transação.') . '<br />';
        $msg .= $this->__('Utilize a conciliação de transações.');

        return $msg;
    }

    /**
     * Get list of payment PagSeguro
     * @return array $transactionArray - Array with transactions
     */
    protected function getPagSeguroPaymentList()
    {
        try {
            $transactions = $this->requestWebservice()->getTransactionService('searchByDate', 1, 1000);
            $pages = $transactions->getTotalPages();

            if ($pages > 1) {
                for ($i = 1; $i < ($pages + 1); $i++) {
                    $transactions = $this->requestWebservice()->getTransactionService('searchByDate', $i, 1000);
                    $transactionArray .= array_push($transactions->getTransactions());
                }
            } else {
                $transactionArray = $transactions->getTransactions();
            }

            return $transactionArray;
        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Filters by payments PagSeguro containing the same request Store
     * @var int $orderId - Id of order
     * @var string $info['code'] - Transaction code of PagSeguro
     * @var string $info['status'] - Status of payment of PagSeguro
     * @method array $this->createArrayPayments - Stores the array that contains only the payments
     * that were made in the store at PagSeguro
     */
    protected function getMagentoPayments()
    {
        $reference = $this->getStoreReference();
        $paymentList = $this->getPagSeguroPaymentList();
        $this->arrayPayments = '';

        if ($paymentList) {
            foreach ($paymentList as $info) {
                if ($reference == $this->getReferenceDecrypt($info->getReference())) {
                    $orderId = $this->getReferenceDecryptOrderID($info->getReference());
                    $order = Mage::getModel('sales/order')->load($orderId);

                    if ($_SESSION['store_id'] != '') {
                        if ($order->getStoreId() == $_SESSION['store_id']) {
                            $this->createArrayPayments($orderId, $info->getCode(), $info->getStatus()->getValue());
                        }
                    } elseif ($order) {
                        $this->createArrayPayments($orderId, $info->getCode(), $info->getStatus()->getValue());
                    }

                    $_SESSION['store_id'] == '';
                }
            }
        }
    }

    /**
    * Get the transactions to be shown in the table
    * @param  array $array - Contains transaction set
    * @return json $dataSet - Contains json that the table interprets by updates ajax
    */
    public function getTransactionGrid($array)
    {
        $dataSet = '[';
        $j = 1;

        foreach ($array as $info) {
            $i = 1;
            $dataSet .= ($j > 1) ? ',[' : '[';

            foreach ($info as $item) {
                $dataSet .= (count($info) != $i) ? '"' . $item . '",' : '"' . $item . '"';
                $i++;
            }

            $dataSet .= ']';
            $j++;
        }

        $dataSet .= ']';

        return $dataSet;
    }

    /**
    * Makes a query in webservice to detect if the credentials are correct.
    * If are all right, is assigned value 1 else assigned value 2 to field credentials of table core_config_data
    */
    public function checkCredentials()
    {
        try {
            $transactions = $this->requestWebservice()->getTransactionService('searchByDate', 1, 1, $initialDate);
            Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 1);
        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 0);
            }
        }
    }

    /**
    * Cuts the value to 4 characters and converts to float
    * @param object $PaymentRequest - Object responsible for passing the parameters to the webservice.
    * @return object $PaymentRequest - Returns to the discount parameters object
    */
    public function getDiscount($paymentRequest)
    {
        $storeId = Mage::app()->getStore()->getStoreId();

        if (Mage::getStoreConfig('payment/pagseguro/discount_credit_card', $storeId) == 1) {
            $creditCard = (double) Mage::getStoreConfig('payment/pagseguro/discount_credit_card_value', $storeId);

            if ($creditCard && $creditCard != 0.00) {
                $paymentRequest->addPaymentMethodConfig('CREDIT_CARD', $creditCard, 'DISCOUNT_PERCENT');
            }
        }

        if (Mage::getStoreConfig('payment/pagseguro/discount_electronic_debit', $storeId) == 1) {
            $eft = (double) Mage::getStoreConfig('payment/pagseguro/discount_electronic_debit_value', $storeId);

            if ($eft && $eft != 0.00) {
                $paymentRequest->addPaymentMethodConfig('EFT', $eft, 'DISCOUNT_PERCENT');
            }
        }

        if (Mage::getStoreConfig('payment/pagseguro/discount_boleto', $storeId) == 1) {
            $boleto = (double) Mage::getStoreConfig('payment/pagseguro/discount_boleto_value', $storeId);

            if ($boleto && $boleto != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BOLETO', $boleto, 'DISCOUNT_PERCENT');
            }
        }

        if (Mage::getStoreConfig('payment/pagseguro/discount_deposit_account', $storeId)) {
            $deposit = (double) Mage::getStoreConfig('payment/pagseguro/discount_deposit_account_value', $storeId);

            if ($deposit && $deposit != 0.00) {
                $paymentRequest->addPaymentMethodConfig('DEPOSIT', $deposit, 'DISCOUNT_PERCENT');
            }
        }

        if (Mage::getStoreConfig('payment/pagseguro/discount_balance', $storeId)) {
            $balance = (double) Mage::getStoreConfig('payment/pagseguro/discount_balance_value', $storeId);

            if ($balance && $balance != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BALANCE', $balance, 'DISCOUNT_PERCENT');
            }
        }

        return $paymentRequest;
    }
    
    /**
    * Update status in a Magento Order.
    * @param string $class - Represents a PagSeguro Service type
    * @param int $orderId - Id of magento order
    * @param mixed $transactionCode - Code of transaction PagSeguro
    * @param int $orderStatus - Status of magento order
    */
    public function updateOrderStatusMagento($class, $orderId, $transactionCode, $orderStatus)
    {
        if ($this->getLastStatusOrder($orderId) != $orderStatus) {
            if ($class == 'UOL_PagSeguro_Helper_Canceled') {
                if ($this->requestWebservice()->requestPagSeguroService($class, $transactionCode) == 'OK') {
                    $orderStatus = 'cancelada_ps';
                }
            } elseif ($class == 'UOL_PagSeguro_Helper_Refund') {
                if ($this->requestWebservice()->requestPagSeguroService($class, $transactionCode) == 'OK') {
                    $orderStatus = 'devolvida_ps';
                }
            }

            $this->notifyCustomer($orderId, $orderStatus);
            Mage::helper('pagseguro/log')->setUpdateOrderLog($class, $orderId, $transactionCode, $orderStatus);
        }

        $this->setTransactionRecord($orderId, $transactionCode);
    }
    
    /**
    * Set a transaction record
    * @param int $orderId - Id of Magento order
    * @param int $orderStatus - Status of Magento order
    */
    private function notifyCustomer($orderId, $orderStatus)
    {
        $status = $orderStatus;
        $comment = null;
        $notify = true;
        $order = Mage::getModel('sales/order')->load($orderId);
        $order->addStatusToHistory($status, $comment, $notify);
        $order->sendOrderUpdateEmail($notify, $comment);

        // Makes the notification of the order of historic displays the correct date and time
        Mage::app()->getLocale()->date();
        $order->save();
    }
    
    /**
    * Set a transaction record
    * @param int $orderId - Id of magento order
    * @param mixed $transactionCode - Code of transaction PagSeguro
    * @param bool $send
    */
    public function setTransactionRecord($orderId, $transactionCode = false, $send = false)
    {
        //Get the resource model
        $resource = Mage::getSingleton('core/resource');

        //Retrieve the read connection
        $readConnection = $resource->getConnection('core_read');

        //Retrieve the write connection
        $writeConnection = $resource->getConnection('core_write');

        $tp    = (string) Mage::getConfig()->getTablePrefix();
        $table = $tp . 'pagseguro_orders';

        //Select sent column from pagseguro_orders to verify if exists a register
        $query = 'SELECT order_id, sent FROM ' . $resource->getTableName($table) . ' WHERE order_id = ' . $orderId;
        $result = $readConnection->fetchAll($query);

        if (!empty($result)) {
            if ($send == true) {
                $sent  = $result[0]['sent'] + 1;
                $value = "sent = '" . $sent . "'";
            } else {
                $value = "transaction_code = '" . $transactionCode . "'";
            }

            $sql = "UPDATE `" . $table . "` SET " . $value . " WHERE order_id = " . $orderId;
        } else {
            $environment = ucfirst(Mage::getStoreConfig('payment/pagseguro/environment'));
            if ($send == true) {
                $column = "(order_id, sent, environment)";
                $values = "('$orderId', 1, '$environment')";
            } else {
                $column = "(order_id, transaction_code, environment)";
                $values = "('$orderId', '$transactionCode', '$environment')";
            }

            $sql = "INSERT INTO `" . $table . "` " . $column . " VALUES " . $values;
        }

        $writeConnection->query($sql);
    }
    
    /**
    * Request webservice
    * @return $webservice of UOL_PagSeguro_Helper_Webservice
    */
    public function requestWebservice()
    {
        $webservice = Mage::helper('pagseguro/webservice');

        return $webservice;
    }
    
    /**
    * Request Payment Method
    * @return $paymentMethod of UOL_PagSeguro_Model_PaymentMethod
    */
    public function requestPaymentMethod()
    {
        $paymentMethod = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        return $paymentMethod;
    }
    
    /**
    * Request Notification Method
    * @return $notificationMethod of UOL_PagSeguro_Model_NotificationMethod
    */
    public function requestNotificationMethod()
    {
        $notificationMethod = Mage::getSingleton('UOL_PagSeguro_Model_NotificationMethod');

        return $notificationMethod;
    }
}
