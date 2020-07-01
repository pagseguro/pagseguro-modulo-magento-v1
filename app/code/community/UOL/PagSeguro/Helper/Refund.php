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
class UOL_PagSeguro_Helper_Refund extends UOL_PagSeguro_Helper_Data
{
    /**
     * @var array
     */
    protected $arrayPayments;
    /**
     * @var array
     */
    private $PagSeguroPaymentList;
    /**
     * @var int
     */
    private $days;
    /**
     * @var array
     */
    private $magentoPaymentList;

    /**
     * Returns payment array
     *
     * @return mixed $this->arrayPayment
     */
    public function getPaymentsArray()
    {
        return $this->arrayPayments;
    }

    /**
     * Executes the essentials functions for this helper
     *
     * @param $days
     */
    public function initialize($days)
    {
        $this->days = $days;
        $this->getPagSeguroPayments();
        $this->getMagentoPayments();
        $this->requestTransactionsToRefund();
    }

    /**
     * Get PagSeguroTransaction from webservice in a date range.
     *
     * @param null $page
     *
     * @throws Exception
     */
    private function getPagSeguroPayments($page = null)
    {
        if (is_null($page)) {
            $page = 1;
        }
        $date = new DateTime ("now");
        $date->setTimezone(new DateTimeZone ("America/Sao_Paulo"));
        $dateInterval = "P".( string )$this->days."D";
        $date->sub(new DateInterval ($dateInterval));
        $date->setTime(00, 00, 00);
        $date = $date->format("Y-m-d\TH:i:s");
        try {
            if (is_null($this->PagSeguroPaymentList)) {
                $this->PagSeguroPaymentList = $this->webserviceHelper()->getTransactionsByDate($page, 1000,
                    $date);
            } else {
                $PagSeguroPaymentList = $this->webserviceHelper()->getTransactionsByDate($page, 1000, $date);
                $this->PagSeguroPaymentList->setDate($PagSeguroPaymentList->getDate());
                $this->PagSeguroPaymentList->setCurrentPage($PagSeguroPaymentList->getCurrentPage());
                $this->PagSeguroPaymentList->setTotalPages($PagSeguroPaymentList->getTotalPages());
                $this->PagSeguroPaymentList->setResultsInThisPage(
                    $PagSeguroPaymentList->getResultsInThisPage() + $this->PagSeguroPaymentList->getResultsInThisPage
                );
                $this->PagSeguroPaymentList->setTransactions(
                    array_merge(
                        $this->PagSeguroPaymentList->getTransactions(),
                        $PagSeguroPaymentList->getTransactions()
                    )
                );
            }
            if (!is_null($this->PagSeguroPaymentList) && $this->PagSeguroPaymentList->getTotalPages() > $page) {
                $this->getPagSeguroPayments(++$page);
            }
        } catch (Exception $pse) {
            throw $pse;
        }
    }

    /**
     * Get magento orders in a date range.
     */
    protected function getMagentoPayments()
    {
        $date = new DateTime ("now");
        $date->setTimezone(new DateTimeZone ("America/Sao_Paulo"));
        $dateInterval = "P".( string )$this->days."D";
        $date->sub(new DateInterval ($dateInterval));
        $date->setTime(00, 00, 00);
        $date = $date->format("Y-m-d\TH:i:s");
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from' => $date, 'to' => date('Y-m-d H:i:s')));
        /** used to validate if an order is already refunded (could be refunded only one time) */
        $partiallyRefundedOrdersArray = $this->getPartiallyRefundedOrders();

        foreach ($collection as $order) {
            if (! in_array($order->getId(), $partiallyRefundedOrdersArray)) {
                $this->magentoPaymentList[] = $order->getId();
            }
        }
    }

