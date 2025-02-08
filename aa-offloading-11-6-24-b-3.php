<?php
/*
Plugin Name: Comprehensive Cache Control and Plugin Offloader
Description: Manages selective plugin loading and cache control for specific WordPress admin-ajax actions and dependent assets.
Version: 1.6
Author: Your Name
*/

if (!class_exists('ComprehensiveCacheControlOffloader')) {
    class ComprehensiveCacheControlOffloader {

        private $action_to_patterns;
        private $safe_plugins;
        private $action_to_function;

        public function __construct() {
            // Map actions to corresponding functions dynamically
            $this->action_to_function = [
                'cred_submit_form' => 'custom_selective_plugin_loading_cred_submit_form',
                'get_bb_pusher_threads' => 'custom_selective_plugin_loading_get_bb_pusher_threads',
                'wp_ulike_process' => 'custom_selective_plugin_loading_wp_ulike_process',
                'wp_ulike_get_likers' => 'custom_selective_plugin_loading_wp_ulike_get_likers',
                'wpv_get_view_query_results' => 'custom_selective_plugin_loading_toolset_view_ajax',
                'new_activity_comment' => 'custom_selective_plugin_loading_new_activity_comment',
                'post_draft_activity' => 'custom_selective_plugin_loading_post_draft_activity',
                'activity_filter' => 'custom_selective_plugin_loading_activity_filter',
                'activity_mark_fav' => 'custom_selective_plugin_loading_activity_mark_fav',
                'heartbeat' => 'custom_selective_plugin_loading_heartbeat',
                'onesignal_update_device_info' => 'custom_selective_plugin_loading_onesignal_update_device_info',
                'messages_send_reply' => 'custom_selective_plugin_loading_messages_send_reply',
                'bb_pusher_update_current_thread_unread_count' => 'custom_selective_plugin_loading_bb_pusher_update_current_thread_unread_count',
                'bbapp_queue' => 'custom_selective_plugin_loading_bb_pusher_update_current_thread_unread_count',
                'bbapp_linkd_embed_data' => 'custom_selective_plugin_loading_bbapp_linkd_embed_data',
                'buddyboss_theme_get_header_unread_messages' => 'custom_selective_plugin_loading_buddyboss_theme_get_header_unread_messages',
                'messages_get_thread_messages' => 'custom_selective_plugin_loading_messages_get_thread_messages',
                'messages_get_user_message_threads' => 'custom_selective_plugin_loading_messages_get_user_message_threads',
                'as_async_request_queue_runner' => 'custom_selective_plugin_loading_as_async_request_queue_runner', 
                'cred_association_form_ajax_role_find' => 'custom_selective_plugin_loading_cred_association_form_ajax_role_find',
                'post_update' => 'custom_selective_plugin_loading_post_update',
                'wpematico_cron' => 'custom_selective_plugin_loading_wpematico_cron',
                'elementor_save' => 'custom_selective_plugin_loading_elementor_save',
                'elementor_ajax' => 'custom_selective_plugin_loading_elementor_ajax',
                'inline-save' => 'custom_selective_plugin_loading_inline_save',
                'mepr_process_login_form' => 'custom_selective_plugin_loading_memberpress',
                'taxopress_autoterms_content_by_ajax' => 'custom_selective_plugin_loading_taxopress_autoterms_content_by_ajax',
            ];

            // Initialize patterns and safe plugins
            $this->initialize_patterns();
            $this->initialize_safe_plugins();

            // Hook into plugin loading process
            add_filter('option_active_plugins', [$this, 'custom_selective_plugin_loading'], 20);
            add_filter('site_option_active_sitewide_plugins', [$this, 'custom_selective_plugin_loading'], 20);
        }

        private function initialize_patterns() {
             // Define common patterns used across multiple actions
        $common_patterns = [
            '/^wp\-includes\/.*/',                    // Matches all files under wp-includes
            '/^wp\-admin\/.*/',                       // Matches all files under wp-admin
            '/^wp\-admin\/admin\-ajax\.php.*/',       // Matches admin-ajax.php with any query parameters
            '/^wp\-content\/.*/',                     // Matches all files under wp-content
            '/^wp\-content\/plugins\/.*/',            // Matches all files in plugins directory
            '/^wp\-content\/themes\/.*/',             // Matches all files in themes directory
            '/^wp\-content\/uploads\/.*/',            // Matches all files in uploads directory
            '/^wp\-json\/.*/'                         // Matches all REST API requests
        ];

        $bb_patterns = [
            '/^plugins\/buddyboss.*/',                    // Matches all files under wp-includes
            '/^wp\-json\/buddyboss.*/'                         // Matches all REST API requests
        ];

        $this->action_to_patterns = [
            'cred_submit_form' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\.form\.js$/',
                '/^cred\-frontend\-editor\/public\/js\/frontend\.js$/',
                ], $common_patterns),
            'bbapp_queue' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-messages\.js$/',
                '/^buddyboss\-platform\/bp\-core\/js\/vendor\/medium\-editor\.js$/',
                '/^buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-pro\-core\-pusher\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-shared\-worker\-wrapper\.js$/',
                ], $common_patterns, $bb_patterns),

            'bbapp_linkd_embed_data' => array_merge([
                '/projects/\?mobile-page-mode\=1',
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^buddyboss\-platform\/bp\-core\/js\/vendor\/medium\-editor\.js$/',
                '/^buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-pro\-core\-pusher\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-shared\-worker\-wrapper\.js$/',
                
                ], $common_patterns, $bb_patterns),

            'get_bb_pusher_threads' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-nouveau\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-activity\.js$/',
                ], $common_patterns, $bb_patterns),
                
            'wp_ulike_process' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-ulike\.min\.js$/',
                '/^wp\-ulike\-pro\.min\.js$/',
                ], $common_patterns, $bb_patterns),

            'wp_ulike_get_likers' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-ulike\.min\.js$/',
                '/^wp\-ulike\-pro\.min\.js$/',
                ], $common_patterns, $bb_patterns),

            'wpv_get_view_query_results' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/wp\-views\/public\/js\/views\-frontend\.js$/',
                ], $common_patterns),

            'new_activity_comment' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-(nouveau|activity)\.js$/',
                ], $common_patterns, $bb_patterns),

            'activity_mark_fav' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-(nouveau|activity)\.js$/',
                ], $common_patterns, $bb_patterns),  // <- Added comma here
                
            'heartbeat' => array_merge([
                '/^wp\-includes\/.*/',  // Matches all files under wp-includes
                '/^wp\-admin\/.*/',      // Matches all files under wp-admin
                ], $common_patterns, $bb_patterns),

            'onesignal_update_device_info' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/onesignal\/assets\/js\/bb\-onesignal\.js$/',
                '/^cdn\.onesignal\.com\/sdks\/OneSignalPageSDKES6\.js$/',
                '/^onesignal\.js$/',
                '/^buddyboss\-platform\-pro\/includes\/integrations\/onesignal\/assets\/js\/bb\-onesignal\.js$/',
                '/^utils\.js$/',
                ], $common_patterns, $bb_patterns),

            'buddyboss_theme_get_header_unread_messages' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/.*/',
                '/^wp\-admin\/.*/',
                '/^wp\-admin\/admin\-ajax\.php.*/',
                '/^\/wp\-includes\/integrations\/pusher\/assets\/js\/bb\-pro\-core\-pusher\.js$/',
                '/^\/wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-shared\-worker\-wrapper\.js$/',
                ], $common_patterns, $bb_patterns),

            'messages_send_reply' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^wp\-includes\/.*/',
                '/^wp\-admin\/.*/',
                '/^wp\-admin\/admin\-ajax\.php.*/',
                '/^\/wp\-content\/plugins\/buddyboss\-platform.*/',
                '/^\/wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-messages\.js$/',
                '/^\/wp\-content\/plugins\/buddyboss\-platform\/bp\-core\/js\/vendor\/medium\-editor\.js$/',
                '/^\/wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-pro\-core\-pusher\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-shared\-worker\-wrapper\.js$/',
                ], $common_patterns, $bb_patterns),

            'bb_pusher_update_current_thread_unread_count' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-messages\.js$/',
                '/^buddyboss\-platform\-pro\/includes\/integrations\/pusher\/assets\/js\/bb\-pro\-core\-pusher\.js$/',
                '/^buddyboss\-platform\/bp\-core\/js\/vendor\/medium\-editor\.js$/',
                ], $common_patterns, $bb_patterns),
            'messages_get_thread_messages' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-messages\.js$/',
                '/^wp\-includes\/js\/(backbone|wp\-backbone)\.min\.js$/',
                ], $common_patterns, $bb_patterns),
            'messages_get_user_message_threads' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-messages\.js$/',
                '/^wp\-includes\/js\/(backbone|wp\-backbone)\.min\.js$/',
                ], $common_patterns, $bb_patterns),
            'as_async_request_queue_runner' => array_merge([
                '/^wp\-includes\//',                     // Correct path delimiter added for directories
                '/^wp\-admin\//',                         // Correct path delimiter and typo fixed
                '/^buddyboss\-platform\//',               // Added trailing slash for directories
                '/^buddyboss\-platform\-pro\//',          // Added trailing slash for directories
                '/^buddyboss\-app\//',                    // Added trailing slash for directories
                '/^gh\//',                                // Added trailing slash for a directory
                '/^groundhogg\//',                        // Added trailing slash for directories
                '/^as_async_request_queue_runner$/',       // Regex for exact match (if used as a standalone string)
                ], $common_patterns, $bb_patterns),
            'cred_association_form_ajax_role_find' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/cred\-frontend\-editor\/vendor\/toolset\/toolset\-common\/res\/lib\/select2\/select2\.js$/',
                ], $common_patterns),
            'post_update' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-activity\-post\-form\.js$/',
                ], $common_patterns, $bb_patterns),

            'post_draft_activity' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-activity\-post\-form\.js$/',
                ], $common_patterns, $bb_patterns),
                
            'activity_filter' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-includes\/js\/wp\-util\.js$/',
                '/^wp\-content\/plugins\/buddyboss\-platform\/bp\-templates\/bp\-nouveau\/js\/buddypress\-activity\-post\-form\.js$/',
                ], $common_patterns, $bb_patterns),
            'inline-save' => array_merge([
                '/https:\/\/townsquare\.news\/wp\-includes\/js\/jquery\/jquery\.js\?ver=3\.7\.1$/',
                '/https:\/\/townsquare\.news\/wp\-includes\/js\/jquery\/jquery\-migrate\.js\?ver=3\.4\.1$/',
                '/https:\/\/townsquare\.news\/wp\-admin\/js\/inline\-edit\-post\.js$/',
                '/^fastModeContent\.tsx$/',
                ], $common_patterns, $bb_patterns),
            'wpematico_cron' => array_merge([
                '/^wp\-content\/plugins\/wpematico\/.*/',
                '/^wp\-content\/plugins\/wpematico_fullcontent\/.*/',
                '/^wp\-content\/plugins\/wpematico_googlo_news\/.*/',
                '/^wp\-content\/plugins\/wpematico_professional\/.*/',
                ], $common_patterns, $bb_patterns),
            'elementor_ajax' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/js\/common\.js$/',
                '/^wp\-includes\/js\/underscore\.min\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/js\/editor(\-loader\-v1)?\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/lib\/backbone\/backbone\.marionette\.js$/',
                ], $common_patterns, $bb_patterns),
            'elementor_save' => array_merge([
                '/^wp\-includes\/js\/jquery\/jquery\.js$/',
                '/^wp\-includes\/js\/jquery\/jquery\-migrate\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/js\/common\.js$/',
                '/^wp\-includes\/js\/underscore\.min\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/js\/editor(\-loader\-v1)?\.js$/',
                '/^wp\-content\/plugins\/elementor\/assets\/lib\/backbone\/backbone\.marionette\.js$/',
                ], $common_patterns, $bb_patterns),
            'taxopress_autoterms_content_by_ajax' => array_merge([
                '/^wp\-content\/plugins\/taxopress\/.*/',
                '/^wp\-content\/plugins/taxopress-pro\/.*/',
                '/^wp\-content\/plugins\/.*/',
                '/^wp\-includes\/.*/',
                '/^wp-admin\/load-scripts.php\?.*/'
                ], $common_patterns, $bb_patterns)
];            
    }

        private function initialize_safe_plugins() {
            $this->safe_plugins = [
                'cred_submit_form' => [
                    'fifu-premium/fifu-premium.php',
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'types/wpcf.php',
                    'cred-frontend-editor/plugin.php',
                    'relationship_offloader_8_4/custom-toolset-relationship-manager_8_4.php'
                ],
                'bbapp_queue' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'bbapp_linkd_embed_data' => [
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php',
                    'wedevs-project-manager/cpm.php',
                    'wedevs-project-manager-business/cpm-pro.php',
                ],  

                'get_bb_pusher_threads' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'messages_send_reply' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ], 
                'bb_pusher_update_current_thread_unread_count' => [                    
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'wp_ulike_process' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'wp-ulike/wp-ulike.php',
                    'wp-ulike-pro/wp-ulike-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                ],
                'wpv_get_view_query_results' => [
                    'object-cache-pro/object-cache-pro.php',
                    'types/wpcf.php',
                    'wp-views/wp-views.php',
                    'fifu-premium/fifu-premium.php'
                ],
                'new_activity_comment' => [                        
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'activity_mark_fav' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'heartbeat' => [  
                    'object-cache-pro/object-cache-pro.php',
                    'mu-plugins/bb_heartbeat_redis-1.php', 
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'onesignal_update_device_info' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'onesignal-free-web-push-notifications/onesignal.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'buddyboss_theme_get_header_unread_messages' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'messages_get_thread_messages' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'messages_get_user_message_threads' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'as_async_request_queue_runner' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php',
                    'groundhogg/groundhogg.php',
                    'groundhogg-aws',
                    'groundhogg-pro',
                    'groundhogg-sms.php',
                    'memberpress/memberpress.php',
                    'memberpress-developer-tools/main.php'
                ],    
                'cred_association_form_ajax_role_find' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'types/wpcf.php',
                    'cred-frontend-editor/plugin.php'
                ],
                'post_update' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'post_draft_activity' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'activity_filter' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'buddyboss-platform/bp-loader.php',
                    'buddyboss-platform-pro/buddyboss-platform-pro.php',
                    'buddyboss-app/buddyboss-app.php'
                ],
                'inline-save' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'types/wpcf.php',
                    'cred-frontend-editor/plugin.php',
                    'fifu-premium/fifu-premium.php',
                ],
                
                'wpematico_cron' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'wpematico/wpematico.php',
                    'wpematico_fullcontent/wpematico_fullcontent.php',
                    'wpematico_googlo_news/wpematico_googlo_news.php',
                    'wpematico_professional/wpematicopro.php',
                ],

                'elementor_ajax' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'elementor/elementor.php',
                    'elementor-pro/elementor-pro.php',
                    'memberpress-elementor/memberpress-elementor.php',
                    'powerpack-elements/powerpack-elements.php',
                    'buddyboss-platform/bp-loader.php',
                    'types/wpcf.php'
                ],
                'elementor_save' => [
                    'bunnycdn/bunnycdn.php',
                    'object-cache-pro/object-cache-pro.php',
                    'elementor/elementor.php',
                    'elementor-pro/elementor-pro.php',
                    'memberpress-elementor/memberpress-elementor.php',
                    'powerpack-elements/powerpack-elements.php',
                    'buddyboss-platform/bp-loader.php',                        
                    'types/wpcf.php'
                ],
                'taxopress_autoterms_content_by_ajax' => [
                    'types/wpcf.php',
                    'taxopress/taxopress.php',
                    'taxopress-pro/taxopress-pro.php',
                    'object-cache-pro/object-cache-pro.php'
                ]
            ];            
        }

