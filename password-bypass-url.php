<?php
/**
 * Plugin Name: Password Bypass via URL
 * Description: Allows access to password-protected posts/pages using a URL with the password as a query parameter (?password=yourpassword).
 * Version: 1.0
 * Author: Strong Anchor Tech
 * Author URI: https://stronganchortech.com
 */

add_action('template_redirect', function () {
    // Check if the "password" parameter exists in the URL
    if (isset($_GET['password']) && is_singular()) {
        global $post;

        // Check if the current post/page is password-protected
        if (post_password_required($post)) {
            // Validate the password in the URL against the post's password
            if ($_GET['password'] === $post->post_password) {
                // Set the password cookie for this post/page
                setcookie('wp-postpass_' . COOKIEHASH, $_GET['password'], time() + 86400, COOKIEPATH);

                // Redirect to refresh the page without the query parameter
                wp_redirect(get_permalink($post));
                exit;
            }
        }
    }
});
