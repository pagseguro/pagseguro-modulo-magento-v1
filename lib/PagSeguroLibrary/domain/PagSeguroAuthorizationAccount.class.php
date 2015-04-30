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
     * @var $publicKey
     */
    private $publicKey;

    /***
     * Initializes a new instance of the PagSeguroAuthorizationAccount class
     * @param null|string $account
     * @throws string Exception
     */
    public function __construct($account = null)
    {
        if (isset($account)) {
            $this->setPublicKey($account);
        } else {
            throw new Exception("Wasn't possible construct the account");
        }
    }

    /***
     * @return string of public key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /***
     * Sets the authorization account public key
     * @param string $value
     */
    public function setPublicKey($value)
    {
        if (isset($value)) {
            $this->publicKey = $value;
        }
    }
}
