<?php

class Export_data{
        
    /**
     * __construct
     * 
     * Constructor contains actions hooks.
     *
     * @return void
     */
    function __construct() {

        // Hook for handle csv request for authentic and unauthentic users.
        add_action( 'wp_ajax_export_users', array( $this, 'handle_csv_export_request' ) );
        add_action( 'wp_ajax_nopriv_export_users', array( $this,  'handle_csv_export_request' ) );

        // Hook for display download csv and role dropdown list in the header.
        add_action( 'admin_head-users.php', array( $this, 'add_csv_export_button' ) );
        
    }
    
    /**
     * handle_csv_export_request
     *
     * Handle download csv request.
     * 
     * Download csv file with filename and table header.
     * 
     * Display selected role user or display all users in csv file.
     * 
     * @return void
     */
    function handle_csv_export_request() {

        // Check exports users is set.
        if ( isset( $_GET['action']) && $_GET['action'] == 'export_users' ) {

            // If any role selected, get all users of selected role.
            if ( isset( $_POST['role'] ) && !empty( $_POST['role'] ) ) {
                // Get selected role.
                $role = sanitize_text_field( $_POST['role'] );
                $args = array(
                    'role'   => $role,
                    'fields' => array( 'display_name', 'user_email', 'roles' ),
                    'number' => -1,
                );
                $users = get_users( $args );
            } else {
                
                // Get all users.
                $users = get_users();
            }
        }

        // Get current user data.
        $user = wp_get_current_user();

        // Set CSV filename.
        $filename = strtolower( str_replace( ' ', '-', $role) ) . '-' . strtolower( str_replace( ' ', '-', $user->display_name) ) . '.csv';

        // Set CSV header.
        $header = array( 'Name', 'Email', 'Role' );
        
        // Get user data.
        $users = get_users( array( 'role' => $role ) );
        
        // Download CSV file.
        header( 'Content-Type: application/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
        
        // Open CSV file for writing.
        $handle = fopen( 'php://output', 'w' );

        // Write header to CSV file.
        fputcsv( $handle, $header );
        
        // Loop through users and write data to CSV file.
        foreach ( $users as $user ) {
            $data = array(
                $user->display_name,
                $user->user_email,
                implode( ', ', $user->roles )
            );
            fputcsv( $handle, $data );
        }
        
        // Close CSV file
        fclose( $handle );
        exit;
    }

    /**
     * add_csv_export_button
     *
     * Display dropdown list of roles and download csv button.
     * 
     * Script to display csv form before table nav class.
     * 
     * @return void
     */
    function add_csv_export_button() {

        global $wp_roles;

        // Get users roles name.
        $editable_roles = $wp_roles->get_names();

        // Get current role.
        $current_role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

        $url = add_query_arg( array( 'action' => 'export_users' ), admin_url( 'admin-ajax.php' ) );

        // Script to display form below table nav class.
        ob_start(); ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.tablenav.top .clear').before('<div class="export-csv"><form id="export-csv-form" method="post" action="<?php echo $url; ?>" style="margin-left: 40%;"><select id="export-csv-role" name="role" style="width: 180px;"><option value="">All Roles</option><?php foreach ( $editable_roles as $role => $name ) : ?><option value="<?php echo esc_attr( $role ); ?>" <?php selected( $role, $current_role ); ?>><?php echo esc_html( $name ); ?></option><?php endforeach; ?></select><button type="submit" class="button" style="margin-left:10px;"><?php _e( 'Download CSV', 'text-domain' ); ?></button></form></div>');
        });
        </script>
        <?php
    }
}
new Export_data();

// add_action( 'restrict_manage_users', 'add_csv_export_button' );
// function add_csv_export_button() {
//         $current_role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';
//         $roles        = get_editable_roles();
//         $url          = add_query_arg( array( 'action' => 'export_users' ), admin_url( 'admin-ajax.php' ) );

//         echo '<div class="alignright actions bulkactions" style="margin-left: 20px;">';
//         // Add the dropdown list of roles.
//         echo '<form method="POST" >';
//         echo '<select name="role" id="user_role" style="width: 180px;" onchange="this.form.submit()">';
//         echo '<option value="">All Roles</option>';
//         foreach ( $roles as $role_key => $role ) {
//             $selected = ( $current_role == $role_key ) ? 'selected' : '';
//             echo '<option value="' . $role_key . '" ' . $selected . '>' . $role['name'] . '</option>';
//         }
//         echo '</select>';
//         echo '</form>';

//         // Add the "Download CSV" button.
//         $url = add_query_arg( array( 'role' => $current_role ), $url );

//         echo '<a href="' . esc_url( $url ) . '" class="button" style="margin-left: 10px;">Download CSV</a>';

//         echo '</div>';
//     }
// // add_action( 'admin_init', 'handle_csv_export_request' );
// function handle_csv_export_request() {
//     if ( isset( $_GET['action']) && $_GET['action'] == 'export_users' ) {
//         $role = isset( $_GET['role'] ) ? $_GET['role'] : '';
//         export_user_data( $role );
//     }
// }
// function export_user_data( $role ) {
//     $user = wp_get_current_user();

//     // Set CSV filename.
//     $filename = strtolower( str_replace( ' ', '-', $role) ) . '-' . strtolower( str_replace( ' ', '-', $user->display_name) ) . '.csv';

//     // Set CSV header.
//     $header = array( 'Name', 'Email', 'Role' );

//     // Get user data.
//     $args = array( 'role' => $role );
//     $users = new WP_User_Query( $args );

//     // Download CSV file.
//     header( 'Content-Type: application/csv' );
//     header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

//     // Open CSV file for writing.
//     $handle = fopen( 'php://output', 'w' );

//     // Write header to CSV file.
//     fputcsv( $handle, $header );

//     // Loop through users and write data to CSV file.
//     foreach ( $users->get_results() as $user ) {
//         $data = array(
//             $user->display_name,
//             $user->user_email,
//             implode( ', ', $user->roles )
//         );
//         fputcsv( $handle, $data );
//     }

//     // Close CSV file.
//     fclose( $handle );
//     exit;
// }

?>