<?php
/*
Plugin Name: FCM Push Notification
Description:Keep your app users informed with timely notifications via Firebase Cloud Messaging (FCM) whenever new content is published or existing content is updated.
Version:1.0.0
Author:tmob
Author URI:https://www.buymeacoffee.com/tmob
License:GPL2
License URI:https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
FCM Push Notification is free software distributed under the GNU General Public License. 
It comes with no warranty and can be redistributed and modified. 
For details, visit https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Create custom database table
function create_custom_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'custom_notifications'; // Prefix with WordPress database prefix

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        title text NOT NULL,
        body text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function create_show_categories_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'show_categories';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category_ids text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function create_version_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'fcm_plugin_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        version_string varchar(255) NOT NULL,
        categories_string varchar(255) NOT NULL,
        is_force_update tinyint(1) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create the table when the plugin is activated
register_activation_hook(__FILE__, 'create_custom_table');
register_activation_hook(__FILE__, 'create_show_categories_table');
register_activation_hook(__FILE__, 'create_version_table');

function insert_custom_notification($post_id, $title, $body)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'custom_notifications'; // Prefix with WordPress database prefix

    $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'title' => $title,
            'body' => $body
        ),
        array(
            '%d',
            '%s',
            '%s'
        )
    );
}

// Drop tables on plugin deactivation
function custom_remove_tables_on_deactivation()
{
    global $wpdb;

    $table_name1 = $wpdb->prefix . 'custom_notifications'; // Prefix with WordPress database prefix
    $table_name2 = $wpdb->prefix . 'fcm_plugin_data';
    $table_name3 = $wpdb->prefix . 'show_categories';


    $wpdb->query("DROP TABLE IF EXISTS $table_name1, $table_name2 , $table_name3");
}

register_deactivation_hook(__FILE__, 'custom_remove_tables_on_deactivation');

// Remove custom database table when the plugin is uninstalled
function custom_remove_table_on_uninstall()
{
    global $wpdb;

    $table_name1 = $wpdb->prefix . 'custom_notifications'; // Prefix with WordPress database prefix
    $table_name2 = $wpdb->prefix . 'show_categories';
    $table_name3 = $wpdb->prefix . 'fcm_plugin_data';

    $wpdb->query("DROP TABLE IF EXISTS $table_name1, $table_name2, $table_name3");
}

register_uninstall_hook(__FILE__, 'custom_remove_table_on_uninstall');


if (!defined('ABSPATH')) {
    exit;
}

if (!defined("FCMPLUGIN_VERSION_CURRENT")) define("FCMPLUGIN_VERSION_CURRENT", '1');
if (!defined("FCMPLUGIN_URL")) define("FCMPLUGIN_URL", plugin_dir_url(__FILE__));
if (!defined("FCMPLUGIN_PLUGIN_DIR")) define("FCMPLUGIN_PLUGIN_DIR", plugin_dir_path(__FILE__));
if (!defined("FCMPLUGIN_PLUGIN_NM")) define("FCMPLUGIN_PLUGIN_NM", 'FCM Push Notification');
if (!defined("FCMPLUGIN_TRANSLATION")) define("FCMPLUGIN_TRANSLATION", 'fcmplugin_translation');

/* FCMPLUGIN */
class FCMPLUGIN_Push_Notification
{

    public function __construct()
    {

        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array($this, 'fcmplugin_activate'));
        register_deactivation_hook(__FILE__, array($this, 'fcmplugin_deactivate'));
        add_action('admin_menu', array($this, 'fcmplugin_setup_admin_menu'));
        add_action('admin_init', array($this, 'fcmplugin_settings'));

        add_action('add_meta_boxes', array($this, 'fcmplugin_featured_meta'), 1);
        add_action('save_post', array($this, 'fcmplugin_meta_save'), 1, 1);

        add_action('future_to_publish', array($this, 'fcmplugin_future_to_publish'), 10, 1);


