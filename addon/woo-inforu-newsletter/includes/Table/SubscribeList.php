<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */

namespace WC_Inforu_Newsletter\Table;

use WC_Inforu_Newsletter\Subscribe;

if (!class_exists('WP_List_Table')) {
    require_once(realpath(ABSPATH . 'wp-admin/includes/class-wp-list-table.php'));
}

class SubscribeList extends \WP_List_Table
{
    public function __construct($args = array())
    {
        parent::__construct($args);

        add_filter('manage_woocommerce_page_wc-inforu-newsletter_columns', [$this, 'get_columns']);
    }

    function get_columns()
    {
        $columns = array(
            'email' => __('Email', 'wcin'),
            'status' => __('Status', 'wcin'),
            'created_at' => __('Created At')
        );

        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'created_at' => array('created_at', false),
        );

        return $sortable_columns;
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $limit = $this->get_items_per_page('subscribe_per_page', 20);
        $model = new Subscribe();
        $this->set_pagination_args([
            'total_items' => $model->count(), //WE have to calculate the total number of items
            'per_page' => $limit //WE have to determine how many items to show on a page
        ]);

        $currPage = $this->get_pagenum();

        $args = [
            'limit' => $limit,
            'current_page' => $currPage
        ];
        if (isset($_GET['orderby'])) {
            $args['orderby'] = sanitize_text_field($_GET['orderby']);
            $args['order'] = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
        }

        $this->items = $model->getRows($args);
    }

    /**
     * @param object $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            default:
                return $item[$column_name];
        }
    }

    function column_status($item)
    {
        if (isset($item['status']) && $item['status'] == Subscribe::STATUS_SUBSCRIBED) {
            $html = '<span class="order-status status-processing">';
            $html .= '<span>';
            $html .= __('Subscribed');
            $html .= '</span>';
            $html .= '</span>';
        } else {
            $html = '<span class="order-status status-pending">';
            $html .= '<span>';
            $html .= __('Unsubscribed');
            $html .= '</span>';
            $html .= '</span>';
        }

        return $html;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-update[]" value="%s" />', $item['id']
        );
    }

    /**
     * Text displayed when no subscribe data is available
     */
    public function no_items()
    {
        _e('No subscribe avaliable.', 'wcin');
    }
}