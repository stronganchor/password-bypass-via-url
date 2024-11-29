<?php
/**
 * Plugin Name: Password Bypass via URL
 * Description: Allows access to password-protected posts/pages using a URL with the password as a query parameter (?password=yourpassword).
 * Version: 1.1
 * Author: Strong Anchor Tech
 * Author URI: https://stronganchortech.com
 */

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
