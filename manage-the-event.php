<?php
/*
Plugin Name: Manage The Event
Plugin URI: https://example.com/custom-database-plugin
Description: A plugin for event manageent calendar using OOP approach.
Author: Your Muhammad Aniab
version: 2.0
Author URI: http://muhammadaniab.com/
*/

class me_manage_the_event{

    public function __construct() {
        add_action('init',[$this,'init']);
        add_action('init',[$this,'register_custom_post']);

        add_action('add_meta_boxes', [$this,'create_meta_box']);
        add_action('save_post', [$this,'save_event_date_meta']);

        add_action('', [$this,'']);
        register_activation_hook( __FILE__, array( $this,"activation") );

        add_filter('the_content', [$this,'add_event_content_change']);
    }

    public function init(){
        add_filter("manage_posts_columns", array( $this,"add_columns") );
        add_action("manage_posts_custom_column", array( $this,"customize_column"),10, 2);

        add_action('admin_menu',[$this,'create_admin_event']);   
        
        add_action('admin_enqueue_scripts', [$this,'connect_style_file']);
    }
    function activation(){
		flush_rewrite_rules();
	}

    public function register_custom_post(){
        /**
		 * Post Type: Event.
		 */
	
		$labels = [
			"name" => esc_html__( "Events" ),
			"singular_name" => esc_html__( "Event" ),
			"add_new" => esc_html__( "Add new Event" ),
		];
	
		$args = [
			"label" => esc_html__( "Events" ),
			"labels" => $labels,
			"public" => true,
			"show_ui" => true,
			"has_archive" => true,
			"supports" => [ "title", "editor", "thumbnail" ],
		];
		register_post_type( "events", $args );
    }

    public function create_meta_box() {
        add_meta_box(
            "date_id",
            "Event Date",
            [$this,'display_the_date_meta'],
            'events',
        );
    }

    public function display_the_date_meta($post) {
        // Retrieve the existing event date meta value
        $event_date = get_post_meta($post->ID, 'event_date', true);
        ?>
		<label for="event_date">The Event Date</label>
		<input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($event_date); ?>" >
		<?php
    }

    public function save_event_date_meta($post_id) {
        // Check if the event date is set in the $_POST data
        if (isset($_POST['event_date'])) {
            // Sanitize and save the event date meta
            $event_date = sanitize_text_field($_POST['event_date']);
            update_post_meta($post_id, 'event_date', $event_date);
        }
    }

    function add_columns( $columns ) {

		//$columns['Thumbnail'] = 'Thumbnail'; //How to add any key with value in an array
        $current_screen=get_current_screen();
        if( $current_screen->id == 'edit-events') {
            $new_columns=[];
		    foreach( $columns as $column_name => $column_data ) {
			if($column_name=='title'){
				$new_columns[$column_name]=$column_data; // Columns autometically added.
				$new_columns["Event_Date"]="Event_Date";
			}
			else {
				$new_columns[$column_name]=$column_data;
			}
			
		}
		return $new_columns ;
        }
        else {
            return $columns;
        }
		
	}

    function customize_column( $column, $post_id ) {
		if($column=="Event_Date"){
            /* $current_screen=get_current_screen();
            echo $current_screen->id; */
            echo get_post_meta($post_id, 'event_date', true);
			//echo "Data";
		}

	}

    public function create_admin_event(){

        add_submenu_page(
			'edit.php?post_type=events',
			'Event Calendar',
			'Event Calendar',
			'manage_options',
			'event-calendar',
			[$this,'display_function']
		);

	}

    public function display_function(){
        
        include_once(plugin_dir_path(__FILE__).'pages/calendar.php');

	}

    function add_event_content_change($content) {

        if(is_singular('events')) {

            $featured_image =  get_the_post_thumbnail();
            $event_date = get_post_meta(get_the_ID(), 'event_date', true);

            $full_content='<div class="event-thumbnail">' .$featured_image. '</div>';
            $full_content.= '<div class="event-date"><h3>'.$event_date.'</h3></div>';
            $full_content.= $content;
            return $full_content;

        }
        else {
            return $content;
        }
    }

    function connect_style_file() {
		$loc = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'css_handle', $loc . 'css/style.css', array(), '1.0 ' );
	}

}

new me_manage_the_event();