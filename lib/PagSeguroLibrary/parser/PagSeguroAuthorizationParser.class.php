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
 * Class PagSeguroAuthorizationParser
 */
class PagSeguroAuthorizationParser extends PagSeguroServiceParser
{

    /***
     * @param $authorization PagSeguroAuthorizationRequest
     * @param $credentials PagSeguroAuthorizationCredentials
     * @return mixed
     */
    public static function getData($authorization, $credentials)
    {

        $data = null;

        // AppID
        if ($credentials->getAppId() != null) {
            $data['appId'] = $credentials->getAppId();
        }
        // AppKey
        if ($credentials->getAppKey() != null) {
            $data['appKey'] = $credentials->getAppKey();
        }
        // Reference
        if ($authorization->getReference() != null) {
            $data['reference'] = $authorization->getReference();
        }
        // RedirectURL
        if ($authorization->getRedirectURL() != null) {
            $data['redirectURL'] = $authorization->getRedirectURL();
        }
        // NotificationURL
        if ($authorization->getNotificationURL() != null) {
            $data['notificationURL'] = $authorization->getNotificationURL();
        }
        // Permissions
        if ($authorization->getPermissions()->getPermissions() != null) {
            $data['permissions'] = implode(',',$authorization->getPermissions()->getPermissions());
        }

        // parameter
        if (count($authorization->getParameter()->getItems()) > 0) {
            foreach ($authorization->getParameter()->getItems() as $item) {
                if ($item instanceof PagSeguroParameterItem) {
                    if (!PagSeguroHelper::isEmpty($item->getKey()) && !PagSeguroHelper::isEmpty($item->getValue())) {
                        if (!PagSeguroHelper::isEmpty($item->getGroup())) {
                            $data[$item->getKey() . '' . $item->getGroup()] = $item->getValue();
                        } else {
                            $data[$item->getKey()] = $item->getValue();
                        }
                    }
                }
            }
        }
        
        return $data;
    }

    /***
     * @param $str_xml
     * @return PagSeguroAuthorization
     */
    public static function readAuthorization($str_xml)
    {
        // Parser
        $parser = new PagSeguroXmlParser($str_xml);

        return self::buildAuthorization(new PagSeguroAuthorization(),
            $parser->getResult('authorization'));
    }

    /***
     * @param $str_xml
     * @return PagSeguroAuthorization
     */
    public static function readSearchResult($str_xml)
    {
        // Parser
        $parser = new PagSeguroXmlParser($str_xml);

        $authorization = new PagSeguroAuthorizationSearchResult();

        // <authorizationSearchResult>
        $data = $parser->getResult('authorizationSearchResult');

        // <authorizationSearchResult><date>
        if (isset($data["date"])) {
            $authorization->setDate($data['date']);
        }

        //<authorizationSearchResult><authorizations><authorization>
        if (isset($data['authorizations']) && is_array($data['authorizations'])) {

            if (isset($data['authorizations']['authorization'])
                && $data["resultsInThisPage"] > 1) {

                $i = 0;
                foreach ($data['authorizations']['authorization'] as $key => $value) {
                    $newAuthorization = new PagSeguroAuthorization();
                    $nAuthorization[$i++] = self::buildAuthorization($newAuthorization,$value);
                }
                $authorization->setAuthorizations($nAuthorization);

            } else {

                $newAuthorization = new PagSeguroAuthorization();
                $authorization->setAuthorizations(
                    self::buildAuthorization($newAuthorization,
                        $data['authorizations']['authorization']));
            }

        }

        // <authorizationSearchResult><resultsInThisPage>
        if (isset($data["resultsInThisPage"])) {
            $authorization->setResultsInThisPage($data['resultsInThisPage']);
        }
        // <authorizationSearchResult><totalPages>
        if (isset($data["totalPages"])) {
            $authorization->setTotalPages($data['totalPages']);
        }
        // <authorizationSearchResult><currentPage>
        if (isset($data["currentPage"])) {
            $authorization->setCurrentPage($data["currentPage"]);
        }

        return $authorization;
    }

    /**
     * @param PagSeguroAuthorization $authorization
     * @param $data
     */
    private function buildAuthorization(PagSeguroAuthorization $authorization, $data)
    {
        // <authorization><code>
        if (isset($data["code"])) {
            $authorization->setCode($data['code']);
        }

        // <authorization><creationDate>
        if (isset($data["creationDate"])) {
            $authorization->setCreationDate($data['creationDate']);
        }

        // <authorization><reference>
        if (isset($data["reference"])) {
            $authorization->setReference($data['reference']);
        }

        // <authorization><account><publicKey>
        if (isset($data["account"]) and isset($data["account"]['publicKey'])) {
            $authorization->setAccount(new PagSeguroAuthorizationAccount($data["account"]['publicKey']));
        }

        // <authorization><permissions>
        if (isset($data["permissions"])) {
            if (isset($data["permissions"]["permission"])) {

                foreach ($data["permissions"]["permission"] as $permission) {

                    $permissions[] = new PagSeguroAuthorizationPermission(
                        $permission['code'],
                        $permission['status'],
                        $permission['lastUpdate']
                    );
                }
            }
            $permissions = new PagSeguroAuthorizationPermissions($permissions);
            $authorization->setPermissions($permissions);

           return $authorization;
        }
    }

    /***
     * @param $str_xml
     * @return PagSeguroParserData Success
     */
    public static function readSuccessXml($str_xml)
    {
        $parser = new PagSeguroXmlParser($str_xml);

        $data = $parser->getResult('authorizationRequest');
        $authorizationParserData = new PagSeguroParserData();
        $authorizationParserData->setCode($data['code']);
        $authorizationParserData->setRegistrationDate($data['date']);
        return $authorizationParserData;
    }

    /***
     * @param $error Authorization error
     * @return object()
     */
    private static function readError($error)
    {
    	$err = new stdClass();
    	$err->message = key($error);
    	$err->status = true;

    	return $err;
    }
}
