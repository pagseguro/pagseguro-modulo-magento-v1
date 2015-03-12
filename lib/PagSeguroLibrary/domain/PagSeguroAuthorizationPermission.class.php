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
 * Authorization permission information
 */
class PagSeguroAuthorizationPermission
{

    /**
     * @var $code the permission code.
     */
    private $code;
    /**
     * @var $status the permission status
     */
    private $status;
    /**
     * @var $lastUpdate date of the last permission update
     */
    private $lastUpdate;

    /***
     * Initializes a new instance of the PagSeguroAuthorizationPermission class
     * @param null|string $code
     * @param null|string $status
     * @param null|date $lastUpdate
     * @throws string Exception
     */
    public function __construct($code = null, $status = null, $lastUpdate = null)
    {
        if (isset($code) and isset($status) and isset($lastUpdate)) {
            $this->setCode($code);
            $this->setStatus($status);
            $this->settLastUpdate($lastUpdate);
        } else {
            throw new Exception("Wasn't possible construct the permission");
        }
    }

    /***
     * @return string of authorization permission code
     */
    public function getCode()
    {
        return $this->code;
    }

    /***
     * Sets the authorization permission code
     * @param mixed $code
     */
    public function setCode($code)
    {
        if (isset($code)) {
            $this->code = $code;
        }
    }

    /***
     * @return string of authorization permission status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /***
     * Sets the authorization permission status
     * @param string $status
     */
    public function setStatus($status)
    {
        if (isset($status)) {
            $this->status = $status;
        }
    }

    /***
     * @return string of authorization last update
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /***
     * Sets the authorization permission last update
     * @param date $lastUpdate
     */
    public function settLastUpdate($lastUpdate)
    {
        if (isset($lastUpdate)) {
            $this->lastUpdate = $lastUpdate;
        }
    }
}