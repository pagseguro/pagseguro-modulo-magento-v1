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
 * Represents a page of authorizations returned by the authorization search service
 */
class PagSeguroAuthorizationSearchResult
{

    /***
     * Date/time when this search was executed
     */
    private $date;

    /***
     * Authorizations in the current page
     */
    private $resultsInThisPage;

    /***
     * Total number of pages
     */
    private $totalPages;

    /***
     * Current page.
     */
    private $currentPage;

    /***
     * Authorization summaries in this page
     */
    private $authorizations;

    /***
     * @return the current page number
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /***
     * Sets the current page number
     * @param integer $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /***
     * @return the date/time when this search was executed
     */
    public function getDate()
    {
        return $this->date;
    }

    /***
     * Set the date/time when this search was executed
     * @param date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /***
     * @return the number of authorizations summaries in the current page
     */
    public function getResultsInThisPage()
    {
        return $this->resultsInThisPage;
    }

    /***
     * Sets the number of authorizations summaries in the current page
     *
     * @param resultsInThisPage
     */
    public function setResultsInThisPage($resultsInThisPage)
    {
        $this->resultsInThisPage = $resultsInThisPage;
    }

    /***
     * @return the total number of pages
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /***
     * Sets the total number of pages
     *
     * @param totalPages
     */
    public function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;
    }

    /***
     * @return PagSeguroAuthorizations the authorizations summaries in this page
     * @see PagSeguroAuthorizations
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /***
     * Sets the authorizations summaries in this page
     * @param PagSeguroAuthorization $authorizations
     */
    public function setAuthorizations($authorizations)
    {
        $this->authorizations = $authorizations;
    }

    /***
     * @return String a string that represents the current object
     */
    public function toString()
    {
        $authorizations = array();

        $authorizations['Date'] = $this->date;
        $authorizations['CurrentPage'] = $this->currentPage;
        $authorizations['TotalPages'] = $this->totalPages;
        $authorizations['Transactions in this page'] = $this->resultsInThisPage;

        return "PagSeguroAuthorizationsSearchResult: " . implode(' - ', $authorizations);

    }
}
