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
 * Encapsulates web service calls regarding PagSeguro authorization request
 */
class PagSeguroAuthorizationService
{

    /***
     *
     */
    const SERVICE_NAME = 'authorizationService';

    /***
     * @param PagSeguroConnectionData $connectionData
     * @return string
     */
    private static function buildAuthorizationUrl(PagSeguroConnectionData $connectionData)
    {
        return $connectionData->getServiceUrl() . $connectionData->getResource('requestUrl') . '?';
    }

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param string $code
     * @return string
     */
    private static function buildAuthorizationApprovalUrl(PagSeguroConnectionData $connectionData, $code)
    {
        return $connectionData->getBaseUrl() . $connectionData->getResource('approvalUrl') . '?code=' . $code;
    }

    /***
     * @param PagSeguroCredentials $credentials
     * @param PagSeguroAuthorizationRequest $authorizationRequest
     * @param bool $onlyAuthorizationCode
     * @return bool|string
     * @throws Exception
     */
    public static function createAuthorizationRequest(
        PagSeguroCredentials $credentials,
        PagSeguroAuthorizationRequest $authorizationRequest,
        $onlyAuthorizationCode
    ){

        LogPagSeguro::info("PagSeguroAuthorizationService.Register(" . $authorizationRequest->toString() . ") - begin");

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->post(
                self::buildAuthorizationUrl($connectionData),
                PagSeguroAuthorizationParser::getData($authorizationRequest, $credentials),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            return self::authorizationReturn(
                $connection, $authorizationRequest, $connectionData, $onlyAuthorizationCode);

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
     * @param PagSeguroAuthorizationRequest $authorizationRequest
     * @param PagSeguroConnectionData $connectionData
     * @param null $onlyAuthorizationCode
     * @return bool|mixed|string
     * @throws PagSeguroServiceException
     */
    private static function authorizationReturn(
        PagSeguroHttpConnection $connection,
        PagSeguroAuthorizationRequest $authorizationRequest,
        PagSeguroConnectionData $connectionData,
        $onlyAuthorizationCode = null
    )
    {
        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

        switch ($httpStatus->getType()) {

            case 'OK':
                $authorization = PagSeguroAuthorizationParser::readSuccessXml($connection->getResponse());

                if ($onlyAuthorizationCode) {
                    $authorizationReturn = $authorization->getCode();
                } else {
                    $authorizationReturn = self::buildAuthorizationApprovalUrl($connectionData,
                        $authorization->getCode());
                }
                LogPagSeguro::info(
                    "PagSeguroAuthorizationService.Register(" . $authorizationRequest->toString() . ") - end {1}" .
                    $authorization->getCode()
                );
                break;

            case 'BAD_REQUEST':
                $errors = PagSeguroPaymentParser::readErrors($connection->getResponse());
                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::error(
                    "PagSeguroAuthorizationService.Register(" . $authorizationRequest->toString() . ") - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;

            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::error(
                    "PagSeguroAuthorizationService.Register(" . $authorizationRequest->toString() . ") - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;

        }
        return (isset($authorizationReturn) ? $authorizationReturn : false);
    }
}
