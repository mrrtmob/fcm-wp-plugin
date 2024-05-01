=== FCM WP Push Notification ===
Contributors: tmob
Donate link: https://www.buymeacoffee.com/tmob
Tags: fcm, firebase, push, notification, android, ios, flutter
Requires at least: 4.6
Tested up to: 5.8.2
Stable tag: 1.0
Requires PHP: 5.6.20
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: fcm-push-notification
Domain Path: /languages

Keep your app users informed with timely notifications via Firebase Cloud Messaging (FCM) whenever new content is published or existing content is updated.

== Description ==

Notifications for posts, pages and custom post types.

Works with scheduled posts.

Send notifications to users of your app from your website using Google's service, Firebase Push Notification.

The notification sent includes the block with the data message to be handled by the application, even when it is in the background.

Configure the plugin to start sending notifications.

Send custom field values ​​in the notification, in the data option.

Send a notification when you post news or update your content. When editing, the option is deselected to send you to accidentally send a new notification. Check if you want to send a new notification when editing.

Compatible with apps developed with the SDK Flutter.

You need to register users on the same topic (fcm) that was informed in the plugin configuration. This plugin is not intended for sending notifications to websites.

Support my work
<https://www.buymeacoffee.com/tmob>

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/fcm-push-notification-from-wp` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->FCM Push Notification from WordPress screen to configure the plugin.
4. In the FCM Options put the FCM API Key and put the topic name registered in your app.
5. Optionally put the image url to display in the notification.

== Frequently Asked Questions ==

= Does it work with scheduled posts? =

Yes. When a post changes the status from scheduled to published, a notification is sent. If the option was checked when saving the post.

= Can I send to one user only? =

No. You can only send to the topic informed in the plugin configuration.

All users receive notification.

= Can I disable sending on the publication screen? =

Yes. Uncheck the checkbox to not send a notification.

= Can I send to my site user? =

No. sends only to users who are using your android/ios app.

== Screenshots ==

1. Plugin settings screen.
2. Sending from a post.
3. Sending from a custom post type.
4. Sending from a page.
5. Sending from a scheduled post.
6. Notification and data message fields.
7. Test performed using WordPress 5.8.1. Opening the notification within the app.
8. Test performed using WordPress 5.8.1. Notification being displayed when the app is closed.

== Changelog ==

= 1.0.0 =
* First version.