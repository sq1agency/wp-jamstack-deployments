<?php

function wpse_load_plugin_css() {
	wp_enqueue_script('deployment-script', plugin_dir_url( __FILE__ ) . 'scripts.js' );
    wp_enqueue_style('deployment-styles', plugin_dir_url( __FILE__ ) . 'styles.css' );
}
add_action( 'admin_print_styles', 'wpse_load_plugin_css' );

if (!function_exists('jamstack_webhook_notification')) {
    add_action( 'rest_api_init', function () {
        register_rest_route( 'wp-jamstack-deployments/v1', '/webhook', array(
        'methods'  => 'POST',
        'callback' => 'my_awesome_func',
        ) );
    } );

    function my_awesome_func( $request ) {
		global $wpdb;
        $data = $request->get_json_params();
		$result = $wpdb->get_results('SELECT * FROM `wp_jamstack_deployments`');
		return $result;
    }
}

if (!function_exists('jamstack_deployments_get_options')) {
    /**
     * Return the plugin settings/options
     *
     * @return array
     */
    function jamstack_deployments_get_options() {
        return get_option(CRGEARY_JAMSTACK_DEPLOYMENTS_OPTIONS_KEY);
    }
}

if (!function_exists('jamstack_deployments_get_webhook_url')) {
    /**
     * Return the webhook url
     *
     * @return string|null
     */
    function jamstack_deployments_get_webhook_url() {
        $options = jamstack_deployments_get_options();
        return isset($options['webhook_url']) ? $options['webhook_url'] : null;
    }
}

if (!function_exists('jamstack_deployments_get_webhook_app_id')) {
    /**
     * Return the webhook url
     *
     * @return string|null
     */
    function jamstack_deployments_get_webhook_app_id() {
        $options = jamstack_deployments_get_options();
        return isset($options['webhook_amplify_app_id']) ? $options['webhook_amplify_app_id'] : '#########';
    }
}

if (!function_exists('jamstack_deployments_get_webhook_method')) {
    /**
     * Return the webhook method (get/post)
     *
     * @return string
     */
    function jamstack_deployments_get_webhook_method() {
        $options = jamstack_deployments_get_options();
        $method = isset($options['webhook_method']) ? $options['webhook_method'] : 'post';
        return mb_strtolower($method);
    }
}

if (!function_exists('jamstack_deployments_get_webhook_enviroment')) {
    /**
     * Return the deployment enviroment that will be triggerd
     *
     * @return string
     */
    function jamstack_deployments_get_webhook_enviroment() {
        $options = jamstack_deployments_get_options();
        $env = isset($options['webhook_env']) ? $options['webhook_env'] : 'development';
        return mb_strtolower($env);
    }
}

if (!function_exists('jamstack_deployments_fire_webhook')) {
    /**
     * Fire a request to the webhook.
     *
     * @return void
     */
    function jamstack_deployments_fire_webhook() {
        \Crgeary\JAMstackDeployments\WebhookTrigger::fireWebhook();
    }
}

if (!function_exists('jamstack_deployments_force_fire_webhook')) {
    /**
     * Fire a request to the webhook immediately.
     *
     * @return void
     */
    function jamstack_deployments_force_fire_webhook() {
        \Crgeary\JAMstackDeployments\WebhookTrigger::fireWebhook();
    }
}

if (!function_exists('jamstack_deployments_fire_webhook_save_post')) {
    /**
     * Fire a request to the webhook when a post has been saved.
     *
     * @param int $id
     * @param WP_Post $post
     * @param boolean $update
     * @return void
     */
    function jamstack_deployments_fire_webhook_save_post($id, $post, $update) {
        \Crgeary\JAMstackDeployments\WebhookTrigger::triggerSavePost($id, $post, $update);
    }
    add_action('save_post', 'jamstack_deployments_fire_webhook_save_post', 10, 3);
}

if (!function_exists('jamstack_deployments_fire_webhook_created_term')) {
    /**
     * Fire a request to the webhook when a term has been created.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    function jamstack_deployments_fire_webhook_created_term($id, $tax_id, $tax_slug) {
        \Crgeary\JAMstackDeployments\WebhookTrigger::triggerSaveTerm($id, $tax_id, $tax_slug);
    }
    add_action('created_term', 'jamstack_deployments_fire_webhook_created_term', 10, 3);
}

if (!function_exists('jamstack_deployments_fire_webhook_delete_term')) {
    /**
     * Fire a request to the webhook when a term has been removed.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @param object $term
     * @param array $object_ids
     * @return void
     */
    function jamstack_deployments_fire_webhook_delete_term($id, $tax_id, $tax_slug, $term, $object_ids) {
        \Crgeary\JAMstackDeployments\WebhookTrigger::triggerSaveTerm($id, $tax_id, $tax_slug, $term, $object_ids);
    }
    add_action('delete_term', 'jamstack_deployments_fire_webhook_delete_term', 10, 5);
}

if (!function_exists('jamstack_deployments_fire_webhook_edit_term')) {
    /**
     * Fire a request to the webhook when a term has been modified.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    function jamstack_deployments_fire_webhook_edit_term($id, $tax_id, $tax_slug) {
        \Crgeary\JAMstackDeployments\WebhookTrigger::triggerEditTerm($id, $tax_id, $tax_slug);
    }
    add_action('edit_term', 'jamstack_deployments_fire_webhook_edit_term', 10, 3);
}
