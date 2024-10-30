<?php
/**
 * @category Betanet
 * @copyright Betanet (https://www.betanet.co.il/)
 */
namespace WC_Inforu_Newsletter;

class Subscribe
{
    const STATUS_UNSUBSCRIBED = 0;
    const STATUS_SUBSCRIBED = 1;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $columns = [
        'email',
        'user_id',
        'status',
        'change_status_at'
    ];

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'inforu_subscribe';
    }

    /**
     * @param array $data
     * @return bool|int
     */
    public function save($data)
    {
        if (!empty($data['id'])) {
            return $this->update($data);
        }

        return $this->add($data);
    }

    /**
     * @param array $data
     * @return bool|int
     */
    protected function add($data)
    {
        $bindData = $this->filterColumns($data);

        global $wpdb;
        return $wpdb->insert($this->table, $bindData);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filterColumns($data)
    {
        $columns = $this->columns;

        $found = array_filter($data, function($item) use ($columns) {
            return in_array($item, $columns);
        }, ARRAY_FILTER_USE_KEY);

        return $found;
    }

    /**
     * @param array $data
     * @return bool|int
     */
    protected function update($data)
    {
        $bindData = $this->filterColumns($data);

        global $wpdb;
        return $wpdb->update($this->table, $bindData, ['id' => $data['id']]);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getByEmail($email)
    {
        global $wpdb;
        $sql = sprintf('SELECT * FROM `%s` WHERE `email` = \'%s\' LIMIT 1', $this->table, $email);

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        global $wpdb;
        $sql = sprintf('SELECT * FROM `%s` WHERE `id` = \'%s\' LIMIT 1', $this->table, $id);

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * @param array $args
     * @return array|null|object
     */
    public function getRows($args = [])
    {
        $defaults = [
            'limit' => 20,
            'current_page' => 1,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];
        $args = array_merge($defaults, $args);

        $limit = $args['limit'];
        $offset = ($args['current_page'] - 1) * $limit;
        $orderby = $args['orderby'];
        $order = $args['order'];

        $sql = vsprintf('SELECT * FROM `%s` ORDER BY `%s` %s LIMIT %d OFFSET %d', [
            $this->table,
            $orderby,
            $order,
            $limit,
            $offset
        ]);

        global $wpdb;
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * @return null|string
     */
    public function count()
    {
        $sql = sprintf('SELECT COUNT(*) FROM `%s`', $this->table);

        global $wpdb;
        return $wpdb->get_var($sql);
    }
}