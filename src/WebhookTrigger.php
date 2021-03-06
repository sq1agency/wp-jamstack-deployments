<?php

namespace Crgeary\JAMstackDeployments;

class WebhookTrigger
{
    /**
     * Setup hooks for triggering the webhook
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'trigger']);
        //add_action('admin_bar_menu', [__CLASS__, 'adminBarTriggerButton']);

        add_action('admin_footer', [__CLASS__, 'adminBarCss']);
        add_action('wp_footer', [__CLASS__, 'adminBarCss']);

    }

    /**
     * When a post is saved or updated, fire this
     *
     * @param int $id
     * @param object $post
     * @param bool $update
     * @return void
     */
    public static function triggerSavePost($id, $post, $update)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
            return;
        }

        $statuses = apply_filters('jamstack_deployments_post_statuses', ['publish', 'private', 'trash'], $id, $post);

        if (!in_array(get_post_status($id), $statuses, true)) {
            return;
        }

        $option = jamstack_deployments_get_options();
        $post_types = apply_filters('jamstack_deployments_post_types', $option['webhook_post_types'] ?: [], $id, $post);

        if (!in_array(get_post_type($id), $post_types, true)) {
            return;
        }

        self::fireWebhook();
    }

    /**
     * Fire a request to the webhook when a term has been created.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    public static function triggerSaveTerm($id, $tax_id, $tax_slug)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook();
    }

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
    public static function triggerDeleteTerm($id, $tax_id, $tax_slug, $term, $object_ids)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook();
    }

    /**
     * Fire a request to the webhook when a term has been modified.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    public static function triggerEditTerm($id, $tax_id, $tax_slug)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook();
    }

    /**
     * Check if the given taxonomy is one that should fire the webhook
     *
     * @param int $id
     * @param int $tax_id
     * @param string $tax_slug
     * @return boolean
     */
    protected static function canFireForTaxonomy($id, $tax_id, $tax_slug)
    {
        $option = jamstack_deployments_get_options();
        $taxonomies = apply_filters('jamstack_deployments_taxonomies', $option['webhook_taxonomies'] ?: [], $id, $tax_id);

        return in_array($tax_slug, $taxonomies, true);
    }

    /**
     * Show the admin bar css
     *
     * @todo move this somewhere else
     * @return void
     */
    public static function adminBarCss()
    {
        if (!is_admin_bar_showing()) {
            return;
        }

        ?><style>

        #wpadminbar .wp-jamstack-deployments-button > a {
            background-color: rgba(238,114,17, 1);
            color: #FFFFFF !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }
        #wpadminbar .wp-jamstack-deployments-button > a:hover,
        #wpadminbar .wp-jamstack-deployments-button > a:focus {
            background-color: rgba(214, 125, 55, 1) !important;
        }

        </style>
<?php
    }

    /**
     * Add a "trigger webhook" button to the admin bar
     *
     * @param object $bar
     * @return void
     */
    public static function adminBarTriggerButton($bar)
    {
        $uri = wp_nonce_url(
            admin_url('admin.php?page=wp-jamstack-deployments-settings&action=jamstack-deployment-trigger'),
            'crgeary_jamstack_deployment_trigger',
            'crgeary_jamstack_deployment_trigger'
        );

        $bar->add_node([
            'id' => 'wp-jamstack-deployments',
            'title' => 'Deploy Settings',
            'parent' => 'top-secondary',
            'href' => $uri,
            'meta' => [
                'class' => 'wp-jamstack-deployments-button'
            ]
        ]);
    }

    /**
     * Trigger a request manually from the admin settings
     *
     * @return void
     */
    public static function trigger()
    {
        if (!isset($_GET['action']) || 'jamstack-deployment-trigger' !== $_GET['action']) {
            return;
        }

        check_admin_referer('crgeary_jamstack_deployment_trigger', 'crgeary_jamstack_deployment_trigger');

        self::fireWebhook();

// 		global $wpdb;
// 		$wpdb->insert('wp-jamstack_deployments', array(
//     		'deployment_status' => 'deploying',
// 			));

        wp_redirect(admin_url('admin.php?page=wp-jamstack-deployments-settings&jamstack-deploy-status=started-deploying'));
        exit;
    }

    /**
     * Fire off a request to the webhook
     *
     * @return WP_Error|array
     */
    public static function fireWebhook()
    {
        $webhook = jamstack_deployments_get_webhook_url();

        if (!$webhook) {
            return;
        }

        if (false === filter_var($webhook, FILTER_VALIDATE_URL)) {
            return;
        }

        $deployment_environment = jamstack_deployments_get_webhook_enviroment();
        $app_id = jamstack_deployments_get_webhook_app_id();

        /*
            This will be dynamic coming from some properties set in the admin
        */
        $payload = array('repo_name' => 'ansira-gatsby', 'branch_name' => $deployment_environment, 'job_type' => 'RELEASE', 'source' => site_url(), 'app_id' => $app_id);

        $args = [
            'blocking' => false,
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'data_format' => 'body',
            'body'        => json_encode($payload)
        ];

        $method = jamstack_deployments_get_webhook_method();

        do_action('jamstack_deployments_before_fire_webhook');

        if ($method === 'get') {
            $return = wp_safe_remote_get($webhook, $args);
        } else {
            $return = wp_safe_remote_post($webhook, $args);
        }

        do_action('jamstack_deployments_after_fire_webhook');

        return $return;
    }
}