public function custom_selective_plugin_loading($plugins) {
    if ($this->is_targeted_action()) {
        $action = $_POST['action'];

                // Call the appropriate function if it exists
                if (isset($this->action_to_function[$action]) && method_exists($this, $this->action_to_function[$action])) {
                    $method = $this->action_to_function[$action];
                    return $this->$method($plugins);
                }

                // Apply safe plugins if no specific function is available
                if (isset($this->safe_plugins[$action])) {
                    $plugins = array_filter($plugins, function ($plugin) use ($action) {
                        return in_array($plugin, $this->safe_plugins[$action], true);
                    });

                    $this->filter_plugins_for_assets($action);
                }
            }
            return $plugins;
        }

        private function is_targeted_action() {
            return defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && isset($this->safe_plugins[$_POST['action']]);
        }

        private function filter_plugins_for_assets($action) {
            $request_uri = $_SERVER['REQUEST_URI'];
            foreach ($this->action_to_patterns[$action] as $pattern) {
                if (@preg_match($pattern, $request_uri)) {
                    error_log("Matched asset with pattern: $pattern");
                }
            }
        }

/*        
        private function filter_plugins($plugins, $action) {
            $filtered_plugins = array_filter($plugins, function ($plugin) use ($action) {
                return isset($this->safe_plugins[$action]) && in_array($plugin, $this->safe_plugins[$action], true);
            });
            return $filtered_plugins;
        }
*/
        private function filter_plugins($plugins, $action) {
            // Ensure $plugins is an array
            if (!is_array($plugins)) {
                $plugins = [];
            }

            $filtered_plugins = array_filter($plugins, function ($plugin) use ($action) {
                return isset($this->safe_plugins[$action]) && in_array($plugin, $this->safe_plugins[$action], true);
            });
            return $filtered_plugins;
        }
