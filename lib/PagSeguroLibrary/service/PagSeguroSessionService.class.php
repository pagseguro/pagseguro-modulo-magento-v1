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
 * Encapsulates web service calls regarding PagSeguro session requests
 */
class PagSeguroSessionService
{

    /***
     * Build URL for get session.
     * @param PagSeguroConnectionData $connectionData 
     * @return string session URL
     */
    private static function buildSessionURL($connectionData)
    {
        return  $connectionData->getWebserviceUrl() . $connectionData->getSessionUrl();  
    }

    /***
     * Get session for direct payment from webservice 
     * @param $credentials PagSeguroAccountCredentials 
     * @return bool|string
     * @throws Exception|PagSeguroServiceException
     * @throws Exception
     */
    public static function getSession($credentials)
    {
        $connectionData = new PagSeguroConnectionData($credentials, 'sessionService');

        $url = self::buildSessionURL($connectionData)  . "?" . $connectionData->getCredentialsUrlQuery();

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

                    $session = PagSeguroSessionParser::readResult($connection->getResponse());

                    return $session->getId();

                    LogPagSeguro::info(
                        "PagSeguroSessionService.getSession()(" . $session->toString() . ") - end {1}"
                    );
                    break;

                case 'BAD_REQUEST':
                    $errors = PagSeguroSessionParser::readErrors($connection->getStatus());
                    $e = new PagSeguroServiceException($httpStatus, $errors);
                    LogPagSeguro::error(
                        "PagSeguroSessionService.getSession() - error " . 
                        $e->getOneLineMessage()
                    );
                    throw $e;
                    break;

                default:

                    $e = new PagSeguroServiceException($httpStatus);
                    LogPagSeguro::error(
                        "PagSeguroSessionService.getSession() - error " . 
                        $e->getOneLineMessage() 
                    );
                    throw $e;
                    break;
            }
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            LogPagSeguro::error("Exception: " . $e->getMessage());
            throw $e;
        }
    }

}
