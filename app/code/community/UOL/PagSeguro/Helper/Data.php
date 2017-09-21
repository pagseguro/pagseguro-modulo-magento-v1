<?php

/**
 ************************************************************************
 * Copyright [2015] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */
class UOL_PagSeguro_Helper_Data extends Mage_Payment_Helper_Data
{
    /**
     *
     */
    const REFUND_CLASS = "UOL_PagSeguro_Helper_Refund";
    /**
     *
     */
    const CANCELED_CLASS = "UOL_PagSeguro_Helper_Canceled";
    /**
     *
     */
    const TABLE_NAME = "pagseguro_orders";
    /**
     * @var array
     */
    protected $arrayPayments = array();
    /**
     * @var array
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
        9 => "em_contestacao_ps",
    );

    /**
     * UOL_PagSeguro_Helper_Data constructor.
     */
    public function __construct()
    {
        $this->environmentNotification();
    }

    /**
     *
     */
    private function environmentNotification()
    {
        $environment = Mage::getStoreConfig('payment/pagseguro/environment');
        //Define table name with their prefix
        $tp = (string)Mage::getConfig()->getTablePrefix();
        $table = $tp.'adminnotification_inbox';
        $sql = "SELECT notification_id  FROM `".$table."` WHERE title LIKE '%[UOL_PagSeguro]%'";
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
     * @param $table
     */
    private function insertEnvironmentNotice($table)
    {
        // force default time zone
        Mage::app()->getLocale()->date();
        $date = date("Y-m-d H:i:s");
        $title = $this->__("[UOL_PagSeguro] Suas transações serão feitas em um ambiente de testes.");
        $description = $this->__("Nenhuma das transações realizadas nesse ambiente tem valor monetário.");
        $sql = "INSERT INTO `".$table."` (severity, date_added, title, description, is_read, is_remove)
                VALUES (4, '$date', '$title', '$description', 0, 0)";
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);
        unset($connection);
    }

    /**
     * @param $table
     *
     * @return mixed
     */
    private function getEnvironmentIncrement($table)
    {
        $sql = "SELECT MAX(notification_id) AS 'max_id' FROM `".$table."`";
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $readConnection->fetchAll($sql);

        return $results[0]['max_id'];
    }

