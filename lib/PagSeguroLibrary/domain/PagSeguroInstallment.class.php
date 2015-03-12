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
 * Installment information
 */
class PagSeguroInstallment
{
    /***
     * Installment quantity
     */
    private $quantity;
    /***
     * Installment value
     */
    private $value;

    /***
     * Initializes a new instance of the PagSeguroInstallment class
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data) {
            if (isset($data['quantity'])) {
                $this->quantity = $data['quantity'];
            }
            if (isset($data['value'])) {
                $this->value = $data['value'];
            }
        }    
    }

    /***
     * Set installment quantity
     * @param $quantity int
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /***
     * @return int installment quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /***
     * Set installment value
     * @param $value float
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /***
     * @return float installment value
     */
    public function getValue()
    {
        return $this->value;
    }

    /***
     * Sets the installment value and quantity
     * @param $quantity int
     * @param $value float
     */
    public function setInstallment($quantity, $value = null)
    {
        $param = $quantity;
        if (isset($param) && is_array($param) || is_object($param)) {
            $this->quantity = $param['quantity'];
            $this->value = $param['value'];
        } else {
            $this->quantity = $quantity;
            $this->value = $value;
        }
    }
}