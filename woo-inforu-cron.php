<?php
/**
 * @category Inforu
 * @copyright Inforu Company
 */

if (!defined('WC_INFORU_BASEDIR')) {
    define('WC_INFORU_BASEDIR', dirname(__DIR__, 3));
}

$wpLoadFile = WC_INFORU_BASEDIR . DIRECTORY_SEPARATOR . 'wp-load.php';
$wpLoadFile = realpath($wpLoadFile);

if (!file_exists($wpLoadFile)) {
    return;
}

require_once $wpLoadFile;

do_action('wc_inforu_cron_send_orders');
do_action('wc_inforu_cron_cart_abandoned');
