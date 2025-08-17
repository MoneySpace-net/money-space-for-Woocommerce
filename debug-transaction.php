<?php
/**
 * MoneySpace Transaction Debugger
 * To# Common failure reasons for this type of transaction
echo "<h4>🚨 Common Failure Reasons for " . safe_html($bank_code ?? 'Credit Card') . " Transactions:</h4>"; to investigate transaction failures
 */

// Simple HTML escape function for non-WordPress context
function safe_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Transaction ID to investigate
$transaction_id = 'KTBCC1708252158074498283';

echo "<h2>🔍 MoneySpace Transaction Debugger</h2>";
echo "<h3>Transaction ID: " . safe_html($transaction_id) . "</h3>";

// Analyze the transaction ID format
echo "<h4>📊 Transaction ID Analysis:</h4>";
echo "<ul>";

// Parse the transaction ID components
if (preg_match('/^(KTB|BAY|FCY)CC(\d{13})(\d+)$/', $transaction_id, $matches)) {
    $bank_code = $matches[1];
    $timestamp_part = $matches[2];
    $sequence_part = $matches[3];
    
    echo "<li><strong>Bank Code:</strong> " . $bank_code . "</li>";
    echo "<li><strong>Timestamp Part:</strong> " . $timestamp_part . "</li>";
    echo "<li><strong>Sequence Part:</strong> " . $sequence_part . "</li>";
    
    // Try to decode timestamp (if it's Unix timestamp in milliseconds)
    $timestamp_seconds = substr($timestamp_part, 0, 10);
    $date = date('Y-m-d H:i:s', (int)$timestamp_seconds);
    echo "<li><strong>Estimated Date:</strong> " . $date . " (if Unix timestamp)</li>";
    
    // Analyze bank
    $bank_names = [
        'KTB' => 'KTbank',
        'BAY' => 'Bank of Ayudhya',
        'FCY' => 'First Choice Pay'
    ];
    $bank_name = isset($bank_names[$bank_code]) ? $bank_names[$bank_code] : 'Unknown Bank';
    echo "<li><strong>Bank:</strong> " . $bank_name . "</li>";
} else {
    echo "<li><strong>Format:</strong> Does not match expected pattern (BANKCC + timestamp + sequence)</li>";
}

echo "</ul>";

// Common failure reasons for this type of transaction
echo "<h4>🚨 Common Failure Reasons for " . esc_html($bank_code ?? 'Credit Card') . " Transactions:</h4>";
echo "<ol>";
echo "<li><strong>Card Declined by Bank:</strong> Insufficient funds, card blocked, or spending limit exceeded</li>";
echo "<li><strong>3D Secure Authentication Failed:</strong> Customer didn't complete OTP verification</li>";
echo "<li><strong>Invalid Card Details:</strong> Wrong card number, expiry date, or CVV</li>";
echo "<li><strong>Network Timeout:</strong> Connection timeout during payment processing</li>";
echo "<li><strong>Bank System Error:</strong> Temporary issues with bank's payment system</li>";
echo "<li><strong>Merchant Configuration:</strong> Incorrect MoneySpace API credentials</li>";
echo "<li><strong>Amount Validation:</strong> Transaction amount exceeds daily/monthly limits</li>";
echo "<li><strong>Currency Issues:</strong> Unsupported currency or conversion problems</li>";
echo "</ol>";

// Debug steps
echo "<h4>🔧 Debug Steps to Check:</h4>";
echo "<ol>";
echo "<li><strong>Check WordPress Error Logs:</strong>";
echo "<br><code>tail -f /path/to/wp-content/debug.log | grep MoneySpace</code></li>";

echo "<li><strong>Enable WP_DEBUG in wp-config.php:</strong>";
echo "<br><code>define('WP_DEBUG', true);<br>define('WP_DEBUG_LOG', true);</code></li>";

echo "<li><strong>Check MoneySpace API Response:</strong> Look for API response in logs</li>";

echo "<li><strong>Verify Order Status in WooCommerce:</strong> Check if order status was updated</li>";

