<?php
/**
 * @category UPS
 * @copyright UPS Company
 */
namespace WC_Inforu\Helper;

class Arr
{
    /**
     * @param int|string $key
     * @param array $arr
     * @param null|mixed $default
     * @return mixed|null
     */
    public static function get($key, $arr, $default = null)
    {
        if (!is_array($arr)) {
            return $default;
        }

        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}