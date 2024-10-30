<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter\Form;

abstract class AbstractForm
{
    public function isAjax()
    {
        $httpRequestedWith = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower(sanitize_text_field($_SERVER['HTTP_X_REQUESTED_WITH'])) : '';
        return isset($_REQUEST['_ajax']) || $httpRequestedWith === 'xmlhttprequest';
    }

    public function sendJson($data)
    {
        header('Content-type: application/json');
        echo wp_json_encode($data);
        exit;
    }

    public function goBack()
    {
        wp_safe_redirect(wp_get_raw_referer());
        exit;
    }
}
