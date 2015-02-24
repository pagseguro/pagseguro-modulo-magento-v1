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

class UOL_PagSeguro_Model_Observer
{

	/**
	 * Query the existing transaction codes with the id of the request and assembles an array with these codes.
	 * @param object $observer - It is an object of Event of observe.
	 */
	public function salesOrderGridCollectionLoadBefore($observer)
	{
	    $collection = $observer->getOrderGridCollection();
	    $select = $collection->getSelect();
		$tableCollection = Mage::getSingleton('core/resource')->getTableName('pagseguro_orders');
	    $select->joinLeft(array('payment' => $tableCollection),
	    						'payment.order_id = main_table.entity_id',
								array('payment_code'=>'transaction_code', 'payment_environment' => 'environment')
								);
	}
}