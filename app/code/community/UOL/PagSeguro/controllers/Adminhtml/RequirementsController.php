<?php

/**
 ************************************************************************
 * Copyright [2015] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */
class UOL_PagSeguro_Adminhtml_RequirementsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var UOL_PagSeguro_Helper_Log
     */
    private $log;
    /**
     * @var UOL_PagSeguro_Helper_Requirements
     */
    private $requirements;

    /**
     * UOL_PagSeguro_Adminhtml_ConciliationController constructor.
     */
    public function _construct()
    {
        $this->log = new UOL_PagSeguro_Helper_Log();
    }

    /**
     * @return string
     */
    public function doRequirementsAction()
    {
        $this->builder();
        $this->log->setRequirementsLog();
        print json_encode($this->requirements->validateRequirements());
        exit;
    }

    /**
     * Initializes helpers and instance vars.
     */
    private function builder()
    {
        $this->requirements = Mage::helper('pagseguro/requirements');
        $this->log = Mage::helper('pagseguro/log');
    }

    /**
     * Render canceled layout in administration interface
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('pagseguro_menu')->renderLayout();
    }
}
