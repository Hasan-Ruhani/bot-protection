<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register REST API routes
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/get-form-action/(?P<form_id>[a-zA-Z0-9_]+)', [
        'methods' => 'GET',
        'callback' => 'serve_specific_protected_action_url',
        'args' => [
            'form_id' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

/**
 * Fetch a specific action URL by form ID.
 */
function serve_specific_protected_action_url(WP_REST_Request $request) {
    $form_id = $request['form_id'];
    $urls = get_option('bot_protection_urls', []);

    if (isset($urls[$form_id])) {
        return new WP_REST_Response($urls[$form_id], 200);
    }

    return new WP_REST_Response(['error' => 'Form ID not found'], 404);
}
