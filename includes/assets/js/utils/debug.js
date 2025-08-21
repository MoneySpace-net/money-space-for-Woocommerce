/**
 * MoneySpace Debug Utility
 * 
 * Provides conditional logging that only outputs in debug mode
 * Security: Debug mode requires authentication or specific environment
 */

/**
 * Check if current environment allows debug mode
 * @returns {boolean}
 */
const isProductionEnvironment = () => {
    // Check if we're in production via WordPress
    return (typeof window !== 'undefined' && 
            window.moneyspaceConfig && 
            window.moneyspaceConfig.environment === 'production');
};

/**
 * Check if user is authorized for debug mode
 * @returns {boolean}
 */
const isDebugAuthorized = () => {
    // Only allow debug for admin users or specific capability
    return (typeof window !== 'undefined' && 
            window.moneyspaceConfig && 
            (window.moneyspaceConfig.userCanDebug === true || 
             window.moneyspaceConfig.isAdmin === true));
};

/**
 * Generate debug token for secure debug mode
 * @returns {string}
 */
const getDebugToken = () => {
    if (typeof window !== 'undefined' && window.moneyspaceConfig) {
        return window.moneyspaceConfig.debugToken || '';
    }
    return '';
};

/**
 * Check if debug mode is enabled with security controls
 * @returns {boolean}
 */
export const isDebugMode = () => {
    // In production, require authorization and valid token
    if (isProductionEnvironment()) {
        // Method 1: Admin/authorized users only
        if (isDebugAuthorized()) {
            return (
                (typeof window !== 'undefined' && window.moneyspaceDebug === true) ||
                (typeof window !== 'undefined' && localStorage.getItem('moneyspace_debug') === 'true')
            );
        }
        
        // Method 2: Secure token-based debug (for support staff)
        const urlParams = new URLSearchParams(window.location.search);
        const debugToken = urlParams.get('debug_token');
        const validToken = getDebugToken();
        
        if (debugToken && validToken && debugToken === validToken) {
            return true;
        }
        
        // No debug mode in production for unauthorized users
        return false;
    }
    
    // Development/staging environment - allow simple debug activation
    return (
        (typeof window !== 'undefined' && window.location.search.includes('debug=1')) ||
        (typeof window !== 'undefined' && window.moneyspaceDebug === true) ||
        (typeof process !== 'undefined' && process.env.NODE_ENV === 'development') ||
        (typeof window !== 'undefined' && localStorage.getItem('moneyspace_debug') === 'true')
    );
};

/**
 * Debug logging function - only logs when debug mode is enabled
 * @param {string} message - The message to log
 * @param {any} data - Optional data to log
 */
export const debugLog = (message, data = null) => {
    if (isDebugMode()) {
        if (data !== null) {
            console.log(`[MoneySpace] ${message}`, data);
        } else {
            console.log(`[MoneySpace] ${message}`);
        }
    }
};

/**
 * Debug error logging - always logs errors but with debug prefix when in debug mode
 * @param {string} message - The error message
 * @param {any} error - Optional error object
 */
export const debugError = (message, error = null) => {
    const prefix = isDebugMode() ? '[MoneySpace Error] ' : '';
    if (error !== null) {
        console.error(`${prefix}${message}`, error);
    } else {
        console.error(`${prefix}${message}`);
    }
};

/**
 * Debug warning logging - only logs when debug mode is enabled
 * @param {string} message - The warning message
 * @param {any} data - Optional data to log
 */
export const debugWarn = (message, data = null) => {
    if (isDebugMode()) {
        if (data !== null) {
            console.warn(`[MoneySpace] ${message}`, data);
        } else {
            console.warn(`[MoneySpace] ${message}`);
        }
    }
};

export default {
    isDebugMode,
    debugLog,
    debugError,
    debugWarn
};
