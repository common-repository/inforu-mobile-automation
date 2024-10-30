<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter;

class Shortcode
{
    public static function subscribeForm()
    {
        \WC_Inforu_Newsletter::view()->render('subscribe-form.php');
    }
}
