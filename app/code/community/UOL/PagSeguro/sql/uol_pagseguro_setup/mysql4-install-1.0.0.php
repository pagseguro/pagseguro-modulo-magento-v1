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
				 
// Verifies that no record of the status PagSeguro created, if you have not created	 
$sql .= "INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'aguardando_pagamento_ps' AS STATUS, 'Aguardando Pagamento' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'aguardando_pagamento_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'em_analise_ps' AS STATUS, 'Em análise' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'em_analise_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'paga_ps' AS STATUS, 'Paga' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'paga_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'disponivel_ps' AS STATUS, 'Disponível' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'disponivel_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'em_disputa_ps' AS STATUS, 'Em Disputa' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'em_disputa_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'devolvida_ps' AS STATUS, 'Devolvida' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'devolvida_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status (STATUS, label)
		 SELECT p.status, p.label FROM(SELECT 'cancelada_ps' AS STATUS, 'Cancelada' AS label) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status WHERE STATUS = 'cancelada_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)";
		 	 
// Verifies that no record of the status PagSeguro to be displayed on a new order if it has not created
$sql .= "SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'devolvida_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'devolvida_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'cancelada_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'cancelada_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'em_disputa_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'em_disputa_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'disponivel_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'disponivel_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'paga_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'paga_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'em_analise_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'em_analise_ps') = 0;
		 INSERT INTO ".$tp."sales_order_status_state (STATUS, state, is_default)
		 SELECT p.status, p.state, p.is_default FROM
		 (SELECT 'aguardando_pagamento_ps' AS STATUS, 'new' AS state, '0' AS is_default) p
		 WHERE (SELECT COUNT(STATUS) FROM ".$tp."sales_order_status_state WHERE STATUS = 'aguardando_pagamento_ps') = 0;";
$installer->run($sql);
$installer->endSetup();