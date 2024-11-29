<?php
if (!class_exists('BotProtectionUpdater')) {
    class BotProtectionUpdater {
        private $plugin_slug;
        private $version;
        private $github_repo_url;

        public function __construct($plugin_slug, $version, $github_repo_url) {
            $this->plugin_slug = $plugin_slug;
            $this->version = $version;
            $this->github_repo_url = $github_repo_url;

            add_filter('site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
            add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        }

        public function modify_transient($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $remote_version = $this->get_remote_version();
            if (version_compare($this->version, $remote_version, '<')) {
                $obj = new stdClass();
                $obj->slug = $this->plugin_slug;
                $obj->plugin = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
                $obj->new_version = $remote_version;
                $obj->url = $this->github_repo_url;
                $obj->package = $this->github_repo_url . '/releases/download/' . $remote_version . '/' . $this->plugin_slug . '.zip';

                $transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = $obj;
            }

            return $transient;
        }

        private function get_remote_version() {
            $response = wp_remote_get($this->github_repo_url . '/releases/latest', array('user-agent' => 'WordPress/' . $GLOBALS['wp_version'] . '; ' . home_url()));
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
                return false;
            }
            $release_data = json_decode(wp_remote_retrieve_body($response), true);
            return $release_data['tag_name'];
        }

        public function plugin_info($false, $action, $args) {
            if ($action != 'plugin_information' || $args->slug != $this->plugin_slug) {
                return false;
            }

            // Fetch the release data from GitHub
            $response = wp_remote_get($this->github_repo_url . '/releases/latest');
            if (is_wp_error($response)) {
                return false;
            }

            $release_data = json_decode(wp_remote_retrieve_body($response), true);
            $obj = new stdClass();
            $obj->name = $release_data['name'];
            $obj->slug = $this->plugin_slug;
            $obj->version = $release_data['tag_name'];
            $obj->author = '<a href="https://codeforsite.com/">Hasan</a>';
            $obj->homepage = $this->github_repo_url;
            $obj->download_link = $this->github_repo_url . '/releases/download/' . $release_data['tag_name'] . '/' . $this->plugin_slug . '.zip';
            $obj->sections = array(
                'description' => $release_data['body'] // Assuming GitHub release body is set as the description
            );

            return $obj;
        }
    }
}
