<?php
/*
Plugin Name: Cranleigh School Sports Desk
Description: A Wordpress plugin which holds the data behind sports matches for a variety of teams, needs a post, taxonomy, get templates to display correctly
Author: Jona Young (jy@cranleigh.org)
Version: 1.0
Author URI: http://www.cranleigh.org
*/

class cran_SportsDesk {

    /* Constructor for the class called on init */
    function cran_SportsDesk(){
                register_post_type('match', array(
                        //'label' => __('Matches'),
                        //'singular_label' => __('Match'),
                        'labels' => array('name' => "Matches",'menu_name' => 'Sports Desk','singular_name' => 'Match',"all_items" => 'Match List','add_new' => 'Add Match','add_new_item' => 'New Match'),
                        'public' => true,
                        'show_ui' => true, // UI in admin panel
                        '_builtin' => false, // It's a custom post type, not built in
                        '_edit_link' => 'post.php?post=%d',
                        'capability_type' => 'post',
                        'hierarchical' => false,
                        'rewrite' => array("slug" => "match","with_front" => false), // Permalinks
                        'query_var' => "match", // This goes to the WP_Query schema
                        'supports' => array('title','author', 'excerpt', 'editor' ,/*'custom-fields'*/),
			'menu_icon' => plugins_url( 'img/16-Stop-Watch.png' , __FILE__ ),
			'has_archive' => true,	//Create an archive to display historic data
		));
               //register the supporting taxonomies
		register_taxonomy("team", array("match"), array("hierarchical" => true, "label" => "Teams", "singular_label" => "team", "rewrite" =>array("slug" => "team","with_front" => false), "show_in_nav_menu" => true));
		register_taxonomy("opponent", array("match"), array("hierarchical" => true, "label" => "Opponents", "singular_label" => "Opponent", "rewrite" => array("slug" => "opponent","with_front" => false), "show_in_nav_menu" => false));
		register_taxonomy("venue", array("match"), array("hierarchical" => true, "label" => "Venue", "singular_label" => "Venue", "rewrite" => array("slug" => "venue","with_front" => false),"show_in_nav_menu" => false));
		register_taxonomy("week", array("match"), array("hierarchical" => true, "label" => "Weeks", "singular_label" => "Week", "rewrite" => array("slug" => "week","with_front" => false),"show_in_nav_menu" => false));
		flush_rewrite_rules();
		// Add the hooks for the admin display
                //This function adds the columns to the admin panel
                add_filter("manage_edit-match_columns", array(&$this, "edit_columns"));
                //This function populates it with data
	        add_action("manage_posts_custom_column", array(&$this, "custom_columns"));
		//add the pluging settings, etc
 		add_action('admin_menu',array(&$this,'create_settings_menu'));
		#add_filter("manage_edit-match_sortable_columns", array(&$this,'wr_event_sortable_columns')); 
                //We want to be able to add styles to the admin so we can mark a canceled match as such.  
		add_action('admin_footer',array(&$this,'style_posts_list'));
		//Add shortcode for sportsdesk
		add_shortcode( 'sportsdesk', array(&$this, 'sd_shortcode') );
		add_shortcode( 'awayfixturelocations', array(&$this, 'awayfixloc_shortcode') );
		add_filter('the_posts',array(&$this,'show_all_future_posts') );	
		add_filter('the_content',array(&$this,'awayfixture_content_filter'));
	}
	function sd_shortcode($atts){
		//extract( shortcode_atts( array('context' => 'current'), $atts ) );
		//include("display_sportsdesk.php");
		//include('sportsdesk_header.php');
		//query_posts('week=week-10-michaelmas-2012');
		//Check post for an edit	
		if(!$_POST['post_content']){
			get_template_part('taxonomy','week');
			wp_reset_query();
		}
	}
	function awayfixloc_shortcode($atts){
		include("display_awayfixturelocations.php");
	}
	function show_all_future_posts($posts){
	  	  global $wp_query, $wpdb;
                  #print_r($wp_query->query['post_type']);

   		    if(is_single() && $wp_query->post_count == 0 && $wp_query->query['post_type'] == "match")
		   {
      			$posts = $wpdb->get_results($wp_query->request);
   		   }

   		   return $posts;
	}
        function style_posts_list(){
	    print "<style>";
            print ".type-match.status-pending{background-color: #FFEBE8 !important; }";
            print "</style>";
	}