        add_filter('plugin_action_links_fcm-push-notification-from-wp/fcm-push-notification.php', array($this, 'fcmplugin_settings_link'));
    }

    function fcmplugin_featured_meta()
    {
        //add_meta_box( 'fcmplugin_ckmeta_send_notification', __( 'Push Notification', FCMPLUGIN_TRANSLATION ), array($this, 'fcmplugin_meta_callback'), 'post', 'side', 'high', null );

        /* set meta box to a post type */
        $args  = array(
            'public' => true,
        );

        $post_types = get_post_types($args, 'objects');

        if ($post_types) {

            // If there are any custom public post types.
            foreach ($post_types  as $post_type) {
                if ($post_type->name != 'attachment') {
                    if ($this->get_options_posttype($post_type->name)) {
                        add_meta_box('fcmplugin_ckmeta_send_notification', esc_attr(__('Push Notification', FCMPLUGIN_TRANSLATION)), array($this, 'fcmplugin_meta_callback'), $post_type->name, 'side', 'high', null);
                    }
                }
            }
        }
    }

    function fcmplugin_meta_callback($post)
    {
        global $pagenow;
        wp_nonce_field(basename(__FILE__), 'fcmplugin_nonce');
        $fcmplugin_stored_meta = get_post_meta($post->ID);
        $checked = get_option('fcmplugin_disable') != 1; //$fcmplugin_stored_meta['send-fcm-checkbox'][0];

        //$this->write_log('fcmplugin_meta_callback: $checked: ' . $checked);
        //$this->write_log('fcmplugin_meta_callback: $post->post_status: ' . $post->post_status);

?>

        <p>
            <span class="fcm-row-title"><?php echo esc_html(__('Check if send a push notification: ', FCMPLUGIN_TRANSLATION)); ?></span>
        <div class="fcm-row-content">
            <label for="send-fcm-checkbox">
                <?php if (in_array($pagenow, array('post-new.php')) ||  $post->post_status == 'future') { ?>
                    <input type="checkbox" name="send-fcm-checkbox" id="send-fcm-checkbox" value="1" <?php if (isset($fcmplugin_stored_meta['send-fcm-checkbox'])) checked($checked, '1'); ?> />
                <?php } else { ?>
                    <input type="checkbox" name="send-fcm-checkbox" id="send-fcm-checkbox" value="1" />
                <?php } ?>
                <?php echo esc_attr(__('Send Push Notification', FCMPLUGIN_TRANSLATION)); ?>
            </label>

        </div>
        </p>

<?php
    }

    /**
     * Saves the custom meta input
     */
    function fcmplugin_meta_save($post_id)
    {

        // Checks save status - overcome autosave, etc.
        $is_autosave = wp_is_post_autosave($post_id);
        $is_revision = wp_is_post_revision($post_id);
        $is_valid_nonce = (isset($_POST['fcmplugin_nonce']) && wp_verify_nonce($_POST['fcmplugin_nonce'], basename(__FILE__))) ? 'true' : 'false';

        //$this->write_log('fcmplugin_meta_save');

        // Exits script depending on save status
        if ($is_autosave || $is_revision || !$is_valid_nonce) {
            return;
        }

        //$this->write_log('remove_action: wp_insert_post');
        remove_action('wp_insert_post', array($this, 'fcmplugin_on_post_save'), 10);

        if (isset($_POST['send-fcm-checkbox'])) {
            update_post_meta($post_id, 'send-fcm-checkbox', '1');
            //$this->write_log('add_action: send-fcm-checkbox 1');
        } else {
            //$this->write_log('add_action: send-fcm-checkbox 0');
            update_post_meta($post_id, 'send-fcm-checkbox', '0');
        }

        //$this->write_log('add_action: wp_insert_post');
        add_action('wp_insert_post', array($this, 'fcmplugin_on_post_save'), 10, 3);
    }

    function fcmplugin_future_to_publish($post)
    {
        //$this->write_log('fcmplugin_future_to_publish: CHAMOU EVENTO');
        $this->fcmplugin_send_notification_on_save($post, true);
    }

    function fcmplugin_on_post_save($post_id, $post, $update)
    {
        $this->fcmplugin_send_notification_on_save($post, $update);
    }

    private function fcmplugin_send_notification_on_save($post, $update)
    {

        if (get_option('fcmplugin_api') && get_option('fcmplugin_topic')) {

            //new post/page
            if (isset($post->post_status)) {

                if ($update && ($post->post_status == 'publish')) {

                    $send_fcmplugin_checkbox = get_post_meta($post->ID, 'send-fcm-checkbox', true);

                    if ($send_fcmplugin_checkbox) {

                        if ($this->get_options_posttype($post->post_type)) {
                            $result = $this->fcmplugin_notification($post, false, false, '');
                        } elseif ($this->get_options_posttype($post->post_type)) {
                            $result = $this->fcmplugin_notification($post, false, false, '');
                        }

                        update_post_meta($post->ID, 'send-fcm-checkbox', '0');
                    }
                }
            }
        }
    }

    public function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    public function get_options_posttype($post_type)
    {
        return get_option('fcmplugin_posttype_' . $post_type) == 1;
    }

    public function fcmplugin_setup_admin_menu()
    {
        add_submenu_page('options-general.php', __('Firebase Push Notification', FCMPLUGIN_TRANSLATION), FCMPLUGIN_PLUGIN_NM, 'manage_options', 'fcmplugin_push_notification', array($this, 'fcmplugin_admin_page'));

        add_submenu_page(
            null,
            __('Test Push Notification', FCMPLUGIN_TRANSLATION),
            'Test Notification',
            'administrator',
            'test_push_notification',
            array($this, 'fcmplugin_send_test_notification')
        );
    }

    public function fcmplugin_admin_page()
    {
        include(plugin_dir_path(__FILE__) . 'fcm-admin-panel.php');
    }

    public function fcmplugin_activate()
    {
    }

    public function fcmplugin_deactivate()
    {
    }


    function fcmplugin_settings()
    {
        register_setting('fcmplugin_group', 'fcmplugin_api');
        register_setting('fcmplugin_group', 'fcmplugin_topic');
        register_setting('fcmplugin_group', 'fcmplugin_disable');
        register_setting('fcmplugin_group', 'fcmplugin_page_disable');
        register_setting('fcmplugin_group', 'fcmplugin_channel');
        register_setting('fcmplugin_group', 'fcmplugin_default_image');

        register_setting('fcmplugin_group', 'fcmplugin_sound');

        register_setting('fcmplugin_group', 'fcmplugin_custom_fields');

        /* set checkboxs post types */
        $args  = array(
            'public' => true,
        );

        $post_types = get_post_types($args, 'objects');

        if ($post_types) { // If there are any custom public post types.

            foreach ($post_types  as $post_type) {
                //$this->write_log('add action 4: ' . $post_type->name);
                if ($post_type->name != 'attachment') {
                    register_setting('fcmplugin_group', 'fcmplugin_posttype_' . $post_type->name);
                }
            }
        }
    }


    function fcmplugin_send_test_notification()
    {

        $test = new FCMPLUGINTestSendPushNotification;

        $test->post_type = "test";
        $test->ID = 0;
        $test->post_title = "Test Push Notification";
        $test->post_content = "Test from Firebase Push Notification Plugin";
        $test->post_excerpt = "Test from Firebase Push Notification Plugin";
        $test->post_url = "https://blizzer.tech";


        $result = $this->fcmplugin_notification($test, false, false, '');

        echo '<div class="row">';
        echo '<div><h2>API Return</h2>';

        echo '<pre>';
        printf($result);
        echo '</pre>';

        echo '<p><a href="' . admin_url('admin.php') . '?page=test_push_notification">Send again</a></p>';
        echo '<p><a href="' . admin_url('admin.php') . '?page=fcmplugin_push_notification">FCM Options</a></p>';

        echo '</div>';
    }

    //function fcmplugin_notification($title, $content, $resume, $post_id, $image){
    function fcmplugin_notification($post, $sendOnlyData, $showLocalNotification, $command)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcm_plugin_data';

        // Retrieve categories from the fcm_plugin_data table
        $categories_string = $wpdb->get_var("SELECT categories_string FROM $table_name ORDER BY id DESC LIMIT 1");

        // Convert the comma-separated category IDs string to an array
        $categories_array = explode(',', $categories_string);

        // Check if the post belongs to category ID 
        $post_categories = wp_get_post_categories($post->ID);
        if (array_intersect($categories_array, $post_categories)) {

            $from = get_bloginfo('name');
            //$content = 'There are new post notification from '.$from;

            $post_type = esc_attr($post->post_type);
            $post_id = esc_attr($post->ID);
            $post_title = $post->post_title;
            $content = esc_html(wp_strip_all_tags(preg_replace("/\r|\n/", " ", $post->post_content)));

            $content = wp_specialchars_decode($content, ENT_QUOTES);

            $shotcodes_tags = array('vc_row', 'vc_column', 'vc_column', 'vc_column_text', 'vc_message');
            $content = preg_replace('/\[(\/?(' . implode('|', $shotcodes_tags) . ').*?(?=\]))\]/', ' ', $content);

            $content = preg_replace('/\[(\/?.*?(?=\]))\]/', ' ', $content);

            $resume = wp_specialchars_decode(esc_attr($post->post_excerpt), ENT_QUOTES);

            $post_url = esc_url(get_the_permalink($post->ID));

            $thumb_id = get_post_thumbnail_id($post_id);

            $thumb_url = wp_get_attachment_image_src($thumb_id, 'full');

            $image = $thumb_url ? esc_url($thumb_url[0]) : '';

            if (_mb_strlen($image) == 0) {
                $image = get_option('fcmplugin_default_image');
            }

            $sound =  esc_attr(get_option('fcmplugin_sound'));


            $topic =  esc_attr(get_option('fcmplugin_topic'));
            $apiKey = esc_attr(get_option('fcmplugin_api'));
            $url = 'https://fcm.googleapis.com/fcm/send';

            $customFields =  esc_attr(get_option('fcmplugin_custom_fields'));
            //$this->write_log('customFields: ' . $customFields);

            $arrCustomFieldsValues = [];

            if (_mb_strlen($customFields) > 0) {

                $arrCustomFields = explode("|", $customFields);
                foreach ($arrCustomFields as $i => $customField) :

                    $arrCustomFieldsValues[$customField] = esc_attr(get_post_meta($post_id, $customField, TRUE));
                endforeach;
            } else {
                //$this->write_log('arrCustomFields: Vazio');
            }

            $notification_data = array(
                'click_action'          => 'FLUTTER_NOTIFICATION_CLICK',
                'message'               => _mb_strlen($resume) == 0 ? _mb_substr(wp_strip_all_tags($content), 0, 55) . '...' : $resume,
                'post_type'             => $post_type,
                'post_id'               => $post_id,
                'title'                 => $post_title,
                'image'                 => $image,
                'url'                   => $post_url,
                'show_in_notification'  => $showLocalNotification,
                'command'               => $command,
                'dialog_title'          => $post_title,
                'dialog_text'           => _mb_strlen($resume) == 0 ? _mb_substr(wp_strip_all_tags($content), 0, 100) . '...' : $resume,
                'dialog_image'          => $image,
                'sound'                 => _mb_strlen($sound) == 0 ? 'default' : $sound,
                'customm_fields'        => $arrCustomFieldsValues,
                'test'                    => 'test'
            );

            $this->write_log('notification_data: ' . json_encode($notification_data));

            $notification = array(
                'title'                 => $post_title,
                'body'                  => _mb_strlen($resume) == 0 ? _mb_substr(wp_strip_all_tags($content), 0, 55) . '...' : $resume,
                'content_available'     => true,
                'android_channel_id'    => get_option('fcmplugin_channel'),
                'click_action'          => 'FLUTTER_NOTIFICATION_CLICK',
                'sound'                 => _mb_strlen($sound) == 0 ? 'default' : $sound,
                'image'                 => $image,
            );

            $post = array(
                'to'                    => '/topics/' . $topic,
                'collapse_key'          => 'type_a',
                'notification'          => $notification,
                'priority'              => 'high',
                'data'                  => $notification_data,
                'timeToLive'            => 10,
            );

            $payload = json_encode($post);

            $args = array(
                'timeout'           => 45,
                'redirection'       => 5,
                'httpversion'       => '1.1',
                'method'            => 'POST',
                'body'              => $payload,
                'sslverify'         => false,
                'headers'           => array(
                    'Content-Type'      => 'application/json',
                    'Authorization'     => 'key=' . $apiKey,
                ),
                'cookies'           => array()
            );

            $response = wp_remote_post($url, $args);
            insert_custom_notification($notification_data['post_id'], $notification_data['title'], $notification_data['message']);

            return json_encode($post);
        }

        return json_encode(array('message' => 'Notification not sent for this post.'));
    }


    function fcmplugin_settings_link($links)
    {
        // Build and escape the URL.
        $url = esc_url(add_query_arg(
            'page',
            'fcmplugin_push_notification',
            get_admin_url() . 'admin.php'
        ));
        // Create the link.
        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
        // Adds the link to the end of the array.
        array_push(
            $links,
            $settings_link
        );
        return $links;
    } //end nc_settings_link()


}

