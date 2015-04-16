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
    private $dateStart = '';

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
        return Mage::getConfig()->getModuleConfig("UOL_PagSeguro")->version;
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
     * @return date $dateStart - Example Y-m-dT00:00
     */
    public function getDateStart()
    {
        $this->dateStart = $_SESSION['dateStart'];

        if ($this->dateStart != '') {
            $dateStart = $this->dateStart . 'T00:00';
        } else {
            $dateStart = date('Y-m-d') . 'T00:00';
        }

        return $dateStart;
    }

    /**
     * Get date finally for query
     * @return date $date - Returns the end date (Y-m-dTH:i)
     */
    public function getDateFinally()
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
        $obj = Mage::getSingleton('adminhtml/url');
        $url = $obj->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));

        return $url;
    }

    /**
     * Creating log for conciliation and abandoned
     * @param string $phrase - It's the phrase that completes the log
     * @param string $module - It's the title that completes the log
     */
    public function setLog($phrase, $module)
    {
        $obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        // value 0/1
        $log = $obj->getConfigData('log');

        if ($log == 1) {
            if ($obj->getConfigData('log_file') != '') {
                $directoryLog = Mage::getBaseDir() . '/' . $obj->getConfigData('log_file');
            } else {
                $directoryLog = Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguro.log';
            }
            $date = '{' . Mage::app()->getLocale()->date() . '}';
            $return = $date . $module . $phrase . "\r\n";
            file_put_contents($directoryLog, $return, FILE_APPEND);
        }
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
        $tp    = (string)Mage::getConfig()->getTablePrefix();
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
     * Get html of header of backend
     * @return string $html - Html of header
     */
    public function getHeader($logo)
    {
        $logo = Mage::getBaseUrl('skin') . 'adminhtml/default/default/uol/pagseguro/images/logo.png';
        $url = 'https://pagseguro.uol.com.br/registration/registration.jhtml?ep=7&tipo=cadastro#!vendedor';
        $version = $this->__('Versão %s', $this->getVersion());
        $id = 'pagseguro-registration-button';

        $html = '<div id="pagseguro-module-header">
                    <div class="wrapper">
                        <div id="pagseguro-logo">
                            <img class="pagseguro_logo" src="' . $logo . '" />
                            <div id="pagseguro-module-version">' . $version . '</div>
                        </div>
                        <a id="' . $id . '" class="pagseguro-button gray-theme" href="' . $url . '" target="_blank">
                            ' . $this->__('Faça seu cadastro') . '
                        </a>
                    </div>
                </div>';

        return $html;
    }

    /**
     * Get url of access the page correct
     * @param string $path - Path of the page to be returned
     * @return string $url - Returns the url of page.
     */
    private function getSideMenuUrl($path)
    {
        $obj = Mage::getSingleton('adminhtml/url');

        if ($path == 'pagseguro_configuration') {
            $url = $obj->getUrl('adminhtml/system_config/edit/section/payment/key');
        } else {
            $correctPath = str_replace('pagseguro_', 'adminhtml_', $path);
            $url = $obj->getUrl('pagseguro/' . $correctPath);
        }

        return $url;
    }

    /**
     * Get html of side menu of backend
     * @return string $html - Html of side menu
     */
    public function getSideMenu()
    {
        // Set controller name of page in variable $page
        $page = str_replace('adminhtml_', 'pagseguro_', Mage::app()->getRequest()->getControllerName());
        $menu = new Mage_Adminhtml_Block_Page_Menu();
        $menuArray = $menu->getMenuArray();

        $html = '<div id="pagseguro-module-menu">' .
                '   <ul>';

        foreach ($menuArray['pagseguro_menu']['children'] as $key => $item) {
            $selected = ($page == $key) ? ' class="selected"' : '';

            $html .= '<li id="menu-item-' . $key . '"' . $selected . ' data-has-form="true">';

            if ($item['children']) {
                $html .= '<span class="children"><i class="icon"></i>' . $item['label'] . '</span>
                          <ul>';

                foreach ($item['children'] as $key => $subItem) {
                    $selected = ($page == $key) ? ' class="selected"' : '';

                    $html .= '<li id="menu-subitem-' . $key . '"' . $selected . ' data-has-form="true">
                                <a href="' . $this->getSideMenuUrl($key) . '">
                                ' . $subItem['label'] . '
                                </a>
                              </li>';
                }

                $html .= '</ul>';
            } else {
                $html .= '<a href="' . $this->getSideMenuUrl($key) . '">
                            ' . $item['label'] . '
                        </a>';
            }

            $html .= '</li>';
        }

        $html .= '  </ul>' .
                 '</div>';

        return $html;
    }

    /*
     * Checks if email was filled and token
     * Checks if email and token are valid
     * If not completed one or both, is directed and notified so it can be filled
     */
    public function checkTransactionAccess()
    {
        $obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        // Displays this error in title
        $module = 'PagSeguro - ';

        // Receive url editing methods ja payment with key
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');
        $email = $obj->getConfigData('email');
        $token = $obj->getConfigData('token');

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
    public function setDateStart($days)
    {
        $_SESSION['dateStart'] = $this->getDateSubtracted($days);
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
        $obj = Mage::getModel('sales/order')->load($orderId);

        return $obj->getStatus();
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
     * Get url of transaction search service
     * @return string $url - Returns full url to query
     */
    protected function getUrlTransactionSearchService()
    {
        // Capture the url query the webservice
        $url = $PagSeguroResources['webserviceUrl'][$this->environment] .
               $PagSeguroResources['transactionSearchService']['servicePath'];

        return $url;
    }

    /**
    * Gets transactions filtered of server of PagSeguro
    * @param object $credential - Server access credentials
    * @param int $page - Page of records to be visited
    * @param date $dtStart - Date start of filter
    * @param date $dtFinaly - Date end of filter
    * @return object $transactions - It contains all transactions found
    */
    private function getTransactionService($credential, $page, $dtStart, $dtFinaly)
    {
        $transactions = PagSeguroTransactionSearchService::searchByDate($credential, $page, 1000, $dtStart, $dFinaly);

        return $transactions;
    }

    /**
     * Get list of payment PagSeguro
     * @return array $transactionArray - Array with transactions
     */
    protected function getPagSeguroPaymentList()
    {
        include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
        $obj = Mage::getSingleton('UOL_PagSeguro_Model_PaymentMethod');

        try {
            $credential = $obj->getCredentialsInformation();
            $dateStart  = $this->getDateStart();
            $dateFinaly = $this->getDateFinally();

            $transactions = $this->getTransactionService($credential, 1, $dateStart, $dateFinaly);
            $pages = $transactions->getTotalPages();

            if ($pages > 1) {
                for ($i = 1; $i < ($pages + 1); $i++) {
                    $transactions = $this->getTransactionService($credential, $i, $dateStart, $dateFinaly);
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
     * Updates the order status of Magento
     * Creates notification in the historical in order of Magento and sends email to the customer
     * Insert the transaction code of PagSeguro in order of Magento
     * @param int $orderId - Id of order of Magento
     * @param string $transactionCode - Transaction code of PagSeguro
     * @param string $orderStatus - Status of transaction of PagSeguro
     */
    public function updateOrderStatusMagento($orderId, $transactionCode, $orderStatus)
    {
        $this->setConciliationUpdateOrderLog($orderId, $transactionCode, $orderStatus);

        if ($this->getLastStatusOrder($orderId) != $orderStatus) {
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

        //Get the resource model
        $resource = Mage::getSingleton('core/resource');

        //Retrieve the read connection
        $readConnection = $resource->getConnection('core_read');

        //Retrieve the write connection
        $writeConnection = $resource->getConnection('core_write');

        $tp    = (string)Mage::getConfig()->getTablePrefix();
        $table = $tp . 'pagseguro_orders';

        //Select sent column from pagseguro_orders to verify if exists a register
        $query = 'SELECT order_id FROM ' . $resource->getTableName($table) . ' WHERE order_id = ' . $orderId;
        $result = $readConnection->fetchAll($query);

        if (!empty($result)) {
            $sql = "UPDATE `" . $table . "` SET `transaction_code` = '$transactionCode' WHERE order_id = " . $orderId;
        } else {
            $environment = ucfirst(Mage::getStoreConfig('payment/pagseguro/environment'));
            $sql = $query = "INSERT INTO " . $table . " (order_id, transaction_code, environment)
                             VALUES ('$orderId', '$transactionCode', '$environment')";
        }

        $writeConnection->query($sql);
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
}
