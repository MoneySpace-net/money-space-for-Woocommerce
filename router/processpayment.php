<?php

namespace MoneySpace;
use MoneySpace\MNS_Router;

class MNS_Processpayment
{

    public static function init()
    {
        add_action('moneyspace_router_generate_routes', array(__CLASS__, 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('processpayment', array(
            'path' => '^process/payment/(.*?)$',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/process-payment.php', MoneySpacePayment::plugin_abspath() . 'view/process-payment.php')
        ));
    }
}
