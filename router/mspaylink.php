<?php

class MNS_Paylink
{

    public static function init()
    {
        add_action('mns_router_generate_routes', array(get_class(), 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('mspaylink', array(
            'path' => '^mspaylink/(.*?)$',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/mspaylink.php', dirname(__FILE__) . DIRECTORY_SEPARATOR . '../view/mspaylink.php')
        ));
    }
}
