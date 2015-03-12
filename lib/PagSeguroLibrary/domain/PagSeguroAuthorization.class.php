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
 * Class PagSeguroAuthorization
 * Represents a PagSeguro application authorization
 */
class PagSeguroAuthorization
{

    /***
     * Authorization code
     */
    private $code;

    /***
     * Authorization date
     */
    private $date;

    /***
     * Creation date
     * Date of authorization creation
     */
    private $creationDate;


    /***
     *  Reference code
     *  You can use the reference code to store an identifier so you can
     *  associate the PagSeguro authorization to a authorization in your system.
     */
    private $reference;

    /***
     * Represents all permissions returned by the authorization
     * @see PagSeguroAuthorizationPermisssions
     */
    private $permissions;

    /***
     * Represents the account
     * @see PagSeguroAuthorizationAccount
     */
    private $account;

    /***
     * @return string of authorization code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /***
     * Sets the authorization code
     * @param string $code
     */
    public function setCode($code)
    {
        if (isset($code)) {
            $this->code = $code;
        }
    }

    /***
     * @return string of authorization date.
     */
    public function getDate()
    {
        return $this->date;
    }

    /***
     * Sets the authorization date
     * @param string $date
     */
    public function setDate($date)
    {
        if (isset($date)) {
            $this->date = $date;
        }
    }

    /***
     * @return string of authorization creation date.
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /***
     * Sets the authorization creation date
     * @param string $creationDate
     */
    public function setCreationDate($creationDate)
    {
        if (isset($creationDate)) {
            $this->creationDate = $creationDate;
        }
    }

    /***
     * @return string of authorization reference.
     */
    public function getReference()
    {
        return $this->reference;
    }

    /***
     * Sets the authorization reference
     * @param string $reference
     */
    public function setReference($reference)
    {
        if (isset($reference)) {
            $this->reference = $reference;
        }
    }

    /***
     * @return string of authorization permissions.
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /***
     * Sets the authorization permissions
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        if (isset($permissions)) {
            $this->permissions = $permissions;
        }
    }

    /***
     * @return string of authorization account.
     */
    public function getAccount()
    {
        return $this->account;
    }

    /***
     * Sets the authorization account
     * @param string $account
     */
    public function setAccount($account)
    {
        if (isset($account)) {
            $this->account = $account;
        }
    }

    /***
     * @return String that resents the current object
     */
    public function toString()
    {
        $authorization = array();
        $authorization['code'] = $this->code;
        $authorization['reference'] = $this->reference;
        return "Authorization: " . implode(' - ', $authorization);
    }
}
