<?php
/**
 * Plugin Name: Password Bypass via URL
 * Description: Allows access to password-protected posts/pages using a URL with the password as a query parameter (?password=yourpassword).
 * Version: 1.1
 * Update URI: https://github.com/stronganchor/password-bypass-via-url
 * Author: Strong Anchor Tech
 * Author URI: https://stronganchortech.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function password_bypass_url_get_update_branch() {
    $branch = 'main';

    if ( defined( 'PASSWORD_BYPASS_URL_UPDATE_BRANCH' ) && is_string( PASSWORD_BYPASS_URL_UPDATE_BRANCH ) ) {
        $override = trim( PASSWORD_BYPASS_URL_UPDATE_BRANCH );
        if ( '' !== $override ) {
            $branch = $override;
        }
    }

    return (string) apply_filters( 'password_bypass_url_update_branch', $branch );
}

function password_bypass_url_bootstrap_update_checker() {
    $checker_file = plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
    if ( ! file_exists( $checker_file ) ) {
        return;
    }

    require_once $checker_file;

    if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
        return;
    }

    $repo_url = (string) apply_filters( 'password_bypass_url_update_repository', 'https://github.com/stronganchor/password-bypass-via-url' );
    $slug     = dirname( plugin_basename( __FILE__ ) );

    $update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        $repo_url,
        __FILE__,
        $slug
    );

    $update_checker->setBranch( password_bypass_url_get_update_branch() );

    foreach ( array( 'PASSWORD_BYPASS_URL_GITHUB_TOKEN', 'STRONGANCHOR_GITHUB_TOKEN', 'ANCHOR_GITHUB_TOKEN' ) as $constant_name ) {
        if ( ! defined( $constant_name ) || ! is_string( constant( $constant_name ) ) ) {
            continue;
        }

        $token = trim( (string) constant( $constant_name ) );
        if ( '' !== $token ) {
            $update_checker->setAuthentication( $token );
            break;
        }
    }
}

password_bypass_url_bootstrap_update_checker();

add_action('template_redirect', function () {
    // Check if the "password" parameter exists in the URL
    if (isset($_GET['password']) && is_singular()) {
        global $post;

        // Ensure the post is password-protected
        if (post_password_required($post)) {
            // Get the post's password
            $post_password = get_post_field('post_password', $post->ID);

            // Verify the password from the query parameter
            if ($_GET['password'] === $post_password) {
                // Set the password cookie to bypass protection
                setcookie('wp-postpass_' . COOKIEHASH, $_GET['password'], time() + 86400, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

                // Redirect to the clean URL without the password query parameter
                wp_redirect(get_permalink($post));
                exit;
            }
        }
    }
});