/* to test a send notification */
class FCMPLUGINTestSendPushNotification
{
    public  $ID;
    public  $post_type;
    public  $post_content;
    public  $post_excerpt;
    public  $post_url;
}

$FCMPLUGIN_Push_Notification_OBJ = new FCMPLUGIN_Push_Notification();

// Add a custom endpoint
function custom_api_endpoint()
{
    register_rest_route('v1', '/notifications/', array(
        'methods' => 'GET',
        'callback' => 'custom_api_callback',
    ));
}

add_action('rest_api_init', 'custom_api_endpoint');

// ========================= api get version ============================
add_action('rest_api_init', 'custom_endpoint');

function custom_endpoint()
{
    register_rest_route('wp/v2', '/version', array(
        'methods' => 'GET',
        'callback' => 'custom_endpoint_callback',
    ));
}

function custom_endpoint_callback($data)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'fcm_plugin_data';
    $latest_version = $wpdb->get_var("SELECT version_string FROM $table_name ORDER BY id DESC LIMIT 1");
    $is_force_update = $wpdb->get_var("SELECT is_force_update FROM $table_name ORDER BY id DESC LIMIT 1");
    $response = array(
        'version' => $latest_version,
        'is_force_update' => $is_force_update,
    );
    return rest_ensure_response($response);
}
// ========================= api get version =============================


