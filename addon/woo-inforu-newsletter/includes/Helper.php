<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter;

class Helper
{
    /**
     * @param string $email
     * @return int|bool
     */
    public function getUserIdByEmail($email)
    {
        $userdata = \WP_User::get_data_by('email', $email);
        if ($userdata && $userdata->ID) {
            return $userdata->ID;
        }

        return false;
    }
}