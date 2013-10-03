<?php

class Updates
{
    //Verifica se existe a tabela 'pagseguro_sales_code', caso não exista, é apagado 
    //o valor do 'core_resource', em sequencia ele cria automaticamente o 'core_resource' e a
    //tabela 'pagseguro_sales_code'
    public static function createTableModule($collection = null)
    {
        $sql = "SHOW TABLES LIKE 'pagseguro_sales_code'";
        $table_exists = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);

        if(!count($table_exists)) {
            $sql = "CREATE TABLE IF NOT EXISTS `pagseguro_sales_code` (
                    `entity_id` int(11) NOT NULL auto_increment,
                    `order_id` int(11),
                    `transaction_code` varchar(80) NOT NULL,
                    PRIMARY KEY (`entity_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->query($sql);
        } else {
            if(!is_null($collection)) {
                $collection->getSelect()->joinLeft('pagseguro_sales_code', 'main_table.entity_id = pagseguro_sales_code.order_id', array('transaction_code'));
            }
        }
    }
}
