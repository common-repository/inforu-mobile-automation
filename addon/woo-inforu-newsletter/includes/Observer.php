<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu_Newsletter;

use WC_Inforu_Newsletter\Table\SubscribeList;

class Observer
{
    public function observe()
    {
        // hooks
        add_action('wp', [$this, 'handleForm']);
        add_action('woocommerce_checkout_order_processed', array($this, 'orderProcessed'), 10, 3);
        add_action('woocommerce_checkout_before_terms_and_conditions', [$this, 'checkoutCheckbox']);
        add_action('woocommerce_edit_account_form', [$this, 'myAccountCheckbox']);
        add_action('woocommerce_save_account_details', [$this, 'saveMyAccount'], 1);
        add_action('woocommerce_register_form', [$this, 'registerCheckbox'], 1);
        add_action('woocommerce_created_customer', array($this, 'registerSuccess'), 10, 2);
        add_action('admin_menu', array($this, 'newsletterMenu'), 50);

        add_filter('wc_inforu_place_order_data', [$this, 'inforuPlaceOrderData']);
        add_filter('wc_inforu_save_account_data', [$this, 'inforuSaveAccountData']);
        add_filter('wc_inforu_register_success_data', [$this, 'inforuRegisterSuccess']);
    }

    public function handleForm()
    {
        if (empty($_POST['_wcinnonce'])) {
            return;
        }

        switch (true) {
            case wp_verify_nonce($_POST['_wcinnonce'], 'wcin_subscribe'):
                $form = new \WC_Inforu_Newsletter\Form\Subscribe();
                $form->handle();
                break;
        }
    }

    /**
     * @param int $orderId
     * @param array $data
     * @param \WC_Order $order
     */
    public function orderProcessed($orderId, $data, $order)
    {
        if (!isset($_REQUEST['wcin_subscribe'])) {
            return;
        }

        $email = $order->get_billing_email();
        if (!$email) {
            return;
        }

        $model = new Subscribe();
        $subscribe = $model->getByEmail($email);
        if ($subscribe) {
            return;
        }

        $data = [
            'email' => $email,
            'status' => Subscribe::STATUS_SUBSCRIBED
        ];
        $userId = \WC_Inforu_Newsletter::helper()->getUserIdByEmail($email);
        if ($userId) {
            $data['user_id'] = $userId;
        }

        $model->save($data);
    }

    /**
     * @param int $userId
     */
    public function saveMyAccount($userId)
    {
        $user = get_user_by('id', $userId);
        $status = isset($_POST['wcin_subscribe']) ? sanitize_key($_POST['wcin_subscribe']) : 0;
        $data = [
            'user_id' => $user->ID,
            'email' => $user->user_email,
            'status' => $status
        ];

        $model = new Subscribe();
        $subscribe = $model->getByEmail($user->user_email);
        if ($subscribe) {
            $data = array_merge($data, [
                'id' => $subscribe['id'],
                'change_status_at' => date('Y-m-d H:i:s')
            ]);
        }
        $model->save($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function inforuPlaceOrderData($data)
    {
        if (!is_user_logged_in()) {
            if (!empty($_REQUEST['wcin_subscribe'])) {
                $data['SubscriberStatus'] = 'Subscribed';
            }
        } else {
            $user = get_user_by('id', get_current_user_id());
            $model = new Subscribe();
            $subscribe = $model->getByEmail($user->user_email);
            if ($subscribe && $subscribe['status'] == Subscribe::STATUS_SUBSCRIBED) {
                $data['SubscriberStatus'] = 'Subscribed';
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function inforuSaveAccountData($data)
    {
        if (is_user_logged_in()) {
            $user = get_user_by('id', get_current_user_id());
            $model = new Subscribe();
            $subscribe = $model->getByEmail($user->user_email);
            if ($subscribe && $subscribe['status'] == Subscribe::STATUS_SUBSCRIBED) {
                $data['SubscriberStatus'] = 'Subscribed';
            } else {
                $data['SubscriberStatus'] = 'Unsubscribed';
            }

        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function inforuRegisterSuccess($data)
    {
        if (!empty($_REQUEST['wcin_subscribe'])) {
            $data['SubscriberStatus'] = 'Subscribed';
        }

        return $data;
    }

    /**
     * @return string
     */
    public function checkoutCheckbox()
    {
        if (is_user_logged_in()) {
            return '';
        }

        \WC_Inforu_Newsletter::view()->render('subscribe-checkbox.php');
        return '';
    }

    /**
     * @return string
     */
    public function registerCheckbox()
    {
        if (is_user_logged_in()) {
            return '';
        }

        \WC_Inforu_Newsletter::view()->render('subscribe-checkbox.php');
        return '';
    }

    /**
     * @param int $customerId
     * @param array $customerData
     */
    public function registerSuccess($customerId, $customerData)
    {
        if (!empty($_REQUEST['wcin_subscribe']) && !empty($customerData['user_email'])) {
            $email = sanitize_email($customerData['user_email']);
            $model = new Subscribe();
            $data = [
                'email' => $email,
                'user_id' => $customerId,
                'status' => Subscribe::STATUS_SUBSCRIBED,
            ];
            $model->save($data);
        }
    }

    /**
     * Add subscribe checkbox
     */
    public function myAccountCheckbox()
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $subscribed = false;
        $user = get_user_by('id', get_current_user_id());
        $model = new Subscribe();
        $subscribe = $model->getByEmail($user->user_email);
        if ($subscribe && $subscribe['status'] == Subscribe::STATUS_SUBSCRIBED) {
            $subscribed = true;
        }

        \WC_Inforu_Newsletter::view()->render('subscribe-checkbox.php', [
            'subscribed' => $subscribed
        ]);
        return '';
    }

    /**
     * Register menu page
     */
    public function newsletterMenu()
    {
        add_submenu_page('woocommerce', __('Inforu Newsletter', 'wcin'), __('Newsletter', 'wcin'), 'manage_woocommerce', 'wc-inforu-newsletter', array($this, 'listingPage'));
    }

    /**
     * Render list newsletter
     */
    public function listingPage()
    {
        wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array());

        $table = new SubscribeList();
        $table->prepare_items();
        $table->display();
    }
}