    /**
     * @param $table
     * @param $id
     */
    private function removeEnvironmentNotice($table, $id)
    {
        $sql = "DELETE FROM `".$table."` WHERE notification_id = ".$id;
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);
        unset($connection);
    }

    /**
     * @throws Exception
     */
    final public function checkCredentials()
    {
        $yesterday = new DateTime('yesterday'); 
        $useCache = Mage::app()->useCache();
        if ($useCache['config']) {
            Mage::app()->getCacheInstance()->flush();
        }
        try {
            $this->webserviceHelper()->getTransactionsByDate(1, 1, $yesterday->format('Y-m-d\TH:i'));
            Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 1);
        } catch (Exception $e) {
            Mage::getConfig()->saveConfig('uol_pagseguro/store/credentials', 0);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    final public function webserviceHelper()
    {
        return Mage::helper('pagseguro/webservice');
    }

    /**
     *
     */
    public function checkTransactionAccess()
    {
        $module = 'PagSeguro - ';
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');
        $email = $this->paymentModel()->getConfigData('email');
        $token = $this->paymentModel()->getConfigData('token');
        if ($email) {
            if (!$token) {
                $message = $module.$this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
                Mage::app()->getResponse()->setRedirect($configUrl);
            }
        } else {
            $message = $module.$this->__('Preencha o e-mail do vendedor.');
            Mage::getSingleton('core/session')->addError($message);
            if (!$token) {
                $message = $module.$this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
            }
            Mage::app()->getResponse()->setRedirect($configUrl);
        }
    }

    /**
     * @return false|Mage_Core_Model_Abstract
     */
    final public function paymentModel()
    {
        return Mage::getModel('UOL_PagSeguro_Model_PaymentMethod');
    }

    /**
     * @param $size
     * @param $uppercase
     * @param $number
     *
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
            $string .= $character[$rand - 1];
        }

        return $string;
    }

    /**
     * @param $phone
     *
     * @return array
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
     * @param $paymentRequest
     *
     * @return mixed
     */
    public function getDiscount($paymentRequest)
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig('payment/pagseguro/discount_credit_card', $storeId) == 1) {
            $creditCard = (double)Mage::getStoreConfig('payment/pagseguro/discount_credit_card_value', $storeId);
            if ($creditCard && $creditCard != 0.00) {
                $paymentRequest->addPaymentMethodConfig('CREDIT_CARD', $creditCard, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/pagseguro/discount_electronic_debit', $storeId) == 1) {
            $eft = (double)Mage::getStoreConfig('payment/pagseguro/discount_electronic_debit_value', $storeId);
            if ($eft && $eft != 0.00) {
                $paymentRequest->addPaymentMethodConfig('EFT', $eft, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/pagseguro/discount_boleto', $storeId) == 1) {
            $boleto = (double)Mage::getStoreConfig('payment/pagseguro/discount_boleto_value', $storeId);
            if ($boleto && $boleto != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BOLETO', $boleto, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/pagseguro/discount_deposit_account', $storeId)) {
            $deposit = (double)Mage::getStoreConfig('payment/pagseguro/discount_deposit_account_value', $storeId);
            if ($deposit && $deposit != 0.00) {
                $paymentRequest->addPaymentMethodConfig('DEPOSIT', $deposit, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/pagseguro/discount_balance', $storeId)) {
            $balance = (double)Mage::getStoreConfig('payment/pagseguro/discount_balance_value', $storeId);
            if ($balance && $balance != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BALANCE', $balance, 'DISCOUNT_PERCENT');
            }
        }

        return $paymentRequest;
    }

    /**
     * @param $idOrder
     *
     * @return mixed
     */
    public function getEditOrderUrl($idOrder)
    {
        $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
        $url = $adminhtmlUrl->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));

        return $url;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function getPaymentStatusFromKey($key)
    {
        if (array_key_exists($key, $this->arrayPaymentStatusList)) {
            return $this->arrayPaymentStatusList[$key];
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return bool|int
     */
    public function getPaymentStatusFromValue($value)
    {
        $key = array_search($value, $this->arrayPaymentStatusList);
        if (is_numeric($key)) {
            return (int)$key;
        }

        return false;
    }

    /**
     * @param $key
     *
     * @return bool|string
     */
    public function getPaymentStatusToString($key)
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
     * @param $reference
     *
     * @return bool|string
     */
    public function getReferenceDecrypt($reference)
    {
        return substr($reference, 0, 5);
    }

    /**
     * @param $reference
     *
     * @return mixed
     */
    public function getReferenceDecryptOrderID($reference)
    {
        return str_replace(substr($reference, 0, 5), '', $reference);
    }

    /**
     * @return mixed
     */
    public function getStoreReference()
    {
        return Mage::getStoreConfig('uol_pagseguro/store/reference');
    }

    /**
     * @param $array
     *
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
                $dataSet .= (count($info) != $i) ? '"'.$item.'",' : '"'.$item.'"';
                $i++;
            }
            $dataSet .= ']';
            $j++;
        }
        $dataSet .= ']';

        return $dataSet;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function installmentsModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_InstallmentsMethod');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    final public function notificationModel()
    {
        return Mage::getSingleton('UOL_PagSeguro_Model_NotificationMethod');
    }

    /**
     * @param $class
     * @param $orderId
     * @param $transactionCode
     * @param $orderStatus
     *
     * @throws Exception
     */
    public function updateOrderStatusMagento($class, $orderId, $transactionCode, $orderStatus)
    {
        try {
            if (
                $this->getLastStatusOrder($orderId) != $orderStatus
                || $class == self::CANCELED_CLASS
                || $class == self::REFUND_CLASS
            ) {
                if ($class == self::CANCELED_CLASS) {
                    if ($this->webserviceHelper()->cancelRequest($transactionCode)->getResult() == 'OK') {
                        $orderStatus = 'cancelada_ps';
                    }
                }
                if ($class == self::REFUND_CLASS) {
                    if ($this->webserviceHelper()->refundRequest($transactionCode)->getResult() == 'OK') {
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
     * @param $orderId
     *
     * @return mixed
     */
    protected function getLastStatusOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getStatus();
    }

    /**
     * @param $orderId
     * @param $orderStatus
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
     * @param      $orderId
     * @param bool $transactionCode
     * @param bool $send
     */
    final public function setTransactionRecord($orderId, $transactionCode = false, $send = false)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName(self::TABLE_NAME);
        //Select sent column from pagseguro_orders to verify if exists a register
        $query = "SELECT `order_id`, `sent` FROM `$table` WHERE `order_id` = $orderId";
        $result = $readConnection->fetchAll($query);
        if (!empty($result)) {
            if ($send == true) {
                $sent = $result[0]['sent'] + 1;
                $value = "sent = '".$sent."'";
            } else {
                $value = "transaction_code = '".$transactionCode."'";
            }
            $sql = "UPDATE `".$table."` SET ".$value." WHERE order_id = ".$orderId;
        } else {
            $environment = ucfirst(Mage::getStoreConfig('payment/pagseguro/environment'));
            if ($send == true) {
                $column = " (`order_id`, `sent`, `environment`) ";
                $values = " (`$orderId`, 1, `$environment`) ";
            } else {
                $column = " (order_id, transaction_code, environment) ";
                $values = " (`$orderId', `$transactionCode`, `$environment`) ";
            }
            $sql = "INSERT INTO $table $column VALUES $values";
        }
        $writeConnection->query($sql);
    }

    /**
     * @param $action
     *
     * @return string
     */
    protected function alertConciliation($action)
    {
        $message = $this->__('Não foi possível executar esta ação. Utilize a conciliação de transações primeiro');
        $message .= $this->__(' ou tente novamente mais tarde.');

        return $message;
    }

    /**
     * @param $date
     *
     * @return false|string
     */
    protected function getOrderMagetoDateConvert($date)
    {
        return date("d/m/Y H:i:s", Mage::getModel("core/date")->timestamp($date));
    }
    
    public function getPagSeguroDirectPaymentJs()
    {
         if (Mage::getStoreConfig('payment/pagseguro/environment') === 'production') {
            return 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
        }

        return 'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
    }

    /**
     * Format original document and return it as an array, with it "washed" value
     * and type
     * @param string $document
     * @return array
     * @throws Exception
     */
    public function formatDocument($document)
    {
       $documentNumbers = preg_replace('/[^0-9]/', '', $document);
       switch (strlen($documentNumbers)) {
            case 14:
                return ['number' => $documentNumbers, 'type' => 'CNPJ'];
                break;
            case 11:
                return ['number' => $documentNumbers, 'type' => 'CPF'];
                break;
            default:
                throw new Exception('Invalid document');
                break;
        }
    }
}
