<?php

if (!defined('ABSPATH')) {
    exit;
}

// Add menu for plugin
add_action('admin_menu', 'bot_protection_add_admin_menu');
function bot_protection_add_admin_menu() {
    add_menu_page(
        'Bot Protection',
        'Bot Protection',
        'manage_options',
        'bot-protection',
        'bot_protection_admin_page',
        'dashicons-shield',
        20
    );
}

/**
 * Render the admin dashboard page
 */
function bot_protection_admin_page() {
    $error_message = ''; // Variable to hold error messages

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_label']) && isset($_POST['form_url'])) {
        $label = sanitize_text_field($_POST['form_label']);
        $url = esc_url_raw($_POST['form_url']);
        $id = uniqid('cfs_form_');

        $urls = get_option('bot_protection_urls', []);

        // Check for duplicate URLs
        foreach ($urls as $entry) {
            if ($entry['url'] === $url) {
                $error_message = 'Duplicate action URL is not allowed!';
                break;
            }
        }

        if (empty($error_message)) {
            $urls[$id] = ['label' => $label, 'url' => $url];
            update_option('bot_protection_urls', $urls);
        }
    }

    // Handle deletion
    if (isset($_GET['delete_id'])) {
        $delete_id = sanitize_text_field($_GET['delete_id']);
        $urls = get_option('bot_protection_urls', []);
        if (isset($urls[$delete_id])) {
            unset($urls[$delete_id]);
            update_option('bot_protection_urls', $urls);
        }
    }

    $urls = get_option('bot_protection_urls', []);
    ?>
    <div class="wrap">
        <h1>Bot Protection - Manage Form Action URLs</h1>

        <!-- Display error message -->
        <?php if (!empty($error_message)) : ?>
            <div class="notice notice-error is-dismissible" id="duplicate-error" style="margin-bottom: 20px;">
                <p><?php echo esc_html($error_message); ?></p>
                <button type="button" class="notice-dismiss" onclick="document.getElementById('duplicate-error').remove();" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #d63638; float: right;">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Add New Form -->
        <form method="POST" action="">
            <table class="form-table">
                <tr>
                    <th><label for="form_label">Site URL</label></th>
                    <td><input type="text" name="form_label" id="form_label" required></td>
                </tr>
                <tr>
                    <th><label for="form_url">Form Action URL</label></th>
                    <td><input type="url" name="form_url" id="form_url" required></td>
                </tr>
            </table>
            <p>
                <button type="submit" class="button button-primary">Add Form Action URL</button>
            </p>
        </form>

        <h2>Existing Form Action URLs</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site URL</th>
                    <th>Form Action URL</th>
                    <th>Attribute</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($urls)) : ?>
                    <?php foreach ($urls as $id => $data) : ?>
                        <tr>
                            <td><?php echo esc_html($id); ?></td>
                            <td><?php echo esc_html($data['label']); ?></td>
                            <td><?php echo esc_url($data['url']); ?></td>
                            <td>
                                <code>data-dynamic-action</code>
                            </td>
                            <td>
                                <a href="?page=bot-protection&delete_id=<?php echo esc_attr($id); ?>"
                                   class="button button-secondary delete-action-url" 
                                   onclick="return confirm('Are you sure you want to delete this action URL?');">Delete</a>
                                <button class="button button-secondary" onclick="navigator.clipboard.writeText('<?php echo esc_attr($id); ?>');">Copy ID</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No URLs added yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
