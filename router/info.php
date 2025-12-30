<?php

namespace MoneySpace;
use MoneySpace\MNS_Router;

class MNS_Info
{

    public static function init()
    {
        add_action('moneyspace_router_generate_routes', array(__CLASS__, 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('info', array(
            'path' => '^ms/info',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/info.php', MoneySpacePayment::plugin_abspath() . 'view/info.php')
        ));
    }
}
