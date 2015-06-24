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

class UOL_PagSeguro_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    private $log;

    public function indexAction()
    {
        $this->log = Mage::helper('pagseguro/log');
        $helper = Mage::helper('pagseguro');

        $origin = $this->getRequest()->getPost('origin');
        $sendmail = $this->getRequest()->getPost('sendmail');
        $days = $this->getRequest()->getPost('days');

        // Saves the day searching for the global variable that receives the array
        if ($days) {
            $_SESSION['days'] = $days;
            $helper->setInitialDate($days);
        }

        if ($origin == 'abandoned') {
            echo $this->getAbandonedGrid($days);
        } elseif ($origin == 'canceled') {
            echo $this->getCanceledGrid($days);
        } elseif ($origin == 'conciliation') {
            echo $this->getConciliationGrid($days);
        } elseif ($origin == 'refund') {
            echo $this->getRefundGrid($days);
        } elseif ($origin == 'requirements') {
            echo $this->getRequirements();
        }
    }

    private function getAbandonedGrid($days)
    {
        $abandoned = Mage::helper('pagseguro/abandoned');
        $abandoned->checkAbandonedAccess($days);
        $abandoned->setAdminLocaleCode(Mage::app()->getLocale()->getLocaleCode());

        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $abandoned->sendAbandonedEmail($value['id'], $value['recovery']);
            }

            $abandoned->setInitialDate($_SESSION['days']);

            return 'run';
        } else {
            if ($_SESSION['days'] != 0) {
                $this->log->setSearchTransactionLog(get_class($abandoned), $days);
            }
        }

        try {
            if ($abandonedArray = $abandoned->getArrayAbandoned()) {
                return $abandoned->getTransactionGrid($abandonedArray);
            }
        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    private function getCanceledGrid($days)
    {
        $canceled = Mage::helper('pagseguro/canceled');

        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $canceled->updateOrderStatusMagento(get_class($canceled), $value['id'], $value['code']);
            }

            $canceled->setInitialDate($_SESSION['days']);
        } else {
            if ($_SESSION['days'] != 0) {
                $this->log->setSearchTransactionLog(get_class($canceled), $days);
            }
        }

        try {
            if ($canceledArray = $canceled->getArrayPayments()) {
                return $canceled->getTransactionGrid($canceledArray);
            } else {
                return 'run';
            }
        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    private function getConciliationGrid($days)
    {
        $conciliation = Mage::helper('pagseguro/conciliation');

        // Upgrade from Magento order status
        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $class = get_class($conciliation);
                $conciliation->updateOrderStatusMagento($class, $value['id'], $value['code'], $value['status']);
            }

            $conciliation->setInitialDate($_SESSION['days']);
        } else {
            if ($_SESSION['days'] != 0) {
                $this->log->setSearchTransactionLog(get_class($conciliation), $days);
            }
        }

        try {
            if ($conciliationArray = $conciliation->getArrayPayments()) {
                return $conciliation->getTransactionGrid($conciliationArray);
            } else {
                return 'run';
            }
        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    private function getRefundGrid($days)
    {
        $refund = Mage::helper('pagseguro/refund');
        
        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $refund->updateOrderStatusMagento(get_class($refund), $value['id'], $value['code']);
            }
            
            $refund->setInitialDate($_SESSION['days']);
        } else {
            if ($_SESSION['days'] != 0) {
                $this->log->setSearchTransactionLog(get_class($refund), $days);
            }
        }
        
        try {
            
            if ($refundArray = $refund->getArrayPayments()) {
                return $refund->getTransactionGrid($refundArray);
            } else {
                return 'run';
            }
        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    private function getRequirements()
    {
        $requeriments = Mage::helper('pagseguro/requirements');
        $this->log->setRequirementsLog();

        $json = json_encode($requeriments->validateRequirements());

        return $json;
    }
}