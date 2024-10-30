<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu;

class Integration extends \WC_Integration
{
    public function __construct()
    {
        $this->id = \WC_Inforu::TEXT_DOMAIN;
        $this->method_title = __('Inforu', \WC_Inforu::TEXT_DOMAIN);
//        $this->method_description = __('Google Analytics is a free service offered by Google that generates detailed statistics about the visitors to a website.', 'woocommerce-google-analytics-integration');
//        $this->dismissed_info_banner = get_option('woocommerce_dismissed_info_banner');

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_integration_'. \WC_Inforu::TEXT_DOMAIN, array($this, 'process_admin_options'));
//        add_action('woocommerce_update_options_integration_'. \WC_Inforu::TEXT_DOMAIN, array($this, 'show_options_info'));
    }

    /**
     * Loads all of our options for this plugin
     * @return array An array of options that can be passed to other classes
     */
    public function init_options()
    {
        $options = array(
            'event_customer_save_account',
            'event_customer_register',
            'event_newsletter_save',
            'event_place_order',
            'event_cart_abandoned',
            'cart_abandoned_trigger',
            'api_username',
            'api_token',
            'api_url',
        );

        $constructor = array();
        foreach ($options as $option) {
            $constructor[$option] = $this->$option = $this->get_option($option);
        }

        return $constructor;
    }

    /**
     * Tells WooCommerce which settings to display under the "integration" tab
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'api_setting' => array(
                'title' => __('API Setting', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'title'
            ),
            'api_username' => array(
                'title' => __('Username', \WC_Inforu::TEXT_DOMAIN),
                'description' => __('Your INFORU api username', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'text',
                'default' => ''
            ),
            'api_token' => array(
                'title' => __('Token', \WC_Inforu::TEXT_DOMAIN),
                'description' => __('Your INFORU api token', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'text',
                'checkboxgroup' => 'start',
                'default' => ''
            ),
            'api_url' => array(
                'title' => __('URL', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'text',
                'default' => ''
            ),
            'events' => array(
                'title' => __('Events', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'title'
            ),
            'event_customer_save_account' => array(
                'title' => __('Customer account edited', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'event_customer_register' => array(
                'title' => __('Customer Register Success', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'event_newsletter_save' => array(
                'title' => __('Newsletter Subscriber Save After', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'event_place_order' => array(
                'title' => __('Place Order After', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'event_cart_abandoned' => array(
                'title' => __('Cart Abandoned', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'cart_abandoned_trigger_time' => array(
                'title' => __('Minutes to Trigger Cart Abandoned', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'text',
                'default' => 3
            ),
            'order_statuses' => array(
                'title' => __('Order Statuses allow to sync', \WC_Inforu::TEXT_DOMAIN),
                'desc_tip'     => __( 'Leave empty will allow all statuses', \WC_Inforu::TEXT_DOMAIN ),
                'type' => 'multiselect',
                'css'      => 'height: 200px;',
                'default' => ['wc-processing'],
                'options'  => wc_get_order_statuses()
            ),
//            'newsletter_setting' => array(
//                'title' => __('Newsletter Settings', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'title'
//            ),
//            'newsletter_groups' => array(
//                'title' => __('Newsletter Groups', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'text',
//                'default' => ''
//            ),
//            'customer_setting' => array(
//                'title' => __('Customer Settings', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'title'
//            ),
//            'customer_address_type' => array(
//                'title' => __('Address type', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'select',
//                'default' => 'billing',
//                'options' => [
//                    'billing' => __('Billing', \WC_Inforu::TEXT_DOMAIN),
//                    'shipping' => __('Shipping', \WC_Inforu::TEXT_DOMAIN)
//                ]
//            ),
//            'customer_fields_mapping' => array(
//                'title' => __('Fields Mapping', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'select',
//                'options' => ['0' => 'Test']
//            ),
//            'customer_custom_mapping' => array(
//                'title' => __('Custom Mapping', \WC_Inforu::TEXT_DOMAIN),
//                'type' => 'select',
//                'options' => ['0' => 'Test']
//            ),
            'developer' => array(
                'title' => __('Developer', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'title'
            ),
            'debug' => array(
                'title' => __('Debug mode', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'desc_tip' => __('Enable log api request & response', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'debug_place_order' => array(
                'title' => __('Debug place order', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'desc_tip' => __('Enable log for event place order trigger on site', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'addon' => array(
                'title' => __('Add-on', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'title'
            ),
            'addon_newsletter' => array(
                'title' => __('Newsletter', \WC_Inforu::TEXT_DOMAIN),
                'label' => __('Enabled', \WC_Inforu::TEXT_DOMAIN),
                'desc_tip' => __('Enable to use INFORU built-in newsletter', \WC_Inforu::TEXT_DOMAIN),
                'type' => 'checkbox',
                'default' => 'no'
            ),
        );
    }

    /**
     * Init settings for gateways.
     */
    public function init_settings()
    {
        parent::init_settings();
        $this->enabled = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
    }
}