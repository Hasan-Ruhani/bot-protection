<?php
if (!class_exists('BotProtectionUpdater')) {
    class BotProtectionUpdater {
        private $plugin_slug;
        private $github_url;

        public function __construct($plugin_slug, $github_url) {
            $this->plugin_slug = $plugin_slug;
            $this->github_url = $github_url;

            add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
            add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        }

        public function check_for_update($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_slug);
            $current_version = $plugin_data['Version'];

            $response = wp_remote_get("{$this->github_url}/releases/latest");
            if (is_wp_error($response)) {
                return $transient;
            }

            $release_info = json_decode(wp_remote_retrieve_body($response));
            if (empty($release_info->tag_name)) {
                return $transient;
            }

            $latest_version = ltrim($release_info->tag_name, 'v'); // Remove 'v' from tag name
            if (version_compare($current_version, $latest_version, '<')) {
                $transient->response[$this->plugin_slug] = (object)[
                    'slug'        => $this->plugin_slug,
                    'new_version' => $latest_version,
                    'package'     => $release_info->assets[0]->browser_download_url,
                    'url'         => $release_info->html_url
                ];
            }

            return $transient;
        }

        public function plugin_info($res, $action, $args) {
            if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
                return $res;
            }

            $response = wp_remote_get("{$this->github_url}/releases/latest");
            if (is_wp_error($response)) {
                return $res;
            }

            $release_info = json_decode(wp_remote_retrieve_body($response));
            if (empty($release_info->tag_name)) {
                return $res;
            }

            $latest_version = ltrim($release_info->tag_name, 'v'); // Remove 'v' from tag name

            return (object)[
                'name'        => 'Bot Protection',
                'slug'        => $this->plugin_slug,
                'version'     => $latest_version,
                'download_link' => $release_info->assets[0]->browser_download_url,
                'author'      => '<a href="https://github.com/Hasan-Ruhani">Hasan</a>',
                'sections'    => [
                    'description' => $release_info->body ?? 'A WordPress plugin for bot protection.'
                ]
            ];
        }
    }
}