	function wr_event_sortable_columns( $columns ) {
    #$columns['matchdate'] = 'matchdate';
         $columns = array(
                  "matchdate" => "Match Date",
                  "sport" => "Sport",
                  "team" => "Team",
                  "opponent" => "Opponent",
                  "venue" => "Venue",
                  "result" => "Result",
                  "score" => "Score",
                  "status" => "Status",
                  "merlinid" => "Fixture ID"
         );
         return $columns;
}
/*function match_column_orderby( $vars ) {
        if($vars['orderby'] = 'merlinid'){
           #print "merlin";
           #print_r($vars);
           $vars = array_merge( $vars,
            array(
                'meta_key'  => 'fixture_id',
                'orderby'   => 'meta_value_num'
                #'order'     => 'asc'
            )
        );
}
    return $vars;

}*/



    /* This is where we define the columns that will be shown for this custom post type */
    function edit_columns($columns){
         $columns = array(
		  "cb" => "<input type='checkbox' />",
                  "status" => "Status",
                  "matchdate" => "Match Date",
                  "week" => "Week",
                  "term" => "Term",
                  "sport" => "Sport",
                  "team" => "Team",
                  "opponent" => "Opponent",
                  "venue" => "Venue",
                  "result" => "Result",
                  "score" => "Score",
                  "merlinid" => "Fixture ID"
         );
         return $columns;
    }
    function custom_columns($column){
        global $post;
        //The value of $column matches up with the key's held in the edit_columns function above
        switch ($column){
	    case "matchdate":
                print '<a href="post.php?post='.$post->ID.'&action=edit">';
                print get_post_meta($post->ID,'fixture_datetime',true);
                print '</a>';
                break;
            case "status":
                if(get_post_status($post->ID)=="pending"){
                      print "<strong>Cancelled</strong>";
		}elseif(get_post_status($post->ID)=="publish"){
                      //Check future or past
                      #$timestamp = get_post_meta($post->ID,'fixture_datetime');
                      #$timestamp = strtotime($timestamp[0]);
                      #$time_now = time();
                      #print_r(get_post_meta($post->ID,'fixture_datetime'));
                      #print $timestamp;
                      #if($timestamp >= $time_now){
                      #    print "Going Ahead";
                      #}elseif($timestamp < $time_now){
                          print "Played";
                      #}
                }elseif(get_post_status($post->ID)=="future"){
			print "Scheduled";
		}
                break;
            case "week":
                print get_post_meta($post->ID,'week',true);
                break;
            case "term":
                print convert_term_to_words(get_post_meta($post->ID,'term',true),get_post_meta($post->ID,'academicyear',true));
		break;
 	    case "result":
                #echo 'Hello';
                #print_r($post);
                print get_post_meta($post->ID,'result',true);
                break;
 	    case "score":
                print get_post_meta($post->ID,'score',true);
                break;
            case "opponent":
                $opp = wp_get_post_terms($post->ID,"opponent");
                print $opp[0]->name;
                break;
            case "venue":
                $venue = wp_get_post_terms($post->ID,"venue");
                print $venue[0]->name;
                break;
            case "team":
                $team = wp_get_post_terms($post->ID,"team");
                //print_r($team);
                print $team[0]->name;
                break;
            case "sport":
                $team = wp_get_post_terms($post->ID,"team");
                //print_r($team[0]);
                //print_r($team);
                $sport = get_term($team[0]->parent,"team");
                print $sport->name;
                break;
            case "merlinid":
                print get_post_meta($post->ID,'fixture_id',true);
                break;
        }
    }
    
