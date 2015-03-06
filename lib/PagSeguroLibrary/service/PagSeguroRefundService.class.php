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
 * Encapsulates web service calls regarding PagSeguro refund requests
 */
class PagSeguroRefundService
{

    const SERVICE_NAME = 'refundService';

    private static function buildRefundURL($connectionData, $transactionCode, $refundValue = null)
    {
        if (is_null($refundValue)) {
            return  $connectionData->getServiceUrl() . '?' . $connectionData->getCredentialsUrlQuery()
                    . "&transactionCode=" . $transactionCode;
        } else {
            return  $connectionData->getServiceUrl() . '?' . $connectionData->getCredentialsUrlQuery()
                    . "&transactionCode=" . $transactionCode . "&refundValue=" . $refundValue;
        }
    }

    public static function createRefundRequest(
        PagSeguroCredentials $credentials,
        $transactionCode,
        $refundValue = null
    ){

        LogPagSeguro::info("PagSeguroRefundService.Register(".$transactionCode.") - begin");
        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        if (is_null($refundValue)) {
            $url = self::buildRefundURL($connectionData, $transactionCode);
        } else {
            $url = self::buildRefundURL($connectionData, $transactionCode, $refundValue);
        }

        try {

           $connection = new PagSeguroHttpConnection();
           $connection->post(
                $url,
                array(),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
           );

            $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

            switch ($httpStatus->getType()) {
                case 'OK':

                    $result = PagSeguroRefundParser::readSuccessXml($connection->getResponse());
                    LogPagSeguro::info(
                        "PagSeguroRefundService.createRefundRequest(".$result.") - end "
                    );
                    break;
                case 'BAD_REQUEST':
                    $errors = PagSeguroRefundParser::readErrors($connection->getResponse());
                    $err = new PagSeguroServiceException($httpStatus, $errors);
                    LogPagSeguro::error(
                        "PagSeguroRefundService.createRefundRequest() - error " .
                        $err->getOneLineMessage()
                    );
                    throw $err;
                    break;
                default:
                    $err = new PagSeguroServiceException($httpStatus);
                    LogPagSeguro::error(
                        "PagSeguroRefundService.createRefundRequest() - error " .
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