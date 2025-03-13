<?php
/**
 * Plugin Name: Ikonic Features
 * Description: Custom features including IP redirection, Projects CPT, Ajax endpoints, and API integrations
 * Version: 1.0.0
 * Author: Ikonic
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// IP Address Redirection
function ikonic_check_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (strpos($ip, '77.29') === 0) {
        wp_redirect('https://www.google.com');
        exit;
    }
}
add_action('init', 'ikonic_check_ip');

// Register Projects Custom Post Type
function ikonic_register_projects_cpt() {
    $labels = array(
        'name' => 'Projects',
        'singular_name' => 'Project',
        'menu_name' => 'Projects',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Project',
        'edit_item' => 'Edit Project',
        'new_item' => 'New Project',
        'view_item' => 'View Project',
        'search_items' => 'Search Projects',
        'not_found' => 'No projects found',
        'not_found_in_trash' => 'No projects found in Trash'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'projects'),
        'show_in_rest' => true,
    );

    register_post_type('projects', $args);
}
add_action('init', 'ikonic_register_projects_cpt');

// Register Project Type Taxonomy
function ikonic_register_project_type_taxonomy() {
    $labels = array(
        'name' => 'Project Types',
        'singular_name' => 'Project Type',
        'menu_name' => 'Project Types',
        'search_items' => 'Search Project Types',
        'all_items' => 'All Project Types',
        'parent_item' => 'Parent Project Type',
        'parent_item_colon' => 'Parent Project Type:',
        'edit_item' => 'Edit Project Type',
        'update_item' => 'Update Project Type',
        'add_new_item' => 'Add New Project Type',
        'new_item_name' => 'New Project Type Name'
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'project-type')
    );

    register_taxonomy('project_type', 'projects', $args);
}
add_action('init', 'ikonic_register_project_type_taxonomy');

// Projects Archive Page - Posts Per Page
function ikonic_projects_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('projects')) {
        $query->set('posts_per_page', 6);
    }
}
add_action('pre_get_posts', 'ikonic_projects_posts_per_page');

// Ajax Endpoint for Projects
function ikonic_get_architecture_projects() {
    $response = array(
        'success' => true,
        'data' => array()
    );

    $posts_per_page = is_user_logged_in() ? 6 : 3;

    $args = array(
        'post_type' => 'projects',
        'posts_per_page' => $posts_per_page,
        'orderby' => 'date',
        'order' => 'DESC',
        'tax_query' => array(
            array(
                'taxonomy' => 'project_type',
                'field' => 'slug',
                'terms' => 'architecture'
            )
        )
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $response['data'][] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'link' => get_permalink()
            );
        }
    }

    wp_reset_postdata();
    wp_send_json($response);
}
add_action('wp_ajax_get_architecture_projects', 'ikonic_get_architecture_projects');
add_action('wp_ajax_nopriv_get_architecture_projects', 'ikonic_get_architecture_projects');

// Coffee API Function
function hs_give_me_coffee() {
    $response = wp_remote_get('https://coffee.alexflipnote.dev/random.json');
    
    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return isset($data->file) ? $data->file : false;
}

// Kanye Quotes Shortcode
function ikonic_kanye_quotes() {
    $quotes = array();
    
    for ($i = 0; $i < 5; $i++) {
        $response = wp_remote_get('https://api.kanye.rest/');
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);
            if (isset($data->quote)) {
                $quotes[] = $data->quote;
            }
        }
    }

    $output = '<div class="kanye-quotes">';
    $output .= '<h2>Kanye West Quotes</h2>';
    $output .= '<ul>';
    foreach ($quotes as $quote) {
        $output .= '<li>' . esc_html($quote) . '</li>';
    }
    $output .= '</ul>';
    $output .= '</div>';

    return $output;
}
add_shortcode('kanye_quotes', 'ikonic_kanye_quotes');

// Add some basic styles for the quotes
function ikonic_add_styles() {
    ?>
    <style>
        .kanye-quotes {
            max-width: 800px;
            margin: 2em auto;
            padding: 20px;
        }
        .kanye-quotes ul {
            list-style: none;
            padding: 0;
        }
        .kanye-quotes li {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-style: italic;
        }
    </style>
    <?php
}
add_action('wp_head', 'ikonic_add_styles'); 