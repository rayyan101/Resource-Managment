 <?php
 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('RM_CustomFields')) {

    class RM_CustomFields
     {
        /**
        * @var  string  $prefix  The prefix for storing custom fields in the postmeta table
        */
        var $prefix = '_mcf_';
        /**
        * @var  array  $postTypes  An array of public custom post types, plus the standard "post" and "page" - add the custom types you want to include here
        */
        var $postTypes = array( "resource" );
        /**
        * @var  array  $customFields  Defines the custom fields available
        */
        var $customFields = array(
        array(
            "name"          => "ticket_title_custom",
            "title"         => "Ticket Title",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "edit_pages"
        ),
        array(
            "name"          => "user_name_custom",
            "title"         => "User Name",
            "description"   => "",
            "type"          =>   "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "edit_posts"
        ),
        array(
            "name"          => "address_custom",
            "title"         => "Address",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "service_custom",
            "title"         => "Service Name",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"														
        ),
        array(
            "name"          => "state_custom",
            "title"         => "State",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
       array(
            "name"          => "contact_custom",
            "title"         => "Contact",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "zipcode_custom",
            "title"         => "Zipcode",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "date_custom",
            "title"         => "Date",
            "description"   => "",
            "type"          => "text",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "desc_custom",
            "title"         => "Description",
            "description"   => "",
            "type"          => "textarea",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "tech_comments_custom",
            "title"         => "Technician Comments",
            "description"   => "",
            "type"          => "textarea",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),
        array(
            "name"          => "tech_custom",
            "title"         => "Assign Technician",
            "description"   => "",
            "type"          => "select",
            "scope"         =>   array( "resource" ),
            "capability"    => "manage_options"
        ),

        );
        /**
        * PHP 4 Compatible Constructor
        */
        function MyCustomFields() { $this->__construct(); }
        /**
        * PHP 5 Constructor
        */
        function __construct() {
            add_action( 'admin_menu', array( $this, 'createCustomFields' ) );
            add_action( 'save_post', array( $this, 'saveCustomFields' ), 1, 2 );
            // Comment this line out if you want to keep default custom fields meta box
            add_action( 'do_meta_boxes', array( $this, 'removeDefaultCustomFields' ), 10, 3 );
        }
        /**
        * Remove the default Custom Fields meta box
        */
        function removeDefaultCustomFields( $type, $context, $post ) {
            foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
                foreach ( $this->postTypes as $postType ) {
                    remove_meta_box( 'postcustom', $postType, $context );
                }
            }
        }
        /**
        * Create the new Custom Fields meta box
        */
        function createCustomFields() {
            if ( function_exists( 'add_meta_box' ) ) {
                foreach ( $this->postTypes as $postType ) {
                    add_meta_box( 'my-custom-fields', 'Custom Fields', array( $this, 'displayCustomFields' ), $postType, 'normal', 'high' );
                }
            }
        }
        /**
        * Display the new Custom Fields meta box
        */
        function displayCustomFields() {
            global $post;
            ?>
            <div class="form-wrap">
                <?php
                wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );
                foreach ( $this->customFields as $customField ) {
                    // Check scope
                    $scope = $customField[ 'scope' ];
                    $output = false;
                    foreach ( $scope as $scopeItem ) {
                        switch ( $scopeItem ) {
                            default: {
                                if ( $post->post_type == $scopeItem )
                                    $output = true;
                                break;
                            }
                        }
                        if ( $output ) break;
                    }
                    // Check capability
                    if ( !current_user_can( $customField['capability'], $post->ID ) )
                        $output = false;
                    // Output if allowed
                    if ( $output ) { ?>
                        <div class="form-field form-required">
                            <?php
                            switch ( $customField[ 'type' ] ) {
                                case "checkbox": {
                                    // Checkbox
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label>&amp;nbsp;&amp;nbsp;';
                                    echo '<input type="checkbox" name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="yes"';
                                    if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )
                                        echo ' checked="checked"';
                                    echo '" style="width: auto;" />';
                                    break;
                                }
                                case "textarea":
                                case "wysiwyg": {
                                    // Text area
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
                                    echo '<textarea readonly name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '</textarea>';
                                    // WYSIWYG
                                    if ( $customField[ 'type' ] == "wysiwyg" ) { ?>
                                        <script type="text/javascript">
                                            jQuery( document ).ready( function() {
                                                jQuery( "<?php echo $this->prefix . $customField[ 'name' ]; ?>" ).addClass( "mceEditor" );
                                                if ( typeof( tinyMCE ) == "object" &amp;&amp; typeof( tinyMCE.execCommand ) == "function" ) {
                                                    tinyMCE.execCommand( "mceAddControl", false, "<?php echo $this->prefix . $customField[ 'name' ]; ?>" );
                                                }
                                            });
                                        </script>
                                    <?php }
                                    break;
                                }
                                case "select": {
                                    // Checkbox
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label><br>';
                                    echo '<select name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="yes"';
                                    if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )
                                        echo ' checked="checked"';
                                    echo '" style="width: auto;">';
                                    ?>
                                    <option value="">:: Select Technician ::</option>
									        <?php
									        $args = array(
									            'role'    => 'ticket-system-tech',
									        );
									        $users = get_users( $args );
									        foreach ($users as $user) {
								            ?>
								            <option value="<?php echo $user->ID; ?>" <?= ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) ==  $user->ID)? 'selected': 'not'; ?> ><?php echo $user->display_name; ?> </option>
								            <?php
								        }
								        echo "</select>";
        						
                                    break;
                                }
                                default: {
                                    // Plain text field
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
                                    echo '<input readonly type="text" name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';
                                    break;
                                }
                            }
                            ?>
                            <?php if ( $customField[ 'description' ] ) echo '<p>' . $customField[ 'description' ] . '</p>'; ?>
                        </div>
                    <?php
                    }
                } ?>
            </div>
            <?php
        }
        /**
        * Save the new Custom Fields values
        */
        function saveCustomFields( $post_id, $post ) {
            if ( !isset( $_POST[ 'my-custom-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) )
                return;
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
            if ( ! in_array( $post->post_type, $this->postTypes ) )
                return;
            foreach ( $this->customFields as $customField ) {
                if ( current_user_can( $customField['capability'], $post_id ) ) {
                    if ( isset( $_POST[ $this->prefix . $customField['name'] ] ) && trim( $_POST[ $this->prefix . $customField['name'] ] ) ) {
                        $value = $_POST[ $this->prefix . $customField['name'] ];
                        // Auto-paragraphs for any WYSIWYG
                        if ( $customField['type'] == "wysiwyg" ) $value = wpautop( $value );
                        update_post_meta( $post_id, $this->prefix . $customField[ 'name' ], $value );
                    } else {
                        delete_post_meta( $post_id, $this->prefix . $customField[ 'name' ] );
                    }
                }
            }
        }
 
    } // End Class
}
 
 
// Instantiate the class
$RM_CustomFields = new RM_CustomFields();

	