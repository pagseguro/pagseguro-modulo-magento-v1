<?php

require_once "../../../vendor/autoload.php";

\PagSeguro\Library::initialize();

$code = '8F17A22C51C641C1B6A40AF8C2FF94E2';

try {
    $response = \PagSeguro\Services\Transactions\Search\Code::search(
        \PagSeguro\Configuration\Configure::getAccountCredentials(),
        $code
    );

    echo "<pre>";
    print_r($response);
} catch (Exception $e) {
    die($e->getMessage());
}
