<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu\Event;

use WC_Inforu\Api;

class PlaceOrder
{
    /**
     * @param \WC_Order $order
     */
    public function execute($order)
    {
        if (\WC_Inforu::app()->getOption('debug_place_order') === 'yes'
            && function_exists('debug_backtrace')
        ) {
            $file = 'inforu-' . date('Y_m_d') . '.log';
            $info = json_encode(debug_backtrace());
            wplogger()->info($info, $file);
        }
        $request = $this->buildData($order);
        try {
            $api = new Api();
            $api->setData($request)->doRequest();
        } catch (\Exception $e) {
            //
        }

    }

    /**
     * @param \WC_Order $order
     * @return array
     */
    public function buildData($order)
    {
        $helper = new \WC_Inforu\Helper();
        $customerIp = get_post_meta($order->get_id(), '_customer_ip_address', true);

        $request = [
            'Event' => 'sales_order_place_after',
            'IP' => ($customerIp ? $customerIp : $helper->getClientIp()),
            'SubscriberStatus' => $helper->getSubscriberStatus(1),
            'AddToGroupName' => $helper->getSubscribeGroups(),
            'AvailableGroups' => $helper->getGroupIds(),
            'ContactRefId' => $order->get_customer_id() ?: '',
            'CustomerEmail' => $order->get_billing_email(),
            'PhoneNumber' => $order->get_billing_phone(),
            'CustomerFirstName' => $order->get_billing_first_name(),
            'CustomerLastName' => $order->get_billing_last_name(),
            'OrderNumber' => $order->get_id(),
            'OrderAmount' => $order->get_total('raw'),
        ];
        $items = [];
        /** @var \WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item) {
            /** @var \WC_Product $product */
            $product = $item->get_product();
            $imageId = $product->get_image_id();
            $imageUrl = '';
            if ($imageId) {
                $imageUrl = wp_get_attachment_image_url($imageId);
            }
            $lineItem = [
                'ProductCode' => $product->get_sku(),
                'ProductName' => $item->get_name(),
                'ProductPrice' => $product->get_price(),
                'ProductQty' => $item->get_quantity(),
                'ProductDescription' => $product->get_description(),
                'ProductLink' => $product->get_permalink(),
                'ProductImage' => $imageUrl,
            ];

            $items[] = $lineItem;
        }
        $request['OrderItems'] = $items;

        if ($order->get_customer_id()) {
            $customer = new \WC_Customer($order->get_customer_id());
            $request['ContactRefId'] = $customer->get_id();
        }

        $storeInfo = \WC_Inforu::app()->getStoreInfo();
        $request = array_merge($storeInfo, $request);

        return apply_filters('wc_inforu_place_order_data', $request);
    }
}