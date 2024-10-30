<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
?>
<div class="wcin-subscribe">
    <p>
        <input type="checkbox" id="wcin-subscribe" name="wcin_subscribe" value="1" <?php if (isset($subscribed) && $subscribed): ?>checked="checked"<?php endif; ?>/>
        <label for="wcin-subscribe"><?php _e('Sign Up for Newsletter', 'wcin') ?></label>
    </p>
</div>
