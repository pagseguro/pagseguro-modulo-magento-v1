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
 * Encapsulates web service calls to search for PagSeguro transactions
 */
class PagSeguroTransactionSearchService
{

    /**
     *
     */
    const SERVICE_NAME = 'transactionSearchService';

    private static $logService;

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param $transactionCode
     * @return string
     */
    private static function buildSearchUrlByCode(PagSeguroConnectionData $connectionData, $transactionCode)
    {
        $url = $connectionData->getServiceUrl('v3');
        return "{$url}/{$transactionCode}/?" . $connectionData->getCredentialsUrlQuery();
    }

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param array $searchParams
     * @return string
     */
    private static function buildSearchUrlByDate(PagSeguroConnectionData $connectionData, array $searchParams)
    {
        $url = $connectionData->getServiceUrl('v2');
        $initialDate = $searchParams['initialDate'] != null ? $searchParams['initialDate'] : "";
        $finalDate = $searchParams['finalDate'] != null ? ("&finalDate=" . $searchParams['finalDate']) : "";
        if ($searchParams['pageNumber'] != null) {
            $page = "&page=" . $searchParams['pageNumber'];
        }
        if ($searchParams['maxPageResults'] != null) {
            $maxPageResults = "&maxPageResults=" . $searchParams['maxPageResults'];
        }
        return "{$url}/?" . $connectionData->getCredentialsUrlQuery() .
            "&initialDate={$initialDate}{$finalDate}{$page}{$maxPageResults}";
    }

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param array $searchParams
     * @return string
     */
    private static function buildSearchUrlAbandoned(PagSeguroConnectionData $connectionData, array $searchParams)
    {
        $url = $connectionData->getServiceUrl('v2');

        $initialDate = $searchParams['initialDate'] != null ? $searchParams['initialDate'] : "";
        $finalDate = $searchParams['finalDate'] != null ? ("&finalDate=" . $searchParams['finalDate']) : "";
        if ($searchParams['pageNumber'] != null) {
            $page = "&page=" . $searchParams['pageNumber'];
        }
        if ($searchParams['maxPageResults'] != null) {
            $maxPageResults = "&maxPageResults=" . $searchParams['maxPageResults'];
        }
        return "{$url}/abandoned/?" . $connectionData->getCredentialsUrlQuery() .
            "&initialDate={$initialDate}&finalDate={$finalDate}{$page}{$maxPageResults}";
    }

    /**
     * @param PagSeguroConnectionData $connectionData
     * @param $reference
     * @param null $searchParams
     * @return string
     */
    private static function buildSearchUrlByReference(
        PagSeguroConnectionData $connectionData,
        $reference,
        $searchParams = null
    ){
        $url = $connectionData->getServiceUrl('v2');
        if ($searchParams == null) {
            return "{$url}?" . $connectionData->getCredentialsUrlQuery() . "&reference=" . $reference;
        } else {

            $initialDate = $searchParams['initialDate'] != null ? $searchParams['initialDate'] : "";
            $finalDate = $searchParams['finalDate'] != null ? ("&finalDate=" . $searchParams['finalDate']) : "";
            if ($searchParams['pageNumber'] != null) {
                $page = "&page=" . $searchParams['pageNumber'];
            }
            if ($searchParams['maxPageResults'] != null) {
                $maxPageResults = "&maxPageResults=" . $searchParams['maxPageResults'];
            }

            return "{$url}?" . $connectionData->getCredentialsUrlQuery() . "&reference=" . $reference
                   . "&initialDate={$initialDate}&finalDate={$finalDate}{$page}{$maxPageResults}";
        }
    }

