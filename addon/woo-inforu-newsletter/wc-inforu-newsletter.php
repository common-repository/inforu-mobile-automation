<?php
/**
 * Plugin Name: Woocommerce Inforu Newsletter
 * Plugin URI: https://pickuppoint.co.il/Documentation/WP
 * Description: A simple add-on integration newsletter with Woocommerce Inforun Integration
 * Version: 1.0.0
 * Author: Betanet
 * Author URI: https://www.betanet.co.il/
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wcin
 * Domain Path: /i18n/languages
 * WC requires at least: 4.0.0
 * WC tested up to: 4.9.4
 * @category    Integration
 * @copyright   Copyright: (c) 2020 Betanet
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

class WC_Inforu_Newsletter
{
    const VERSION = '1.0.0';

    protected static $instance;

    /**
     * @var \WC_Inforu_Newsletter\View
     */
    public $view;

    /**
     * @var \WC_Inforu_Newsletter\Observer
     */
    protected $observer;

    /**
     * @var \WC_Inforu_Newsletter\Helper
     */
    public $helper;

    /**
     * @return WC_Inforu_Newsletter
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function run()
    {
        spl_autoload_register(__CLASS__ . '::autoload');

        $this->registerHook();
        $this->shortcodes();
        $this->observer()->observe();
    }

    protected function registerHook()
    {
        register_activation_hook(__FILE__, ['WC_Inforu_Newsletter', 'install']);
        register_deactivation_hook(__FILE__, ['WC_Inforu_Newsletter', 'uninstall']);
    }

    /**
     * Register shortcodes
     */
    protected function shortcodes()
    {
        add_shortcode('wcin_form', [\WC_Inforu_Newsletter\Shortcode::class, 'subscribeForm']);
    }

    /**
     * @return \WC_Inforu_Newsletter\View
     */
    public static function view()
    {
        if (!self::instance()->view) {
            self::instance()->view = new \WC_Inforu_Newsletter\View();
        }

        return self::instance()->view;
    }

    /**
     * @return \WC_Inforu_Newsletter\Helper
     */
    public static function helper()
    {
        if (!self::instance()->helper) {
            self::instance()->helper = new \WC_Inforu_Newsletter\Helper();
        }

        return self::instance()->helper;
    }

    /**
     * @return \WC_Inforu_Newsletter\Observer
     */
    public function observer()
    {
        if (!$this->observer) {
            $this->observer = new \WC_Inforu_Newsletter\Observer();
        }

        return $this->observer;
    }

    public static function install()
    {
        $setup = new \WC_Inforu_Newsletter\Setup();
        $setup->run();
    }

    public static function uninstall()
    {
        //
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
        if ($prefix !== 'WC_Inforu_Newsletter') {
            return;
        }

        $file = __DIR__ . '/includes/' . implode('/', $parts) . '.php';
        $file = realpath($file);
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

WC_Inforu_Newsletter::instance()->run();
