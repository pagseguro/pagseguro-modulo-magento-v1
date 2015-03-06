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
 * Authorization account information
 */
class PagSeguroAuthorizationAccount
{

    /**
     * @var $privateKey
     */
    private $privateKey;

    /***
     * Initializes a new instance of the PagSeguroAuthorizationAccount class
     * @param null|string $account
     * @throws string Exception
     */
    public function __construct($account = null)
    {
        if (isset($account)) {
            $this->setPrivateKey($account);
        } else {
            throw new Exception("Wasn't possible construct the account");
        }
    }

    /***
     * @return string of private key
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /***
     * Sets the authorization account private key
     * @param string $value
     */
    public function setPrivateKey($value)
    {
        if (isset($value)) {
            $this->privateKey = $value;
        }
    }
}