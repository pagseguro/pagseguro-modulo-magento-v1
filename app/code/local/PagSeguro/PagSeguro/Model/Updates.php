<?php

class Updates
{
    //Verifica se existe a tabela 'pagseguro_sales_code', caso nÃƒÂ£o exista, ÃƒÂ© apagado 
    //o valor do 'core_resource', em sequencia ele cria automaticamente o 'core_resource' e a
    //tabela 'pagseguro_sales_code'
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
        }
        
    	// Adiciona os templates e skin necessÃ¡rios para o funcionamento do lighbox
    	$sql = "SELECT value FROM ". $table_prefix ."core_config_data WHERE path =";
        $sqlTemplate = $sql. "'design/theme/template'";
        $connectionTemplate = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($sqlTemplate);

		if($connectionTemplate != 'pagseguro') {
			$sql = "INSERT INTO " . $table_prefix . "core_config_data (path, value) VALUES('design/theme/template' , 'pagseguro') 	ON DUPLICATE KEY UPDATE path = 'design/theme/template', VALUE = 'pagseguro'";
	        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
	        $connection->query($sql);
		}
    }
}
