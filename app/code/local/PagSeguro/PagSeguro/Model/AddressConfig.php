<?php

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

        return array(
            $texto,
            '',
            ''
        );
    }

    //    private static function separaNumeroComplemento($n)
    //    {
    //        $semnumeros=self::dados('semnumeros');
    //        $n = self::endtrim($n);
    //        foreach ($semnumeros as $sn) {
    //            if ($n == $sn) {
    //                return array($n, '');
    //            }
    //            if (substr($n, 0, strlen($sn)) == $sn) {
    //                return array(substr($n, 0, strlen($sn)), substr($n, strlen($sn)));
    //            }
    //        }
    //        $q=preg_split('/\D/', $n);
    //        $pos=strlen($q[0]);
    //        return array(substr($n, 0, $pos), substr($n, $pos));
    //    }

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
