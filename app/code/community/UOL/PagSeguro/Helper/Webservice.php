<?php

/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

use UOL_PagSeguro_Helper_Data as HelperData;

class UOL_PagSeguro_Helper_Webservice extends HelperData
{
    /**
     * @var PagSeguroAccountCredentials
     */
    private $credentials;
    
    /**
     * @var PagSeguroTransactionSearchService
     */
    private $searchService;
    
    /**
     * @var PagSeguroCancelService
     */
    private $cancelService;
    
    /**
     * @var PagSeguroRefundService
     */
    private $refundService;
    
    /**
     * @var PagSeguroNotificationService
     */
    private $notificationService;
    
    /**
     * @var unknown
     */
    private $initialDate;

    /**
     * Construct
     */
    public function __construct()
    {
        include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
        $this->credentials = $this->paymentModel()->getCredentialsInformation();
        $this->searchService = new PagSeguroTransactionSearchService();
        $this->cancelService = new PagSeguroCancelService();
        $this->refundService = new PagSeguroRefundService();
        $this->notificationService = new PagSeguroNotificationService();
    }
    
    /**
     * Request a PagSeguro Service
     * @param $class string type of service
     * @param $transactionCode code for this transaction
     */
    public function requestPagSeguroService($class, $transactionCode)
    {
        try {
            if ($class == 'UOL_PagSeguro_Helper_Canceled') {
                return $this->cancelService->createRequest($this->credentials, $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Adminhtml_RefundController') {
                return $this->refundService->createRefundRequest($this->credentials, $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Model_NotificationMethod') {
                return $this->notificationService->checkTransaction($this->credentials, $transactionCode);
            }

        } catch (PagSeguroServiceException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get a list with abandoned transactions
     * @throws Exception
     */
    public function getPagSeguroAbandonedList()
    {
        $this->initialDate = $this->getDateSubtracted($this->days);

        try {
            $response = $this->searchService->searchAbandoned($this->credentials, 1, 1000, $this->getInitialDate());

            return  $response->getTransactions();

        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }
    }
    
    /**
     * Get a transaction information
     * @param $type string type of transaction service
     * @param $page int quantity of pages in result
     * @param $nRecords int quantity of records to return in result
     */
    public function getTransactionService($type, $page, $nRecords)
    {

        try {
            if ($type == 'searchByDate') {
                return $this->searchService->searchByDate(
                    $this->credentials,
                    $page,
                    $nRecords,
                    $this->getInitialDate()
                );
            }
        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Request abandoned PagSeguroTransactions
     * @param DateTime $initialDate
     * @param string $page
     * @param string $resultsInPage
     * @return PagSeguroTransactionSearchResult
     */
    public function abandonedRequest($initialDate, $page = null, $resultsInPage = null)
    {
        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($resultsInPage)) {
            $resultsInPage = 1000;
        }

        return $this->searchService->searchAbandoned($this->credentials, $page, $resultsInPage, $initialDate);

    }

    /**
     * Request a PagSeguro refund
     * @param string $transactionCode
     * @return Ambigous <boolean, NULL, string, unknown>
     */
    public function refundRequest($transactionCode)
    {
        return $this->refundService->createRefundRequest($this->credentials, $transactionCode);
    }

    /**
     * Request a PagSeguro cancel
     * @param string $transactionCode
     * @return Ambigous <boolean, NULL, string, unknown>
     */
    public function cancelRequest($transactionCode)
    {
        return $this->cancelService->createRequest($this->credentials, $transactionCode);
    }

    /**
     * Request a list of  PagSeguraTransaction in a date range
     * @param Int $page
     * @param Int $maxPageResults
     * @param DateTime $initialDate
     * @return PagSeguroTransactionSearchResult
     */
    public function getTransactionsByDate(
        Int $page,
        Int $maxPageResults,
        DateTime $initialDate
    ) {
        return $this->searchService->searchByDate($this->credentials, $page, $maxPageResults, $initialDate);
    }

    /**
     * Request a list of  PagSeguraTransaction by notificationCode
     * @param String $transactionCode
     * @return PagSeguroTransactionSearchResult
     */
    public function getNotification($transactionCode)
    {
        return $this->notificationService->checkTransaction($this->credentials, $transactionCode);
    }
}
