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
    /**
     * Creates the layout of the administration
     * Receives the post and comes back
     */
    public function indexAction()
    {
        $helper = Mage::helper('pagseguro');
        $origin = $this->getRequest()->getPost('origin');
        $sendmail = $this->getRequest()->getPost('sendmail');
        $days = $this->getRequest()->getPost('days');

        // Saves the day searching for the global variable that receives the array
        if ($days) {
            $_SESSION['days'] = $days;
            $helper->setDateStart($days);
        }

        if ($origin == 'abandoned') {
            $json = $this->getRequest()->getPost('json');
            if ($json) {
                echo $this->sendAbandonedMail($json);
            } else {
                echo $this->getAbandonedGrid($days);
            }
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

    /**
     * Generates the data abandoned to populate the table
     * @return array $dataSet - Array of data for table
     */
    private function getAbandonedGrid($days)
    {
        $abandoned = Mage::helper('pagseguro/abandoned');
        $abandoned->setAbandonedListLog($days);
        $abandoned->checkAbandonedAccess($days);

        try {
            if ($abandonedArray = $abandoned->getArrayAbandoned()) {
                return $abandoned->getTransactionGrid($abandonedArray);
            }
        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    /**
     * Generates emailing abandoned customer
     * @param array $json - Records to send
     * @return string $run - String to guide it displays the notification message
     */
    private function sendAbandonedMail($json)
    {
        $abandoned = Mage::helper('pagseguro/abandoned');
        $abandoned->setAdminLocaleCode(Mage::app()->getLocale()->getLocaleCode());

        foreach ($json as $value) {
            $abandoned->sendAbandonedEmail($value['id'], $value['recovery']);
        }

        return 'run';
    }

    /**
     * Generates the data transactions to populate the table
     * @return array $dataSet - Array of data for table
     */
    private function getCanceledGrid($days)
    {
        $helper = Mage::helper('pagseguro/canceled');

        try {
            if ($canceledArray = $helper->getArrayPayments()) {
                return $helper->getTransactionGrid($canceledArray);
            } else {
                return 'run';
            }

        } catch (Exception $e) {
            return trim($e->getMessage());
        }
    }

    /**
     * Generates the data conciliation to populate the table
     * @return array $dataSet - Array of data for table
     */
    private function getConciliationGrid($days)
    {
        $conciliation = Mage::helper('pagseguro/conciliation');

        // Upgrade from Magento order status
        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $conciliation->updateOrderStatusMagento($value['id'], $value['code'], $value['status']);
            }

            $conciliation->setDateStart($_SESSION['days']);
        } else {
            if ($_SESSION['days'] != 0) {
                $conciliation->setConciliationListLog($days);
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

    /**
     * Generates the data transactions to populate the table
     * @return array $dataSet - Array of data for table
     */
    private function getRefundGrid($days)
    {
        $refund = Mage::helper('pagseguro/refund');

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

    /**
     * Generates the data abandoned to populate the table
     * @return array $dataSet - Array of data for table
     */
    private function getRequirements()
    {
        $requeriments = Mage::helper('pagseguro/requirements');
        $requeriments->setRequirementsLog();

        return json_encode($requeriments->validateRequirements());
    }
}
