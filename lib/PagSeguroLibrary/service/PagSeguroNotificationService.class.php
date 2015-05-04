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
 * Encapsulates web service calls regarding PagSeguro notifications
 */
class PagSeguroNotificationService
{

    /***
     *
     */
    const SERVICE_NAME = 'notificationService';

    private static $logService;

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param $notificationCode
     * @return string
     */
    private static function buildTransactionNotificationUrl(PagSeguroConnectionData $connectionData, $notificationCode)
    {
        $url = $connectionData->getServiceUrl();
        return "{$url}/{$notificationCode}/?" . $connectionData->getCredentialsUrlQuery();
    }

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param $notificationCode
     * @return string
     */
    private static function buildAuthorizationNotificationUrl(PagSeguroConnectionData $connectionData, $notificationCode)
    {
        $url = $connectionData->getWebserviceUrl() . '/' . $connectionData->getResource('applicationPath');
        return "{$url}/{$notificationCode}/?" . $connectionData->getCredentialsUrlQuery();
    }

    /***
     * Returns a transaction from a notification code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $notificationCode
     * @throws PagSeguroServiceException
     * @throws Exception
     * @return PagSeguroTransaction
     * @see PagSeguroTransaction
     */
    public static function checkTransaction(PagSeguroCredentials $credentials, $notificationCode)
    {

        LogPagSeguro::info("PagSeguroNotificationService.CheckTransaction(notificationCode=$notificationCode) - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildTransactionNotificationUrl($connectionData, $notificationCode),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            $transaction = PagSeguroTransactionParser::readTransaction($connection->getResponse());

            self::$logService = "CheckTransaction";
            return self::searchReturn($connection, $transaction, $notificationCode);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }

    /***
     * Returns a authorization from a notification code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $notificationCode
     * @throws PagSeguroServiceException
     * @throws Exception
     * @return PagSeguroAuthorization
     * @see PagSeguroAuthorization
     */
    public static function checkAuthorization(PagSeguroCredentials $credentials, $notificationCode)
    {

        LogPagSeguro::info(
            "PagSeguroNotificationService.CheckAuthorization(notificationCode=$notificationCode) - begin"
        );

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildAuthorizationNotificationUrl($connectionData, $notificationCode),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            $authorization = PagSeguroAuthorizationParser::readAuthorization($connection->getResponse());
            self::$logService = "CheckAuthorization";
            return self::searchReturn($connection, $authorization, $notificationCode);

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
     * @param string $code
     * @return bool|mixed|string
     * @throws PagSeguroServiceException
     */
    private function searchReturn($connection, $parsers, $code)
    {
        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

        switch ($httpStatus->getType()) {

            case 'OK':

                LogPagSeguro::info(
                    sprintf("PagSeguroNotificationService.%s(notificationCode=$code) - end ", self::$logService) .
                    $parsers->toString() . ")"
                );
                break;

            case 'BAD_REQUEST':

                $errors = PagSeguroServiceParser::readErrors($connection->getResponse());

                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::info(
                    sprintf("PagSeguroNotificationService.%s(notificationCode=$code) - error ", self::$logService) .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;

            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::info(
                    sprintf("PagSeguroNotificationService.%s(notificationCode=$code) - error ", self::$logService) .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
        }
        return isset($parsers) ? $parsers : null;
    }

}
