<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu\Event;

use WC_Inforu\Api;

class SaveNewsletter
{
    public function execute($email, $status = 0)
    {
        $data = $this->buildData($email, $status);
        $api = new Api();
        $api->setData($data)->doRequest();
    }

    /**
     * @param int $email
     * @return array
     */
    protected function buildData($email, $status)
    {
        $helper = new \WC_Inforu\Helper();
        $data = [
            'Event' => 'newsletter_subscriber_save_after',
            'IP' => $helper->getClientIp(),
            'CustomerEmail' => $email,
            'SubscriberStatus' => $helper->getSubscriberStatus($status),
            'AddToGroupName' => $helper->getSubscribeGroups(),
            'AvailableGroups' => $helper->getGroupIds(),
        ];

        $userData = \WP_User::get_data_by('email', $email);
        if ($userData && $userData->ID) {
            $customer = new \WC_Customer($userData->ID);
            $customerFields = $helper->mapCustomerFields($customer);
            $data = array_merge($data, $customerFields);
        }

        $storeInfo = \WC_Inforu::app()->getStoreInfo();
        $data = array_merge($storeInfo, $data);

        return apply_filters('wc_inforu_save_newsletter_data', $data);
    }
}