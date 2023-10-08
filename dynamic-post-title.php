<?php
/*
Plugin Name: Dynamic Post Titles
Plugin URI: https://www.iniyan.in
Description: Enhance your WordPress website with the "Dynamic Post Titles" plugin, a powerful tool that adds a creative twist to your post titles. With this plugin, you can provide multiple title options for each post and have them displayed dynamically, engaging your audience with fresh and captivating titles.
Version: 1.0.0
Author: Iniyan
Author URI: https://www.iniyan.in
*/

// Enqueue custom CSS for the metabox
add_action('admin_enqueue_scripts', 'dynamic_post_titles_enqueue_styles');
function dynamic_post_titles_enqueue_styles() {
    wp_enqueue_style('dynamic-post-titles-styles', plugin_dir_url(__FILE__) . 'css/dynamic-post-titles-styles.css');
}

// Add the post title fields to the post editor
add_action('add_meta_boxes', 'dynamic_post_title_add_metabox');
function dynamic_post_title_add_metabox() {
    add_meta_box('dynamic_post_title_fields', 'Dynamic Post Titles', 'dynamic_post_title_fields_metabox', 'post', 'normal', 'high');
}

function dynamic_post_title_fields_metabox($post) {
    // Get the post title fields
    $title_fields = get_post_meta($post->ID, 'dynamic_post_title_fields', true);

    // Display the post title fields
    for ($i = 1; $i <= 5; $i++) {
        ?>
        <input type="text" name="dynamic_post_title_fields[<?php echo $i; ?>]" placeholder="Title <?php echo $i; ?>" value="<?php echo esc_attr(isset($title_fields[$i]) ? $title_fields[$i] : ''); ?>" /><br>
        <?php
    }
}

// Save the post title fields
add_action('save_post', 'dynamic_post_title_save_fields');
function dynamic_post_title_save_fields($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check for the 'post' post type
    if ($post_id && get_post_type($post_id) === 'post') {
        // Get the post title fields
        $title_fields = isset($_POST['dynamic_post_title_fields']) ? $_POST['dynamic_post_title_fields'] : array();

        // Update the post title fields
        update_post_meta($post_id, 'dynamic_post_title_fields', $title_fields);
    }
}

// Display the post title on the front end for all queries
add_filter('the_title', 'dynamic_post_title_display_title', 10, 2);
function dynamic_post_title_display_title($title, $post_id) {
    // Get the post title fields
    $title_fields = get_post_meta($post_id, 'dynamic_post_title_fields', true);

    // If there are any post title fields, randomly select one and display it
    if (!empty($title_fields)) {
        $rand_key = array_rand($title_fields);
        $title = $title_fields[$rand_key];
    }

    return $title;
}

// Remove the post title from the menu items and use the filtered title
add_filter('wp_nav_menu_objects', 'dynamic_post_title_remove_from_menu_items', 10, 2);
function dynamic_post_title_remove_from_menu_items($items, $args) {
    foreach ($items as $key => $item) {
        if ($item->object_id === get_the_ID()) {
            $item->title = dynamic_post_title_display_title(get_the_title(), get_the_ID());
        }
    }
    return $items;
}
