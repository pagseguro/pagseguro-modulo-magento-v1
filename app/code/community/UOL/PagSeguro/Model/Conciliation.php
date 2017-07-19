<?php

/**
 * Created by PhpStorm.
 * User: thiago.medeiros
 * Date: 28/06/2017
 * Time: 16:25
 */
class UOL_PagSeguro_Model_Conciliation
{
    /**
     * UOL_PagSeguro_Model_Conciliation constructor.
     */
    public function __construct()
    {
        $this->library = new UOL_PagSeguro_Model_Library();
    }

    /**
     * @param array $options
     *
     * @return null|string
     */
    public function searchByDate($options = [])
    {
        $response = null;
        try {
            $response = \PagSeguro\Services\Transactions\Search\Date::search(
                $this->library->getAccountCredentials(),
                $options
            );
        } catch (Exception $exception) {
            \PagSeguro\Resources\Log\Logger::error($exception); //TODO add log function in helpers
            Mage::logException($exception);
        }

        return $response;
    }
}