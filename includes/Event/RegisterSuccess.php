<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu\Event;

use WC_Inforu\Api;

class RegisterSuccess
{
    /**
     * @param int $customerId
     */
    public function execute($customerId)
    {
        $data = $this->buildData($customerId);
        $api = new Api();
        $api->setData($data)->doRequest();
    }

    /**
     * @param int $customerId
     * @return array
     */
    protected function buildData($customerId)
    {
        $helper = new \WC_Inforu\Helper();
        $customer = new \WC_Customer($customerId);
        $data = [
            'Event' => 'customer_register_success',
            'IP' => $helper->getClientIp(),
            'ContactRefId' => $customer->get_id(),
            'SubscriberStatus' => $helper->getSubscriberStatus(1),
            'AddToGroupName' => $helper->getSubscribeGroups(),
            'AvailableGroups' => $helper->getGroupIds(),
        ];

        $customerFields = $helper->mapCustomerFields($customer);
        $data = array_merge($data, $customerFields);
        $storeInfo = \WC_Inforu::app()->getStoreInfo();
        $data = array_merge($storeInfo, $data);

        return apply_filters('wc_inforu_register_success_data', $data);
    }
}