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

class PagSeguroTransactionCreditorFees
{
    /***
     * the intermediation rate amount of the transaction creditor fees
     */
    private $intermediationRateAmount;

    /***
     * the intermediation fee amount of the transaction creditor fees
     */
    private $intermediationFeeAmount;

    /***
     * @param array|null $value
     */
    public function __construct(array $value = null)
    {
        if ($value) {
            $this->setIntermediationRateAmount($value['intermediationRateAmount']);
            $this->setIntermediationFeeAmount($value['intermediationFeeAmount']);
        }
    }

    /***
     * Sets the transaction intermediation Rate Amount
     * @param float $intermediationRateAmount
     */
    public function setIntermediationRateAmount($intermediationRateAmount)
    {
        $this->intermediationRateAmount = $intermediationRateAmount;
    }

    /***
     * @return float the transaction intermediation Rate Amount
     */
    public function getIntermediationRateAmount()
    {
        return $this->intermediationRateAmount;
    }

    /***
     * Sets the transaction intermediation Fee Amount
     * @param float $intermediationFeeAmount
     */
    public function setIntermediationFeeAmount($intermediationFeeAmount)
    {
        $this->intermediationFeeAmount = $intermediationFeeAmount;
    }

    /***
     * @return float the transaction intermediation Fee Amount
     */
    public function getIntermediationFeeAmount()
    {
        return $this->intermediationFeeAmount;
    }
}
