<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu\Event;

use WC_Inforu\Api;
use WC_Inforu\Helper\Arr;

class CartAbandoned
{
    public function execute()
    {
        $abandonedCart = $this->getAbandonedCart();
        if (!$abandonedCart) {
            return;
        }

        $api = new Api();
        try {
            foreach ($abandonedCart as $cartData) {
                $data = $this->buildData($cartData);
                if (!$data) {
                    continue;
                }

                $api->setData($data)->doRequest();
            }
        } catch (\Exception $e) {
            $file = 'inforu-' . date('Y_m_d') . '.log';
            wplogger()->info($e->getMessage(), $file);
        }
    }

    protected function buildData($cartData)
    {
        $helper = new \WC_Inforu\Helper();

        $request = [
            'Event' => 'cart_abandoned',
            'IP' => $helper->getClientIp(),
            'SubscriberStatus' => $helper->getSubscriberStatus(1),
            'AddToGroupName' => $helper->getSubscribeGroups(),
            'AvailableGroups' => $helper->getGroupIds(),
        ];
        $request = array_merge($request, $cartData);

        return apply_filters('wc_inforu_cart_abandoned_data', $request);
    }

    //
    protected function getAbandonedCart()
    {
        global $wpdb;
        $triggerTime = (int) \WC_Inforu::app()->getOption('cart_abandoned_trigger_time', 3);
        $debugMode = \WC_Inforu::app()->getOption('debug');

        $table = $wpdb->prefix . 'woocommerce_sessions';
        $query = "SELECT * FROM `$table` WHERE `updated_at` <= (NOW() - INTERVAL $triggerTime MINUTE)";

        $lastUpdated = get_option('wc_inforu_abandoned_last_updated');
        if ($lastUpdated) {
//            $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdated);
            $query .= " AND `updated_at` > '$lastUpdated'";
        } else {
            $query .= " AND `updated_at` > (NOW() - INTERVAL 30 DAY)";
        }
        $query .= " AND `session_value` <> ''";
        $query .= " ORDER BY `updated_at` ASC LIMIT 20";

//        if ($debugMode === 'yes') {
//            $file = 'inforu-' . date('Y_m_d') . '.log';
//            wplogger()->info($query, $file);
//        }

        $carts = [];
        $rows = $wpdb->get_results($query, ARRAY_A);
        if ($rows) {
            $helper = new \WC_Inforu\Helper();
            foreach ($rows as $row) {
                $lastUpdated = $row['updated_at'];
                $sessionData = unserialize($row['session_value']);
//                wplogger()->info(implode("\t", [
//                    'line: '. __LINE__,
//                    json_encode($sessionData)
//                ]));
                if (empty($sessionData['cart']) || empty($sessionData['customer'])) {
                    continue;
                }
                $customerData = unserialize($sessionData['customer']);
                if (empty($customerData['email'])) {
                    continue;
                }
                $customerEmail = $customerData['email'];
                $customerId = Arr::get('id', $customerData, '');
                $customerPhone = Arr::get('phone', $customerData, '');
                $customerFirstName = Arr::get('first_name', $customerData, '');
                $customerLastName = Arr::get('last_name', $customerData, '');

                if (!$customerFirstName || !$customerLastName) {
                    $wpuser = get_user_by($customerEmail, 'email');
                    if (!$wpuser) {
                        continue;
                    }

                    if (!$customerFirstName) {
                        $customerFirstName = get_user_meta($wpuser->ID, 'first_name');
                    }

                    if (!$customerLastName) {
                        $customerLastName = get_user_meta($wpuser->ID, 'last_name');
                    }
                    if (!$customerFirstName || !$customerLastName) {
                        continue;
                    }
                }


                $cartTotals = unserialize($sessionData['cart_totals']);
                $lineItems = unserialize($sessionData['cart']);

                $cartItems= [];
                foreach ($lineItems as $lineItem) {
                    if (empty($lineItem['product_id'])) {
                        continue;
                    }
                    $productId = !empty($lineItem['variation_id']) ? $lineItem['variation_id'] : $lineItem['product_id'];
                    $product = wc_get_product($productId);
                    $imageId = $product->get_image_id();
                    $imageUrl = '';
                    if ($imageId) {
                        $imageUrl = wp_get_attachment_image_url($imageId);
                    }
                    $cartItems[] = [
                        'ProductCode' => $product->get_sku(),
                        'ProductName' => $product->get_name(),
                        'ProductPrice' => $product->get_price('raw'),
                        'ProductQty' => $lineItem['quantity'],
                        'ProductDescription' => $product->get_description('raw'),
                        'ProductLink' => $product->get_permalink(),
                        'ProductImage' => $imageUrl
                    ];
                }

                if (!$cartItems) {
                    continue;
                }

                $cartData = [
                    'CustomerEmail' => $customerEmail,
                    'CustomerFirstName' => $customerFirstName,
                    'CustomerLastName' => $customerLastName,
                    'CustomerId' => $customerId,
                    'ContactRefId' => $customerId,
                    'PhoneNumber' => $customerPhone,
                    'SubscriberStatus' => $helper->getSubscriberStatus(1),
                    'QuoteUpdated' => $row['updated_at'],
                    'QuoteAmount' => $cartTotals['subtotal'],
                    'QuoteItems' => $cartItems
                ];

                $carts[] = $cartData;
            }
        }

        if (!$lastUpdated) {
            $lastUpdated = date('Y-m-d H:i:s');
        }
        // change last updated time
        update_option('wc_inforu_abandoned_last_updated', $lastUpdated);

        return $carts;
    }
}