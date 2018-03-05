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

class UOL_PagSeguro_Helper_Transaction extends HelperData
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
        $pagseguroOrders = $this->getTransactionsDatabase($paramsFilter);

        if(count($pagseguroOrders) > 0)
        {
            foreach ($pagseguroOrders as $key => $psOrder)
            {
                $order = $this->getOrderMagento($psOrder['order_id'], $paramsFilter);

                if(Mage::getStoreConfig('payment/pagseguro/environment') == strtolower(trim($this->getOrderEnvironment($psOrder)))) {
                    if(!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                        if (Mage::getSingleton('core/session')->getData("store_id") == $order['store_id']) {
                            $this->pagSeguroOrders = $psOrder;
                            $this->pagSeguroOrders['status'] = $order['status'];

                            if (!empty($this->getPaymentStatusFromValue($order['status']))){
                                $this->arrayPagSeguroOrders[] = $this->build($order);
                            }
                        }
                    }else {
                        $this->pagSeguroOrders = $psOrder;
                        $this->pagSeguroOrders['status'] = $order['status'];

                        if (!empty($this->getPaymentStatusFromValue($order['status']))){
                            $this->arrayPagSeguroOrders[] = $this->build($order);
                        }
                    }
                }
            }
        }
    }

    private function getTransactionsDatabase($paramsFilter)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getConfig()->getTablePrefix().'pagseguro_orders';

        $select  = "SELECT * FROM " . $table;

        $where = array();

        if(isset($paramsFilter['idMagento'])){
            $where[] = "order_id = ".$paramsFilter['idMagento'];
        }

        if(isset($paramsFilter['idPagSeguro'])){
            $where[] = "transaction_code = '".$paramsFilter['idPagSeguro']."'";
        }

        if(isset($paramsFilter['environment'])){
            $where[] = "environment = '".$paramsFilter['environment']."'";
        }

        if(count($where) > 0){
            $select .= ' WHERE ' . implode(' AND ', $where);
        }

        return $connection->fetchAll($select);
    }

    private function getOrderMagento($orderId, $params)
    {
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('entity_id', $orderId);

        if(!empty($params['startDate']) && !empty($params['endDate'])){
            $startDate = date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $params['startDate'])));
            $endDate = date('Y-m-d'.' 23:59:59', strtotime(str_replace("/", "-", $params['endDate'])));
            $order->addFieldToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        if(!empty($params['status'])){
            $order->addFieldToFilter('status', $this->getPaymentStatusFromKey($params['status']));
        }

        return current($order->getData());
    }

    public function build($order)
    {
        $action = "<a class='edit' target='_blank' href='".$this->getEditOrderUrl($order['entity_id'])."'>";
        $action .= $this->__('Ver detalhes pedido')."</a>";

        $config = "class='action' data-config='".$this->pagSeguroOrders['transaction_code']."'";

        $action .= "<br><a ".$config." href='javascript:void(0)'>";
        $action .= $this->__('Ver detalhes transação')."</a>";

        return array(
            'date'           => $this->getOrderMagetoDateConvert($order['created_at']),
            'id_magento'     => "#".$order['increment_id'],
            'id_pagseguro'   => $this->pagSeguroOrders['transaction_code'],
            'environment'    => $this->pagSeguroOrders['environment'],
            'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($order['status'])),
            'action'         => $action
        );
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
            'type'              => $this->pagSeguroTransaction->getType(),
            'status'            => $this->getPaymentStatusToString($this->pagSeguroTransaction->getStatus()),
            'lastEventDate'     => $this->getOrderMagetoDateConvert($this->pagSeguroTransaction->getLastEventDate()),
            'installmentCount'  => $this->pagSeguroTransaction->getInstallmentCount(),
            'cancelationSource' => $this->getTitleCancellationSourceTransaction($this->pagSeguroTransaction->getCancelationSource()),
            'discountAmount'    => str_replace(".", ",", $this->pagSeguroTransaction->getDiscountAmount()),
            'escrowEndDate'     => $this->getOrderMagetoDateConvert($this->pagSeguroTransaction->getEscrowEndDate()),
            'extraAmount'       => str_replace(".", ",", $this->pagSeguroTransaction->getExtraAmount()),
            'feeAmount'         => $this->pagSeguroTransaction->getFeeAmount(),
            'grossAmount'       => str_replace(".", ",", $this->pagSeguroTransaction->getGrossAmount()),
            'netAmount'         => str_replace(".", ",", $this->pagSeguroTransaction->getNetAmount()),
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
                'intermediationRateAmount'  => str_replace(".", ",", $this->pagSeguroTransaction->getCreditorFees()->getIntermediationRateAmount()),
                'intermediationFeeAmount'   => str_replace(".", ",", $this->pagSeguroTransaction->getCreditorFees()->getIntermediationFeeAmount()),
                'installmentFeeAmount'      => str_replace(".", ",", $this->pagSeguroTransaction->getCreditorFees()->getInstallmentFeeAmount()),
                'operationalFeeAmount'      => str_replace(".", ",", $this->pagSeguroTransaction->getCreditorFees()->getOperationalFeeAmount()),
                'commissionFeeAmount'       => str_replace(".", ",", $this->pagSeguroTransaction->getCreditorFees()->getCommissionFeeAmount())
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
                    'amount'        => str_replace(".", ",", $item->getAmount()),
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
}