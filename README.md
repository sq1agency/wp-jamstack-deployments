# JAMstack Deployments (AWS Amplify)

A WordPress plugin for JAMstack deployments on AWS Amplify (and other platforms).

<img src="deploy-button.png" width="280" height="67" />

## AWS Amplify

We will trigger a build by sending a POST request to an endpoint. This will trigger a build for our Gatsby site in AWS Amplify. Here is a tutorial on how to set this up.

[AWS Amplify Console and Triggering a Build from an API Endpoint](https://medium.com/ansira-development/aws-amplify-console-and-triggering-a-build-from-an-api-endpoint-cff5f99a5a7f)


## Description

This plugin provides a way to fire off a request to a webhook when a post, page or custom post type has been created, udpated or deleted. You're also able to fire off a request manually at the click of a button, or programmatically via a WordPress action.

## WordPress Actions/Filters

The plugin attempts to trigger builds when you update your content, and has settings that you can use to define what post types & taxonomies should be monitored. However, when you need more control, there are actions & filters you can use to get the job done.

### Post Types

You can choose which posts types should trigger builds from the plugin settings. However, you may require more control, or need to overwrite the settings, you can do so using the `jamstack_deployments_post_types` filter. By default, this filter contains an array of post types that we monitor. You can add or remove them as required.

For example, if you want to force the plugin to trigger builds for the `'post'` post type regardless of the settings, you can do so with the following code:

```php
add_filter('jamstack_deployments_post_types', function ($post_types, $post_id, $post) {
    if (!in_array($post->post_type, $post_types, true)) {
        $post_types[] = 'post';
    }
    return $post_types;
}, 10, 3);
```

### Taxonomies

Like post types, you can choose which taxonmies should trigger builds from the plugin settings. But there may be times you need more control. For this, you can use the `jamstack_deployments_taxonomies` filter. By defualt, this filter contains an array of taxonomies that we monitor.

For example, if you want to force the plugin to trigger builds for the `'post_tag'` taxonomy regardless of the settings, you can do so with the following code:

```php
add_filter('jamstack_deployments_taxonomies', function ($taxonomies, $term_id, $tax_id) {
    $tax = get_taxonomy($tax_id);
    if (!in_array($tax->name, $taxonomies, true)) {
        $taxonomies[] = 'post_tag';
    }
    return $taxonomies;
}, 10, 3);
```

### Post Statuses

You can use the `jamstack_deployments_post_statuses` filter to change which post statuses we monitor. The default is to monitor `'publish'`, `'private'` and `'trash'`.

Here is an example that adds `'review'` to the array of post statuses that we monitor & will trigger builds for.

```php
add_filter('jamstack_deployments_post_statuses', function ($statuses, $post_id, $post) {
    $statuses[] = 'review';
    return $statuses;
}, 10, 3);
```

## Custom Actions

The `jamstack_deployments_fire_webhook` action can be used to fire the webhook and trigger a build at a custom point that you specify. For example, if you want to fire the webhook when a user registers, then you can use:

```php
add_action('user_register', 'jamstack_deployments_fire_webhook');
```

## Running Code Before & After Webhooks

You can run code directly before or after you fire the webhook using the following actions:

* Before: `jamstack_deployments_before_fire_webhook`
* After: `jamstack_deployments_after_fire_webhook`

## License
[GPL-3.0](LICENSE.md)
