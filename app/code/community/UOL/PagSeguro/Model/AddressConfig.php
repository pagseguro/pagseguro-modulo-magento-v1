<?php

/**
************************************************************************
Copyright [2014] [PagSeguro Internet Ltda.]

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
    private static function endtrim($e)
    {
        return preg_replace('/^\W+|\W+$/', '', $e);
    }

    private static function ordenaDados($texto)
    {
        $quebrado=preg_split('/[-,\\n]/', $texto);

        for ($i=0; $i<strlen($quebrado[0]); $i++) {
            if (is_numeric(substr($quebrado[0], $i, 1))) {
                return array(
                    substr($quebrado[0], 0, $i),
                    substr($quebrado[0], $i),
                    $quebrado[1]
                    );
            }
        }

        $texto = preg_replace('/\s/', ' ', $texto);
        $encontrar=substr($texto, -strlen($texto));
		
        for ($i=0; $i<strlen($texto); $i++) {
            if (is_numeric(substr($encontrar, $i, 1))) {
                return array(
                    substr($texto, 0, -strlen($texto)+$i),
                    substr($texto, -strlen($texto)+$i),
                    ''
                    );
            }
        }

        return array($texto, '', '');
    }

    public static function trataEndereco($end)
    {
        $endereco=$end;
        $numero='s/nº';
        $complemento='';
        $bairro='';

        $quebrado=preg_split('/[-,\\n]/', $end);

        if (sizeof($quebrado) == 4) {
            list($endereco, $numero, $complemento, $bairro)=$quebrado;
        } elseif (sizeof($quebrado) == 3) {
            list($endereco, $numero, $complemento) = $quebrado;
        } elseif (sizeof($quebrado) == 2 || sizeof($quebrado)== 1) {
            list($endereco,  $numero,  $complemento) = self::ordenaDados($end);
        } else {
            $endereco = $end;
        }

        return array(
            self::endtrim(substr($endereco, 0, 69)),
            self::endtrim($numero),
            self::endtrim($complemento),
            self::endtrim($bairro)
        );
    }
}