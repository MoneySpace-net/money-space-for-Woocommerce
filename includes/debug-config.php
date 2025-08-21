<?php
/**
 * Secure Debug Configuration
 * Provides security controls for debug mode activation
 */

if (!defined('ABSPATH')) {
    exit;
}

class MoneySpace_Debug_Config {
    
    /**
     * Check if current environment is production
     */
    public static function is_production() {
        // Check WordPress environment
        if (defined('WP_ENVIRONMENT_TYPE')) {
            return constant('WP_ENVIRONMENT_TYPE') === 'production';
        }
        
        // Check if debug constants suggest production
        if (defined('WP_DEBUG') && WP_DEBUG === false && 
            defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === false) {
            return true;
        }
        
        // Check for production-like domain patterns
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $production_patterns = [
            '/^(?!.*\.(dev|test|staging|local))/i',
            '/\.(com|net|org|co)$/i'
        ];
        
        foreach ($production_patterns as $pattern) {
            if (preg_match($pattern, $host)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if current user can access debug mode
     */
    public static function user_can_debug() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Allow administrators
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Allow users with specific capability
        if (current_user_can('moneyspace_debug')) {
            return true;
        }
        
        // Allow shop managers for WooCommerce
        if (current_user_can('manage_woocommerce')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate secure debug token
     */
    public static function generate_debug_token() {
        // Only generate for authorized users
        if (!self::user_can_debug()) {
            return '';
        }
        
        // Create time-limited token
        $user_id = get_current_user_id();
        $timestamp = time();
        $expiry = $timestamp + (30 * 60); // 30 minutes
        
        // Create secure hash
        $data = $user_id . '|' . $expiry . '|' . SECURE_AUTH_KEY;
        $hash = hash_hmac('sha256', $data, SECURE_AUTH_KEY);
        
        return base64_encode($user_id . '|' . $expiry . '|' . $hash);
    }
    
    /**
     * Validate debug token
     */
    public static function validate_debug_token($token) {
        if (empty($token)) {
            return false;
        }
        
        $decoded = base64_decode($token);
        if (!$decoded) {
            return false;
        }
        
        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($user_id, $expiry, $hash) = $parts;
        
        // Check expiry
        if (time() > intval($expiry)) {
            return false;
        }
        
        // Verify hash
        $data = $user_id . '|' . $expiry . '|' . SECURE_AUTH_KEY;
        $expected_hash = hash_hmac('sha256', $data, SECURE_AUTH_KEY);
        
        if (!hash_equals($expected_hash, $hash)) {
            return false;
        }
        
        // Verify user still has debug permissions
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return false;
        }
        
        // Set current user context for permission check
        wp_set_current_user($user_id);
        
        return self::user_can_debug();
    }
    
    /**
     * Enqueue debug configuration for JavaScript
     */
    public static function enqueue_debug_config() {
        $config = [
            'environment' => self::is_production() ? 'production' : 'development',
            'userCanDebug' => self::user_can_debug(),
            'isAdmin' => current_user_can('manage_options'),
            'debugToken' => self::generate_debug_token(),
            'debugEnabled' => defined('MONEYSPACE_DEBUG') && constant('MONEYSPACE_DEBUG'),
        ];
        
        // Only include sensitive data for authorized users
        if (!self::user_can_debug()) {
            $config['debugToken'] = '';
        }
        
        wp_localize_script('moneyspace-debug-config', 'moneyspaceConfig', $config);
    }
    
    /**
     * Handle debug token validation from URL
     */
    public static function handle_debug_request() {
        if (!isset($_GET['debug_token'])) {
            return;
        }
        
        $token = sanitize_text_field($_GET['debug_token']);
        
        if (self::validate_debug_token($token)) {
            // Set session flag for debug mode
            if (!session_id()) {
                session_start();
            }
            $_SESSION['moneyspace_debug_authorized'] = time() + (30 * 60);
            
            // Log the debug access
            error_log(sprintf(
                '[MoneySpace Debug] Authorized debug access for user %d at %s',
                get_current_user_id(),
                current_time('mysql')
            ));
        }
    }
}

// Initialize debug configuration
add_action('wp_enqueue_scripts', function() {
    // Register debug config script
        wp_register_script(
            'moneyspace-debug-config',
            '',
            [],
            '2.13.5',
            true
        );    MoneySpace_Debug_Config::enqueue_debug_config();
});

// Handle debug token requests
add_action('init', [MoneySpace_Debug_Config::class, 'handle_debug_request']);

// Add debug capability to admin users
add_action('init', function() {
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_role->add_cap('moneyspace_debug');
    }
    
    $shop_manager_role = get_role('shop_manager');
    if ($shop_manager_role) {
        $shop_manager_role->add_cap('moneyspace_debug');
    }
});