echo "<li><strong>Check MoneySpace Dashboard:</strong> Login to MoneySpace merchant portal to see transaction details</li>";

echo "<li><strong>Test with Debug Mode:</strong>";
echo "<br><code>localStorage.setItem('moneyspace_debug', 'true');</code></li>";
echo "</ol>";

// SQL Query to find related orders (if this is run in WordPress context)
if (function_exists('get_posts')) {
    echo "<h4>🔍 Search for Related Orders:</h4>";
    
    // Search for orders with this transaction ID
    $orders = get_posts([
        'post_type' => 'shop_order',
        'meta_query' => [
            [
                'key' => 'MNS_transaction_orderid',
                'value' => $transaction_id,
                'compare' => '='
            ]
        ],
        'post_status' => 'any',
        'numberposts' => 10
    ]);
    
    if (!empty($orders)) {
        foreach ($orders as $order_post) {
            $order = wc_get_order($order_post->ID);
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Order #" . $order->get_id() . "</strong><br>";
            echo "Status: " . $order->get_status() . "<br>";
            echo "Total: " . $order->get_total() . " " . $order->get_currency() . "<br>";
            echo "Date: " . $order->get_date_created()->format('Y-m-d H:i:s') . "<br>";
            echo "Payment Method: " . $order->get_payment_method_title() . "<br>";
            
            // Get failure reason if available
            $failure_reason = get_post_meta($order->get_id(), 'MNS_failure_reason', true);
            if ($failure_reason) {
                echo "<strong style='color: red;'>Failure Reason:</strong> " . safe_html($failure_reason) . "<br>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No orders found with transaction ID: " . safe_html($transaction_id) . "</p>";
        
        // Search by partial transaction ID
        $partial_search = substr($transaction_id, -8); // Last 8 characters
        echo "<p>Searching by partial ID: " . $partial_search . "</p>";
        
        $partial_orders = get_posts([
            'post_type' => 'shop_order',
            'meta_query' => [
                [
                    'key' => 'MNS_transaction_orderid',
                    'value' => $partial_search,
                    'compare' => 'LIKE'
                ]
            ],
            'post_status' => 'any',
            'numberposts' => 5
        ]);
        
        if (!empty($partial_orders)) {
            echo "<h5>Similar Transaction IDs found:</h5>";
            foreach ($partial_orders as $order_post) {
                $order = wc_get_order($order_post->ID);
                $found_tx_id = get_post_meta($order->get_id(), 'MNS_transaction_orderid', true);
                echo "Order #" . $order->get_id() . " - " . $found_tx_id . " (" . $order->get_status() . ")<br>";
            }
        }
    }
}

// Manual investigation tools
echo "<h4>🛠️ Manual Investigation Tools:</h4>";
?>

<div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
<h5>Run these commands in browser console on checkout page:</h5>
<pre>
// Enable MoneySpace debugging
localStorage.setItem('moneyspace_debug', 'true');
location.reload();

// Check if transaction exists in browser storage
console.log('MoneySpace Debug Data:', localStorage.getItem('moneyspace_debug_data'));

// Monitor network requests
// Open Developer Tools → Network tab → look for MoneySpace API calls

// Check for JavaScript errors
// Open Developer Tools → Console tab → look for errors
</pre>
</div>

<div style="background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;">
<h5>🎯 Quick Fix Recommendations:</h5>
<ul>
<li><strong>For Customers:</strong> Try a different card or contact your bank</li>
<li><strong>For Merchants:</strong> Check MoneySpace dashboard for detailed error message</li>
<li><strong>For Developers:</strong> Enable debug logging and check API response</li>
</ul>
</div>

<?php
echo "<h4>📞 Next Steps:</h4>";
echo "<p>1. <strong>Check MoneySpace Merchant Dashboard</strong> for detailed transaction logs</p>";
echo "<p>2. <strong>Contact MoneySpace Support</strong> with transaction ID if issue persists</p>";
echo "<p>3. <strong>Enable debug logging</strong> to capture more details for future transactions</p>";
echo "<p>4. <strong>Test with a different card</strong> to isolate if it's card-specific</p>";
?>
