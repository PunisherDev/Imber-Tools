<?php
/*
Plugin Name: Imber Tools
Description: Imber Development Tools plugin.
Version: 1.1
Author: Imber Development
Author URI:  https://imber.cc/
*/

function custom_plugin_description($plugin_meta, $plugin_file) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        $additional_links = array(
            'Web Development' => 'https://imber.cc/web-development/',
            'Imber Bot' => 'https://imber.cc/imber-bot/',
            'Custom Bots' => 'https://imber.cc/custom-discord-bots/',
            'Discord' => 'https://imber.cc/discord/',
        );

        foreach ($additional_links as $text => $url) {
            $plugin_meta[] = '<a href="' . $url . '" target="_blank">' . $text . '</a>';
        }
    }

    return $plugin_meta;
}

add_filter('plugin_row_meta', 'custom_plugin_description', 10, 2);


function add_custom_styles_and_scripts() {
    // Enqueue the WordPress color picker script
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_script('my-script-handle', plugin_dir_url(__FILE__) . 'color-picker.js', array('wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'add_custom_styles_and_scripts');


function theme_header_color_settings() {
    add_menu_page('Imber Tools', 'Imber Tools', 'manage_options', 'header-settings', 'render_header_settings_page');
}
add_action('admin_menu', 'theme_header_color_settings');

function render_header_settings_page() {
    $site_url = esc_url(home_url('/'));
    $logo_image_url = 'https://imber.cc/wp-content/uploads/2021/10/cropped-test2.webp'; // Your logo image URL

    ?>
    <div class="wrap">
        <img src="<?php echo $logo_image_url; ?>" alt="Logo" style="max-width: 100%; height: auto;">
        <h1>Imber Development Tools</h1>
        <form method="post" action="options.php">
            <?php settings_fields('theme-header-settings-group'); ?>
            <?php do_settings_sections('theme-header-settings-group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Custom Embed Color</th>
                    <td>
                        <input type="checkbox" name="enable_custom_header_color" value="1" <?php checked(1, get_option('enable_custom_header_color')); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Embed Color</th>
                    <td>
                        <input type="text" value="#32a3da" class="my-color-field" name="header_color" data-default-color="#32a3da" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Disable Page Author</th>
                    <td>
                        <input type="checkbox" name="enable_author_meta" value="1" <?php checked(1, get_option('enable_author_meta')); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Discord Redirect</th>
                    <td>
                        <input type="checkbox" name="enable_discord_redirect" value="1" <?php checked(1, get_option('enable_discord_redirect')); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Redirect URL</th>
                    <td>
                        <p><?php echo $site_url; ?><input type="text" name="discord_redirect_url" value="<?php echo esc_attr(get_option('discord_redirect_url', 'discord')); ?>"></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Discord Invite Link</th>
                    <td>
                        <p>https://discord.gg/<input type="text" name="discord_invite_link" value="<?php echo esc_attr(get_option('discord_invite_link', "INVITE-CODE-HERE")); ?>"></p>
                    </td>
                </tr>
            </table>
            <div class="button-container">
                <input type="submit" class="button button-primary" value="Save Changes">
                <a href="https://imber.cc/discord" class="button button-discord" target="_blank">Join our Discord</a>
            </div>
        </form>
    </div>

    <style>
        .button-discord {
            background-color: #7289da;
            color: #fff;
        }
    </style>
    <?php
}

add_action('woocommerce_product_before_set_stock', function ($product) {
    $product_id = $product->get_id();
    
    $old_stock = get_post_meta($product_id, '_stock', true);
    $new_stock = $product->get_stock_quantity();
    
    update_post_meta($product_id, 'old_stock_quantity', $old_stock);
    update_post_meta($product_id, 'new_stock_quantity', $new_stock);
});

add_action('woocommerce_variation_before_set_stock', function ($product) {
    $product_id = $product->get_id();
    
    $old_stock = get_post_meta($product_id, '_stock', true);
    $new_stock = $product->get_stock_quantity();
    
    update_post_meta($product_id, 'old_stock_quantity', $old_stock);
    update_post_meta($product_id, 'new_stock_quantity', $new_stock);
});

function theme_header_settings_init() {
    register_setting('theme-header-settings-group', 'enable_custom_header_color');
    register_setting('theme-header-settings-group', 'header_color');
    register_setting('theme-header-settings-group', 'enable_author_meta');
    register_setting('theme-header-settings-group', 'enable_discord_redirect');
    register_setting('theme-header-settings-group', 'discord_redirect_url');
    register_setting('theme-header-settings-group', 'discord_invite_link');
}
add_action('admin_init', 'theme_header_settings_init');

function add_custom_header_theme_color() {
    $enable_custom_header_color = get_option('enable_custom_header_color');
    if ($enable_custom_header_color) {
        $header_color = get_option('header_color');
        if (!empty($header_color)) {
            echo '<!-- Theme color for Discord embeds added with Imber Tools plugin -->';
            echo '<meta name="theme-color" content="' . esc_attr($header_color) . '" />';
        }
    }
}
add_action('wp_head', 'add_custom_header_theme_color');

function enable_discord_redirect() {
    $enable_discord_redirect = get_option('enable_discord_redirect');
    $discord_redirect_url = get_option('discord_redirect_url', '/discord');
    $discord_invite_link = get_option('discord_invite_link');

    if ($enable_discord_redirect && !empty($discord_invite_link)) {
        $discord_redirect_url = ltrim($discord_redirect_url, '/');
        $requested_url = trim($_SERVER['REQUEST_URI'], '/');

        if ($requested_url === $discord_redirect_url) {
            // The requested URL matches the Discord redirect URL
            $discord_invite_link = 'https://discord.gg/' . $discord_invite_link;
            wp_redirect($discord_invite_link, 301);
            exit;
        }
    }
}
add_action('parse_request', 'enable_discord_redirect');

function remove_author_meta() {
    $enable_author_meta = get_option('enable_author_meta');
    if ($enable_author_meta) {
        add_filter('get_the_author', '__return_false');
        add_filter('the_author', '__return_false');
        add_filter('author_link', '__return_false');
    }
}
add_action('init', 'remove_author_meta');
