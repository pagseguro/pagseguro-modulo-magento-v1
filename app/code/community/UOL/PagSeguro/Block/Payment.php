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
class UOL_PagSeguro_Block_Payment extends Mage_Core_Block_Template
{
    /**
     * @return array
     */
    protected function getConvertCode()
    {
        $code = $this->getRequest()->getParam("code");
        $payment_url = $this->base64UrlDecode($code);
        $resultado = parse_url($payment_url);
        parse_str($resultado['query']);

        return array('code' => $code, 'fullUrl' => $payment_url);
    }

    /**
     * @param $b64Text
     * @return bool|string
     */
    private function base64UrlDecode($b64Text)
    {
        return base64_decode(strtr($b64Text, '-_,', '+/='));
    }
}
