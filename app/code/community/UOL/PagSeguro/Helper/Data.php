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

class UOL_PagSeguro_Helper_Data extends Mage_Payment_Helper_Data
{

    /**
     * @var Array
     */
    protected $arrayPayments = array();

    /**
     * @var Array
     */
    private $arrayPaymentStatusList = array(
        0 => "pending",
        1 => "aguardando_pagamento_ps",
        2 => "em_analise_ps",
        3 => "paga_ps",
        4 => "disponivel_ps",
        5 => "em_disputa_ps",
        6 => "devolvida_ps",
        7 => "cancelada_ps",
        8 => "chargeback_debitado_ps",
        9 => "em_contestacao_ps"
    );
    
    const REFUND_CLASS = "UOL_PagSeguro_Helper_Refund";
    const CANCELED_CLASS = "UOL_PagSeguro_Helper_Canceled";
    const TABLE_NAME = "pagseguro_orders";

    public function __construct()
    {
        $this->changeEnvironment();
        $this->environmentNotification();
    }

    /**
     * Get module version
     */
    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version;
    }

    /**
     * Creates a new store reference
     * @param bool $size
     * @param bool $uppercase
     * @param bool $number
     * @return string
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
     * Get store reference
     * @return mixed
     */
    public function getStoreReference()
    {
        return  Mage::getStoreConfig('uol_pagseguro/store/reference');
    }

    /**
     * Decrypt a reference and returns the reference string
     * @param string $reference
     * @return string
     */
    public function getReferenceDecrypt($reference)
    {
        return substr($reference, 0, 5);
    }

    /**
     * Decrypt a reference and returns the reference order identifier
     * @param string $reference
     * @return string
     */
    public function getReferenceDecryptOrderID($reference)
    {
        return str_replace(substr($reference, 0, 5), '', $reference);
    }

    /**
     * Get magento order URL
     * @param int $idOrder
     * @return URI
     */
    public function getEditOrderUrl($idOrder)
    {
        $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
        $url = $adminhtmlUrl->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));

        return $url;
    }

    /**
     * Change the environment
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

    /**
     * Checks configuration and validades
     * If not completed one or both, is directed and notified so it can be filled
     */
    public function checkTransactionAccess()
    {
        // Displays this error in title
        $module = 'PagSeguro - ';

        // Receive url editing methods ja payment with key
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');
        $email = $this->paymentModel()->getConfigData('email');
        $token = $this->paymentModel()->getConfigData('token');

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
     * Get the date of the request from Magento and convert to the format (d/m/Y)
     * @param date $date - Initial date of order, in default format of Magento
     * @return date $dateConverted - Returns the date converted
     */
    protected function getOrderMagetoDateConvert($date)
    {
        return date("d/m/Y H:i:s", Mage::getModel("core/date")->timestamp($date));
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
        $message = $this->__('Não foi possível executar esta ação. Utilize a conciliação de transações primeiro');
        $message.= $this->__(' ou tente novamente mais tarde.');
        
        return $message;
    }

    /**
    * Cuts the value to 4 characters and converts to float
    * @param object $PaymentRequest - Object responsible for passing the parameters to the webserviceHelper.
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
     * Check PagSeguroAccountCredentials config
     * @throws Exception
     */
    final public function checkCredentials()
    {
        $date = new DateTime(date("Y-m-d\TH:i:s"));
        $date->sub(new DateInterval("P1D"));
        
        $useCache = Mage::app()->useCache();

        if ($useCache['config'])
        {
            Mage::app()->getCacheInstance()->flush();
        } 

        try {
            $this->webserviceHelper()->getTransactionsByDate(1, 1, $date);
            Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 1);

        } catch (PagSeguroServiceException $e) {
            Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 0);
            throw new Exception($e->getMessage());
        }
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
        try {
            if ($this->getLastStatusOrder($orderId) != $orderStatus) {
                if ($class == self::CANCELED_CLASS) {
                    if ($this->webserviceHelper()->cancelRequest($transactionCode) == 'OK') {
                        $orderStatus = 'cancelada_ps';
                    }
                }

                if ($class == self::REFUND_CLASS) {
                    if ($this->webserviceHelper()->refundRequest($transactionCode) == 'OK') {
                        $orderStatus = 'devolvida_ps';
                    }
                }

                $this->notifyCustomer($orderId, $orderStatus);
                Mage::helper('pagseguro/log')->setUpdateOrderLog($class, $orderId, $transactionCode, $orderStatus);
            }

            $this->setTransactionRecord($orderId, $transactionCode);
        } catch (Exception $pse) {
            throw $pse;
        }
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
    final public function setTransactionRecord($orderId, $transactionCode = false, $send = false)
    {

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        
        $table = $resource->getTableName(self::TABLE_NAME);
        
        //Select sent column from pagseguro_orders to verify if exists a register
        $query = 'SELECT order_id, sent FROM ' . $table . ' WHERE order_id = ' . $orderId;
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
     * Convert to Json manually
     * @param array $array
     * @return string
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
    * Request webservice
    * @return UOL_PagSeguro_Helper_Webservice
    */
    final public function webserviceHelper()
    {
        return Mage::helper('pagseguro/webservice');
    }
    
    /**
    * Request payment method
    * @return UOL_PagSeguro_Model_PaymentMethod
    */
    final public function paymentModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');
    }
    
    /**
    * Request notification method
    * @return UOL_PagSeguro_Model_NotificationMethod
    */
    final public function notificationModel()
    {
        return  Mage::getSingleton('UOL_PagSeguro_Model_NotificationMethod');
    }

    /**
     * Get the name of payment status
     * @param Integer $key
     * @return multitype:|boolean
     */
    public function getPaymentStatusFromKey(Integer $key)
    {
        if (array_key_exists($key, $this->arrayPaymentStatusList)) {
            return $this->arrayPaymentStatusList[$key];
        }

        return false;
    }

    /**
     * Get the key of payment status
     * @param String $value
     * @return number|boolean
     */
    public function getPaymentStatusFromValue(String $value)
    {
        $key = array_search($value, $this->arrayPaymentStatusList);

        if (is_numeric($key)) {
            return (int)$key;
        }

        return false;
    }

    /**
     * Get the name to string of payment status
     * @param Integer $key
     * @return Ambigous <string, string, multitype:>|boolean
     */
    public function getPaymentStatusToString(Integer $key)
    {
        if (array_key_exists($key, $this->arrayPaymentStatusList)) {
            switch ($key) {
                case 0:
                    return $this->__('Pendente');
                    break;
                case 1:
                    return $this->__('Aguardando pagamento');
                    break;
                case 2:
                    return $this->__('Em an&aacute;lise');
                    break;
                case 3:
                    return $this->__('Paga');
                    break;
                case 4:
                    return $this->__('Dispon&iacute;vel');
                    break;
                case 5:
                    return $this->__('Em disputa');
                    break;
                case 6:
                    return $this->__('Devolvida');
                    break;
                case 7:
                    return $this->__('Cancelada');
                    break;
                case 8:
                    return $this->__('Chargeback Debitado');
                    break;
                case 9:
                    return $this->__('Em Contestação');
                    break;
            }
        }
        return false;
    }
    
    /**
     * Format string phone number
     * @param string $phone
     * @return array of area code and number
     */
    public function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $ddd = '';

        if (strlen($phone) > 9) {
            if (substr($phone, 0, 1) == 0) {
                $phone = substr($phone, 1);
            }

            $ddd = substr($phone, 0, 2);
            $phone = substr($phone, 2);
        }

        return array('areaCode' => $ddd, 'number' => $phone);
    }

    /**
    * Request installments method
    * @return UOL_PagSeguro_Model_InstallmentsMethod
     */
    public function installmentsModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_InstallmentsMethod');
    }
}
