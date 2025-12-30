<?php

namespace MoneySpace;
use MoneySpace\MNS_Router;

class MNS_Webhook
{

    public static function init()
    {
        add_action('moneyspace_router_generate_routes', array(__CLASS__, 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('webhook', array(
            'path' => '^ms/webhook',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/webhook.php', MoneySpacePayment::plugin_abspath() . 'view/webhook.php')
        ));
    }
}
