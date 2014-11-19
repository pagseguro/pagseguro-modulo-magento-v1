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

class Updates
{
    /*
     * Verify if exists 'pagseguro_sales_code', if doesn't reset the 'core_resource' value and 
     * creates the 'core_resource' and the 'pagseguro_sales_code' table automatically. 
     */
    public static function createTableModule($collection = null)
    {
        $table_prefix = (string)Mage::getConfig()->getTablePrefix();
        $sql = "SHOW TABLES LIKE '" . $table_prefix . "pagseguro_sales_code'";
        $table_exists = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
		
        if (!count($table_exists)) {
            $sql = "CREATE TABLE IF NOT EXISTS `". $table_prefix ."pagseguro_sales_code` (
                    `entity_id` int(11) NOT NULL auto_increment,
                    `order_id` int(11),
                    `transaction_code` varchar(80) NOT NULL,
                    PRIMARY KEY (`entity_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->query($sql);
        } else {
            if (!is_null($collection)) {
                $collection->getSelect()->joinLeft(
                    $table_prefix . 'pagseguro_sales_code',
                    'main_table.entity_id = ' . $table_prefix . 'pagseguro_sales_code.order_id',
                    array('transaction_code')
                );
            }					 
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->query($sql);            
        }
    }
}