// ========================= api get categories =============================
// Callback function for the custom endpoint
function custom_api_callback($data)
{
    global $wpdb;

    // Get page and limit from the request data
    $page = isset($data['page']) ? intval($data['page']) : 1; // Default to page 1 if not provided
    $limit = isset($data['limit']) ? intval($data['limit']) : 10; // Default limit to 10 if not provided

    // Calculate the offset based on page and limit
    $offset = ($page - 1) * $limit;

    // Construct the SQL query with limit and offset
    $table_name = $wpdb->prefix . 'custom_notifications';
    $sql = $wpdb->prepare("
        SELECT * 
        FROM $table_name
        ORDER BY created_at DESC
        LIMIT %d
        OFFSET %d
    ", $limit, $offset);

    // Execute the query
    $notifications = $wpdb->get_results($sql, ARRAY_A);

    // Return the array of notification objects as JSON response
    return $notifications;
}


// Add a custom API endpoint to retrieve categories and their details
function custom_categories_details_api_endpoint()
{
    register_rest_route('wp/v2', '/show-categories/', array(
        'methods' => 'GET',
        'callback' => 'custom_categories_details_api_callback',
    ));
}

add_action('rest_api_init', 'custom_categories_details_api_endpoint');

// Callback function for the custom categories endpoint
function custom_categories_details_api_callback($data)
{
    global $wpdb;

    // Fetch category IDs from the show_categories table
    $table_name = $wpdb->prefix . 'show_categories';
    $category_ids = $wpdb->get_col("SELECT category_ids FROM $table_name");

    // Initialize an array to store category details
    $categories_details = array();

    // Loop through each category ID and query its details
    foreach ($category_ids as $category_id_string) {
        // Convert comma-separated string to an array of IDs
        $category_ids_array = explode(',', $category_id_string);

        // Fetch category details based on the IDs
        foreach ($category_ids_array as $category_id) {
            $category = get_category($category_id);
            if ($category) {
                $category_data = array(
                    'id' => $category->term_id,
                    'count' => $category->count,
                    'description' => $category->description,
                    'link' => get_category_link($category->term_id),
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'taxonomy' => $category->taxonomy,
                    'parent' => $category->parent,
                    'meta' => $category->meta,
                    '_links' => array(
                        'self' => array(
                            array(
                                'href' => rest_url("wp/v2/categories/{$category->term_id}")
                            )
                        ),
                        'collection' => array(
                            array(
                                'href' => rest_url("wp/v2/categories")
                            )
                        ),
                        'about' => array(
                            array(
                                'href' => rest_url("wp/v2/taxonomies/{$category->taxonomy}")
                            )
                        ),
                        'up' => array(
                            array(
                                'embeddable' => true,
                                'href' => rest_url("wp/v2/categories/{$category->parent}")
                            )
                        ),
                        'wp:post_type' => array(
                            array(
                                'href' => rest_url("wp/v2/posts?categories={$category->term_id}")
                            )
                        ),
                        'curies' => array(
                            array(
                                'name' => 'wp',
                                'href' => 'https://api.w.org/{rel}',
                                'templated' => true
                            )
                        )
                    )
                );
                $categories_details[] = $category_data;
            }
        }
    }

    // Return the array of category details as JSON response
    return $categories_details;
}

// ========================= api get categories =============================
