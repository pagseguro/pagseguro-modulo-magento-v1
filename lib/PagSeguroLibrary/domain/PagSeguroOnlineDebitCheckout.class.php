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
class PagSeguroOnlineDebitCheckout
{
	
    /***
     * @var bankName
     */
    private $bankName;

    /***
     * Initializes a new instance of the PagSeguroOnlineDebitCheckout class
     * @param array $data
     */
    public function __construct(array $data = null)
    {

        if ($data) {
            if (isset($data['bankName'])) {
                $this->setBankName($data['bankName']);
            }
        }
        
    }

    /***
     * Sets the bank name
     * @param String $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /***
     * @return string bank name
     */
    public function getBankName()
    {
        return $this->bankName;
    }
}