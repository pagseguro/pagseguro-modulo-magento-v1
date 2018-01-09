<?php

require_once "../../../vendor/autoload.php";

\PagSeguro\Library::initialize();

$code = '2ED002E234444A0D9469EF14F0D5A9C1';

try {
    $response = \PagSeguro\Services\Application\Search\Code::search(
        \PagSeguro\Configuration\Configure::getApplicationCredentials(),
        $code
    );

    echo "<pre>";
    print_r($response);
} catch (Exception $e) {
    die($e->getMessage());
}
