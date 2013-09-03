<?php

// Production environment
$PagSeguroResources['environment'] = array();
$PagSeguroResources['environment']['production']['webserviceUrl'] = "https://ws.pagseguro.uol.com.br";
$PagSeguroResources['environment']['development']['webserviceUrl'] = "https://dev.ws.pagseguro.uol.com.br";

// Payment service
$PagSeguroResources['paymentService'] = array();
$PagSeguroResources['paymentService']['servicePath'] = "/v2/checkout";
$PagSeguroResources['paymentService']['checkoutUrl'] = "https://pagseguro.uol.com.br/v2/checkout/payment.html";
$PagSeguroResources['paymentService']['serviceTimeout'] = 20;

// Notification service
$PagSeguroResources['notificationService'] = array();
$PagSeguroResources['notificationService']['servicePath'] = "/v2/transactions/notifications";
$PagSeguroResources['notificationService']['serviceTimeout'] = 20;

// Transaction search service
$PagSeguroResources['transactionSearchService'] = array();
$PagSeguroResources['transactionSearchService']['servicePath'] = "/v2/transactions";
$PagSeguroResources['transactionSearchService']['serviceTimeout'] = 20;
