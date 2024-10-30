<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu;

use WC_Inforu\Event\PlaceOrder;
use WC_Inforu\Event\RegisterSuccess;
use WC_Inforu\Event\SaveAccount;
use WC_Inforu\Event\SaveNewsletter;

/**
 * Class Frontend
 * @package WC_Inforu
 * @deprecated
 * @see \WC_Inforu\App
 */
class Frontend extends Backend implements AppInterface
{
    public function registerHooks()
    {
        add_action('woocommerce_created_customer', array($this, 'handleRegister'));
        add_action('woocommerce_save_account_details', array($this, 'handleSaveAccount'));
        add_action('woocommerce_checkout_order_processed', array($this, 'handlePlaceOrder'), 10, 3);
    }

    /**
     * @param int $customerId
     */
    public function handleRegister($customerId)
    {
        if (\WC_Inforu::app()->getOption('event_customer_register') !== 'yes') {
            return;
        }

        $action = new RegisterSuccess();
        $action->execute($customerId);
    }

    /**
     * @param int $customerId
     */
    public function handleSaveAccount($customerId)
    {
        if (\WC_Inforu::app()->getOption('event_customer_save_account') !== 'yes') {
            return;
        }

        $action = new SaveAccount();
        $action->execute($customerId);
    }

    /**
     * @param int $orderId
     * @param array $data
     * @param \WC_Order $order
     */
    public function handlePlaceOrder($orderId, $data, $order)
    {
        if (\WC_Inforu::app()->getOption('event_place_order') !== 'yes') {
            return;
        }

        $action = new PlaceOrder();
        $action->execute($order, $data);
    }

    public function handleSaveNewsletter($customer)
    {
        $action = new SaveNewsletter();
        $action->execute($customer);
    }
}