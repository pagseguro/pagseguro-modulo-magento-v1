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
use Mage_Payment_Model_Method_Abstract as MethodAbstract;

/**
 * PagSeguro installments model
 */
class UOL_PagSeguro_Model_InstallmentsMethod extends MethodAbstract
{
    protected $_code = 'pagseguro';
    /**
     * @var UOL_PagSeguro_Model_Library
     */
    private $library;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->helper = Mage::helper('pagseguro');
        $this->library = new UOL_PagSeguro_Model_Library();
    }

    /**
     * Get the bigger installments list returned by the PagSeguro service
     *
     * @param mixed  $amount
     * @param string $brand
     *
     * @return array | false
     */
    public function create($amount, $brand = '')
    {
        $this->helper = Mage::helper('pagseguro');
        try {
            $installments = PagSeguro\Services\Installment::create(
                $this->library->getAccountCredentials(),
                [
                    'amount' => $amount,
                    'card_brand' => $brand
                ]
            );
            $format = $this->output($installments, true);

            return $format['installments'];
        } catch (Exception $exception) {
            Mage::log($exception->getMessage());

            return false;
        }
    }

    /**
     * Return a formated output of installments
     *
     * @param array $installments
     * @param bool  $maxInstallment
     *
     * @return array
     */
    private function output($installments, $maxInstallment)
    {
        return ($maxInstallment) ?
            $this->formatOutput($this->getMaxInstallment($installments->getInstallments())) :
            $this->formatOutput($installments);
    }

    /**
     * Format the installment to the be show in the view
     *
     * @param  array $installments
     *
     * @return array
     */
    private function formatOutput($installments)
    {
        $response = $this->getOptions();
        foreach ($installments as $installment) {
            $response['installments'][] = $this->formatInstallments($installment);
        }

        return $response;
    }

    /**
     * Format a installment for output
     *
     * @param $installment
     *
     * @return array
     */
    private function formatInstallments($installment)
    {
        return array(
            'quantity'    => $installment->getQuantity(),
            'amount'      => $installment->getAmount(),
            'totalAmount' => PagSeguro\Helpers\Currency::toDecimal($installment->getTotalAmount()),
            'text'        => str_replace('.', ',', $this->getInstallmentText($installment)),
        );
    }

    /**
     * Mount the text message of the installment
     *
     * @param  object $installment
     *
     * @return string
     */
    private function getInstallmentText($installment)
    {
        return sprintf(
            "%s x de R$ %.2f %s juros",
            $installment->getQuantity(),
            $installment->getAmount(),
            $this->getInterestFreeText($installment->getInterestFree()));
    }

    /**
     * Get the string relative to if it is an interest free or not
     *
     * @param string $insterestFree
     *
     * @return string
     */
    private function getInterestFreeText($insterestFree)
    {
        return ($insterestFree == 'true') ? 'sem' : 'com';
    }

    /**
     * Get the bigger installments list in the installments
     *
     * @param array $installments
     *
     * @return array
     */
    private function getMaxInstallment($installments)
    {
        $final = $current = array('brand' => '', 'start' => 0, 'final' => 0, 'quantity' => 0);
        foreach ($installments as $key => $installment) {
            if ($current['brand'] !== $installment->getCardBrand()) {
                $current['brand'] = $installment->getCardBrand();
                $current['start'] = $key;
            }
            $current['quantity'] = $installment->getQuantity();
            $current['end'] = $key;
            if ($current['quantity'] > $final['quantity']) {
                $final = $current;
            }
        }

        return array_slice(
            $installments,
            $final['start'],
            $final['end'] - $final['start'] + 1
        );
    }

    /**
     * Check if installments show is enabled
     */
    public function enabled()
    {
        return (Mage::getStoreConfig('payment/pagseguro/installments') == 1) ?
            true :
            false;
    }
}
