<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
?>
<form method="post">
    <input type="email" id="wcin-subscribe" name="email" value="" required="required" />
    <input type="hidden" name="_wcinnonce" value="<?php echo wp_create_nonce('wcin_subscribe') ?>" />
    <button class="button" type="submit"><?php _e('Subscribe', 'wcin') ?></button>
</form>
