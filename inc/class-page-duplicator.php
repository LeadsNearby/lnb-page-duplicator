<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LeadsNearby_Page_Duplicator {

    public $post = null;
    public $post_id = null;
    public $new_post_id = null;
    public $current_user;
    public $success = false;

    function __construct( $post_id ) {

        $this->post = get_post( $post_id );
        $this->post_id = $post_id;
        $this->current_user = wp_get_current_user();

        $this->duplicate_post();

    }


    function duplicate_post() {

        $this->duplicate_post_data();
        $this->duplicate_post_meta();
        $this->duplicate_post_terms();

        if( $this->success ) {

            wp_redirect( admin_url( 'post.php?action=edit&post=' . $this->new_post_id ) );

            exit;

        } else {

            static::throw_error( 'Post creation failed, could not find original post: ' . $this->post_id );

        }

    }

    function duplicate_post_data() {

        if ( $this->post ) {

            $args = array(
                'comment_status' => $this->post->comment_status,
                'ping_status'    => $this->post->ping_status,
                'post_author'    => $this->current_user->ID,
                'post_content'   => $this->post->post_content,
                'post_excerpt'   => $this->post->post_excerpt,
                'post_name'      => $this->post->post_name,
                'post_parent'    => $this->post->post_parent,
                'post_password'  => $this->post->post_password,
                'post_status'    => 'draft',
                'post_title'     => $this->post->post_title,
                'post_type'      => $this->post->post_type,
                'to_ping'        => $this->post->to_ping,
                'menu_order'     => $this->post->menu_order
            );

            $this->new_post_id = wp_insert_post( $args );

            if( $this->new_post_id ) {

                $this->success = true;

            }

        }


    }

    function duplicate_post_meta() {

        if( $this->success ) {

            $post_meta_array = get_post_meta( $this->post_id );

            foreach ( $post_meta_array as $key => $value ) {

                $old_meta = get_post_meta( $this->post_id, $key, true );

                if( $old_meta ) {

                    update_post_meta( $this->new_post_id, $key, $old_meta );

                }

            }

        } else {

            $this->success = false;

        }

    }

    function duplicate_post_terms() {

        if( $this->success ) {

            //get all current post terms ad set them to the new post draft
            $taxonomies = get_object_taxonomies( $this->post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            
            foreach( $taxonomies as $taxonomy ) {
            
                $post_terms = wp_get_object_terms( $this->post_id, $taxonomy, array( 'fields' => 'slugs' ) );
            
                wp_set_object_terms( $this->new_post_id, $post_terms, $taxonomy, false );

            }

        } else {

            $this->success = false;

        }

    }

    public static function throw_error( $text ) {

        ob_start(); ?>

        <p><?php echo $text; ?></p>
        <p><a href="<?php echo admin_url( '/edit.php?post_type=page' ) ?>">Return &gt;</a></p>

        <?php $message = ob_get_clean();

        ob_clean();

        wp_die( $message );

    }

}   
     
?>