<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu;

class Logger
{
    protected $dir;

    protected $file;

    public function __construct()
    {
        $this->dir = WP_CONTENT_DIR . '/uploads/logs';
        $this->file = $this->dir . '/inforu-' . date('Y_m_d') . '.log';

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }

        if (!is_file($this->file)) {
            touch($this->file);
        }
    }

    /**
     * @param string $message
     * @return bool|int
     */
    public function info($message)
    {
        return $this->addRow('INFO', $message);
    }

    /**
     * @param string $level
     * @param string $message
     * @return bool|int
     */
    public function addRow($level, $message)
    {
        if (!is_file($this->file) || !is_writable($this->file)
            || \WC_Inforu::app()->getOption('debug') !== 'yes'
        ) {
            return false;
        }

        $text = implode(' ', [
            date('[Y-m-d H:i:s]'),
            strtoupper($level) . ':',
            $message,
            PHP_EOL
        ]);

        return file_put_contents($this->file, $text, FILE_APPEND | LOCK_EX);
    }
}