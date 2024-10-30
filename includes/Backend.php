<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu;

use WC_Inforu\Event\CartAbandoned;

/**
 * Class Backend
 * @package WC_Inforu
 * @deprecated
 * @see \WC_Inforu\App
 */
class Backend implements AppInterface
{
    protected $options;

    protected $storeInfo = [];

    public function __construct()
    {
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_filter('woocommerce_integrations', array($this, 'addIntegration'));
    }

    public function addIntegration($integrations)
    {
        $integrations[] = \WC_Inforu\Integration::class;

        return $integrations;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
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
        $action = new CartAbandoned();
        $action->execute();
    }

    protected function loadOptions()
    {
        if ($this->options === null) {
            $this->options = get_option('woocommerce_'. \WC_Inforu::TEXT_DOMAIN .'_settings', []);
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
}