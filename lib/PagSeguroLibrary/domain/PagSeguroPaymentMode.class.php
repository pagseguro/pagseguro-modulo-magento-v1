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
 * Defines a list of known payment mode types.
 */
class PagSeguroPaymentMode {

    private $modeList = array(
        'DEFAULT' => 'default',
        'GATEWAY' => 'gateway'
        );

    /***
     * Payment mode value
     * Example: DEFAULT
     * @var string
     */
    private  $value;

    /***
     * Initializes a new instance of the PagSeguroPaymentMode class
     * @param array|object $value
     */
    public function __construct($value) {

        if (array_key_exists($value, $this->modeList)) {
            $this->setValue($this->modeList[$value]);
        } else {
           throw new Exception("Payment mode not found");
        }
    }

    /***
     * Set the payment mode value
     * @param string value
     */
    private function setValue($value) {
        $this->value = $value;
    }

    /***
     * @return string $value of payment mode value
     */
    public function getValue() {
        return $this->value;
    }

    /***
     * Find a PagSeguroPaymentMode in a list
     * @param value
     * @return PagSeguroPaymentMode the corresponding to the informed value
     */
    public static function fromValue($value) {
        try {
            return array_search(strtoupper($value), $this->modeList);
        } catch (Exception $e) {
            return NULL;
        }
    }
}