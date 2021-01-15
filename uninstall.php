<?php

// If uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    delete_option($option);
    exit ();
}
