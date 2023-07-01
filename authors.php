<?php
// add_action( 'admin_init' , 'my_column_init' );
// function my_column_init() {
//     add_filter( 'manage_posts_columns' , 'my_manage_columns' );
// }
// function my_manage_columns( $columns ) {
//     unset($columns['author']);
//     return $columns;
// }
// add_action('init', 'remove_author_support');
// function remove_author_support() {
//     remove_post_type_support( 'post', 'author' );
// }
// add_action('after_setup_theme', 'remove_formats', 100);
// function remove_formats() {
//     remove_theme_support('post-formats');
// }

    //add metabox
    function add_multi_author_metabox() {

            add_meta_box(
            'multi_author_metabox',
            'Multi-Author',
            'display_multi_author_metabox',
            'post',
            'normal',
            'high'

        );
    }
    add_action( 'add_meta_boxes', 'add_multi_author_metabox' );
    
    function display_multi_author_metabox( $post ) {
        // Get the saved author IDs
        $authors = get_post_meta( $post->ID, '_multi_author', true );
        $author_ids = ! empty( $authors ) ? $authors : array();
        // Get all user IDs and display them in a dropdown list with multiple selection
        $users = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
        if ( ! empty( $users ) ){
            echo '<input type="text" id="author-search" placeholder="Search authors" autocomplete="off" style="margin-bottom:10px;">';
            echo '<select name="multi_author[]" multiple class="author-select" style= "width:160px;">';
            foreach ( $users as $user ){
                echo '<option value="' . esc_attr( $user->ID ) . '"' . selected( in_array( $user->ID, $author_ids ), true, false ) . '> ' . esc_html( $user->display_name ) . '</option>';
            }
            echo '</select>';
        }

        // Add JavaScript to filter the dropdown based on search input
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#author-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.author-select option').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(value) !== -1);
                });
            });
        });
        </script>
        <?php
    }
    
    function save_multi_author_metabox( $post_id ) {
        // Check if the user has permission to edit the post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        // Get the original author of the post
        $original_author = get_post_field( 'post_author', $post_id );
        // Get the selected author IDs from the post data
        $authors = isset( $_POST['multi_author'] ) ? array_map( 'intval', $_POST['multi_author'] ) : array();
        // If no author is selected, add the original author to the list
        if ( empty( $authors ) ) {
            $authors[] = $original_author;
        }
        // Save the selected author IDs to the post meta
        update_post_meta( $post_id, '_multi_author', $authors );
    }
    add_action( 'save_post', 'save_multi_author_metabox' );

    //Addition of Authors column after Title column
    function add_multi_author_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( 'title' == $key ) {
                $new_columns['multi_author'] = __( 'Authors', 'textdomain' );
            }
        }
        return $new_columns;
    }
    add_filter( 'manage_post_posts_columns', 'add_multi_author_column' );

    //Display multi authors in column.
    function display_multi_author_column( $column_name, $post_id ) {
        if ( $column_name == 'multi_author' ) {
            // Get the saved author IDs
            $authors = get_post_meta( $post_id, '_multi_author', true );
            $author_ids = ! empty( $authors ) ? $authors : array();
    
            // Display all selected authors
            if ( ! empty( $author_ids ) ) {
                $author_list = array();
                foreach ( $author_ids as $author_id ) {
                    $author_name = get_the_author_meta( 'display_name', $author_id );
                    $author_link = add_query_arg( 'author', $author_id, admin_url( 'edit.php' ) );
                    $author_list[] = '<a href="' . esc_url( $author_link ) . '">' . esc_html( $author_name ) . '</a>';
                }
                echo implode( ', ', $author_list );
            } else {
                // Display the default single author
                $author_id = get_post_field( 'post_author', $post_id );
                $author_name = get_the_author_meta( 'display_name', $author_id );
                $author_link = add_query_arg( 'author', $author_id, admin_url( 'edit.php' ) );
                echo '<a href="' . esc_url( $author_link ) . '">' . esc_html( $author_name ) . '</a>';
            }
        }
    }
    add_action( 'manage_posts_custom_column', 'display_multi_author_column', 10, 2 );
    
    //Filter for sorting related author posts.
    function filter_posts_by_author($query) {
        global $pagenow;
        if (is_admin() && $pagenow=='edit.php' && isset($_GET['author']) && !empty($_GET['author'])) {
            $query->query_vars['author'] = $_GET['author'];
        }
    }
    add_filter('parse_query', 'filter_posts_by_author');
    
    // Removing author support from post.
    function remove_author_support() {
        remove_post_type_support( 'post', 'author' );
    }
    add_action('init', 'remove_author_support');
    
    // Adding role in users of dashboard.
    add_role( 'author', 'Author', array( 'read' => true, 'level_1' => true ) );
    
    function custom_posts_column( $value, $column_name, $user_id ) {
        if ( 'posts' == $column_name ) {
            $args = array(
                'author' => $user_id,
                'post_status' => 'publish',
                'posts_per_page' => -1
            );
    
            $posts = get_posts( $args );
            $post_count = count( $posts );
    
            $multi_author_posts = get_posts( array(
                'meta_key' => '_multi_author',
                'meta_value' => $user_id,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ) );
            $multi_author_post_count = count( $multi_author_posts );
    
            $total_count = $post_count + $multi_author_post_count;
    
            $value = '<a href="edit.php?author=' . $user_id . '">' . $total_count . '</a>';
        }
        return $value;
    }
    add_filter( 'manage_users_custom_column', 'custom_posts_column', 10, 3 );
    
    // Update default author value custom meta multi authors.
    function update_default_author( $data, $postarr ) {
        if ( isset( $postarr['multi_author'] ) && ! empty( $postarr['multi_author'] ) ) {

            // Get the first author from the multi-author list
            $author_id = reset( $postarr['multi_author'] );
            // Update the default author value with the selected author
            $data['post_author'] = $author_id;
        }
        return $data;
    }
    add_filter( 'wp_insert_post_data', 'update_default_author', 99, 2 );
?>
