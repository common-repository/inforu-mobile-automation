<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter\Form;

use WC_Inforu_Newsletter\Subscribe as Model;

class Subscribe extends AbstractForm
{
    public function handle()
    {
        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->isAjax()) {
                $this->sendJson([
                    'error' => 1,
                    'message' => __('Please enter a valid email address', 'wcin')
                ]);
            } else {
                $this->goBack();
            }
        }

        $model = new Model();
        $subscribe = $model->getByEmail($email);

        if ($subscribe) {
            if ($this->isAjax()) {
                $this->sendJson([
                    'error' => 1,
                    'message' => __('This email address is already subscribed', 'wcin')
                ]);
            } else {
                $this->goBack();
            }
        }

        $data = [
            'email' => $email,
            'status' => Model::STATUS_SUBSCRIBED,
        ];

        $userId = \WC_Inforu_Newsletter::helper()->getUserIdByEmail($email);
        if ($userId) {
            $data['user_id'] = $userId;
        }

        $model->save($data);

        do_action('wc_inforu_save_newsletter', $data['email'], 1);
        do_action('wcin_subscribe_after', $data);

        if ($this->isAjax()) {
            $this->sendJson([
                'error' => 0,
                'message' => __('Thank you for your subscription', 'wcin')
            ]);
        } else {
            $this->goBack();
        }
    }
}