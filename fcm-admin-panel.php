<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<h1><?php echo __(FCMPLUGIN_PLUGIN_NM, FCMPLUGIN_TRANSLATION); ?></h1>

<?php
$active_tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'front_page_options';
?>

<h2 class="nav-tab-wrapper">
    <a href="<?php echo admin_url('admin.php'); ?>?page=fcmplugin_push_notification&tab=front_page_options" class="nav-tab <?php echo $active_tab == 'front_page_options' ? 'nav-tab-active' : ''; ?>">FCM Options</a>
    <a href="<?php echo admin_url('admin.php'); ?>?page=fcmplugin_push_notification&tab=test_fcm" class="nav-tab <?php echo $active_tab == 'test_fcm' ? 'nav-tab-active' : ''; ?>">Test Push Notification</a>
    <a href="<?php echo admin_url('admin.php'); ?>?page=fcmplugin_push_notification&tab=version" class="nav-tab <?php echo $active_tab == 'version' ? 'nav-tab-active' : ''; ?>">Version</a> <!-- New Tab -->
    <a href="<?php echo admin_url('admin.php'); ?>?page=fcmplugin_push_notification&tab=show_categories" class="nav-tab <?php echo $active_tab == 'show_categories' ? 'nav-tab-active' : ''; ?>">Show Categories</a>
</h2>

