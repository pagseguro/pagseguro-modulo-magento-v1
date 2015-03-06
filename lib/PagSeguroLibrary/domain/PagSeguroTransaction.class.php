<?php
/**
 * 2007-2014 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/***
 * Class PagSeguroTransaction
 * Represents a PagSeguro transaction
 *
 * @property    PagSeguroSender $sender
 *
 */
class PagSeguroTransaction
{

    /***
     * Transaction date
     */
    private $date;

    /***
     * Last event date
     * Date the last notification about this transaction was sent
     */
    private $lastEventDate;

    /***
     * Transaction code
     */
    private $code;

    /***
     *  Reference code
     *  You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     */
    private $reference;

    /***
     * Recovery code
     */
    private $recoveryCode;

    /***
     * Transaction type
     * @see PagSeguroTransactionType
     * @var PagSeguroTransactionType
     */
    private $type;

    /***
     * Transaction Status
     * @see PagSeguroTransactionStatus
     * @var PagSeguroTransactionStatus
     */
    private $status;

    /**
     * Transaction cancellationSource
     * @see PagSeguroTransactionCancellationSource
     * @var PagSeguroTransactionCancellationSource
     */
    private $cancellationSource;

    /***
     * Payment method
     * @see PagSeguroPaymentMethod
     * @var PagSeguroPaymentMethod
     */
    private $paymentMethod;

    /***
     *  Payment Link
     */
    private $paymentLink;

    /***
     * Gross amount of the transaction
     */
    private $grossAmount;

    /***
     * Discount amount
     */
    private $discountAmount;

    /***
     * Fee amount
     */
    private $feeAmount;

    /***
     * Net amount
     */
    private $netAmount;

    /***
     * Escrow End Date
     */
    private $escrowEndDate;

    /***
     * Extra amount
     */
    private $extraAmount;

    /***
     * Installment count
     */
    private $installmentCount;

    /***
     * creditorFees amount
     */
    private $creditorFees;

    /***
     * Operational Fee Amount amount
     */
    private $operationalFeeAmount;

    /***
     * Installment Fee Amount amount
     */
    private $installmentFeeAmount;

    /***
     * Item count 
     */
    private $itemCount;

    /***
     * item/product list in this transaction
     * @see PagSeguroItem
     */
    private $items;

    /***
     * Payer information, who is sending money
     * @see PagSeguroSender
     * @var PagSeguroSender
     */
    private $sender;

    /***
     * Shipping information
     * @see PagSeguroShipping
     * @var PagSeguroShipping
     */
    private $shipping;

    /***
     * Date the last notification about this transaction was sent
     * @return datetime the last event date
     */
    public function getLastEventDate()
    {
        return $this->lastEventDate;
    }

    /***
     * Sets the last event date
     *
     * @param lastEventDate
     */
    public function setLastEventDate($lastEventDate)
    {
        $this->lastEventDate = $lastEventDate;
    }

    /***
     * @return datetime the transaction date
     */
    public function getDate()
    {
        return $this->date;
    }

    /***
     * Sets the transaction date
     *
     * @param string date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /***
     * @return string the transaction code
     */
    public function getCode()
    {
        return $this->code;
    }

    /***
     * Sets the transaction code
     *
     * @param code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /***
     * You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     *
     * @return string the reference code
     */
    public function getReference()
    {
        return $this->reference;
    }

    /***
     * Sets the reference code
     *
     * @param reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

     /***
     * @return string the recovery code
     */
    public function getRecoveryCode()
    {
        return $this->recoveryCode;
    }

    /***
     * Sets the recovery code
     *
     * @param code
     */
    public function setRecoveryCode($recoveryCode)
    {
        $this->recoveryCode = $recoveryCode;
    }

    /***
     * @return PagSeguroTransactionType the transaction type
     * @see PagSeguroTransactionType
     */
    public function getType()
    {
        return $this->type;
    }

    /***
     * Sets the transaction type
     * @param PagSeguroTransactionType $type
     */
    public function setType(PagSeguroTransactionType $type)
    {
        $this->type = $type;
    }

    /***
     * @return PagSeguroTransactionStatus the transaction status
     * @see PagSeguroTransactionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /***
     * Sets the transaction status
     * @param PagSeguroTransactionStatus $status
     */
    public function setStatus(PagSeguroTransactionStatus $status)
    {
        $this->status = $status;
    }

     /**
     * @return PagSeguroTransactionCancellationSource the transaction cancellation source
     * @see PagSeguroTransactionCancellationSource
     */
    public function getCancellationSource()
    {
        return $this->cancellationSource;
    }

    /**
     * Sets the transaction cancellation source
     * @param PagSeguroTransactionCancellationSource $cancellationSource
     */
    public function setCancellationSource(PagSeguroTransactionCancellationSource $cancellationSource)
    {
        $this->cancellationSource = $cancellationSource;
    }

    /***
     * @return PagSeguroPaymentMethod the payment method used in this transaction
     * @see PagSeguroPaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /***
     * Sets the payment link used in this transaction
     * @param url $paymentLink
     */
    public function setPaymentLink($paymentLink)
    {
        $this->paymentLink = $paymentLink;
    }

