<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu;

class Setup
{
    public function run()
    {
        $version = get_option('wc_inforu_setup_version');
        if (!$version || version_compare($version, '1.0.0', '<')) {
            $this->addSessionTimestamp();
        }

        $this->end();
    }

    protected function addSessionTimestamp()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'woocommerce_sessions';
        $wpdb->query("ALTER TABLE `$table` ADD `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    protected function end()
    {
        update_option('wc_inforu_setup_version', \WC_Inforu::VERSION);
    }
}