//

        // Define action-specific methods using dynamic method calls
        public function custom_selective_plugin_loading_cred_submit_form($plugins) {
            return $this->filter_plugins($plugins, 'cred_submit_form');
        }

        public function custom_selective_plugin_loading_activity_mark_fav($plugins) {
            return $this->filter_plugins($plugins, 'activity_mark_fav');
        }
    
        public function custom_selective_plugin_loading_bbapp_linkd_embed_data($plugins) {
            return $this->filter_plugins($plugins, 'bbapp_queue');
        }

        public function custom_selective_plugin_loading_get_bbapp_queue($plugins) {
            return $this->filter_plugins($plugins, 'bbapp_linkd_embed_data');
        }

        public function custom_selective_plugin_loading_get_bb_pusher_threads($plugins) {
            return $this->filter_plugins($plugins, 'get_bb_pusher_threads');
        }
    
        public function custom_selective_plugin_loading_post_draft_activity($plugins) {
            return $this->filter_plugins($plugins, 'post_draft_activity');
        }
    
        public function custom_selective_plugin_loading_messages_get_thread_messages($plugins) {
            return $this->filter_plugins($plugins, 'messages_get_thread_messages');
        }
    
        public function custom_selective_plugin_loading_activity_filter($plugins) {
            return $this->filter_plugins($plugins, 'activity_filter');
        }
        public function custom_selective_plugin_loading_wp_ulike_process($plugins) {
            return $this->filter_plugins($plugins, 'wp_ulike_process');
        }
    
        public function custom_selective_plugin_loading_toolset_view_ajax($plugins) {
            return $this->filter_plugins($plugins, 'wpv_get_view_query_results');
        }
    
        public function custom_selective_plugin_loading_new_activity_comment($plugins) {
            return $this->filter_plugins($plugins, 'new_activity_comment');
        }
    
        public function custom_selective_plugin_loading_bb_pusher_update_current_thread_unread_count($plugins) {
            return $this->filter_plugins($plugins, 'bb_pusher_update_current_thread_unread_count');
        }
    
        public function custom_selective_plugin_loading_heartbeat($plugins) {
            return $this->filter_plugins($plugins, 'heartbeat');
        }
    
        public function custom_selective_plugin_loading_onesignal_update_device_info($plugins) {
            return $this->filter_plugins($plugins, 'onesignal_update_device_info');
        }
    
        public function custom_selective_plugin_loading_buddyboss_theme_get_header_unread_messages($plugins) {
            return $this->filter_plugins($plugins, 'buddyboss_theme_get_header_unread_messages');
        }

        public function custom_selective_plugin_loading_messages_send_reply($plugins) {
            return $this->filter_plugins($plugins, 'messages_send_reply');
        }
    
        public function custom_selective_plugin_loading_messages_get_user_message_threads($plugins) {
            return $this->filter_plugins($plugins, 'messages_get_user_message_threads');
        }

        public function custom_selective_plugin_loading_as_async_request_queue_runner($plugins) {
            return $this->filter_plugins($plugins, 'as_async_request_queue_runner');
        }
    
        public function custom_selective_plugin_loading_cred_association_form_ajax_role_find($plugins) {
            return $this->filter_plugins($plugins, 'cred_association_form_ajax_role_find');
        }
    
        public function custom_selective_plugin_loading_post_update($plugins) {
            return $this->filter_plugins($plugins, 'post_update');
        }
    
        public function custom_selective_plugin_loading_elementor_save($plugins) {
            return $this->filter_plugins($plugins, 'elementor_save');
        }
    
        public function custom_selective_plugin_loading_elementor_ajax($plugins) {
            return $this->filter_plugins($plugins, 'elementor_ajax');
        }
    
        public function custom_selective_plugin_loading_inline_save($plugins) {
            return $this->filter_plugins($plugins, 'inline-save');
        }
    
        public function custom_selective_plugin_loading_memberpress($plugins) {
            return $this->filter_plugins($plugins, 'mepr_process_login_form');
        }
        public function custom_selective_plugin_wpematico_cron($plugins) {
            return $this->filter_plugins($plugins, 'wpematico_cron');
        }
        public function custom_selective_plugin_loading_taxopress_autoterms_content_by_ajax($plugins) {
            return $this->filter_plugins($plugins, 'taxopress_autoterms_content_by_ajax');
        }
        public function __construct() {
        // Hook the script enqueue function to WordPress
        add_action('wp_enqueue_scripts', [$this, 'ajax_offloading_enqueue_script']);
        }
        public function ajax_offloading_enqueue_script() {
        wp_enqueue_script(
            'ajax-offloading',
            plugin_dir_url(__FILE__) . 'js/ajax-offloading.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('ajax-offloading', 'ajaxOffloading', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ajax_offloading_nonce')
        ]);
        }
    // Initialize the class
    new ComprehensiveCacheControlOffloader();
?>
