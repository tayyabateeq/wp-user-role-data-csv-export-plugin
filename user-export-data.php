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

        add_action( 'restrict_manage_users', array ( $this, 'add_csv_export_button' ) );
        add_action( 'admin_init', array ( $this, 'handle_csv_export_request' ) );
        
    }
        
    /**
     * add_csv_export_button
     * 
     * Add ad dropdown list of roles and download csv button.
     * 
     * Script to prevent the page from reloading.
     *
     * @return void
     */
    function add_csv_export_button() {
        
        // Get current role.
        $current_role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

        // Get all roles.
        $roles = get_editable_roles();
        $url = add_query_arg( array( 'action' => 'export_users' ), admin_url( 'admin-ajax.php' ) );
    
        echo '<div class="alignright actions bulkactions" style="margin-left: 20px;">';
    
        // Add the dropdown list of roles.
        echo '<form method="POST" >';
        echo '<select name="role" id="user_role" style="width: 180px;">';
        echo '<option value="">All Roles</option>';
        foreach ( $roles as $role_key => $role ) {
            $selected = ( $current_role == $role_key ) ? 'selected' : '';
            echo '<option value="' . $role_key . '" ' . $selected . '>' . $role['name'] . '</option>';
        }
        echo '</select>';
        echo '</form>';
    
        // Add the "Download CSV" button.
        echo '<a href="#" class="button" id="download_csv_button" style="margin-left: 10px;">Download CSV</a>';
    
        echo '</div>';
    
        // Add a jQuery script to handle the download button click and prevent the page from reloading
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#download_csv_button').on('click', function(e) {
                    e.preventDefault();
                    var selected_role = $('#user_role').val();
                    var url = '<?php echo esc_url( $url ); ?>&role=' + selected_role;
                    window.location.href = url;
                });
            });
        </script>
        <?php
    }

    /**
     * handle_csv_export_request
     * 
     * Download csv file with filename and user data.
     * 
     * Export data of selected role users or export all users in csv file.
     * 
     * @return void
     */
    function handle_csv_export_request() {

        if ( isset( $_GET['action']) && $_GET['action'] == 'export_users' ) {

            $role = isset( $_GET['role'] ) ? $_GET['role'] : '';

            // Get current user.
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
        
            // Close CSV file.
            fclose( $handle );
            exit;
        }
    }
}
new Export_data();

?>