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
 * Class PagSeguroConnectionData
 */
class PagSeguroConnectionData
{

    /***
     * @var
     */
    private $serviceName;
    /***
     * @var PagSeguroCredentials
     */
    private $credentials;
    /***
     * @var
     */
    private $resources;
    /***
     * @var
     */
    private $environment;
    /***
     * @var
     */
    private $webserviceUrl;
    /***
     * @var
     */
    private $paymentUrl;
    /***
     * @var
     */
    private $baseUrl;
    /***
     * @var
     */
    private $installmentUrl;
    /***
     * @var
     */
    private $sessionUrl;
    /***
     * @var
     */
    private $servicePath;
    /***
     * @var
     */
    private $serviceTimeout;
    /***
     * @var
     */
    private $charset;

    /***
     * @param PagSeguroCredentials $credentials
     * @param $serviceName
     */
    public function __construct(PagSeguroCredentials $credentials, $serviceName)
    {

        $this->credentials = $credentials;
        $this->serviceName = $serviceName;

        try {
            $this->setEnvironment(PagSeguroConfig::getEnvironment());
            $this->setWebserviceUrl(PagSeguroResources::getWebserviceUrl($this->getEnvironment()));
            $this->setPaymentUrl(PagSeguroResources::getPaymentUrl($this->getEnvironment()));
            $this->setBaseUrl(PagSeguroResources::getBaseUrl($this->getEnvironment()));
            $this->setInstallmentUrl(PagSeguroResources::getInstallmentUrl());
            $this->setAuthorizationUrl(PagSeguroResources::getAuthorizationUrl());
            $this->setSessionUrl(PagSeguroResources::getSessionUrl());
            $this->setCharset(PagSeguroConfig::getApplicationCharset());

            $this->resources = PagSeguroResources::getData($this->serviceName);
            if (isset($this->resources['servicePath'])) {
                $this->setServicePath($this->resources['servicePath']);
            }
            if (isset($this->resources['serviceTimeout'])) {
                $this->setServiceTimeout($this->resources['serviceTimeout']);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /***
     * @return PagSeguroCredentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /***
     * @param PagSeguroCredentials $credentials
     */
    public function setCredentials(PagSeguroCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /***
     * @return string
     */
    public function getCredentialsUrlQuery()
    {
        return http_build_query($this->credentials->getAttributesMap(), '', '&');
    }

    /***
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /***
     * @param $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /***
     * @return mixed
     */
    public function getWebserviceUrl()
    {
        return $this->webserviceUrl;
    }

    /***
     * @param $webserviceUrl
     */
    public function setWebserviceUrl($webserviceUrl)
    {
        $this->webserviceUrl = $webserviceUrl;
    }

    /***
     * @return mixed
     */
    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    /***
     * @param $paymentUrl
     */
    public function setPaymentUrl($paymentUrl)
    {
        $this->paymentUrl = $paymentUrl;
    }

    /***
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /***
     * @param $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /***
     * @return mixed
     */
    public function getInstallmentUrl()
    {
        return $this->installmentUrl;
    }

    /***
     * @param $installmentUrl
     */
    public function setInstallmentUrl($installmentUrl)
    {
        $this->installmentUrl = $installmentUrl;
    }

    /***
     * @return mixed
     */
    public function getAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    /***
     * @param $installmentUrl
     */
    public function setAuthorizationUrl($authorizationUrl)
    {
        $this->authorizationUrl = $authorizationUrl;
    }

    /***
     * @return mixed
     */
    public function getSessionUrl()
    {
        return $this->sessionUrl;
    }

    /***
     * @param $installmentUrl
     */
    public function setSessionUrl($sessionUrl)
    {
        $this->sessionUrl = $sessionUrl;
    }

    /***
     * @param mixed $version
     * @return mixed
     */
    public function getServicePath($version = null)
    {
        if ($version) {
            return $this->servicePath[$version];
        } else {
            return $this->servicePath;
        }
    }

    /***
     * @param $servicePath
     */
    public function setServicePath($servicePath)
    {
        $this->servicePath = $servicePath;
    }

    /***
     * @return mixed
     */
    public function getServiceTimeout()
    {
        return $this->serviceTimeout;
    }

    /***
     * @param $serviceTimeout
     */
    public function setServiceTimeout($serviceTimeout)
    {
        $this->serviceTimeout = $serviceTimeout;
    }

    /***
     * @param mixed $version
     * @return string
     */
    public function getServiceUrl($version = null)
    {
        if ($version) {
            return $this->getWebserviceUrl() . $this->getServicePath($version);
        } else {
            return $this->getWebserviceUrl() . $this->getServicePath();
        }
    }

    /***
     * @param $resource
     * @return mixed
     */
    public function getResource($resource)
    {
        return $this->resources[$resource];
    }

    /***
     * @return mixed
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /***
     * @param $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }


}
