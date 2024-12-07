<?php

class Plugin_Update
{
    private $slug;
    private $plugin_data;
    private $username;
    private $repo;
    private $plugin_file;
    private $github_response;
    private $cache_key;
    private $cache_expiration = 3600;

    public function __construct($plugin_file, $github_username, $github_repo)
    {
        $this->plugin_file = $plugin_file;
        $this->username = $github_username;
        $this->repo = $github_repo;
        $this->slug = plugin_basename($this->plugin_file);
        $this->cache_key = 'github_plugin_update_' . $this->slug;


        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }

    private function get_repository_info()
    {
        $response = get_transient($this->cache_key);
        if (false === $response) {
            $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo);

            $args = array(
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress Plugin Update Checker',
                ),
            );
            $response = wp_remote_get($request_uri, $args);

            if (is_wp_error($response)) {
                return false;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code != 200) {
                return false;
            }

            $response_body = wp_remote_retrieve_body($response);
            $response = json_decode($response_body);
            set_transient($this->cache_key, $response, $this->cache_expiration);
        }

        $this->github_response = $response;
    }

    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->get_repository_info();

        if (!isset($this->github_response)) {
            return $transient;
        }

        $this->plugin_data = get_plugin_data($this->plugin_file);
        $current_version = $this->plugin_data['Version'];
        $remote_version = ltrim($this->github_response->tag_name, 'v');

        if (version_compare($remote_version, $current_version, '>')) {
            $plugin_slug = $this->slug;

            $obj = new stdClass();
            $obj->slug = $plugin_slug;
            $obj->plugin = $plugin_slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->github_response->assets[0]->browser_download_url;
            $obj->package = $this->github_response->zipball_url;

            $transient->response[$plugin_slug] = $obj;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== $this->slug) {
            return $result;
        }

        $this->get_repository_info();

        if (!isset($this->github_response)) {
            return $result;
        }

        $plugin_data = get_plugin_data($this->plugin_file);
        $remote_version = ltrim($this->github_response->tag_name, 'v');

        $Parsedown = new Parsedown();
        $changelog_html = wp_kses_post($Parsedown->text($this->github_response->body ?? ''));

        $plugin_info = new stdClass();
        $plugin_info->name = $plugin_data['Name'];
        $plugin_info->slug = $this->slug;
        $plugin_info->version = $remote_version;
        $plugin_info->author = $plugin_data['Author'];
        $plugin_info->author_profile = $plugin_data['AuthorURI'];
        $plugin_info->homepage = $plugin_data['PluginURI'];
        $plugin_info->download_link = $this->github_response->zipball_url;
        $plugin_info->requires = $plugin_data['RequiresWP'] ?? '6.5';
        $plugin_info->tested = $plugin_data['TestedWP'] ?? '6.6.2';
        $plugin_info->last_updated = $this->github_response->published_at;
        $plugin_info->sections = array(
            'description' => $plugin_data['Description'],
            'changelog' => $changelog_html ?? '',
        );

        return $plugin_info;
    }
}