    /***
     * Finds a transaction with a matching transaction code
     *
     * @param PagSeguroCredentials $credentials
     * @param String $transactionCode
     * @return PagSeguroTransaction a transaction object
     * @see PagSeguroTransaction
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchByCode(PagSeguroCredentials $credentials, $transactionCode)
    {

        LogPagSeguro::info("PagSeguroTransactionSearchService.SearchByCode($transactionCode) - begin");

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByCode($connectionData, $transactionCode),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            return self::searchByCodeResult($connection, $transactionCode);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        }
        catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }

    }

    /***
 * Search transactions associated with this set of credentials within a date range
 *
 * @param PagSeguroCredentials $credentials
 * @param integer $pageNumber
 * @param integer $maxPageResults
 * @param String $initialDate
 * @param String $finalDate
 * @return a object of PagSeguroTransactionSerachResult class
 * @see PagSeguroTransactionSearchResult
 * @throws PagSeguroServiceException
 * @throws Exception
 */
    public static function searchByDate(
        PagSeguroCredentials $credentials,
        $pageNumber,
        $maxPageResults,
        $initialDate,
        $finalDate = null
    ) {

        LogPagSeguro::info(
            "PagSeguroTransactionSearchService.SearchByDate(initialDate=" . PagSeguroHelper::formatDate($initialDate) .
            ", finalDate=" . PagSeguroHelper::formatDate($finalDate) . ") - begin"
        );

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        $searchParams = self::buildParams($pageNumber, $maxPageResults, $initialDate, $finalDate);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByDate($connectionData, $searchParams),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "SearchByDate";
            return self::searchResult($connection, $initialDate, $finalDate);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }

    }

    /***
     * Search transactions abandoned associated with this set of credentials within a date range
     *
     * @param PagSeguroCredentials $credentials
     * @param String $initialDate
     * @param String $finalDate
     * @param integer $pageNumber
     * @param integer $maxPageResults
     * @return PagSeguroTransactionSearchResult a object of PagSeguroTransactionSearchResult class
     * @see PagSeguroTransactionSearchResult
     * @throws PagSeguroServiceException
     * @throws Exception
     */
    public static function searchAbandoned(
        PagSeguroCredentials $credentials,
        $pageNumber,
        $maxPageResults,
        $initialDate,
        $finalDate = null
    ) {

        LogPagSeguro::info(
            "PagSeguroTransactionSearchService.searchAbandoned(initialDate=" .
            PagSeguroHelper::formatDate($initialDate) . ", finalDate=" .
            PagSeguroHelper::formatDate($finalDate) . ") - begin"
        );

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        $searchParams = self::buildParams($pageNumber, $maxPageResults, $initialDate, $finalDate);

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlAbandoned($connectionData, $searchParams),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "searchAbandoned";
            return self::searchResult($connection, $initialDate, $finalDate);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        }
        catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }

    }

    /**
     * @param PagSeguroCredentials $credentials
     * @param $reference
     * @param null $initialDate
     * @param null $finalDate
     * @param null $pageNumber
     * @param null $maxPageResults
     * @throws Exception
     * @throws PagSeguroServiceException
     */
    public static function searchByReference(
        PagSeguroCredentials $credentials,
        $reference,
        $initialDate = null,
        $finalDate = null,
        $pageNumber = null,
        $maxPageResults = null
    ) {

        LogPagSeguro::info(
            "PagSeguroTransactionSearchService.SearchByReference(reference=".$reference.") - begin"
        );

        $connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        if ($initialDate)
            $searchParams = self::buildParams($pageNumber, $maxPageResults, $initialDate, $finalDate);
        else
            $searchParams = null;

        try {

            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildSearchUrlByReference(
                    $connectionData,
                    $reference,
                    $searchParams
                ),
                $connectionData->getServiceTimeout(),
                $connectionData->getCharset()
            );

            self::$logService = "SearchByReference";
            return self::searchResult($connection);

        } catch (PagSeguroServiceException $err) {
            throw $err;
        } catch (Exception $err) {
            LogPagSeguro::error("Exception: " . $err->getMessage());
            throw $err;
        }

    }

    /**
     * @param $pageNumber
     * @param $maxPageResults
     * @param $initialDate
     * @param null $finalDate
     * @return array
     */
    private function buildParams($pageNumber, $maxPageResults, $initialDate, $finalDate = null)
    {
        $searchParams = array(
            'initialDate' => PagSeguroHelper::formatDate($initialDate),
            'pageNumber' => $pageNumber,
            'maxPageResults' => $maxPageResults
        );

        $searchParams['finalDate'] = $finalDate ? PagSeguroHelper::formatDate($finalDate) : null;

        return $searchParams;
    }

    /**
     * @param $connection
     * @param $code
     * @return bool|PagSeguroTransaction
     * @throws PagSeguroServiceException
     */
    private function searchByCodeResult($connection, $code)
    {
        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

        switch ($httpStatus->getType()) {

            case 'OK':
                $transaction = PagSeguroTransactionParser::readTransaction($connection->getResponse());
                LogPagSeguro::info(
                    "PagSeguroTransactionSearchService.SearchByCode(transactionCode=$code) - end " .
                    $transaction->toString()
                );
                break;

            case 'BAD_REQUEST':
                $errors = PagSeguroTransactionParser::readErrors($connection->getResponse());
                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::error(
                    "PagSeguroTransactionSearchService.SearchByCode(transactionCode=$code) - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;

            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::error(
                    "PagSeguroTransactionSearchService.SearchByCode(transactionCode=$code) - error " .
                    $err->getOneLineMessage()
                );
                throw $err;
                break;
        }
        return isset($transaction) ? $transaction : false;
    }

    /**
     * @param $connection
     * @param null $initialDate
     * @param null $finalDate
     * @return bool|PagSeguroTransactionSearchResult
     * @throws PagSeguroServiceException
     */
    private function searchResult($connection, $initialDate = null, $finalDate = null)
    {

        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());

        switch ($httpStatus->getType()) {

            case 'OK':

                $searchResult = PagSeguroTransactionParser::readSearchResult($connection->getResponse());

                LogPagSeguro::info(
                    sprintf("PagSeguroTransactionSearchService.%s(initialDate=" .
                    PagSeguroHelper::formatDate($initialDate) . ", finalDate=" .
                    PagSeguroHelper::formatDate($finalDate) . ") - end ", self::$logService) . $searchResult->toString()
                );
                break;

            case 'BAD_REQUEST':
                $errors = PagSeguroTransactionParser::readErrors($connection->getResponse());
                $err = new PagSeguroServiceException($httpStatus, $errors);
                LogPagSeguro::error(
                    sprintf("PagSeguroTransactionSearchService.%s(initialDate=" .
                    PagSeguroHelper::formatDate($initialDate) . ", finalDate=" .
                    PagSeguroHelper::formatDate($finalDate) . ") - end ", self::$logService) . $err->getOneLineMessage()
                );
                throw $err;
                break;

            default:
                $err = new PagSeguroServiceException($httpStatus);
                LogPagSeguro::error(
                    sprintf("PagSeguroTransactionSearchService.%s(initialDate=" .
                    PagSeguroHelper::formatDate($initialDate) . ", finalDate=" .
                    PagSeguroHelper::formatDate($finalDate) . ") - end ",  self::$logService) . $err->getOneLineMessage()
                );
                throw $err;
                break;

        }

        return isset($searchResult) ? $searchResult : false;
    }
}
