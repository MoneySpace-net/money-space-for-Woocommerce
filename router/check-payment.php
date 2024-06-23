<?php

namespace MoneySpace;
use MoneySpace\MNS_Router;

class MNS_CheckPayment
{

    public static function init()
    {
        add_action('mns_router_generate_routes', array(get_class(), 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('check-payment', array(
            'path' => '^ms/check-payment/(.*?)$',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/check-payment.php', MoneySpacePayment::plugin_abspath() . 'view/check-payment.php')
        ));
    }
}
