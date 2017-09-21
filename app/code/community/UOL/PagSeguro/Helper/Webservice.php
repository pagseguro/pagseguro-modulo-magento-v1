<?php
/**
 ************************************************************************
 * Copyright [2015] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */

/**
 * Class UOL_PagSeguro_Helper_Webservice
 */
class UOL_PagSeguro_Helper_Webservice extends UOL_PagSeguro_Helper_Data
{
    /**
     * @var UOL_PagSeguro_Model_Library
     */
    private $library;

    /**
     * UOL_PagSeguro_Helper_Webservice constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->library = new UOL_PagSeguro_Model_Library();
    }

    /**
     * @param     $initialDate
     * @param int $page
     * @param int $resultsInPage
     *
     * @return \PagSeguro\Services\Transactions\Search\Abandoned
     */
    public function abandonedRequest($initialDate, $page = 1, $resultsInPage = 1000)
    {
        return new \PagSeguro\Services\Transactions\Search\Abandoned($this->library->getAccountCredentials(),
            array('initial_date' => $initialDate, 'max_per_page' => $resultsInPage, 'page' => $page));
    }

    /**
     * @param $transactionCode
     *
     * @return \PagSeguro\Services\Transactions\Cancel
     */
    public function cancelRequest($transactionCode)
    {
        return \PagSeguro\Services\Transactions\Cancel::create(
            $this->library->getAccountCredentials(),
            $transactionCode
        );
    }

    /**
     * @param $transactionCode
     *
     * @return \PagSeguro\Services\Transactions\Notification
     */
    public function getNotification()
    {
        return \PagSeguro\Services\Transactions\Notification::check(
            $this->library->getAccountCredentials()
        );
    }

    /**
     * @param $page
     * @param $maxPageResults
     * @param $initialDate
     *
     * @return null|string
     * @throws Exception
     */
    public function getPagSeguroAbandonedList($page, $maxPageResults, $initialDate)
    {
        $response = null;
        try {
            $response = \PagSeguro\Services\Transactions\Search\Abandoned::search($this->library->getAccountCredentials(),
                array('initial_date' => $initialDate, 'page' => $page, 'max_per_page' => $maxPageResults));
        } catch (Exception $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }

        return $response;
    }

    /**
     * @param $page
     * @param $maxPageResults
     * @param $initialDate
     *
     * @return null|string
     * @throws Exception
     */
    public function getTransactionsByDate($page, $maxPageResults, $initialDate)
    {
        $response = null;
        try {
            $response = \PagSeguro\Services\Transactions\Search\Date::search(
                $this->library->getAccountCredentials(),
                array('initial_date' => $initialDate, 'page' => $page, 'max_per_page' => $maxPageResults)
            );
        } catch (Exception $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED' || $e->getCode() == 401) {
                throw new Exception($e->getMessage());
            }
        }

        return $response;
    }

    /**
     * @param $transactionCode
     *
     * @return \PagSeguro\Services\Transactions\Refund
     */
    public function refundRequest($transactionCode, $refundValue = null)
    {
        return \PagSeguro\Services\Transactions\Refund::create(
            $this->library->getAccountCredentials(),
            $transactionCode,
            $refundValue
        );
    }

    /**
     * @param $class
     * @param $transactionCode
     *
     * @return mixed|\PagSeguro\Parsers\Cancel\Response|string
     * @throws Exception
     */
    public function requestPagSeguroService($class, $transactionCode)
    {
        try {
            if ($class == 'UOL_PagSeguro_Helper_Canceled') {
                return \PagSeguro\Services\Transactions\Cancel::create($this->library->getAccountCredentials(),
                    $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Adminhtml_RefundController') {
                return \PagSeguro\Services\Transactions\Refund::create($this->library->getAccountCredentials(),
                    $transactionCode);
            } elseif ($class == 'UOL_PagSeguro_Model_NotificationMethod') {
                return \PagSeguro\Services\Transactions\Notification::check($this->library->getAccountCredentials());
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
