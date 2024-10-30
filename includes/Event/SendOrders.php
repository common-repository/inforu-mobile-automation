<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu\Event;

use WC_Inforu\Api;
use WC_Inforu\Helper\Arr;

class SendOrders
{
    public function execute()
    {
        $orders = $this->getOrders();
        if (!$orders) {
            return;
        }

        $placeOrderAction = new PlaceOrder();
        foreach ($orders as $order) {
            try {
                $placeOrderAction->execute($order);
                $order->add_meta_data('inforu_sync_flag', 1, true);
                $order->save_meta_data();
            } catch (\Exception $e) {
                $file = 'inforu-' . date('Y_m_d') . '.log';
                wplogger()->info($e->getMessage(), $file);
            }
        }
    }

    /**
     * @return \WC_Order[]
     */
    protected function getOrders()
    {
        $orders = [];

        $allowedOrderStatuses = \WC_Inforu::app()->getOption('order_statuses');
        if ($allowedOrderStatuses) {
            $orderStatuses = $allowedOrderStatuses;
        } else {
            $orderStatuses = ['wc-processing'];
        }

        $args = [
            'post_type' => 'shop_order',
            'post_status' => $orderStatuses,
            'posts_per_page' => 15,
            'orderby' => 'post_date',
            'order' => 'ASC',
            'date_query' => [
                [
                    'column' => 'post_date',
                    'after' => '-1 day'
                ]
            ],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'inforu_sync_flag',
                    'value' => null,
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => 'inforu_sync_flag',
                    'value' => ''
                ],
                [
                    'key' => 'inforu_sync_flag',
                    'value' => '0'
                ]
            ]
        ];
        $query = new \WP_Query($args);

        while ($query->have_posts()) {
            $query->the_post();
            $order = wc_get_order($query->post->ID);
            if ($order) {
                $orders[] = $order;
            }
        }

        return $orders;
    }
}
