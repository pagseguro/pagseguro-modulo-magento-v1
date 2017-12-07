<?php

require_once "../../../vendor/autoload.php";

\PagSeguro\Library::initialize();

$code = '7DD98273EB72EB7238388469DF9008F43A14';

try {
    $response = \PagSeguro\Services\Application\Search\Notification::search(
        \PagSeguro\Configuration\Configure::getApplicationCredentials(),
        $code
    );

    echo "<pre>";
    print_r($response);
} catch (Exception $e) {
    die($e->getMessage());
}
