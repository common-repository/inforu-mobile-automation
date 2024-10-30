<?php
/**
 * Plugin Name: InforUMobile Automation
 * Plugin URI: https://www.inforu.co.il/%d7%a4%d7%aa%d7%a8%d7%95%d7%a0%d7%95%d7%aa-%d7%9c-ecommerce/
 * Description: A subscriber marketing plugin for WooCommerce
 * Version: 1.3.1
 * Author: InforUMobile
 * Author URI: https://www.inforu.co.il/
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: inforu-mobile-automation
 * Domain Path: /i18n/languages
 * WC requires at least: 4.0.0
 * WC tested up to: 4.9.4
 * @category    Integration
 * @copyright   Copyright: (c) 2020 InforUMobile
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if (!defined('ABSPATH')) return;

class WC_Inforu
{
    const TEXT_DOMAIN = 'inforu-mobile-automation';

    const VERSION = '1.3.1';

    static $instance;

    /**
     * @var \WC_Inforu\App
     */
    protected $app;

    public function registerHooks()
    {
//        add_action('plugin_loaded', [$this, 'run']);
        add_action('wc_inforu_cron_cart_abandoned', [$this->getApp(), 'handleCartAbandoned']);
        add_action('wc_inforu_cron_send_orders', [$this->getApp(), 'sendOrders']);

        register_activation_hook(__FILE__, ['WC_Inforu', 'install']);
        register_deactivation_hook(__FILE__, ['WC_Inforu', 'uninstall']);
    }

    /**
     * @return WC_Inforu
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return $this
     */
    public function run()
    {
        spl_autoload_register(__CLASS__ . '::autoload');

        $this->registerHooks();
        $this->getApp()->run();
        $this->loadAddon();

        return $this;
    }

    /**
     * @return \WC_Inforu\App
     */
    public function getApp()
    {
        if (!$this->app) {
            $this->app = new \WC_Inforu\App();
        }
        return $this->app;
    }

    /**
     * Load dependencies add-on(s)
     */
    public function loadAddon()
    {
        require_once realpath(__DIR__ . '/addon/wp-logger/wp-logger.php');
        $newsletterEnabled = $this->getApp()->getOption('addon_newsletter');
        if ($newsletterEnabled === 'yes') {
            require_once realpath(__DIR__ . '/addon/woo-inforu-newsletter/wc-inforu-newsletter.php');
        }
    }

    /**
     * @param string $name
     */
    public static function autoload($name)
    {
        if (class_exists($name)) {
            return;
        }

        $parts = explode('\\', $name);
        $prefix = array_shift($parts);
        if ($prefix !== 'WC_Inforu') {
            return;
        }

        $file = __DIR__ . '/includes/' . implode('/', $parts) . '.php';
        $file = realpath($file);
        if (file_exists($file)) {
            require_once $file;
        }
    }

    /**
     * @return \WC_Inforu\App
     */
    public static function app()
    {
        return self::instance()->getApp();
    }

    public static function install()
    {
        wp_schedule_event(time(), 'every_minute', 'wc_inforu_cron_cart_abandoned');
        wp_schedule_event(time(), 'every_minute', 'wc_inforu_cron_send_orders');

        $setup = new \WC_Inforu\Setup();
        $setup->run();

        // call add-on install scripts
        require_once realpath(__DIR__ . '/addon/woo-inforu-newsletter/wc-inforu-newsletter.php');
        call_user_func(['WC_Inforu_Newsletter', 'install']);
    }

    public static function uninstall()
    {
        $timestamp = wp_next_scheduled('wc_inforu_cron_cart_abandoned');
        wp_unschedule_event($timestamp, 'wc_inforu_cron_cart_abandoned');
        wp_clear_scheduled_hook('wc_inforu_cron_cart_abandoned');
        $timestamp = wp_next_scheduled('wc_inforu_cron_send_orders');
        wp_unschedule_event($timestamp, 'wc_inforu_cron_send_orders');
        wp_clear_scheduled_hook('wc_inforu_cron_send_orders');
    }
}

WC_Inforu::instance()->run();
