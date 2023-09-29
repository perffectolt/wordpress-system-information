<?php
defined('ABSPATH') or die('Direct script access disallowed.');

class System_Information {

    public function __construct() {
        add_action('admin_menu', array($this, 'create_plugin_menu_item'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function create_plugin_menu_item() {
        add_menu_page('System Information', 'System Information', 'manage_options', 'system_information', array($this, 'display_plugin_page'), 'dashicons-admin-tools', 200);
    }

    public function display_plugin_page() {
        echo '<div class="wrap">';
        echo '<h2>System Information</h2>';
        $this->display_web_server_info();
        $this->display_php_info();
        $this->display_db_info();
        $this->display_cache_info();
        $this->display_proxy_info();
        $this->display_export_button();
        $this->display_users_info();
        echo '</div>';
    }

    private function display_users_info() {
        echo '<h3>WordPress Users Information</h3>';
    
        // Get all users
        $users = get_users();
    
        // Check if there are users
        if (empty($users)) {
            echo '<p>No users found.</p>';
            return;
        }
    
        // Start table
        echo '<table style="width:100%; border-collapse: collapse;">';
        echo '<tr>';
        echo '<th style="border: 1px solid #dddddd; padding: 8px;">Username</th>';
        echo '<th style="border: 1px solid #dddddd; padding: 8px;">Email</th>';
        echo '<th style="border: 1px solid #dddddd; padding: 8px;">Display Name</th>';
        echo '<th style="border: 1px solid #dddddd; padding: 8px;">Roles</th>';
        echo '</tr>';
    
        // Iterate through each user and create a table row
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td style="border: 1px solid #dddddd; padding: 8px;">' . esc_html($user->user_login) . '</td>';
            echo '<td style="border: 1px solid #dddddd; padding: 8px;">' . esc_html($user->user_email) . '</td>';
            echo '<td style="border: 1px solid #dddddd; padding: 8px;">' . esc_html($user->display_name) . '</td>';
            echo '<td style="border: 1px solid #dddddd; padding: 8px;">' . esc_html(implode(', ', $user->roles)) . '</td>';
            echo '</tr>';
        }
    
        // End table
        echo '</table>';
    }
    

    private function display_php_info() {
        echo '<h3>PHP Information</h3>';
        echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
        
        // Get the list of loaded extensions and create a list
        $loaded_extensions = get_loaded_extensions();
        sort($loaded_extensions);  // Sort extensions alphabetically
        
        // Start table
        echo '<p>PHP Modules:</p>';
        echo '<table style="width:100%;">';
        
        // Initialize column counter
        $col_counter = 0;
        $cols_per_row = 5;
    
        // Iterate through each extension and create a table cell
        foreach ($loaded_extensions as $extension) {
            if ($col_counter % $cols_per_row == 0) {
                echo '<tr>';  // Start a new row if it's the first column
            }
            echo '<td>' . $extension . '</td>';
            $col_counter++;
            if ($col_counter % $cols_per_row == 0) {
                echo '</tr>';  // End the row if it's the last column
            }
        }
    
        // Fill in empty cells if necessary and close the last row
        while ($col_counter % $cols_per_row != 0) {
            echo '<td></td>';
            $col_counter++;
        }
        if ($col_counter % $cols_per_row == 0) {
            echo '</tr>';  // End the row if it's the last column
        }
    
        // End table
        echo '</table>';
    }

    private function display_web_server_info() {
        echo '<h3>Web Server Information</h3>';
    
        // Attempt to get the server software
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    
        // Attempt to identify the web server from the SERVER_SOFTWARE variable
        if (stripos($server_software, 'apache') !== false) {
            echo '<p>Web Server: Apache</p>';
        } elseif (stripos($server_software, 'nginx') !== false) {
            echo '<p>Web Server: Nginx</p>';
        } elseif (stripos($server_software, 'litespeed') !== false) {
            echo '<p>Web Server: LiteSpeed</p>';
        } elseif (stripos($server_software, 'microsoft-iis') !== false) {
            echo '<p>Web Server: Microsoft IIS</p>';
        } elseif (stripos($server_software, 'caddy') !== false) {
            echo '<p>Web Server: Caddy</p>';
        } else {
            echo '<p>Web Server: ' . esc_html($server_software) . ' (not recognized)</p>';
        }
    }
    

    private function display_db_info() {
        echo '<h3>Database Information</h3>';
        
        // Database server type
        global $wpdb;
        $db_type = '';
        if ($wpdb->use_mysqli) {
            $db_type = 'MySQL or MariaDB'; // WordPress uses the same driver for MySQL and MariaDB
        } elseif (extension_loaded('pdo_sqlite')) {
            $db_type = 'SQLite';
        } elseif (extension_loaded('pdo_pgsql')) {
            $db_type = 'PostgreSQL';
        } elseif (extension_loaded('mssql')) {
            $db_type = 'MS SQL Server';
        }
        echo '<p>Database Type: ' . $db_type . '</p>';
    
        // Database server
        $db_host = DB_HOST;
        echo '<p>Database Host: ' . $db_host . '</p>';
    
        // Database name
        $db_name = DB_NAME;
        echo '<p>Database Name: ' . $db_name . '</p>';
    
        // Database user
        $db_user = DB_USER;
        echo '<p>Database User: ' . $db_user . '</p>';
    
        // Database password
        echo '<p>Database Password: ' . DB_PASSWORD . '</p>';
    }
    

    private function display_cache_info() {
        echo '<h3>Cache Information</h3>';
        
        // OPcache
        if (function_exists('opcache_get_status')) {
            $opcache = opcache_get_status(false);
            echo '<p>OPcache: Enabled</p>';
            if ($opcache && isset($opcache['opcache_statistics'])) {
                echo '<p>OPcache Hits: ' . $opcache['opcache_statistics']['hits'] . '</p>';
                echo '<p>OPcache Misses: ' . $opcache['opcache_statistics']['misses'] . '</p>';
            }
        } else {
            echo '<p>OPcache: Disabled</p>';
        }
    
        // APC (Alternative PHP Cache)
        if (function_exists('apc_cache_info') && function_exists('apc_sma_info')) {
            echo '<p>APC: Enabled</p>';
            $user_cache = apc_cache_info('user');
            $sma_info = apc_sma_info();
            echo '<p>APC User Cache Entries: ' . count($user_cache['cache_list']) . '</p>';
            echo '<p>APC Memory Size: ' . $sma_info['seg_size'] . '</p>';
        } else {
            echo '<p>APC: Disabled</p>';
        }
    
        // APCu (APC User Cache)
        if (function_exists('apcu_cache_info') && function_exists('apcu_sma_info')) {
            echo '<p>APCu: Enabled</p>';
            $cache_info = apcu_cache_info(true);
            $sma_info = apcu_sma_info();
            echo '<p>APCu Cached Variables: ' . $cache_info['num_entries'] . '</p>';
            echo '<p>APCu Memory Size: ' . $sma_info['seg_size'] . '</p>';
        } else {
            echo '<p>APCu: Disabled</p>';
        }
    
        // Memcached
        if (class_exists('Memcached')) {
            $memcached = new Memcached();
            echo '<p>Memcached: Enabled</p>';
            // Additional checks and statistics for Memcached can be added here
        } else {
            echo '<p>Memcached: Disabled</p>';
        }
    
        // Redis
        if (class_exists('Redis')) {
            $redis = new Redis();
            echo '<p>Redis: Enabled</p>';
            // Additional checks and statistics for Redis can be added here
        } else {
            echo '<p>Redis: Disabled</p>';
        }
    
        // Varnish
        if (function_exists('varnish_stat')) {
            echo '<p>Varnish: Enabled</p>';
            // Additional checks and statistics for Varnish can be added here
        } else {
            echo '<p>Varnish: Disabled</p>';
        }
    
        // Apache mod_cache
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (in_array('mod_cache', $modules)) {
                echo '<p>Apache mod_cache: Enabled</p>';
            } else {
                echo '<p>Apache mod_cache: Disabled</p>';
            }
        }
    
        // WP Super Cache
        if (function_exists('wp_cache_flush')) {
            echo '<p>WP Super Cache: Enabled</p>';
        } else {
            echo '<p>WP Super Cache: Disabled</p>';
        }
    
        // Object Cache (WordPress)
        global $wp_object_cache;
        if (isset($wp_object_cache->cache_hits)) {
            echo '<p>Object Cache Hits (WordPress): ' . $wp_object_cache->cache_hits . '</p>';
            echo '<p>Object Cache Misses (WordPress): ' . $wp_object_cache->cache_misses . '</p>';
        } else {
            echo '<p>Object Cache (WordPress): Not available</p>';
        }
    
        // Add checks for other caching mechanisms as necessary
    }
    
    

    private function display_proxy_info() {
        echo '<h3>Proxy Information</h3>';
    
        // Check for common proxy headers
        $proxy_headers = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_VIA',
            'HTTP_X_COMING_FROM',
            'HTTP_COMING_FROM',
            'HTTP_CLIENT_IP'
        );
    
        $found = false;
        foreach ($proxy_headers as $header) {
            if (!empty($_SERVER[$header])) {
                echo '<p>Proxy Detected - Header: ' . $header . ', Value: ' . $_SERVER[$header] . '</p>';
                $found = true;
                break;
            }
        }
    
        if (!$found) {
            echo '<p>No proxy detected.</p>';
        }
    }
    private function display_export_button() {
        echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
        echo '<input type="hidden" name="action" value="export_plugin_homepage" />';
        wp_nonce_field('export_plugin_homepage');
        echo '<input type="submit" value="Export Plugin Homepage" />';
        echo '</form>';
    }

    public function enqueue_styles() {
        // enqueue your styles here
    }
}

new System_Information();
