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
 * Class PagSeguroInstallmentParser
 */
class PagSeguroInstallmentParser extends PagSeguroServiceParser
{

    /***
     * @param $installment HttpGet Installments
     * @return mixed
     */
    public static function readInstallments($str_json)
    {
    	
    	if (self::decode(preg_replace('/[^a-z_\:\{}\ \"\.\,\-0-9]/i', '', $str_json))) {
    		$arr = self::decode(preg_replace('/[^a-z_\:\{}\ \"\.\,\-0-9]/i', '', $str_json));
    	} else {
    		$arr = self::decode($str_json);
    	}

    	if (!isset($arr->errors)) {
	    	$brand = key($arr->installments);

	    	foreach ($arr->installments->$brand as $key => $installment) {

	    		$installment->cardBrand = $brand;	

	    		$installments[] = new PagSeguroInstallments($installment);	
	    	}
	    	
	    	return $installments;
    	} else {
    		return self::readError($arr->errors);
    	}
    }

    /***
     * @param $error Installment error
     * @return object()
     */
    private static function readError($error)
    {
    	$err = new stdClass();
    	$err->message = key($error);
    	$err->status = true;

    	return $err;
    }

    /***
     * @param $installments Installments
     * @return object installments
     */
    private static function decode($installments)
    {	
    	return json_decode($installments);
    }

}
