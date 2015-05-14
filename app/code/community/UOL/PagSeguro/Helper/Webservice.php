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
    private $credentials;
    private $service;
    private $cancelService;
    private $refundService;
    private $notificationService;

    /**
     * Construct
     */
    public function __construct()
    {
        include_once (Mage::getBaseDir('lib') . '/PagSeguroLibrary/PagSeguroLibrary.php');
        $this->credentials = $this->requestPaymentMethod()->getCredentialsInformation();
        $this->service = new PagSeguroTransactionSearchService();
        $this->cancelService = new PagSeguroCancelService();
        $this->refundService = new PagSeguroRefundService();
        $this->notificationService = new PagSeguroNotificationService();
    }

    public function requestPagSeguroService($class, $transactionCode)
    {
        try {
            if ($class == 'UOL_PagSeguro_Helper_Canceled') {
                $response = $this->cancelService->createRequest($this->credentials, $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Helper_Refund') {
                $response = $this->refundService->createRefundRequest($this->credentials, $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Model_NotificationMethod') {
                $response = $this->notificationService->checkTransaction($this->credentials, $transactionCode);
            }

            return $response;
        } catch (PagSeguroServiceException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getPagSeguroAbandonedList()
    {
        $this->initialDate = $this->getDateSubtracted($this->days);

        try {
            $response = $this->service->searchAbandoned($this->credentials, 1, 1000, $this->getInitialDate());
            $transactions = $response->getTransactions();

            return $transactions;
        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function getTransactionService($type, $page, $nRecords)
    {
        try {
            $initialDate = $this->getInitialDate();
            $finalDate   = $this->getFinalDate();
            $credentials = $this->credentials;

            if ($type == 'searchByDate') {
                $response = $this->service->searchByDate($credentials, $page, $nRecords, $initialDate, $finalDate);
            }

            return $response;
        } catch (PagSeguroServiceException $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }
    }
}
