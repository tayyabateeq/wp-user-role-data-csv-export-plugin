## Table of Contents ##
1. [Export Users Data](#export-users-data)
2. [File Structure](#file-structure)
3. [Task Workflow](#task-workflow)
4. [Code Logics](#code-logics)

# Export Users Data #

Build a WordPress Plugin to export the Type of users in a .CSV File.
Export the email address. 
Names for these Users.
Go to /wp-admin/users.php and Add a button on each role of users, Clicking on a button should download role-{name}.csv file on each role type.

## File Structure ##

+ **index.php** call the required files.
+ **user-export-data.php** defines a class of export data.
+ **testing.php** includes testing codes for best practices.

## Task Workflow ##

+ *add_csv_export_button()* method displays a dropdown list of user roles and a download csv button at with bulk actions.
+ *handle_csv_export_request()* handles the download csv request, it gets current user first, then set a filename, and then open a csv file, write file header, write users data and close the file.
+ User can select a role from dropdown list and then download the csv, the csv contains the data of only selected role.
+ If user did not select any role, csv files contains the data of all users.

## Code Logics

### **add_csv_export_button()** ###

+ Add dropdown list of roles.
```
echo '<option value="">All Roles</option>';
    foreach ( $roles as $role_key => $role ) {
        $selected = ( $current_role == $role_key ) ? 'selected' : '';
        echo '<option value="' . $role_key . '" ' . $selected . '>' . $role['name'] . '</option>';
    }
```
+ Add a jQuery script to handle the download button click and prevent the page from reloading
```
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
```
### **handle_csv_export_request()** ###

+ Set CSV filename.
```
 $filename = strtolower( str_replace( ' ', '-', $role) ) . '-' . strtolower( str_replace( ' ', '-', $user->display_name) ) . '.csv';
```
+ Download CSV file.
```
header( 'Content-Type: application/csv' );
header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
```
+ Open CSV file for writing.
```
    $handle = fopen( 'php://output', 'w' );
```
+ Loop through users and write data to CSV file.
```
foreach ( $users as $user ) {
    $data = array(
        $user->display_name,
        $user->user_email,
        implode( ', ', $user->roles )
        );
    fputcsv( $handle, $data );
}
```