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
 * Represent available payment method groups.
 */
class PagSeguroPaymentMethodGroups
{

    private static $availableGroupList = array(
        'CREDIT_CARD' => 'Pagamento com Cartão de Crédito',
        'BOLETO' => 'Pagamento com Boleto',
        'EFT' => 'Pagamento com Depósito Online',
        'BALANCE' => 'Pagamento com Saldo PagSeguro',
        'DEPOSIT' => 'Pagamento com Depósito'
    );

    /***
     * Get available payment method groups list for payment method config use in PagSeguro transactions
     * @return array
     */
    public static function getAvailableGroupList()
    {
        return self::$availableGroupList;
    }

    /***
     * Check if payment method groups is available for PagSeguro
     * @param string $itemKey
     * @return boolean
     */
    public static function isKeyAvailable($groupKey)
    {
        $groupKey = strtoupper($groupKey);
        return (isset(self::$availableGroupList[$groupKey]));
    }

    /***
     * Gets group description by key
     * @param string $groupKey
     * @return string
     */
    public static function geDescriptionByKey($groupKey)
    {
        $groupKey = strtoupper($groupKey);
        if (isset(self::$availableGroupList[$groupKey])) {
            return self::$availableGroupList[$groupKey];
        } else {
            return false;
        }
    }

    /***
     * Gets group type by description
     * @param string $groupDescription
     * @return string
     */
    public static function getGroupByDescription($groupDescription)
    {
        return array_search(strtolower($groupDescription), array_map('strtolower', self::$availableGroupList));
    }
}
