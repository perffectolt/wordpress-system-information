<?php
defined('ABSPATH') or die('Direct script access disallowed.');

function export_system_information() {
    check_admin_referer('export_system_info');
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    $data = get_system_information();
    $filename = 'system-information.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function get_system_information() {
    global $wpdb;
    $data = array(
        array('Section', 'Info'),
        array('PHP Version', PHP_VERSION),
        array('PHP Modules', implode(', ', get_loaded_extensions())),
        array('Database Server', DB_HOST),
        array('Database Name', DB_NAME),
        array('Database User', DB_USER),
    );
    return $data;
}

add_action('admin_post_export_system_info', 'export_system_information');