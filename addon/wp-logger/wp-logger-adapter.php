<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
class WP_Logger_Adapter
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $file;

    public function __construct()
    {
        $this->dir = WP_CONTENT_DIR . '/uploads/logs';
    }

    /**
     * @param string $message
     * @param string|null $fileName
     * @return bool
     */
    public function write($message, $fileName = null)
    {
        if (!$fileName) {
            $fileName = 'wp-logger.log';
        }
        $this->file = $this->dir . '/' . $fileName;

        if ((!is_file($this->file) && !$this->createFile()) || !is_writable($this->file)) {
            return false;
        }

        $message = PHP_EOL . $message;
        return file_put_contents($this->file, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return bool
     */
    protected function createFile()
    {
        if ((!is_dir($this->dir) && !$this->createDir()) || !is_writable($this->dir)) {
            return false;
        }

        return touch($this->file);
    }

    /**
     * @return bool
     */
    protected function createDir()
    {
        if (is_dir($this->dir)) {
            return true;
        }

        return mkdir($this->dir, 0775, true);
    }
}