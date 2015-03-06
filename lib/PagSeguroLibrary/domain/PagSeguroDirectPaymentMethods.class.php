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
 * Direct payment method information
 *
 */
class PagSeguroDirectPaymentMethods
{

    /***
     * Payment method type
     */
    private $methodsList = array(
            "CREDIT_CARD" => "creditCard",
            "BOLETO" => "boleto",
            "EFT" => "eft"
        );

    /*
     * @var $paymentMethod
     */
    private $paymentMethod;

    /***
     * Initializes a new instance of the PaymentMethods class
     * @param  $paymentMethod
     */
    public function __construct($paymentMethod = null)
    {
        if (isset($paymentMethod)) {
            if (array_key_exists($paymentMethod, $this->methodsList)) {
                $this->setPaymentMethod($this->methodsList[$paymentMethod]);
            } else {
                throw new Exception("Direct payment method not found");
            }
        }
    }

    /***
     * @return the method
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /***
     * Sets the payment method
     * @param $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        if (isset($paymentMethod)) {
            $this->paymentMethod = $paymentMethod;
        }
    }
}