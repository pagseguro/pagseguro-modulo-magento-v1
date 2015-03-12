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
 * Encapsulates web service calls regarding PagSeguro cancel requests
 */
class PagSeguroCancelService
{

    /**
     *
     */
    const SERVICE_NAME = 'cancelService';

    /**
     * @param $connectionData
     * @param $transactionCode
     * @return string
     */
    private static function buildCancelURL($connectionData, $transactionCode)
    {
            return  $connectionData->getServiceUrl() . '?' . $connectionData->getCredentialsUrlQuery()
                    . "&transactionCode=" . $transactionCode;
    }

    /**
     * @param PagSeguroCredentials $credentials
     * @param $transactionCode
     * @throws Exception
     * @throws PagSeguroServiceException
     */
    public static function createRequest(
        PagSeguroCredentials $credentials,
        $transactionCode
    ){

        LogPagSeguro::info("PagSeguroCancelService.Register(".$transactionCode.") - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {

           $connection = new PagSeguroHttpConnection();
           $connection->post(
                self::buildCancelURL($connectionData, $transactionCode),
                array(),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
           );

            $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

            switch ($httpStatus->getType()) {
                case 'OK':

                    $result = PagSeguroCancelParser::readSuccessXml($connection->getResponse());
                    LogPagSeguro::info(
                        "PagSeguroCancelService.createRequest(".$result.") - end "
                    );
                    break;
                case 'BAD_REQUEST':
                    $errors = PagSeguroCancelParser::readErrors($connection->getResponse());
                    $err = new PagSeguroServiceException($httpStatus, $errors);
                    LogPagSeguro::error(
                        "PagSeguroCancelService.createRequest() - error " .
                        $err->getOneLineMessage()
                    );
                    throw $err;
                    break;
                default:
                    $err = new PagSeguroServiceException($httpStatus);
                    LogPagSeguro::error(
                        "PagSeguroCancelService.createRequest() - error " .
                        $err->getOneLineMessage()
                    );
                    throw $err;
                    break;
            }
            return isset($result) ? $result : false;

        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }
    }
}