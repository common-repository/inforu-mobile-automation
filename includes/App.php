<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu;

use WC_Inforu\Event\CartAbandoned;
use WC_Inforu\Event\SendOrders;
use WC_Inforu\Event\PlaceOrder;
use WC_Inforu\Event\RegisterSuccess;
use WC_Inforu\Event\SaveAccount;
use WC_Inforu\Event\SaveNewsletter;

class App implements AppInterface
{
    protected $options;

    protected $storeInfo = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerHook();
    }

    protected function registerHook()
    {
        add_action('woocommerce_created_customer', array($this, 'handleRegister'));
        add_action('woocommerce_save_account_details', array($this, 'handleSaveAccount'));
//        add_action('woocommerce_checkout_order_processed', array($this, 'handlePlaceOrder'), 10, 3);
//        add_action('wc_inforu_save_newsletter', array($this, 'handleSaveNewsletter'), 10, 2);
        add_action('wc_inforu_save_newsletter', array($this, 'handleNewsletterByEmail'), 10, 2);
        add_filter('cron_schedules', [$this, 'customCronSchedule']);
//        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

        if (is_admin()) {
            add_filter('woocommerce_integrations', array($this, 'addIntegration'));
        }
    }

    public function addIntegration($integrations)
    {
        $integrations[] = \WC_Inforu\Integration::class;

        return $integrations;
    }

    /**
     * @return array
     */
    public function getStoreInfo()
    {
        if (!$this->storeInfo) {
            $this->storeInfo = [
                'StoreName' => get_option('blogname'),
                'StoreBaseUrl' => site_url(),
                'LinkToCart' => wc_get_cart_url()
            ];
        }

        return $this->storeInfo;
    }

    public function handleCartAbandoned()
    {
        if ($this->getOption('event_cart_abandoned') !== 'yes' || get_option('wc_inforu_event_cart_abandoned_running') == 1) {
            return;
        }

        update_option('wc_inforu_event_cart_abandoned_running', 1);
        try {
            $action = new CartAbandoned();
            $action->execute();
        } catch (\Exception $e) {
            //
        }
        update_option('wc_inforu_event_cart_abandoned_running', 0);
    }

    public function sendOrders()
    {
        if ($this->getOption('event_place_order') !== 'yes' || get_option('wc_inforu_event_place_order_running') == 1) {
            return;
        }

        update_option('wc_inforu_event_place_order_running', 1);
        try {
            $action = new SendOrders();
            $action->execute();
        } catch (\Exception $e) {
            //
        }

        update_option('wc_inforu_event_place_order_running', 0);
    }

    protected function loadOptions()
    {
        if ($this->options === null) {
            $this->options = get_option('woocommerce_' . \WC_Inforu::TEXT_DOMAIN . '_settings', []);
        }
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return null
     */
    public function getOption($key, $default = null)
    {
        $this->loadOptions();
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $this->loadOptions();
        return $this->options;
    }

    /**
     * @param int $customerId
     */
    public function handleRegister($customerId)
    {
        if ($this->getOption('event_customer_register') !== 'yes') {
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
        if ($this->getOption('event_customer_save_account') !== 'yes') {
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
        if ($this->getOption('event_place_order') !== 'yes') {
            return;
        }

        $action = new PlaceOrder();
        $action->execute($order);
    }

    /**
     * @param string $email
     * @param int $status
     */
    public function handleNewsletterByEmail($email, $status = 0)
    {
        if ($this->getOption('event_newsletter_save') !== 'yes') {
            return;
        }

        $action = new SaveNewsletter();
        $action->execute($email, $status);
    }

    public function customCronSchedule($schedules)
    {
        $schedules['every_minute'] = array(
            'interval' => 1 * MINUTE_IN_SECONDS,
            'display' => __('Every minute', \WC_Inforu::TEXT_DOMAIN)
        );
        return $schedules;
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('wc-inforu-scripts', plugin_dir_url(''). 'woocommerce-inforu-integration/js/scripts.js', array('jquery'), null, true);
        wp_localize_script('wc-inforu-scripts', 'wcInforuConfig',
            array(
                'cronUrl' => get_option('siteurl') . '/wp-cron.php',
            )
        );
    }
}