<?php
if ($active_tab == 'front_page_options') {
?>

    <form action="options.php" method="post">
        <?php settings_fields('fcmplugin_group'); ?>
        <?php do_settings_sections('fcmplugin_group'); ?>
        <table style="width: 100%;">
            <tbody>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_api"><?php echo esc_attr(__("API Key", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_api" name="fcmplugin_api" type="text" value="<?php echo esc_attr(get_option('fcmplugin_api')); ?>" required="required" style="width: 50%" placeholder="Get the API key from https://console.firebase.google.com" /></td>
                </tr>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_channel"><?php echo esc_attr(__("Application Channel", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_channel" placeholder="Name of application channel" name="fcmplugin_channel" type="text" value="<?php echo esc_attr(get_option('fcmplugin_channel'));  ?>" required="required" style="width: 50%" /></td>
                </tr>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_topic"><?php echo esc_attr(__("Topic Configured in Application", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_topic" placeholder="Name of Topic setup in application" name="fcmplugin_topic" type="text" value="<?php echo esc_attr(get_option('fcmplugin_topic'));  ?>" required="required" style="width: 50%" /></td>
                </tr>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_sound"><?php echo esc_attr(__("Notification Sound", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_sound" placeholder="Name of Notification Sound" name="fcmplugin_sound" type="text" value="<?php echo esc_attr(get_option('fcmplugin_sound'));  ?>" required="required" style="width: 50%" /></td>
                </tr>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_default_image"><?php echo esc_attr(__("Default Image Display", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_default_image" placeholder="Url of the default image" name="fcmplugin_default_image" type="text" value="<?php echo esc_attr(get_option('fcmplugin_default_image'));  ?>" style="width: 50%" /></td>
                </tr>

                <tr style="width: 100%; height: 50px;">
                    <td style="width: 30%"><label for="fcmplugin_custom_fields"><?php echo esc_attr(__("Custom Fields", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                    <td style="width: 70%"><input id="fcmplugin_custom_fields" name="fcmplugin_custom_fields" type="text" value="<?php echo esc_attr(get_option('fcmplugin_custom_fields')); ?>" style="width: 50%" placeholder="field_name_1|field_name_2..." /></td>
                </tr>
                <tr style="width: 100%;">
                    <?php
                    /* get public post types, excpet the attachment */

                    $args  = array(
                        'public' => true,
                    );

                    $post_types = get_post_types($args, 'objects');

                    if ($post_types) { // If there are any custom public post types.
                        echo '<td><label>Post Types</label></td>';
                        echo '<td>';

                        foreach ($post_types  as $post_type) {
                            if ($post_type->name != 'attachment') {
                    ?>
                                <p><input id="ck_posttype_<?php echo $post_type->name; ?>" name="fcmplugin_posttype_<?php echo $post_type->name; ?>" type="checkbox" value="1"' <?php checked('1', esc_attr(get_option('fcmplugin_posttype_' . $post_type->name))) ?> />  <?php echo $post_type->name; ?>  </p>
                                <?php
                            }
                        }

                        echo '</td>';
                    }
                                ?>
            </tr>


            <tr style="width: 100%; height: 50px;">
                <td><label for="post_disable"><?php echo esc_attr(__("Disable Push Notification on Post", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                <td><input id="post_disable" name="fcmplugin_disable" type="checkbox" value="1" <?php checked('1', esc_attr(get_option('fcmplugin_disable'))); ?>  /></td>
            </tr>

            <tr style="width: 100%; height: 50px;">
                <td><label for="page_disable"><?php echo esc_attr(__("Disable Push Notification on Page", FCMPLUGIN_TRANSLATION)); ?></label> </td>
                <td><input id="page_disable" name="fcmplugin_page_disable" type="checkbox" value="1" <?php checked('1', esc_attr(get_option('fcmplugin_page_disable'))); ?>  /></td>
            </tr>

            <tr>
                <td> <div class="col-sm-10"><?php submit_button(); ?></td>
            </tr>

        </tbody>
    </table>

</form>

<?php
} else if ($active_tab == 'test_fcm') {
    if (get_option('fcmplugin_api')) {
?>
        <div>    
            <h2><?php echo __("Send a test notification", FCMPLUGIN_TRANSLATION); ?></h2>
            <p><?php echo __("The Firebase API key and topic need to be entered in FCM Options.", FCMPLUGIN_TRANSLATION); ?></p>
            <a href="<?php echo admin_url('admin.php'); ?>?page=test_push_notification"><?php echo __("Click here to send a test notification", FCMPLUGIN_TRANSLATION); ?></a>
        </div>
<?php
    }
} else if ($active_tab == 'show_categories') {
    // Initialize selected categories array
    $selected_categories = array();

    // Load selected categories from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'show_categories';
    $selected_categories_row = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
    if ($selected_categories_row) {
        $selected_categories = explode(',', $selected_categories_row->category_ids);
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_categories"])) {
        if (isset($_POST["selected_categories"]) && is_array($_POST["selected_categories"])) {
            $selected_categories = array_map('intval', $_POST["selected_categories"]); // Sanitize and validate

            // Save selected categories to the database
            $category_ids = implode(',', $selected_categories);
            if ($selected_categories_row) {
                // Update existing row
                $wpdb->update($table_name, array('category_ids' => $category_ids), array('id' => $selected_categories_row->id));
            } else {
                // Insert new row
                $wpdb->insert($table_name, array('category_ids' => $category_ids));
            }

            // Optionally, you can add a success message here
            echo '<div class="updated"><p>Categories saved successfully.</p></div>';
        }
    }
?>
    <div>
        <form action="" method="post" class="form-notification">
            <fieldset>
                <legend><h4 style="margin: 0">Select Categories:</h4></legend>
                <div class="category-wrapper">
                    <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
                        echo '<div class="category-item"><label><input type="checkbox" name="selected_categories[]" value="' . $category->term_id . '" ' . $checked . '> ' . $category->name . '</label><br></div>';
                    }
                    ?>
                </div>
            </fieldset>
            <div class="col-sm-10"><p class="submit"><input type="submit" name="update_categories" id="submit" class="button button-primary" value="Save Categories"></p></div>
        </form>
    </div>
    <style>
        input[type=checkbox] {
            margin: 0 !important;
        }
        
        .form-notification {
            padding: 10px;
            font-size: 1rem;
        }
        
        .form-group {
            display: flex;
            gap: 5px;
            align-items: flex-start;
        }
        
        .form-group.col {
            flex-direction: column;
        }
        
        .form-section {
            margin-bottom: 15px;
            display: grid;
            gap: 5px;
        }
        
        .category-wrapper {
            box-sizing: border-box;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            place-items: stretch;
            gap: 10px;
            padding: 5px 0;
        }
        
        .category-wrapper .category-item {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            display: grid;
            place-items: start;
            gap: 10px;
            border-radius: 0.375rem;
            border: 1px solid #dbdfe6;
            box-shadow: 0 .125rem .25rem rgba(8, 10, 12, .075);
            background: #ffffff;
        }
    </style>
    <?php
} else if ($active_tab == 'version') {

    // Version Content
    $plugin_data = get_plugin_data(__FILE__); // Assuming this file is the main plugin file
    $plugin_version = $plugin_data['Version'];
    ?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_version"])) {
        // Check if the form is submitted and the update_version button is clicked

        // Retrieve the version number from the form
        $version_number = sanitize_text_field($_POST["version_number"]);

        // Retrieve the selected categories from the form
        $selected_categories = isset($_POST["selected_categories"]) ? $_POST["selected_categories"] : array();

        // Prepare the category string for insertion into the database
        $categories_string = implode(",", $selected_categories);

        // Insert data into the fcm_plugin_data table
        global $wpdb;
        $table_name = $wpdb->prefix . 'fcm_plugin_data';

        $wpdb->insert(
            $table_name,
            array(
                'version_string' => $version_number,
                'categories_string' => $categories_string,
                'is_force_update' => 0 // Assuming it's not a force update by default
            ),
            array(
                '%s',
                '%s',
                '%d'
            )
        );

        // You can add additional logic here, such as redirecting the user to another page after submission
    }
?>
<?php
    // Fetch existing version number from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'fcm_plugin_data';
    $latest_version = $wpdb->get_var("SELECT version_string FROM $table_name ORDER BY id DESC LIMIT 1");

    // Fetch categories associated with the latest version from the database
    $latest_categories = $wpdb->get_var("SELECT categories_string FROM $table_name ORDER BY id DESC LIMIT 1");
    $latest_categories_array = explode(',', $latest_categories);
?>

<div>
    <form action="" method="post" class="form-notification">
		<section class="form-section">
			<div class="form-group col">
				<label for="version_number">Enter New Version Number:</label>
				<input type="text" id="version_number" name="version_number" required value="<?php echo esc_attr($latest_version); ?>">
			</div>
			<div class="form-group">
				<label for="is_forced">Forced:</label>
				<input type="checkbox" id="is_forced" name="is_forced">
			</div>
		</section>
		
		<section class="form-section">
			<fieldset>
            <legend>
				<h4 style="margin: 0">Select Categories:</h4>
			</legend>
			<div class="category-wrapper">
				 <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        $checked = in_array($category->term_id, $latest_categories_array) ? 'checked' : '';
                        echo '<div class="category-item"><label><input type="checkbox" name="selected_categories[]" value="' . $category->term_id . '" ' . $checked . '> ' . $category->name . '</label><br></div>';
                    }
                    ?>
			</div>
        </fieldset>
		</section>
		
		<div class="col-sm-10"><p class="submit"><input type="submit" name="update_version" id="submit" class="button button-primary" value="Save Changes"></p></div>
    </form>
</div>
<style>
	input[type=checkbox] {
		margin: 0 !important;
	}
	
	.form-notification {
		padding: 10px;
		font-size: 1rem;
	}
	
	.form-group {
		display: flex;
		gap: 5px;
		align-items: flex-start;
	}
	
	.form-group.col {
		flex-direction: column;
	}
	
	.form-section {
		margin-bottom: 15px;
		display: grid;
		gap: 5px;
	}
	
	.category-wrapper {
		box-sizing: border-box;
		width: 100%;
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
		place-items: stretch;
		gap: 10px;
		padding: 5px 0;
	}
	
	.category-wrapper .category-item {
		box-sizing: border-box;
		width: 100%;
		padding: 10px;
		display: grid;
		place-items: start;
		gap: 10px;
		border-radius: 0.375rem;
		border: 1px solid #dbdfe6;
		box-shadow: 0 .125rem .25rem rgba(8, 10, 12, .075);
		background: #ffffff;
	}
	
</style>
<?php
}
?>