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
use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Transactions extends HelperData
{
    private $pagSeguroOrders;
    private $arrayPagSeguroOrders;
    private $pagSeguroTransaction;
    private $arrayTransaction;
    private $needConciliate = false;

    public function initialize($paramsFilter)
    {
        $this->getPagSeguroTransactions($paramsFilter);
    }

    public function getPagSeguroOrdersArray()
    {
        return $this->arrayPagSeguroOrders;
    }

    public function getTransactionsArray()
    {
        return $this->arrayTransaction;
    }

    public function checkNeedConciliate()
    {
        return $this->needConciliate;
    }

    public function getPagSeguroTransactions($paramsFilter)
    {
        $this->buildArrayPagSeguroOrders($this->getTransactionsDatabase($paramsFilter));
    }

    /**
     * Get all PagSeguro transactions in DB with an transaction_id associated from 
     * pagseguro_orders table joined with orders table
     *
     * @param array $paramsFilter
     * @return array
     */
    private function getTransactionsDatabase($paramsFilter)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $ordersTable = $resource->getTableName('sales/order');
        $pagseguroTable = Mage::getConfig()->getTablePrefix().'pagseguro_orders';

        $select = $read->select()
            ->from(array('order' => $ordersTable), array('status', 'created_at', 'increment_id', 'store_id'))
            ->join(array('ps' => $pagseguroTable), 'order.entity_id = ps.order_id')
            ->where('ps.transaction_code != ?', '')
            ->order('created_at DESC')
        ;

        if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
            $select = $select->where('store_id = ?', Mage::getSingleton('core/session')->getData("store_id"));
        }

        if (Mage::getStoreConfig('payment/pagseguro/environment')) {
            $select = $select->where('environment = ?', Mage::getStoreConfig('payment/pagseguro/environment'));
        }

        if (isset($paramsFilter['idMagento'])) {
            $select = $select->where('order.increment_id = ?', $paramsFilter['idMagento']);
        }

        if (isset($paramsFilter['idPagSeguro'])) {
            $select = $select->where('ps.transaction_code = ?', $paramsFilter['idPagSeguro']);
        }

        if (isset($paramsFilter['environment'])) {
            $select = $select->where('ps.environment = ?', $paramsFilter['environment']);
        }

        if (isset($paramsFilter['status'])) {
            $select = $select->where('order.status = ?', $this->getPaymentStatusFromKey($paramsFilter['status']));
        }

        if (isset($paramsFilter['startDate']) && isset($paramsFilter['endDate'])) {
            $startDate = date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $paramsFilter['startDate'])));
            $endDate = date('Y-m-d'.' 23:59:59', strtotime(str_replace("/", "-", $paramsFilter['endDate'])));
            $select = $select->where('order.created_at >= ?', $startDate)->where('order.created_at <= ?', $endDate);
        }

        $read->prepare($select);
        return $read->fetchAll($select);
    }

    public function getTransactionByCode($transactionCode)
    {
        $this->pagSeguroTransaction = $this->webserviceHelper()->getTransactionsByCode($transactionCode);

        if(!empty($this->pagSeguroTransaction)){
            $orderId = $this->getReferenceDecryptOrderID($this->pagSeguroTransaction->getReference());
            $order = Mage::getModel('sales/order')->load($orderId);

            if ($this->getStoreReference() == $this->getReferenceDecrypt($this->pagSeguroTransaction->getReference())) {
                if ($order->getStatus() == $this->getPaymentStatusFromKey($this->pagSeguroTransaction->getStatus())) {
                    $this->arrayTransaction = $this->buildTransaction();
                }else{
                    $this->needConciliate = true;
                }
            }
        }
    }

    public function buildTransaction()
    {
        return array(
            'date'              => $this->getOrderMagetoDateConvert($this->pagSeguroTransaction->getDate()),
            'code'              => $this->pagSeguroTransaction->getCode(),
            'reference'         => $this->pagSeguroTransaction->getReference(),
            'type'              => $this->getTransactionTypeName($this->pagSeguroTransaction->getType()),
            'status'            => $this->getPaymentStatusToString($this->pagSeguroTransaction->getStatus()),
            'lastEventDate'     => $this->getOrderMagetoDateConvert($this->pagSeguroTransaction->getLastEventDate()),
            'installmentCount'  => $this->pagSeguroTransaction->getInstallmentCount(),
            'cancelationSource' => $this->getTitleCancellationSourceTransaction($this->pagSeguroTransaction->getCancelationSource()),
            'discountAmount'    => $this->pagSeguroTransaction->getDiscountAmount(),
            'escrowEndDate'     => $this->getOrderMagetoDateConvert($this->pagSeguroTransaction->getEscrowEndDate()),
            'extraAmount'       => $this->pagSeguroTransaction->getExtraAmount(),
            'feeAmount'         => $this->pagSeguroTransaction->getFeeAmount(),
            'grossAmount'       => $this->pagSeguroTransaction->getGrossAmount(),
            'netAmount'         => $this->pagSeguroTransaction->getNetAmount(),
            'creditorFees'      => $this->prepareCreditorFees(),
            'itemCount'         => $this->pagSeguroTransaction->getItemCount(),
            'items'             => $this->prepareItems(),
            'paymentMethod'     => $this->preparePaymentMethod(),
            'sender'            => $this->prepareSender(),
            'shipping'          => $this->prepareShipping(),
            'paymentLink'       => $this->pagSeguroTransaction->getPaymentLink(),
            'promoCode'         => $this->pagSeguroTransaction->getPromoCode()
        );
    }

    private function prepareCreditorFees()
    {
        $creditorFees = "";
        if(!empty($this->pagSeguroTransaction->getCreditorFees()))
        {
            $creditorFees = array(
                'intermediationRateAmount'  => $this->pagSeguroTransaction->getCreditorFees()->getIntermediationRateAmount(),
                'intermediationFeeAmount'   => $this->pagSeguroTransaction->getCreditorFees()->getIntermediationFeeAmount(),
                'installmentFeeAmount'      => $this->pagSeguroTransaction->getCreditorFees()->getInstallmentFeeAmount(),
                'operationalFeeAmount'      => $this->pagSeguroTransaction->getCreditorFees()->getOperationalFeeAmount(),
                'commissionFeeAmount'       => $this->pagSeguroTransaction->getCreditorFees()->getCommissionFeeAmount()
            );
        }
        return $creditorFees;
    }

    private function prepareItems()
    {
        $itens = array();

        if($this->pagSeguroTransaction->getItemCount() > 0) {
            foreach ($this->pagSeguroTransaction->getItems() as $item)
            {
                $itens[] = array(
                    'id'            => $item->getId(),
                    'description'   => $item->getDescription(),
                    'quantity'      => $item->getQuantity(),
                    'amount'        => $item->getAmount(),
                    'weight'        => $item->getWeight(),
                    'shippingCost'  => $item->getShippingCost()
                );
            }
        }
        return $itens;
    }

    private function preparePaymentMethod()
    {
        $paymentMethod = "";
        if(!empty($this->pagSeguroTransaction->getPaymentMethod()))
        {
            $paymentMethod = array(
                'code' => $this->pagSeguroTransaction->getPaymentMethod()->getCode(),
                'type' => $this->pagSeguroTransaction->getPaymentMethod()->getType(),
                'titleType' => $this->getTitleTypePaymentMethod($this->pagSeguroTransaction->getPaymentMethod()->getType()),
                'titleCode' => $this->getTitleCodePaymentMethod($this->pagSeguroTransaction->getPaymentMethod()->getCode())
            );
        }
        return $paymentMethod;
    }

    private function prepareSender()
    {
        $documents = array();
        if(count($this->pagSeguroTransaction->getSender()->getDocuments()) > 0) {
            foreach ($this->pagSeguroTransaction->getSender()->getDocuments() as $doc)
            {
                $documents[] = array(
                    'type'      => $doc->getType(),
                    'identifier' => $doc->getIdentifier()
                );
            }
        }

        $sender = array();
        if(!empty($this->pagSeguroTransaction->getSender())){
            $sender = array(
                'name'  => $this->pagSeguroTransaction->getSender()->getName(),
                'email' => $this->pagSeguroTransaction->getSender()->getEmail(),
                'phone' => array(
                            'areaCode' => $this->pagSeguroTransaction->getSender()->getPhone()->getAreaCode(),
                            'number' => $this->pagSeguroTransaction->getSender()->getPhone()->getNumber()
                        ),
                'documents' => $documents
            );
        }
        return $sender;
    }

    private function prepareShipping()
    {
        $shipping = array();
        if(!empty($this->pagSeguroTransaction->getShipping())){
            $shipping = array(
                'addres' => array(
                    'street'    => $this->pagSeguroTransaction->getShipping()->getAddress()->getStreet(),
                    'number'    => $this->pagSeguroTransaction->getShipping()->getAddress()->getNumber(),
                    'complement' => $this->pagSeguroTransaction->getShipping()->getAddress()->getComplement(),
                    'district'  => $this->pagSeguroTransaction->getShipping()->getAddress()->getDistrict(),
                    'postalCode' => $this->pagSeguroTransaction->getShipping()->getAddress()->getPostalCode(),
                    'city'      => $this->pagSeguroTransaction->getShipping()->getAddress()->getCity(),
                    'state'     => $this->pagSeguroTransaction->getShipping()->getAddress()->getState(),
                    'country'   => $this->pagSeguroTransaction->getShipping()->getAddress()->getCountry()
                ),
                'type' => $this->pagSeguroTransaction->getShipping()->getType()->getType(),
                'cost' => $this->pagSeguroTransaction->getShipping()->getCost()->getCost()
            );
        }
        return $shipping;
    }

    private function getOrderEnvironment($orderPagSeguro)
    {
        if ($orderPagSeguro['environment'] == 'Produção') {
            return "production";
        } else {
            return $orderPagSeguro['environment'];
        }
    }

    /**
     * Build an array and set it in the attribute arrayPagSeguroOrders. This array is used to show
     * the PagSeguro transactions table
     *
     * @param array $pagSeguroOrders
     * @return void
     */
    private function buildArrayPagSeguroOrders($pagSeguroOrders)
    {
        $action = "<a class='edit' target='_blank' href='%s'>%s</a> <br><a class='action' data-config='%s'href='javascript:void(0)'>%s</a>";
        foreach ($pagSeguroOrders as $pagSeguroOrder) {
            $this->arrayPagSeguroOrders[] = array(
                'date'           => $this->getOrderMagetoDateConvert($pagSeguroOrder['created_at']),
                'id_magento'     => $pagSeguroOrder['increment_id'],
                'id_pagseguro'   => $pagSeguroOrder['transaction_code'],
                'environment'    => $pagSeguroOrder['environment'],
                'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($pagSeguroOrder['status'])),
                'action'         => sprintf(
                    $action,
                    $this->getEditOrderUrl($pagSeguroOrder['order_id']),
                    $this->__('Ver detalhes pedido'),
                    $pagSeguroOrder['transaction_code'],
                    $this->__('Ver detalhes transação')
                )
            );
        }
    }
}