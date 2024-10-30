<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter;

class View
{
    public function render($template, $data = [])
    {
        $file = $this->getTemplate($template);
        if (!file_exists($file)) {
            return '';
        }

        if ($data) {
            extract($data);
        }

        include $file;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getTemplate($file)
    {
        $path = $this->getTemplateDir() . '/templates/' . $file;

        return apply_filters('wcin_template_file', realpath($path));
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        $dir = dirname(__DIR__);

        return apply_filters('wcin_template_dir', $dir);
    }
}