    /***
     * @return the payment link method used in this transaction
     */
    public function getPaymentLink()
    {
        return $this->paymentLink;
    }

    /***
     * Sets the payment method used in this transaction
     * @param PagSeguroPaymentMethod $paymentMethod
     */
    public function setPaymentMethod(PagSeguroPaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /***
     * @return float the transaction gross amount
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    /***
     * Sets the transaction gross amount
     * @param float $totalValue
     */
    public function setGrossAmount($totalValue)
    {
        $this->grossAmount = $totalValue;
    }

    /***
     * @return float the transaction gross amount
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /***
     * Sets the transaction gross amount
     * @param float $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    /***
     * @return float the fee amount
     */
    public function getFeeAmount()
    {
        return $this->feeAmount;
    }

    /***
     * Sets the transaction fee amount
     * @param float $feeAmount
     */
    public function setFeeAmount($feeAmount)
    {
        $this->feeAmount = $feeAmount;
    }

    /***
     * @return float the net amount
     */
    public function getNetAmount()
    {
        return $this->netAmount;
    }

    /***
     * Sets the net amount
     * @param float $netAmount
     */
    public function setNetAmount($netAmount)
    {
        $this->netAmount = $netAmount;
    }

    /***
     * @return date the escrow end date
     */
    public function getEscrowEndDate()
    {
        return $this->escrowEndDate;
    }

    /***
     * Sets the escrow end date
     * @param date $escrowEndDate
     */
    public function setEscrowEndDate($escrowEndDate)
    {
        $this->escrowEndDate = $escrowEndDate;
    }

    /***
     * @return float the extra amount
     */
    public function getExtraAmount()
    {
        return $this->extraAmount;
    }

    /***
     * Sets the extra amount
     * @param float $extraAmount
     */
    public function setExtraAmount($extraAmount)
    {
        $this->extraAmount = $extraAmount;
    }

    /***
     * @return integer the installment count
     */
    public function getInstallmentCount()
    {
        return $this->installmentCount;
    }

    /***
     * Sets the installment count
     * @param integer $installmentCount
     */
    public function setInstallmentCount($installmentCount)
    {
        $this->installmentCount = $installmentCount;
    }

    /***
     * Sets the transaction creditorFees
     * @param float $creditorFees
     */
    public function setCreditorFees($creditorFees)
    {
        $this->creditorFees = $creditorFees;
    }

    /***
     * @return object the transaction creditor fees
     */
    public function getCreditorFees()
    {
        return $this->creditorFees;
    }

    /***
     * Sets the transaction Operational Fee Amount
     * @param float $operationalFeeAmount
     */
    public function setOperationalFeeAmount($operationalFeeAmount)
    {
        $this->operationalFeeAmount = $operationalFeeAmount;
    }

    /***
     * @return float the transaction operational fee amount
     */
    public function getOperationalFeeAmount()
    {
        return $this->operationalFeeAmount;
    }

    /***
     * Sets the transaction Installment Fee Amount
     * @param float $installmentFeeAmount
     */
    public function setInstallmentFeeAmount($installmentFeeAmount)
    {
        $this->installmentFeeAmount = $installmentFeeAmount;
    }

    /***
     * @return float the transaction installment fee amount
     */
    public function getInstallmentFeeAmount()
    {
        return $this->installmentFeeAmount;
    }

    /***
     * @return array PagSeguroItem the items/products list in this transaction
     * @see PagSeguroItem
     */
    public function getItems()
    {
        return $this->items;
    }

    /***
     * Sets the list of items/products in this transaction
     * @param array $items
     * @see PagSeguroItem
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /***
     * @return integer the items/products count in this transaction
     */
    public function getItemCount()
    {
        return $this->items == null ? null : count($this->items);
    }

    /***
     * @return PagSeguroSender the sender information, who is sending money in this transaction
     * @see PagSeguroSender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /***
     * Sets the sender information, who is sending money in this transaction
     * @param PagSeguroSender $sender
     */
    public function setSender(PagSeguroSender $sender)
    {
        $this->sender = $sender;
    }

    /***
     * @return PagSeguroShipping the shipping information
     * @see PagSeguroShipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /***
     * sets the shipping information for this transaction
     * @param PagSeguroShipping $shipping
     */
    public function setShipping(PagSeguroShipping $shipping)
    {
        $this->shipping = $shipping;
    }

    /***
     * @return String a string that represents the current object
     */
    public function toString()
    {

        $transaction = array();
        $transaction['code'] = $this->code;
        $transaction['email'] = $this->sender ? $this->sender->getEmail() : "null";
        $transaction['date'] = $this->date;
        $transaction['reference'] = $this->reference;
        $transaction['status'] = $this->status ? $this->status->getValue() : "null";
        $transaction['itemsCount'] = is_array($this->items) ? count($this->items) : "null";

        $transaction = "Transaction: " . var_export($transaction, true);

        return $transaction;

    }
}
