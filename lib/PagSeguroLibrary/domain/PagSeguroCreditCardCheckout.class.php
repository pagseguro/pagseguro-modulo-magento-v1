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
 * CreditCard Checkout information
 */
class PagSeguroCreditCardCheckout
{
	
    /***
     * Credit Card Brand
     */
    private $brand;

    /***
     * Credit Card Token
     */
    private $token;

    /***
     * Credit Card Holder
     * @see PagSeguroCreditCardHolder
     */
    private $holder;

    /***
     * Credit Card Installment
     * @see PagSeguroInstallment
     */
    private $installment;

    /***
     * Credit Card Billing Adress
     * @see PagSeguroBilling
     */
    private $billing;

    /***
     * Initializes a new instance of the PagSeguroCreditCardCheckout class
     * @param array $data
     */
    public function __construct(array $data = null)
    {

        if ($data) {
            if (isset($data['token'])) {
                $this->token = $data['token'];
            }
            if (isset($data['holder'])) {
                $this->holder = $data['holder'];
            }
            if (isset($data['installment'])) {
                $this->installment = $data['installment'];
            }
            if (isset($data['billing'])) {
                $this->billing = $data['billing'];
            }
        }
        
    }

    /***
     * Sets the credit card brand
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /***
     * @return string the credit card brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /***
     * Sets the credit card token
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /***
     * @return mixed the credit card token
     */
    public function getToken()
    {
        return $this->token;
    }

    /***
     * Sets the PagSeguroCreditCardHolder
     * @param intanceof PagSeguroCreditCardHolder $holder
     */
    public function setHolder($holder)
    {
        $this->holder = $holder;
    }

    /***
     * @return PagSeguroCreditCardHolder object 
     * @see PagSeguroCreditCardHolder
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /***
     * Sets the PagSeguroInstallment
     * @param intanceof PagSeguroInstallment $installment
     */
    public function setInstallment($installment)
    {
        $this->installment = $installment;
    }

    /***
     * @return PagSeguroInstallment object
     * @see PagSeguroInstallment
     */
    public function getInstallment()
    {
        return $this->installment;
    }

    /***
     * Sets the PagSeguroBilling
     * @param intanceof PagSeguroBilling $billing
     */
    public function setBilling($billing)
    {
        $this->billing = $billing;
    }

    /***
     * @return PagSeguroBilling object
     * @see PagSeguroBilling
     */
    public function getBilling()
    {
        return $this->billing;
    }
}