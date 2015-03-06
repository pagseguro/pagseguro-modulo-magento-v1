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
 * @author    PagSeguro Internet Ltda.
 * @copyright 2007-2014 PagSeguro Internet Ltda.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/***
 * Encapsulates web service calls to search for PagSeguro search authorizations
 */
class PagSeguroAuthorizationSearchService
{
    /**
     *
     */
    const SERVICE_NAME = 'authorizationService';

    /**
     * @var information about the log service
     */
    private static $logService;

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param $authorizationCode
     * @return string
     */
    private static function buildSearchUrlByCode(PagSeguroConnectionData $connectionData, $authorizationCode)
    {
        $url = $connectionData->getServiceUrl();
        return "{$url}/{$authorizationCode}/?" . $connectionData->getCredentialsUrlQuery();
    }
    /**
     * @param PagSeguroConnectionData $connectionData
     * @param $notificationCode
     * @return string
     */
    private static function buildSearchUrlByNotification(PagSeguroConnectionData $connectionData, $notificationCode)
    {
        $url = $connectionData->getServiceUrl();
        return "{$url}/notifications/{$notificationCode}/?" . $connectionData->getCredentialsUrlQuery();
    }
    /**
     * @param PagSeguroConnectionData $connectionData
     * @param null|array $options
     * @return string
     */
    private static function buildSearchUrl(PagSeguroConnectionData $connectionData, $options = null)
    {
        if (!is_null($options)) {
            $options = http_build_query($options, '', '&');
            return $connectionData->getServiceUrl() . "/?" . $connectionData->getCredentialsUrlQuery() . "&" . $options;
        }
        return $connectionData->getServiceUrl() . "/?" . $connectionData->getCredentialsUrlQuery();
    }

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param $reference
     * @return string
     */
    private static function buildSearchUrlByReference(PagSeguroConnectionData $connectionData, $reference)
    {
        $url = $connectionData->getServiceUrl();
        return "{$url}?" . $connectionData->getCredentialsUrlQuery() . '&reference='.$reference;
    }

    /***
     * Finds a authorization with a matching authorization code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $authorizationCode
     * @return PagSeguroAuthorization a authorization object
     * @see PagSeguroAuthorization
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByCode(PagSeguroCredentials $credentials, $authorizationCode)
    {
        LogPagSeguro::info("PagSeguroAuthorizationSearchService.SearchByCode($authorizationCode) - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);
        try {
            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByCode($connectionData, $authorizationCode),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "SearchByCode";
            return self::searchReturn($connection, $authorizationCode);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        }
        catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }
    
    /***
     * Finds a authorization with a matching notification code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $notificationCode
     * @return PagSeguroAuthorization a authorization object
     * @see PagSeguroAuthorization
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByNotificationCode(PagSeguroCredentials $credentials, $notificationCode)
    {
        LogPagSeguro::info("PagSeguroAuthorizationSearchService.searchByNotificationCode($notificationCode) - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);
        try {
            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByNotification($connectionData, $notificationCode),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "SearchByNotificationCode";
            return self::searchReturn($connection, $notificationCode);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }
    /***
     * Finds a authorization with a matching authorization credentials
     *
     * @param PagSeguroCredentials $credentials
     * @param array $options
     * @return PagSeguroAuthorization a authorization object
     * @see PagSeguroAuthorization
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchAuthorizations(PagSeguroCredentials $credentials, array $options = null)
    {
        LogPagSeguro::info("PagSeguroAuthorizationSearchService.searchAuthorizations() - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);
        try {
            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrl($connectionData, $options),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );
            return self::searchAuthorizationsReturn($connection);
        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }

    /***
     * Finds a authorization with a matching authorization code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $authorizationCode
     * @return PagSeguroAuthorization a authorization object
     * @see PagSeguroAuthorization
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByReference(PagSeguroCredentials $credentials, $reference)
    {
        LogPagSeguro::info("PagSeguroAuthorizationSearchService.SearchByReference($reference) - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {
            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByReference($connectionData, $reference),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "SearchByReference";
            return self::searchAuthorizationsReturn($connection, $reference);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        }
        catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }

    /**
     * @param PagSeguroHttpConnection $connection
     * @param string $authorizationCode
     * @return bool|mixed|string
     * @throws PagSeguroServiceException
     */
    private function searchReturn($connection, $code)
    {
        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());
        switch ($httpStatus->getType()) {
            case 'OK':
                $authorization = PagSeguroAuthorizationParser::readAuthorization($connection->getResponse());
                LogPagSeguro::info(
                    sprintf("PagSeguroAuthorizationSearchService.%s(code=$code) - end ", self::$logService) .
                    $authorization->toString()
                );
                break;
            case 'BAD_REQUEST':
                $errors = PagSeguroAuthorizationParser::readErrors($connection->getResponse());
                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::error(
                    sprintf("PagSeguroAuthorizationSearchService.%s(code=$code) - error ", self::$logService) .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::error(
                    sprintf("PagSeguroAuthorizationSearchService.%s(code=$code) - error ", self::$logService) .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
        }
        return isset($authorization) ? $authorization : false;
    }

    /**
     * @param PagSeguroHttpConnection $connection
     * @return bool|mixed|string
     * @throws PagSeguroServiceException
     */
    private function searchAuthorizationsReturn($connection)
    {
        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());
        switch ($httpStatus->getType()) {
            case 'OK':
                $authorization = PagSeguroAuthorizationParser::readSearchResult($connection->getResponse());

                LogPagSeguro::info(
                    "PagSeguroAuthorizationSearchService.searchAuthorizations() - end " .
                    $authorization->toString()
                );
                break;
            case 'BAD_REQUEST':
                $errors = PagSeguroAuthorizationParser::readErrors($connection->getResponse());
                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::error(
                    "PagSeguroAuthorizationSearchService.searchAuthorizations() - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::error(
                    "PagSeguroAuthorizationSearchService.searchAuthorizations() - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
        }
        return isset($authorization) ? $authorization : false;
    }
}