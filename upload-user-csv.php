<?php
/**
 * Plugin Name: Upload User List CSV
 * Description: Adds a settings submenu to upload a CSV of users.
 * Version: 1.0
 * Author: Rajesh Kumar Paul
 */

 // Add submenu under Settings
 add_action('admin_menu', 'upload_user_csv_menu');

 function upload_user_csv_menu() {
     add_submenu_page(
         'options-general.php',          // Parent slug (Settings menu)
         'Upload User CSV',              // Page title
         'Upload User List CSV',         // Menu title
         'manage_options',               // Capability
         'upload-user-csv',              // Menu slug
         'upload_user_csv_page'          // Callback function
     );
 }

 // Page content
 function upload_user_csv_page() {
     ?>
     <div class="wrap">
         <h1>Upload User List CSV</h1>
         <hr>
         <div class="mt-3">
             <form method="post" enctype="multipart/form-data">
             <input type="file" name="user_csv_file" accept=".csv" />
             <input type="submit" name="upload_user_csv" class="button button-primary" value="Upload CSV" />
         </form>
         </div>
         
     </div>
<?php
     // Handle file upload
     // Handle file upload
if (isset($_POST['upload_user_csv']) && !empty($_FILES['user_csv_file']['tmp_name'])) {
    $file = $_FILES['user_csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $row = 0;
        echo "<ul>";
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($row === 0) {
                // Skip header row
                $row++;
                continue;
            }

            $user_login    = sanitize_user($data[0]);
            $user_pass     = sanitize_text_field($data[1]);
            $user_email    = sanitize_email($data[2]);
            $display_name  = sanitize_text_field($data[3]);

            if (username_exists($user_login) || email_exists($user_email)) {
                echo "<li>❌ Skipped: User <strong>$user_login</strong> already exists.</li>";
                continue;
            }

            $user_id = wp_create_user($user_login, $user_pass, $user_email);

            if (!is_wp_error($user_id)) {
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $display_name
                ]);
                echo "<li>✅ Created user: <strong>$user_login</strong></li>";
            } else {
                echo "<li>❌ Error creating user <strong>$user_login</strong>: " . $user_id->get_error_message() . "</li>";
            }

            $row++;
        }
        echo "</ul>";
        fclose($handle);
    } else {
        echo "<p>❌ Failed to open the CSV file.</p>";
    }
}

 }
 
 
?>

