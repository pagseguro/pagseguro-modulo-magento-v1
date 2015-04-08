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

class AddressConfig
{
	/**
	 * Remove the space at the end of the phrase, cut a piece of the phrase
	 * @param string $e - Data to be ordained
	 * @return Returns the phrase removed last  space, or a piece of phrase
	 */
    private static function endTrim($e)
    {
        return preg_replace('/^\W+|\W+$/', '', $e);
    }
	
	/**
	 * Sort the data reported
	 * @param string $text - Text to be ordained
	 * @return array - Returns an array with the sorted data 
	 */
    private static function sortData($text)
    {
        $broken = preg_split('/[-,\\n]/', $text);

        for ($i = 0; $i < strlen($broken[0]); $i++) {
            if (is_numeric(substr($broken[0], $i, 1))) {
                return array(
                    substr($broken[0], 0, $i),
                    substr($broken[0], $i),
                    $broken[1]
                    );
            }
        }

        $text = preg_replace('/\s/', ' ', $text);
        $find = substr($text, -strlen($text));
		
        for ($i  =0; $i < strlen($text); $i++) {
            if (is_numeric(substr($find, $i, 1))) {
                return array(
                    substr($text, 0, -strlen($text)+$i),
                    substr($text, -strlen($text)+$i),
                    ''
                    );
            }
        }

        return array($text, '', '');
    }

	/**
	 * Treatment this address before being sent
	 * @param string $fullAddress - Full address to treatment
	 * @return array - Returns address of treatment in an array
	 */
    public static function treatmentAddress($fullAddress)
    {
        $address = $fullAddress;
        $number  = 's/nº';
        $complement = '';
        $district = '';

        $broken = preg_split('/[-,\\n]/', $fullAddress);

        if (sizeof($broken) == 4) {
            list($address, $number, $complement, $district) = $broken;
        } elseif (sizeof($broken) == 3) {
            list($address, $number, $complement) = $broken;
        } elseif (sizeof($broken) == 2 || sizeof($broken) == 1) {
            list($address, $number, $complement) = self::sortData($fullAddress);
        } else {
            $address = $fullAddress;
        }

        return array(
            self::endTrim(substr($address, 0, 69)),
            self::endTrim($number),
            self::endTrim($complement),
            self::endTrim($district)
        );
    }
}