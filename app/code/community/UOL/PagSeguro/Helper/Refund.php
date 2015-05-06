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

class UOL_PagSeguro_Helper_Refund extends HelperData
{
    // It is used to store the environment of transactions
    private $environment;

    /**
     * Construct
     */
    public function __construct()
    {
        include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
        $this->environment = PagSeguroConfig::getEnvironment();
    }

    /**
     * Creates the complete array with the necessary information for the table
     * @param int $orderId - Id of order of Magento
     * @param string $paymentCode - Transaction code of PagSeguro
     * @param int $paymentStatus - Status of payment of PagSeguro
     * @method array $this->arrayPayments - Set the complete array with the necessary information for the table
     */
    public function createArrayPayments($orderId, $paymentCode, $paymentStatus)
    {
        // Receives the object of order that was entered the id
        $order = Mage::getModel('sales/order')->load($orderId);

        // Receives the status already converted and translated of order of Magento
        $statusMagento = strtolower($this->getPaymentStatusMagento($this->__(ucfirst($order->getStatus()))));

        // Receives the status of the transaction PagSeguro already converted
        $statusPagSeguro = strtolower($this->getPaymentStatusPagSeguro($paymentStatus));

        if ($paymentStatus >= 3 && $paymentStatus <= 5) {
            // Receives the creation date of the application which is converted to the format d/m/Y
            $dateOrder = $this->getOrderMagetoDateConvert($order->getCreatedAt());

            // Receives the number of order
            $idMagento = '#' . $order->getIncrementId();

            // Receives the transaction code of PagSeguro
            $idPagSeguro = $paymentCode;

            // Receives the parameter used to update an order
            $config = $order->getId() .'/'. $idPagSeguro .'/'. $this->getPaymentStatusPagSeguro($paymentStatus, true);

            // If the order is not reconciled, show a warning
            $alertConciliation = $this->alertConciliation($this->__('estornar'));

            if ($statusMagento == $statusPagSeguro) {
                $config = "class='action' data-config='" . $config . "'";
            } else {
                $config = " onclick='Modal.alertConciliation(&#34;" . $alertConciliation . "&#34;)'";
            }

            // Receives the url edit order it from your id
            $editUrl = $this->getEditOrderUrl($orderId);
            $textEdit = $this->__('Ver detalhes');

            // Receives the full html link to edit and action an order
            $actionOrder .= "<a class='edit' target='_blank' href='" . $this->getEditOrderUrl($orderId) . "'>";
            $actionOrder .= $this->__('Ver detalhes') . "</a>";

            $actionOrder .= "<a " . $config . " href='javascript:void(0)'>";
            $actionOrder .= $this->__('Estornar') . "</a>";

            $array = array( 'date' => $dateOrder,
                            'id_magento' => $idMagento,
                            'id_pagseguro' => $idPagSeguro,
                            'status_magento' => $statusMagento,
                            'action' => $actionOrder);

            $this->arrayPayments[] = $array;
        }
    }

    /**
     * Get the full array with only the requests made ​​in Magento with PagSeguro
     * @return array $this->arrayPayments - Returns an array with the necessary information to fill the table
     */
    public function getArrayPayments()
    {
        $this->getMagentoPayments();

        return $this->arrayPayments;
    }

    /**
     * Set log records listed
     * @method setLog - Set log in file
     */
    public function setRefundListLog($days)
    {
        $module = ' [Info] PagSeguroConciliation.';

        // Sentence of log
        $phrase = "Searched( '" . $days . " days - Range of dates ";
        $phrase .= $this->getDateStart() . " until " .
                   $this->getDateFinally() . "' )";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Set log of update order
     * @method setLog - Set log in file
     */
    public function setRefundUpdateOrderLog($orderId, $transactionCode, $orderStatus)
    {
        $module = ' [Info] PagSeguroRefund.';

        // Sentence of log
        $phrase = 'Update(';
        $phrase .= "OrderStatusMagento: array (\n  'orderId' => " . $orderId . ",\n  ";
        $phrase .= "'transactionCode' => '" . $transactionCode . "',\n  ";
        $phrase .= "'orderStatus' => '" . $orderStatus . "'\n))";

        // Creating the update log order
        $this->setLog($phrase, $module);
    }

    /**
     * Refund the order status of Magento
     * Creates notification in the historical in order of Magento and sends email to the customer
     * Insert the transaction code of PagSeguro in order of Magento
     * @param int $orderId - Id of order of Magento
     * @param string $transactionCode - Transaction code of PagSeguro
     */
    public function refundOrderStatusMagento($orderId, $transactionCode)
    {
        if ($this->requestPagSeguroRefundService($transactionCode) == 'OK') {
            $orderStatus = 'devolvida_ps';
            $this->setRefundUpdateOrderLog($orderId, $transactionCode, $orderStatus);

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
    }
}
