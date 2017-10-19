<?php

/**
 * Class UOL_PagSeguro_Model_OrderAddress
 */
class UOL_PagSeguro_Model_OrderAddress
{
    /**
     * @var Mage_Sales_Model_Order_Address
     */
    private $billingAddress;
    /**
     * @var Mage_Sales_Model_Order
     */
    private $order;
    /**
     * @var Mage_Sales_Model_Order_Address
     */
    private $shippingAddress;

    /**
     * UOL_PagSeguro_Model_OrderAddress constructor.
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function __construct(Mage_Sales_Model_Order $order)
    {
        $this->order = $order;
        $this->billingAddress = $this->order->getBillingAddress();
        $this->shippingAddress = $this->order->getShippingAddress();
    }

    /**
     * @return \PagSeguro\Domains\Address
     */
    public function getBillingAddress()
    {
        return $this->setAddress($this->billingAddress);
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return \PagSeguro\Domains\Address
     */
    private function setAddress(Mage_Sales_Model_Order_Address $address)
    {
        $response = new \PagSeguro\Domains\Address();
        
        if (count($address->getStreet()) === 4) {
            //one step checkout default values
            $response->setStreet($address->getStreet1());
            $response->setNumber($address->getStreet2());
            $response->setComplement($address->getStreet3());
            $response->setDistrict($address->getStreet4());
        } else {
            //default configuration
            $parse = $this->parseStreet($address->getStreet1());
            $response->setStreet($parse['street']);
            $response->setNumber($parse['number']);
            $response->setDistrict($address->getStreet2());
            $response->setComplement($address->getStreet3());
        }

        $response->setCity($address->getCity());
        $response->setPostalCode($address->getPostcode());
        $response->setState($this->getRegionAbbreviation($address));
        $response->setCountry($address->getCountry());

        return $response;
    }

    /**
     * @param $fullAddress
     *
     * @return array
     */
    private function parseStreet($fullAddress)
    {
        $fullAddress = explode(', ', $fullAddress);
        $street = $fullAddress[0];
        $number = isset($fullAddress[1]) ? $fullAddress[1] : null;

        return array(
            'street' => $street,
            'number' => $number,
        );
    }

    /**
     * Get a brazilian region name and return the abbreviation if it exists
     *
     * @param address $address
     *
     * @return string
     */
    private function getRegionAbbreviation($address)
    {
        if (!is_null($address->getRegionCode()) && strlen($address->getRegionCode()) == 2) {
            return strtoupper($address->getRegionCode());
        }

        $addressEnum = new \PagSeguro\Enum\Address();

        return (is_string($addressEnum->getType($address->getRegion()))) ?
            strtoupper($addressEnum->getType($address->getRegion())) :
            strtoupper($address->getRegion());
    }

    /**
     * @return \PagSeguro\Domains\Address
     */
    public function getShippingAddress()
    {
        return $this->setAddress($this->shippingAddress);
    }
}
