<?php

namespace MoneySpace;

class MNS_Payform
{

    public static function init()
    {
        add_action('mns_router_generate_routes', array(get_class(), 'generate_routes'), 10, 1);
    }

    public static function generate_routes(MNS_Router $router)
    {
        $router->add_route('payform', array(
            'path' => '^ms/payform',
            'query_vars' => array(
                'pid' => 1,
            ),
            'page_callback' => function () {
                # code ..
            },
            'page_arguments' => array('pid'),
            'access_callback' => TRUE,
            'template' => array('../includes/moneyspace_payment_form.js'
            , dirname(__FILE__) . DIRECTORY_SEPARATOR . '../includes/moneyspace_payment_form.js')
        ));
    }
}
