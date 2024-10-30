<?php
/*
Plugin Name: WP Logger
Plugin URI:
Description: Plugin provide logger api allow developer add log to their plugin
Author: Betanet
Version: 1.0.0
Author URI: https://www.betanet.co.il/
Network: True
Text Domain: wplogger
Domain Path: /languages/
*/

require_once 'wp-logger-adapter.php';

class WP_Logger
{
    protected static $instance;

    /**
     * @var WP_Logger_Adapter
     */
    protected $adapter;

    /**
     * @return WP_Logger
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $message
     * @param null|string $fileName
     * @return bool
     */
    public function critical($message, $fileName = null)
    {
        return $this->add($message, 'CRITICAL', $fileName);
    }

    /**
     * @param string $message
     * @param null|string $fileName
     * @return bool
     */
    public function debug($message, $fileName = null)
    {
        return $this->add($message, 'DEBUG', $fileName);
    }

    /**
     * @param string $message
     * @param null|string $fileName
     * @return bool
     */
    public function error($message, $fileName = null)
    {
        return $this->add($message, 'ERROR', $fileName);
    }

    /**
     * @param string $message
     * @param null|string $fileName
     * @return bool
     */
    public function info($message, $fileName = null)
    {
        return $this->add($message, null, $fileName);
    }

    /**
     * @param string $message
     * @param null|string $level
     * @param null|string $fileName
     * @return bool
     */
    public function add($message, $level = null, $fileName = null)
    {
        switch (strtoupper($level)) {
            default:
            case 'INFO':
                $prefix = 'wplogger.INFO';
                break;

            case 'CRITICAL':
                $prefix = 'wplogger.CRITICAL';
                break;

            case 'DEBUG':
                $prefix = 'wplogger.DEBUG';
                break;

            case 'ERROR':
                $prefix = 'wplogger.ERROR';
                break;
        }

        $message = implode(' ', [
            date('[Y-m-d H:i:s]'),
            $prefix,
            $message
        ]);

        return $this->getAdapter()->write($message, $fileName);
    }

    /**
     * @return WP_Logger_Adapter
     */
    protected function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = new WP_Logger_Adapter();
        }

        return $this->adapter;
    }
}

/**
 * @return WP_Logger
 */
function wplogger()
{
    return WP_Logger::instance();
}