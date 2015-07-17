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

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');

$tp = (string)Mage::getConfig()->getTablePrefix();
$table = $tp . 'pagseguro_conciliation';

function getMagentoVersion()
{
    return substr(str_replace(".", "", Mage::getVersion()), 0, 2);
}

function _tableExists($table, $resource)
{
    if (getMagentoVersion() < 16) {
        return array_search($table, $resource->getConnection('core_write')->listTables());
    } 

    return $resource->getConnection('core_write')->isTableExists($table);
}

// checks if exists registry of reference
if (_tableExists($table, $resource)) {
    $query = 'SELECT reference FROM ' . $resource->getTableName($table);
    $results = $readConnection->fetchAll($query);

    if (!Mage::getStoreConfig('uol_pagseguro/store/reference')) {
        $ref = current(current($results));
    }
} else {
    // Creates a reference to 5 characters that will be used as the only reference that store, the transaction PagSeguro
    $ref = Mage::helper('pagseguro')->createReference(5, 'true', 'true');
}

if (!Mage::getStoreConfig('uol_pagseguro/store/reference')) {
    // save the reference of store
    Mage::getConfig()->saveConfig('uol_pagseguro/store/reference', $ref);
}

$table =  $tp . 'pagseguro_sales_code';
$new_table =  $tp . 'pagseguro_orders';

// checks if exists the table
if (_tableExists($table, $resource)) {
    if (!_tableExists($new_table, $resource)) {
        //copies the transaction table used in version 2.4
        $sql .= "CREATE TABLE `" . $new_table . "` LIKE `" . $table . "`;";
        $sql .= "INSERT `" . $new_table . "` SELECT * FROM `" . $table . "`;";

        // change the table adding sent column
        $sql .= "ALTER TABLE `" . $new_table . "` ADD sent int DEFAULT 0;";
        $sql .= "ALTER TABLE `" . $new_table . "` ADD environment varchar(40);";
    }
} else {
    // Checks for the pagseguro_orders table if it does not exist is created
    $sql .= "CREATE TABLE IF NOT EXISTS `" . $new_table . "` (
             `entity_id` int(11) NOT NULL AUTO_INCREMENT,
             `order_id` int(11),
             `transaction_code` varchar(80) NOT NULL,
             `sent` int DEFAULT 0,
             `environment` varchar(40),
             PRIMARY KEY (`entity_id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
}

$table = $tp . "sales_order_status";

// Verifies that no record of the status PagSeguro created, if you have not created
$sql .= "INSERT INTO `" . $table . "` (STATUS, label)
         SELECT p.status, p.label FROM(SELECT 'chargeback_debitado_ps' AS STATUS, 'Chargeback Debitado' AS label) p
         WHERE (SELECT COUNT(STATUS) FROM `" . $table . "` WHERE STATUS = 'chargeback_debitado_ps') = 0;

         INSERT INTO `" . $table . "` (STATUS, label)
         SELECT p.status, p.label FROM(SELECT 'em_contestacao_ps' AS STATUS, 'Em Contestação' AS label) p
         WHERE (SELECT COUNT(STATUS) FROM `" . $table . "` WHERE STATUS = 'em_contestacao_ps') = 0;";

$table = $tp . "sales_order_status_state";

// Verifies that no record of the status PagSeguro to be displayed on a new order if it has not created
$sql .= "INSERT INTO `" . $table . "` (STATUS, state, is_default)
         SELECT p.status, p.state, p.is_default FROM
         (SELECT 'chargeback_debitado_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
         WHERE (SELECT COUNT(STATUS) FROM `" . $table . "` WHERE STATUS = 'chargeback_debitado_ps') = 0;

         INSERT INTO `" . $table . "` (STATUS, state, is_default)
         SELECT p.status, p.state, p.is_default FROM
         (SELECT 'em_contestacao_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
         WHERE (SELECT COUNT(STATUS) FROM `" . $table . "` WHERE STATUS = 'em_contestacao_ps') = 0;";

$installer->run($sql);
$installer->endSetup();
