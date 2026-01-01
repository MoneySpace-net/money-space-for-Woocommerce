<?php

global $wp_version;
global $woocommerce;

use MoneySpace\Mslogs;
use MoneySpace\MoneySpacePayment;

$moneyspace_gateways = WC()->payment_gateways->get_available_payment_gateways();
$moneyspace_secret_id = $moneyspace_gateways['moneyspace']->settings['secret_id'];
$moneyspace_secret_key = $moneyspace_gateways['moneyspace']->settings['secret_key'];

$moneyspace_datetime = filter_input(INPUT_GET, 'datetime', FILTER_SANITIZE_STRING);
$moneyspace_hash = hash_hmac("sha256", $moneyspace_datetime . $moneyspace_secret_id, $moneyspace_secret_key);

$moneyspace_provided_hash = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING);
// Optional nonce for info route to satisfy WPCS recommendation.
$moneyspace_nonce = filter_input(INPUT_GET, 'ms_nonce', FILTER_SANITIZE_STRING);
$moneyspace_nonce_valid = $moneyspace_nonce ? wp_verify_nonce($moneyspace_nonce, 'moneyspace_info') : false;
if ($moneyspace_hash && $moneyspace_provided_hash && hash_equals($moneyspace_hash, $moneyspace_provided_hash) && ( empty($moneyspace_nonce) || $moneyspace_nonce_valid )) {


    $moneyspace_request = wp_remote_get('https://www.moneyspace.net/merchantapi/v1/store/obj?timeHash=' . $moneyspace_datetime . '&secreteID=' . $moneyspace_secret_id . '&hash=' . $moneyspace_hash, array());
    $moneyspace_response = wp_remote_retrieve_body($moneyspace_request);


    $moneyspace_response_array = json_decode($moneyspace_response);
    $moneyspace_store = $moneyspace_response_array[0]->Store;
    $moneyspace_store_name = null;
    $moneyspace_store_tel = null;
    $moneyspace_store_logo = null;

    if ($moneyspace_store) {
        $moneyspace_store_name = $moneyspace_store[0]->name;
        $moneyspace_store_tel = $moneyspace_store[0]->logo;
        $moneyspace_store_logo = $moneyspace_store[0]->telephone;
    }

    $moneyspace_log = new Mslogs();
    $moneyspace_logs = $moneyspace_log->get();

} else {
    $moneyspace_shop_page_url = get_permalink(wc_get_page_id('shop'));
    wp_safe_redirect(esc_url_raw($moneyspace_shop_page_url));
    exit;
}


function moneyspace_ca_get_woo_version_number()
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

// Helper: versioning for local assets to avoid caching and satisfy plugin check
function moneyspace_asset_version($relative_path) {
    $file = MoneySpacePayment::plugin_abspath() . ltrim($relative_path, '/');
    if (file_exists($file)) {
        return (string) filemtime($file);
    }
    return '2.14.0';
}

?>

<?php
// Enqueue assets (with versions) before rendering the page
wp_enqueue_style('moneyspace-bootstrap', MONEYSPACE_ROOT_URL . 'includes/assets/bootstrap/css/bootstrap.min.css', array(), moneyspace_asset_version('includes/assets/bootstrap/css/bootstrap.min.css'));
wp_enqueue_style('moneyspace-fontawesome-local', MONEYSPACE_ROOT_URL . 'includes/assets/fonts/font-awesome.min.css', array(), moneyspace_asset_version('includes/assets/fonts/font-awesome.min.css'));
wp_enqueue_style('moneyspace-thread-listing-1', MONEYSPACE_ROOT_URL . 'includes/assets/css/Forum---Thread-listing-1.css', array('moneyspace-bootstrap'), moneyspace_asset_version('includes/assets/css/Forum---Thread-listing-1.css'));
wp_enqueue_style('moneyspace-thread-listing', MONEYSPACE_ROOT_URL . 'includes/assets/css/Forum---Thread-listing.css', array('moneyspace-bootstrap'), moneyspace_asset_version('includes/assets/css/Forum---Thread-listing.css'));
wp_enqueue_style('moneyspace-pricing-table', MONEYSPACE_ROOT_URL . 'includes/assets/css/Pricing-Table---EspacioBinariocom.css', array('moneyspace-bootstrap'), moneyspace_asset_version('includes/assets/css/Pricing-Table---EspacioBinariocom.css'));
wp_enqueue_style('moneyspace-styles', MONEYSPACE_ROOT_URL . 'includes/assets/css/styles.css', array('moneyspace-bootstrap'), moneyspace_asset_version('includes/assets/css/styles.css'));
wp_enqueue_script('jquery');
wp_enqueue_script('moneyspace-bootstrap', MONEYSPACE_ROOT_URL . 'includes/assets/bootstrap/js/bootstrap.min.js', array('jquery'), moneyspace_asset_version('includes/assets/bootstrap/js/bootstrap.min.js'), true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>wp_info</title>
    <?php wp_head(); ?>
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
                                <li><?php echo esc_html(PHP_VERSION); ?><span class="fa-li"><i class="fab fa-php" style="color: rgb(27 105 241);"></i></span></li>
                                <li><?php echo esc_html($wp_version); ?><span class="fa-li"><i class="fab fa-wordpress" style="color: rgb(27 105 241);"></i></span></li>
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
                                <li><?php echo esc_html(moneyspace_ca_get_woo_version_number()); ?><span class="fa-li"><i class="fas fa-info-circle" style="color: rgb(27 105 241);"></i></span></li>
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
                                <?php if ($moneyspace_store) { ?>
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
                <?php foreach ($moneyspace_logs as $moneyspace_data) { ?>
                    <tr>
                        <td>
                            <?php
                            switch ((int) $moneyspace_data->m_func_type) {
                                case 1:
                                    echo esc_html('CreditCard 1');
                                    break;
                                case 2:
                                    echo esc_html('CreditCard 2');
                                    break;
                                case 3:
                                    echo esc_html('QR Code');
                                    break;
                                case 4:
                                    echo esc_html('Installment');
                                    break;
                                case 5:
                                    echo esc_html('Check order');
                                    break;
                                case 6:
                                    echo esc_html('Update order');
                                    break;
                                case 7:
                                    echo esc_html('Cancel QR Code');
                                    break;
                                case 8:
                                    echo esc_html('Webhook');
                                    break;
                                default:
                                    echo esc_html($moneyspace_data->m_func_type);
                                    break;
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($moneyspace_data->m_datetime); ?></td>
                        <td><?php echo esc_html($moneyspace_data->m_func_desc); ?></td>
                        <td>
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#ms_response<?php echo esc_attr($moneyspace_data->id); ?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                                ดู
                            </a>
                            <div class="collapse" id="ms_response<?php echo esc_attr($moneyspace_data->id); ?>">
                                <div class="card card-body">
                                    <?php echo esc_html($moneyspace_data->response); ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a class="btn btn-primary" data-bs-toggle="collapse" href="#ms_other<?php echo esc_attr($moneyspace_data->id); ?>" role="button" aria-expanded="false" aria-controls="collapseExample">
                                ดู
                            </a>
                            <div class="collapse" id="ms_other<?php echo esc_attr($moneyspace_data->id); ?>">
                                <div class="card card-body">
                                    <?php echo esc_html($moneyspace_data->m_other); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>

        </table>

    </div>

    <?php wp_footer(); ?>

</body>

</html>