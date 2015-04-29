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

// table prefix
$tp = (string) Mage::getConfig()->getTablePrefix();
$table =  $tp . 'pagseguro_orders';
$columnName = 'discount';
$definition = 'INT DEFAULT 0';

/**
* Adds the discount column in the table pagseguro_orders
* This column is used as a check to add to a discount on order
*/
$resource->getConnection('core_write')->addColumn($table, $columnName, $definition);

/**
 * Removal of the tables used in version 2.4.0.
 * Were kept in version 2.5.0 to give downgrade option to customers.
 */
$sql  = "DROP TABLE IF EXISTS `" . $tp . "pagseguro_conciliation`;";
$sql .= "DROP TABLE IF EXISTS `" . $tp . "pagseguro_sales_code`;";

$installer->run($sql);
$installer->endSetup();
