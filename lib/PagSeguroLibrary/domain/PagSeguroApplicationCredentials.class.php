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
 * Identifies a PagSeguro authorization credentials
 * /
 */
class PagSeguroApplicationCredentials extends PagSeguroCredentials
{

    /***
     * PagSeguro App ID
     */
    private $appId;

    /***
     * Specifies the corresponding appKey to PagSeguro application that is performing the request.
     */
    private $appKey;

    /***
     * Specifies the corresponding authorization Code to PagSeguro application that is performing the request.
     */
    private $authorizationCode;

    /***
     * Initializes a new instance of PagSeguroAuthorizationCredentials class
     *
     * @throws Exception when credentials aren't provided.
     *
     * @param string $appId
     * @param string $appKey
     * @param string $authorizationCode
     */
    public function __construct($appId, $appKey, $authorizationCode = null)
    {
        if ($appId !== null && $appKey !== null) {
            $this->appId = $appId;
            $this->appKey = $appKey;
        }  else {
            throw new Exception("Authorization credentials not set.");
        }

        if ($authorizationCode !== null ) {
            $this->authorizationCode = $authorizationCode;
        }
    }

    /***
     * @return string the appID from this authorization credentials
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /***
     * Sets the app ID from this authorization credentials
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /***
     * @return string the appKey from this authorization credentials
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /***
     * Sets the app ID from this authorization credentials
     * @param string $appKey
     */
    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
    }

    /***
     * @return string the appKey from this authorization credentials
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /***
     * Sets the app ID from this authorization credentials
     * @param string $authorizationCode
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;
    }

    /***
     * @return array a map of name value pairs that compose this set of credentials
     */
    public function getAttributesMap()
    {
        return array(
            'appId' => $this->appId,
            'appKey' => $this->appKey,
            'authorizationCode' => $this->authorizationCode
        );
    }

    /***
     * @return string a string that represents the current object
     */
    public function toString()
    {
        $credentials = array();
        $credentials['AppID'] = $this->appId;
        $credentials['AppKey'] = $this->appKey;
        $credentials['AuthorizationCode'] = $this->appKey;
        return implode(' - ', $credentials);
    }
}
