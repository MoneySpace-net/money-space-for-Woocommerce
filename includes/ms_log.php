<?php

namespace MoneySpace;

class Mslogs
{

    public $table_name;

    public function __construct()
    {

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ms_logs';

        // Use prepare and suppress long operations
        $table_check = $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name) );

        if ($table_check !== $this->table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$this->table_name} (
                 id mediumint(9) NOT NULL AUTO_INCREMENT,
                 response text NOT NULL,
                 m_func_type text NOT NULL,
                 m_func_desc text NOT NULL,
                 m_datetime text NOT NULL,
                 m_other text NOT NULL,
                 UNIQUE KEY id (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function insert($response, $m_type, $m_status, $m_datetime, $m_other)
    {
        global $wpdb;
        $table = $this->table_name;

        // Truncate large payloads to avoid oversized rows and timeouts
        $truncate = function($val) {
            if (!is_string($val)) {
                $val = wp_json_encode($val);
            }
            if ($val === null) {
                $val = '';
            }
            $max = 65535; // TEXT limit safeguard
            return mb_strimwidth($val, 0, $max, '');
        };

        $data = array(
            'response' => $truncate($response),
            'm_func_type' => $truncate($m_type),
            'm_func_desc' => $truncate($m_status),
            'm_datetime' => $truncate($m_datetime),
            'm_other' => $truncate($m_other)
        );
        $format = array('%s', '%s', '%s', '%s', '%s');
        $wpdb->insert($table, $data, $format);
        return $wpdb->insert_id;
    }

    public function get()
    {
      global $wpdb;
      return $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT 200");
    }

    public function getType($m_func_type)
    {
      global $wpdb;
      return $wpdb->get_results(
          $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE m_func_type = %s ORDER BY id DESC LIMIT 200", $m_func_type)
      );
    }
}