    /**
     * Build a array with PagSeguroTransaction where status is refundable
     */
    private function requestTransactionsToRefund()
    {
        foreach ($this->magentoPaymentList as $orderId) {
            $orderHandler = Mage::getModel('sales/order')->load($orderId);
            if (Mage::getStoreConfig('payment/pagseguro/environment')
                == strtolower(trim($this->getOrderEnvironment($orderId)))
            ) {
                if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                    if (Mage::getSingleton('core/session')->getData("store_id") == $orderHandler->getStoreId()) {
                        if ($orderHandler->getStatus() == "paga_ps"
                            or $orderHandler->getStatus() == 'em_disputa_ps'
                            or $orderHandler->getStatus() == 'disponivel_ps'
                        ) {
                            $PagSeguroSummaryItem = $this->findPagSeguroTransactionByReference(
                                $orderHandler->getEntityId()
                            );
                            if (!is_null($PagSeguroSummaryItem)) {
                                $this->arrayPayments[] = $this->build($PagSeguroSummaryItem, $orderHandler);
                            }
                        }
                    }
                } elseif ($orderHandler) {
                    if ($orderHandler->getStatus() == "paga_ps"
                        or $orderHandler->getStatus() == 'em_disputa_ps'
                        or $orderHandler->getStatus() == 'disponivel_ps'
                    ) {
                        $PagSeguroSummaryItem = $this->findPagSeguroTransactionByReference(
                            $orderHandler->getEntityId()
                        );
                        if (!is_null($PagSeguroSummaryItem)) {
                            $this->arrayPayments[] = $this->build($PagSeguroSummaryItem, $orderHandler);
                        }
                    }
                }
            }
        }
        Mage::getSingleton('core/session')->unsetData('store_id');
    }

    /**
     * Get order environment
     *
     * @param int $orderId
     *
     * @return string Order environment
     */
    private function getOrderEnvironment($orderId)
    {
        $reader = Mage::getSingleton("core/resource")->getConnection('core_read');
        $table = Mage::getConfig()->getTablePrefix().'pagseguro_orders';
        $query = "SELECT environment FROM ".$table." WHERE order_id = ".$orderId;
        if ($reader->fetchOne($query) == 'Produção') {
            return "production";
        } else {
            return $reader->fetchOne($query);
        }
    }

    /**
     * Find a PagSeguroTransaction by referece
     *
     * @param $orderId
     *
     * @return mixed
     */
    private function findPagSeguroTransactionByReference($orderId)
    {
        foreach ($this->PagSeguroPaymentList->getTransactions() as $list) {
            if ($this->getReferenceDecrypt($list->getReference()) == $this->getStoreReference()) {
                if ($this->getReferenceDecryptOrderID($list->getReference()) == $orderId) {
                    return $list;
                }
            }
        }
    }

    public function build($PagSeguroSummaryItem, $order)
    {
        $PagSeguroStatusValue = $this->getPaymentStatusFromKey($PagSeguroSummaryItem->getStatus());
        if ($order->getStatus() == $PagSeguroStatusValue) {
            $config = "class='action' data-config='".$order->getId().'/'.$PagSeguroSummaryItem->getCode().'/'.
                $this->getPaymentStatusFromKey($PagSeguroSummaryItem->getStatus())."'";
        } else {
            $config = " onclick='Modal.alertConciliation(&#34;"
                .$this->alertConciliation($this->__('estornar'))."&#34;)'";
        }
        //echo '<pre>';print_r($PagSeguroSummaryItem);exit;
        $actionOrder = "<a class='edit' target='_blank' href='".$this->getEditOrderUrl($order->getId())."'>";
        $actionOrder .= $this->__('Ver detalhes')."</a>";
        $actionOrder .= "<a ".$config." href='javascript:void(0)'>";
        $actionOrder .= $this->__('Estorno total')."</a>";
        $config = "class='action' data-config='".$order->getId().'/'.$PagSeguroSummaryItem->getCode().'/'.$this->getPaymentStatusFromKey($PagSeguroSummaryItem->getStatus()).'/'.$PagSeguroSummaryItem->getGrossAmount().'/'.$order->getIncrementId()."'";
        $actionOrder .= "<a ".$config." href='javascript:void(0)'>";
        $actionOrder .= $this->__('Estorno parcial')."</a>";

        return array(
            'date'           => $this->getOrderMagetoDateConvert($order->getCreatedAt()),
            'id_magento'     => $order->getIncrementId(),
            'id_pagseguro'   => $PagSeguroSummaryItem->getCode(),
            'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($order->getStatus())),
            'action'         => $actionOrder,
        );
    }

    /**
     * Get all pagseguro partially refunded orders id
     *
     * @return array
     */
    private function getPartiallyRefundedOrders()
    {
        $pagseguroOrdersIdArray = array();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $pagseguroTable = Mage::getConfig()->getTablePrefix().'pagseguro_orders';


        $select = $read->select()
            ->from(array('ps' => $pagseguroTable), array('order_id'))
            ->where('ps.partially_refunded = ?', '1')
        ;

        if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
            $select = $select->where('ps.store_id = ?', Mage::getSingleton('core/session')->getData("store_id"));
        }

        if (Mage::getStoreConfig('payment/pagseguro/environment')) {
            $select = $select->where('ps.environment = ?', Mage::getStoreConfig('payment/pagseguro/environment'));
        }

        $read->prepare($select);

        foreach ($read->fetchAll($select) as $value) {
            $pagseguroOrdersIdArray[] = $value['order_id'];
        }

        return $pagseguroOrdersIdArray;
    }

    /**
     * Set 1 to partially_refunded field in pagseguro_orders table
     *
     * @param string $orderId
     * @return void
     */
    public function setPartiallyRefunded($orderId)
    {
        $pagseguroTable = Mage::getConfig()->getTablePrefix().'pagseguro_orders';
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $where = $connection->quoteInto('order_id = ?', $orderId);
        $data = array('partially_refunded' => 1);
        $connection->update($pagseguroTable, $data, $where);
    }
}
