<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu\Event;

class SaveAccount extends RegisterSuccess
{
    /**
     * @param int $customerId
     * @return array
     */
    protected function buildData($customerId)
    {
        $data = parent::buildData($customerId);
        $data['Event'] = 'customer_account_edited';

        return apply_filters('wc_inforu_save_account_data', $data);
    }
}