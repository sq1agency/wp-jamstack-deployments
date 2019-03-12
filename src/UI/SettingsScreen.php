<?php

namespace Crgeary\JAMstackDeployments\UI;

class SettingsScreen
{
    /**
     * Register the requred hooks for the admin screen
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'addMenu']);
    }

    /**
     * Register an tools/management menu for the admin area
     *
     * @return void
     */
    public static function addMenu()
    {
        add_options_page(
            'Deployment Settings',
            'Deployments',
            'manage_options',
            'wp-jamstack-deployments-settings',
            [__CLASS__, 'renderPage']
        );
    }

    /**
     * Render the management/tools page
     *
     * @return void
     */
    public static function renderPage()
    {
        ?><div class="wrap">

            <h2><?= get_admin_page_title(); ?></h2>
	<?php
		               $uri = wp_nonce_url(
                    admin_url('admin.php?page=wp-jamstack-deployments-settings&action=jamstack-deployment-trigger'),
                    'crgeary_jamstack_deployment_trigger',
                    'crgeary_jamstack_deployment_trigger'
                );

                ?>
				 <div class="jamstack-admin-deploy">
	              <p>You must save your settings before triggering a build.</p>
                <a href="<?= esc_url($uri); ?>" class="button deployment-button">Deploy Website</a>
					 </div>
	<div class="jamstack-admin-deploy-started" style="display:none;">
		<h4>
			The website is being deployed and you can only trigger a single deployment at a time. Check the MS Teams channel for when the deployment has completed. If there is an error the channel will get notified. 
		</h4>
	</div>

            <form class="jamstack-form" method="post" action="<?= esc_url(admin_url('options.php')); ?>">
                <?php

                settings_fields(CRGEARY_JAMSTACK_DEPLOYMENTS_OPTIONS_KEY);
                do_settings_sections(CRGEARY_JAMSTACK_DEPLOYMENTS_OPTIONS_KEY);

                submit_button('Save Settings', 'primary', 'submit', false);
				 ?>

            </form>

        </div><?php
    }
}
