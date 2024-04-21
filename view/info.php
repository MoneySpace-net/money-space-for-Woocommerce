<?php

global $wp_version;
global $woocommerce;

use MoneySpace\Mslogs;

$gateways = WC()->payment_gateways->get_available_payment_gateways();
$ms_secret_id = $gateways['moneyspace']->settings['secret_id'];
$ms_secret_key = $gateways['moneyspace']->settings['secret_key'];

$datetime = $_GET['datetime'];
$hash = hash_hmac("sha256", $datetime . $ms_secret_id, $ms_secret_key);

if ($hash == $_GET['hash']) {


    $request = wp_remote_get('https://www.moneyspace.net/merchantapi/v1/store/obj?timeHash=' . $datetime . '&secreteID=' . $ms_secret_id . '&hash=' . $hash, array());
    $response = wp_remote_retrieve_body($request);


    $response_array = json_decode($response);
    $store = $response_array[0]->Store;
    $store_name = null;
    $store_tel = null;
    $store_logo = null;

    if ($store) {
        $store_name = $store[0]->name;
        $store_tel = $store[0]->logo;
        $store_logo = $store[0]->telephone;
    }

    $ms_log = new Mslogs();
    $logs = $ms_log->get();

} else {
    $shop_page_url = get_permalink(wc_get_page_id('shop'));
    wp_redirect($shop_page_url);
}


function ca_get_woo_version_number()
{
    // If get_plugins() isn't available, require it
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Create the plugins folder and file variables
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    // If the plugin version number is set, return it 
    if (isset($plugin_folder[$plugin_file]['Version'])) {
        return $plugin_folder[$plugin_file]['Version'];
    } else {
        // Otherwise return null
        return NULL;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>wp_info</title>
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/bootstrap/css/bootstrap.min.css"); ?>">
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/fonts/font-awesome.min.css"); ?>">
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/css/Forum---Thread-listing-1.css"); ?>">
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/css/Forum---Thread-listing.css"); ?>">
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/css/Pricing-Table---EspacioBinariocom.css"); ?>">
    <link rel="stylesheet" href="<?php _e(MNS_ROOT_URL . "includes/assets/css/styles.css"); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

</head>

<body>
    <section class="pricing py-5">
        <div class="container" style="margin-top: 40px;">
            <div class="row">
                <div class="col col-lg-4">
                    <div class="card mb-5 mb-lg-0">
                        <div class="card-body">
                            <h5 class="text-uppercase text-center text-muted card-title">PHP &amp; Wordpress</h5>
                            <h6 class="text-center card-subtitle mb-2 card-price"></h6>
                            <hr>
                            <ul class="fa-ul">
                                <li><?= PHP_VERSION ?><span class="fa-li"><i class="fab fa-php" style="color: rgb(27 105 241);"></i></span></li>
                                <li><?= $wp_version ?><span class="fa-li"><i class="fab fa-wordpress" style="color: rgb(27 105 241);"></i></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4">
                    <div class="card mb-5 mb-lg-0">
                        <div class="card-body">
                            <h5 class="text-uppercase text-center text-muted card-title">Woocommerce</h5>
                            <h6 class="text-center card-subtitle mb-2 card-price"></h6>
                            <hr>
                            <ul class="fa-ul">
                                <li><?= ca_get_woo_version_number() ?><span class="fa-li"><i class="fas fa-info-circle" style="color: rgb(27 105 241);"></i></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4">
                    <div class="card mb-5 mb-lg-0">
                        <div class="card-body">
                            <h5 class="text-uppercase text-center text-muted card-title">Moneyspace</h5>
                            <h6 class="text-center card-subtitle mb-2 card-price"></h6>
                            <hr>
                            <ul class="fa-ul">
                                <?php if ($store) { ?>
                                    <li>Secret id / key<span class="fa-li"><i class="fa fa-check" style="color: rgb(116,248,35);"></i></span></li>
                                <?php } else { ?>
                                    <li>Secret id / key<span class="fa-li"><i class="fas fa-times" style="color: rgb(255 7 7);"></i></span></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <div class="container" style="margin-top: 30px;">
        <table id="example" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Datetime</th>
                    <th>Description</th>
                    <th>Response</th>
                    <th>Other</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $data) { ?>
                    <tr>
                        <td>
                            <?php switch ($data->m_func_type) {
                                case 1:
                                    echo "CreditCard 1";
                                    break;
                                case 2:
                                    echo "CreditCard 2";
                                    break;
                                case 3:
                                    echo "QR Code";
                                    break;
                                case 4:
                                    echo "Installment";
                                    break;
                                case 5:
                                    echo "Check order";
                                    break;
                                case 6:
                                    echo "Update order";
                                    break;
                                case 7:
                                    echo "Cancel QR Code";
                                    break;
                                case 8:
                                    echo "Webhook";
                                    break;
                            ?>
                                    <?= $data->m_func_type ?>
                            <?php } ?>
                        </td>
                        <td><?= $data->m_datetime ?></td>
                        <td><?= $data->m_func_desc ?></td>
                        <td>
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#ms_response<?=$data->id?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                                ดู
                            </a>
                            <div class="collapse" id="ms_response<?=$data->id?>">
                                <div class="card card-body">
                                    <?= $data->response ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a class="btn btn-primary" data-bs-toggle="collapse" href="#ms_other<?=$data->id?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                                ดู
                            </a>
                            <div class="collapse" id="ms_other<?=$data->id?>">
                                <div class="card card-body">
                                    <?= $data->m_other ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>

        </table>

    </div>

    <script>
        $(document).ready(function() {
            $('#example').DataTable();
        });
    </script>

    <script src="<?php _e(MNS_ROOT_URL . "includes/assets/bootstrap/js/bootstrap.min.js"); ?>"></script>

</body>

</html>