    function create_settings_menu(){
    		add_submenu_page('edit.php?post_type=match','Sports Desk Config','Config','manage_options','sports-desk-config',array(&$this,'settings_page'));
		//reg the setting
		add_action('admin_init',array(&$this,'admin_init'));

    }
    function admin_init(){
		//register some settings
    		register_setting( 'cran_sportsdesk_group', 'cran_term_start' );
    		register_setting( 'cran_sportsdesk_group', 'cran_term' );
    		register_setting( 'cran_sportsdesk_group', 'cran_year' );
    		register_setting( 'cran_sportsdesk_group', 'cran_awayfixturepage' );
    		register_setting( 'cran_sportsdesk_group', 'cran_mode' );
		register_setting( 'cran_sportsdesk_group', 'cran_blackboard_ip' );    		
                register_setting( 'cran_sportsdesk_group', 'cran_blackboard_folder' );

		//Format the metaboxes for custom post type of match
		remove_meta_box('postexcerpt','match','normal');	
    } 
    function settings_page(){
        include('settings.php');
    }
    function awayfixture_content_filter($content){
	//print_r($content);
	$id = get_the_ID();
	$ancestors = get_post_ancestors($id);
	$type = get_post_type($id);
	$parent = $ancestors[0];
	if(($parent == get_option('cran_awayfixturepage')) or ($type == 'match')){
		$new_content = preg_replace("/Phone:/",'<img width="16" height="16" style="padding-right: 2px;" alt="" src="'.plugins_url( 'img/165.png' , __FILE__ ).'">Phone: ',$content);
		$new_content = preg_replace("/Website:/",'<img width="16" height="16" style="padding-right: 2px;" alt="" src="'.plugins_url( 'img/226.png' , __FILE__ ).'">Website: ',$new_content);
		$new_content = preg_replace("/<p>Notes:/",'<img width="16" height="16" style="padding-right: 2px;" alt="" src="'.plugins_url( 'img/95.png' , __FILE__ ).'">Notes: ',$new_content);
		$new_content = preg_replace("/View Map/",'<img width="16" height="16" style="padding-right: 2px;" alt="" src="'.plugins_url( 'img/108.png' , __FILE__ ).'">View Map',$new_content);
		$new_content = preg_replace("/Address:/",'<img width="16" height="16" style="padding-right: 2px;" alt="" src="'.plugins_url( 'img/76.png' , __FILE__ ).'">Address:',$new_content);
	} else {
            //Oh yeah... we want all other pages to be unaffected.
	    $new_content = $content;
	}
	//if (2829 == $post->post_parent) {
	//	print "Away Fixture Page";	
	//}
	return $new_content;
    }

} // END OF CLASS

/* These functions moved out of the main class so we can acess them from the sync script and widgets
*/
include('functions.php');



/* Actions */
add_action("init", "cran_SportsDesk_Init");
//register the results widget
include "results_widget.php";
add_action( 'widgets_init', create_function( '', 'register_widget( "SportsDesk_Results_Widget" );' ) );
//register the fixtures widget
include "fixtures_widget.php";
add_action( 'widgets_init', create_function( '', 'register_widget( "SportsDesk_Fixtures_Widget" );' ) );
include "awaylocations_widet.php";
add_action( 'widgets_init', create_function( '', 'register_widget( "SportsDesk_Locations_Widget" );' ) );
include "general_widget.php";
add_action( 'widgets_init', create_function( '', 'register_widget( "SportsDesk_General_Widget" );' ) );
include "archive_widget.php";
add_action( 'widgets_init', create_function( '', 'register_widget( "SportsDesk_Archive_Widget" );' ) );

include "team_widget.php";
add_action('widgets_init', create_function('', 'register_widget( "SportsDesk_Team_Widget");'));

function cran_SportsDesk_Init() { global $sportsdesk; $sportsdesk = new cran_SportsDesk(); }

?>
