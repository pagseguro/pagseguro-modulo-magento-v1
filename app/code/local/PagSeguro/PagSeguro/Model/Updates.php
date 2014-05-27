<?php

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
                    'main_table.entity_id = pagseguro_sales_code.order_id',
                    array('transaction_code')
                );
            }
        }

    }
}
