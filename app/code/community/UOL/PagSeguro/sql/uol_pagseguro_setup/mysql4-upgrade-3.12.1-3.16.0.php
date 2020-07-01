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

$installer = $this;
$installer->startSetup();

// table prefix
$tp = (string)Mage::getConfig()->getTablePrefix();
$table = $tp . "sales_order_status_state";

$sql = "UPDATE " . $table . " SET `state`='pending_payment' WHERE `status`='aguardando_pagamento_ps';"
    . "UPDATE " . $table . " SET `state`='payment_review' WHERE `status`='em_analise_ps';"
    . "UPDATE " . $table . " SET `state`='processing' WHERE `status`='paga_ps';"
    . "UPDATE " . $table . " SET `state`='processing' WHERE `status`='disponivel_ps';"
    . "UPDATE " . $table . " SET `state`='holded' WHERE `status`='em_disputa_ps';"
    . "UPDATE " . $table . " SET `state`='closed' WHERE `status`='devolvida_ps';"
    . "UPDATE " . $table . " SET `state`='canceled' WHERE `status`='cancelada_ps';"
    . "UPDATE " . $table . " SET `state`='closed' WHERE `status`='chargeback_debitado_ps';"
    . "UPDATE " . $table . " SET `state`='holded' WHERE `status`='em_contestacao_ps';";

$installer->run($sql);
$installer->endSetup();
