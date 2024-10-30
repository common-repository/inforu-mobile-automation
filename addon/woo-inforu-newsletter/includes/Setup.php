<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter;

class Setup
{
    public function run()
    {
        $version = get_option('wc_inforu_newsletter_setup_version');
        if (!$version || version_compare($version, '1.0.0', '<')) {
            $this->addNewsletterTable();
        }

        $this->end();
    }

    protected function addNewsletterTable()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'inforu_subscribe';
        $sql = "CREATE TABLE `$table` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `change_status_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);
    }

    protected function end()
    {
        update_option('wc_inforu_newsletter_setup_version', \WC_Inforu_Newsletter::VERSION);
    }
}