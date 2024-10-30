<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu;

class Helper
{
    /**
     * @param \WC_Customer $customer
     * @return array
     */
    public function mapCustomerFields($customer)
    {
        $arr = [];
//        $mappings = $this->getCustomerMappings();
        $mappings = [
            'email' => 'CustomerEmail',
            'first_name' => 'CustomerFirstName',
            'last_name' => 'CustomerLastName',
            'phone' => 'CustomerPhone'
        ];

        if ($mappings) {
            $data = $customer->get_data();
            $addressType = $this->getAddressType();
            $address = $addressType === 'shipping' ? $customer->get_shipping() : $customer->get_billing();
            foreach ($mappings as $customerAttrCode => $inforuCode) {
                if (empty($data[$customerAttrCode])) {
                    if (!$address) {
                        continue;
                    }

                    if (empty($address[$customerAttrCode])) {
                        continue;
                    }

                    $arr[$inforuCode] = $address[$customerAttrCode];
                    continue;
                }

                $arr[$inforuCode] = $data[$customerAttrCode];
            }
        }

        return $arr;
    }

    /**
     * @param mixed $status
     * @return string
     */
    public function getSubscriberStatus($status = 0)
    {
        switch ($status) {
            default:
            case 0:
            case '':
                $text = 'Unsubscribed';
                break;

            case 1:
                $text = 'Subscribed';
                break;
        }

        return $text;
    }

    /**
     * @return array
     */
    public function getSubscribeGroups()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        $groups = [];

        return $groups;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        return '';
    }

    /**
     * @return array
     */
    public function getCustomerMappings()
    {
        $mappings = \WC_Inforu::app()->getOption('customer_fields_mapping');
        if (!$mappings) {
            return [];
        }

        if (!is_array($mappings)) {
            $mappings = unserialize($mappings);
        }

        return $mappings;
    }

    /**
     * @return string
     */
    public function getAddressType()
    {
        return \WC_Inforu::app()->getOption('customer_address_type', 'billing');
    }
}
