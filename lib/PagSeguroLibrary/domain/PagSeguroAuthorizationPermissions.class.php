<?php
/**
 * 2007-2014 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/***
 * Authorization permissions information
 */
class PagSeguroAuthorizationPermissions
{

    /***
     * Enum: Permission list
     */
    private $permissionList = array(
            "CREATE_CHECKOUTS",
            "RECEIVE_TRANSACTION_NOTIFICATIONS",
            "SEARCH_TRANSACTIONS",
            "MANAGE_PAYMENT_PRE_APPROVALS",
            "DIRECT_PAYMENT",
            "REFUND_TRANSACTIONS",
            "CANCEL_TRANSACTIONS"
        );

    /*
     * @var $permissions
     */
    private $permissions;

    /***
     * Initializes a new instance of the PagSeguroAuthorizationPermissions class
     * @param null|array $permissions
     * @throws string Exception
     */
    public function __construct($permissions = null)
    {
        if (isset($permissions)) {
            $this->permissions = $permissions;
        }
    }

    /***
     * @return array of permissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /***
     * @param array $permissions
     */
    public function setPermissions($permissions)
    {
        if (isset($permissions)) {
            $this->permissions = $permissions;
        }
    }
}