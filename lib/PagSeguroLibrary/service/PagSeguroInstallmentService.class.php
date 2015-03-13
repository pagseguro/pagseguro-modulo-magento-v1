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
 * Encapsulates web service calls regarding PagSeguro installment requests
 */
class PagSeguroInstallmentService
{

    /***
     * Build URL for get installments.
     * @param PagSeguroConnectionData $connectionData
     * @return string of url for connection with webservice.
     */
    private static function buildInstallmentURL($connectionData)
    {
        return $connectionData->getBaseUrl() . $connectionData->getInstallmentUrl();       
    }

    /***
     * Get from webservice installments for direct payment.
     * @param PagSeguroAccountCredentials $credentials
     * @param mixed $session ID
     * @param float $amount
     * @param string $cardBrand
     * @return bool|string
     * @throws Exception|PagSeguroServiceException
     * @throws Exception
     */
    public static function getInstallments(
        $credentials, 
        $session, 
        $amount, 
        $cardBrand)
    {

        $connectionData = new PagSeguroConnectionData($credentials, 'installmentService');

        $url = self::buildInstallmentURL($connectionData) . 
                "?sessionId=" . $session .
                "&amount=". $amount .
                "&creditCardBrand=" . $cardBrand;

        LogPagSeguro::info(
                "PagSeguroInstallmentService.getInstallments(".$amount.",".$cardBrand.") - begin"
            );

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                $url,
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

            switch ($httpStatus->getType()) {

                case 'OK':
                    $installments = PagSeguroInstallmentParser::readInstallments($connection->getResponse());

                    if (is_array($installments)) {

                        LogPagSeguro::info(
                            "PagSeguroInstallmentService.getInstallments() - end {1}"
                        );

                    } else {

                        LogPagSeguro::info(
                            "PagSeguroInstallmentService.getInstallments() - error" .
                            $installments->message
                        );

                        throw new Exception($installments->message);
                    }

                    break;

                case 'BAD_REQUEST':
                    $errors = PagSeguroInstallmentParser::readErrors($connection->getResponse());
                    $e = new PagSeguroServiceException($httpStatus, $errors);
                    LogPagSeguro::error(
                        "PagSeguroInstallmentService.getInstallments() - error " .
                        $e->getOneLineMessage()
                    );
                    throw $e;
                    break;

                default:
                    $e = new PagSeguroServiceException($httpStatus);
                    LogPagSeguro::error(
                        "PagSeguroInstallmentService.getInstallments() - error " .
                        $e->getOneLineMessage()
                    );
                    throw $e;
                    break;

            }
            return (isset($installments) ? $installments : false);

        } catch (PagSeguroServiceException $e) {
            throw $e;
        }
        catch (Exception $e) {
            LogPagSeguro::error("Exception: " . $e->getMessage());
            throw $e;
        }
    }
}
