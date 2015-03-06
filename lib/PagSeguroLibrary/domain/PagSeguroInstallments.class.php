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
 * Installments information
 */
class PagSeguroInstallments
{

    /** 
    * Credit card brand 
    */
    private $cardBrand;

    /** 
     * Installment quantity 
     */
    private $quantity;

    /** 
     * Installment amount  
     */
    private $installmentAmount;

    /** 
     * Installment total amount  
     */
    private $totalAmount;

    /** 
     * Installment interest free 
     */
    private $interestFree;

    /***
     * Initializes a new instance of the PagSeguroInstallments class
     * @param array|string $cardBrand...
     * @param int    $quantity
     * @param float  $amount
     * @param float  $totalAmount
     * @param float  $interestFree
     */
    public function __construct($cardBrand, 
        $quantity = null, 
        $amount = null, 
        $totalAmount = null, 
        $interestFree = null)
    {   
        $param = $cardBrand;
        if (isset($param) && is_array($param) || is_object($param)) {
           $this->setInstallment($param);
        } else {
           $this->setInstallment($cardBrand, $quantity, $amount, $totalAmount, $interestFree);
        }    
    }

    /***
     * Set brand of credit card
     * @param $cardBrand string
     */
    public function setCardBrand($cardBrand)
    {
        $this->cardBrand = $cardBrand;
    }

    /***
     * @return string the credit card brand
     */
    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    /***
     * Set the installments quantity
     * @param $quantity int
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /***
     * @return int of installments quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /***
     * Set the installments amount
     * @param $installmentAmount float
     */
    public function setInstallmentAmount($installmentAmount)
    {
        $this->installmentAmount = $installmentAmount;
    }

    /***
     * @return float of installments amount
     */
    public function getInstallmentAmount()
    {
        return $this->installmentAmount;
    }

    /***
     * Set the installments total amount
     * @param $totalAmount float
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /***
     * @return float of installments total amount
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /***
     * Set the installments interest free
     * @param $interestFree float
     */
    public function setInterestFree($interestFree)
    {
        $this->interestFree = $interestFree;
    }

    /***
     * @return float of installments interest free
     */
    public function getInterestFree()
    {
        return $this->interestFree;
    }

    /***
     * Set the installments
     * @param array|string $cardBrand...
     * @param int    $quantity
     * @param float  $amount
     * @param float  $totalAmount
     * @param float  $interestFree
     */
    public function setInstallment($cardBrand, 
        $quantity = null, 
        $amount = null, 
        $totalAmount = null, 
        $interestFree = null)
    {
        $param = $quantity;
        $param = $cardBrand;
        if (isset($param) && is_array($param) || is_object($param)) {
            if (isset($param->cardBrand)) {
                $this->cardBrand = $param->cardBrand;
            }
             if (isset($param->quantity)) {
                $this->quantity = $param->quantity;
            }
            if (isset($param->installmentAmount)) {
                $this->installmentAmount = $param->installmentAmount;
            }
            if (isset($param->totalAmount)) {
                $this->totalAmount = $param->totalAmount;
            }
            if (isset($param->interestFree)) {
                $this->interestFree = $param->interestFree;
            }
        } else {
            if (isset($cardBrand)) {
                $this->cardBrand = $cardBrand;
            }
            if (isset($quantity)) {
                $this->quantity = $quantity;
            }
            if (isset($installmentAmount)) {
                $this->installmentAmount = $installmentAmount;
            }
            if (isset($totalAmount)) {
                $this->totalAmount = $totalAmount;
            }
            if (isset($interestFree)) {
                $this->interestFree = $interestFree;
            }
        }  
    }
}