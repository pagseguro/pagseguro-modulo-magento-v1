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

namespace remove;

class Remove
{
   /**
     * Construct
     */
    public static function main()
    {
        error_reporting(E_ERROR | E_PARSE);

        $_SESSION["fail"] = null;
        $_SESSION["success"] = null;
        $_SESSION["path"] = null;
        $_SESSION["chmod"] = null;

        $globalConfiguration = "app/etc/modules/";
        $localModuleFile = "app/code/local/";
        $localePT = "app/locale/pt_BR/";
        $localeEN = "app/locale/en_US/";
        $appAdminDefault = "app/design/adminhtml/default/default/";
        $appFrontendBase = "app/design/frontend/base/default/";
        $appFrontendDefault = "app/design/frontend/default/";
        $skinAdminDefault = "skin/adminhtml/default/default/";
        $skinBaseDefault = "skin/frontend/base/default/";

        $array = array ($globalConfiguration . "Mage_PagSeguro.xml",
                        $globalConfiguration . "PagSeguro_PagSeguro.xml",
                        $localModuleFile . "Mage",
                        $localModuleFile . "PagSeguro",
                        $appAdminDefault . "layout/pagseguro.xml",
                        $appAdminDefault . "template/pagseguro",
                        $appFrontendBase . "layout/pagseguro_pagseguro.xml",
                        $appFrontendBase . "template/pagseguro",
                        $appFrontendDefault . "pagseguro",
                        $localePT . "PagSeguro_PagSeguro.csv",
                        $localePT . "template/email/sales/pagseguro_abandoned.html",
                        $localeEN . "template/email/sales/pagseguro_abandoned.html",
                        $skinAdminDefault . "pagseguro",
                        $skinBaseDefault . "js/onepagecheckoutpagseguro.js",
                        $skinBaseDefault . "js/pagseguro.js");

        foreach ($array as $item) {
            Remove::deleteItem($item, true);
        }

        if ($_SESSION['fail']) {
            $fail = $_SESSION['fail'];

            echo "<p><strong>N&atilde;o foi poss&iacute;vel remover o m&oacute;dulo por completo.</strong></p>";
            echo "<p><font color='red'>";
            echo "&Eacute; necess&aacute;rio alterar a permiss&atilde;o dos itens abaixo:</font></p>";
            echo "<p>" . $_SESSION['path'] . "<br /><strong>Exemplo:</strong></p><p>" . $_SESSION['chmod'] . "</p>";
            echo "<p><font color='red'>Ap&oacute;s alterar as permiss&otilde;es, tente novamente.</font></p>";

            $_SESSION['fail'] = null;
            $_SESSION['path'] = null;
            $_SESSION['chmod'] = null;
        } else {
            if ($_SESSION['success']) {
                echo "<p><strong>M&oacute;dulo removido com sucesso!</strong></p>";
            } else {
                echo "<p><strong>O m&oacute;dulo j&aacute; foi removido.</strong></p>";
            }
        }

        if ($_SESSION['success']) {
            echo "<p><strong>Itens removidos:</strong></p><p>" . $_SESSION['success'] . "</p>";
            $_SESSION['success'] = null;
        }

        if ($fail) {
            echo "<p><strong>Itens n&atilde;o removidos (restri&ccedil;&atilde;o de permiss&atilde;o):</strong></p>";
            echo "<p>" . $fail . "</p>";
        }
    }

    /**
    * Make the removal of version 2.2 files that  not have installable package.
    */
    private function deleteItem($item, $base)
    {
        if (file_exists($item)) {
            if (is_dir($item)) {
                $folders = scandir($item);

                foreach ($folders as $folder) {
                    if ($folder != '.' && $folder != '..') {
                        $file = $item . "/" . $folder;

                        if (filetype($file) == "dir") {
                            $this->deleteItem($file);
                        } else {
                            unlink($file);

                            if (file_exists($file)) {
                                $_SESSION["fail"] .= $file . "<br />";
                            } else {
                                $_SESSION["success"] .= $file . "<br />";
                            }
                        }
                    }
                }

                reset($folders);
                rmdir($item);

                if (file_exists($item)) {
                    $_SESSION["fail"] .= $item . "<br />";
                } else {
                    $_SESSION["success"] .= $item. "<br />";
                }
            } else {
                unlink($item);

                if (file_exists($item)) {
                    $_SESSION["fail"] .= $item . "<br />";
                } else {
                    $_SESSION["success"] .= $item . "<br />";
                }
            }

            $_SESSION['path'] .= ($base == true && file_exists($item)) ? $item . "<br />" : "";
            $_SESSION['chmod'] .= ($base == true && file_exists($item)) ? "chmod -R 0777 " . $item . ";<br />" : "";
        }
    }
}

Remove::main();
