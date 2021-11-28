<?php

class MNS_Connect_GW
{

    public static function init()
    {
        add_action('mns_router_generate_routes', array(get_class(), 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('test-connect-gw', array(
            'path' => '^ms/test-connect-gw',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../view/test-connect-gw.php', dirname(__FILE__) . DIRECTORY_SEPARATOR . '../view/test-connect-gw.php')
        ));
    }
}
