<?php

namespace MoneySpace;

class Mslogs
{

    public $table_name;

    public function __construct()
    {

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ms_logs';

        $table_check = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'");

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
        $data = array(
            'response' => $response,
            'm_func_type' => $m_type,
            'm_func_desc' => $m_status,
            'm_datetime' => $m_datetime,
            'm_other' => $m_other
        );
        $format = array('%s', '%s', '%s', '%s', '%s');
        $wpdb->insert($table, $data, $format);
        return $wpdb->insert_id;
    }

    public function get()
    {
      global $wpdb;
      return $wpdb->get_results("SELECT * FROM {$this->table_name}");
    }

    public function getType($m_func_type)
    {
      global $wpdb;
      return $wpdb->get_results(
          $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE m_func_type = %s", $m_func_type)
      );
    }
}