<?php

class CS_SportsdeskSync {

	private $slack_username = "Sportsdesk Sync";
	public $slack_room = "website-project";
	private $slack_hook = 'https://hooks.slack.com/services/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';

	private $debug = false;

	public function __construct() {
		$this->startSync();
	}

	private function checks() {
		if (get_transient( 'senior_sports_sync' )) {
			$this->log("Sync Dead (Awaiting Data)", true);
		}

		if (get_transient('senior_sports_sync_running')) {
			$this->log("Sync Dead (Sync already active)", true);
		} else {
			set_transient( 'senior_sports_sync_running', time(), WEEK_IN_SECONDS );
		}
	}

	public function startSync() {
		global $wpdb;

		$this->checks();


		$this->slack("Checks Successful. The Year is: ".get_option('cran_year').", The Term is: ".get_option('cran_term'));


		// Get the rows from the database that iSAMS has sync'd to.
		$rows = $wpdb->get_results("SELECT * from senior_sports_fixtures where academic_year = ".get_option('cran_year')." AND term = ".get_option('cran_term')." ORDER BY STR_TO_DATE(fixture_datetime, '%M %d %Y %l:%i%p');");
		$this->slack("About to iterate through ".count($rows)." results");
		//Iterate the fixtures in the fixture table

		foreach($rows as $fixture){

			//Only select the id to reduce load and because meta quering won't work properly like this anyway...
			$querystr = "
				SELECT $wpdb->posts.id
				FROM $wpdb->posts, $wpdb->postmeta
				WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
				AND $wpdb->postmeta.meta_key = 'fixture_id'
				AND $wpdb->posts.post_type = 'match'
				AND $wpdb->postmeta.meta_value = $fixture->fixture_id
			";

			$fixture_res = $wpdb->get_results($querystr, OBJECT);
			$existing_fixture = $fixture_res[0];
			$this->debug("======================================" );
			$this->debug("Merlin ID of fixture: ".$fixture->fixture_id);
			$this->debug("====================================== ");

			#if ($existing_fixture->id){
			#} else {
			//Create the post content

			if(get_option('cran_mode') == "enabled"){
				$content = $fixture->result_comment . "<br /><br />" . get_team_list($fixture->fixture_id);
			} else {
				$content = $fixture->result_comment;
			}
			if ($content == null) {
				$content = "To Play";
			}
			//otherwise do an insert
			//Create an array of custom taxonomies (categories) for us in the insert
			$fixture_timestamp = strtotime($fixture->fixture_datetime);

			//If in the future set as future
			if($fixture->status == "Cancelled"){
				//if($fixture_timestamp > time()){
				$status = 'pending';
				$this->debug("Fixture is Cancelled");
				//}elseif($fixture->status == "Cancelled"){
			} elseif($fixture_timestamp > time()){
				$this->debug("Fixture is in the future");
				$status = 'future';
			} else {
				$status = 'publish';
				$this->debug( "Fixture in the past" );
			}

			$fixture_arr = array(
				'post_type' => 'match',
				'post_status' => $status,
				'post_date' => date('Y-m-d H:i:s',$fixture_timestamp),
				'post_date_gmt' => get_gmt_from_date(date('Y-m-d H:i:s',$fixture_timestamp)),
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_title' => $fixture->team." ".$fixture->sport." vs ".$fixture->opponent." Week ".$fixture->week." ".convert_term_to_words($fixture->term,$fixture->academic_year)." ".$fixture->fixture_datetime,
				'post_content' => $content
				//'tax_input' => array(
				//        'team' => $fixture->sport."-".$fixture->team,
				// 'opponent' => $fixture->opponent
				//)
			);

			if($existing_fixture->id){
				$this->debug( "This Fixture already exists, we want to update" );
				//add this fixture id to the array so we can do one wp_insert_post;
				$fixture_arr['ID'] = $existing_fixture->id;
				//   $post_id = $existing_fixture->id;
				$post_id = wp_update_post($fixture_arr, true);
			} else {
				$this->debug("This fixture dosn't exist, create a new one");
				$this->debug(print_r($fixture_arr, true));
				//$post_id = wp_insert_post( $fixture_arr );
				$post_id = wp_insert_post( $fixture_arr, true );
			}
			if (is_wp_error($post_id)) {
				$this->log("WP Error: ".$post_id->get_error_message());
				exit();
			}

			//if it's an update the postid will have been added in the if above
			$this->debug( "Post id:".$post_id );
			update_post_meta($post_id,'fixture_id',$fixture->fixture_id);
			update_post_meta($post_id,'result',$fixture->result);
			update_post_meta($post_id,'score',$fixture->score);
			// We expect the string to be in the format 2-4 where the first score is Cranleigh regardless of home/away status
			$score_breakdown = explode("-",$fixture->score);
			//print_r($score_breakdown);
			update_post_meta($post_id,'cran_score',$score_breakdown[0]);
			update_post_meta($post_id,'opp_score',$score_breakdown[1]);
			update_post_meta($post_id,'week',$fixture->week);
			update_post_meta($post_id,'academic_year',$fixture->academic_year);
			update_post_meta($post_id,'term',$fixture->term);
			update_post_meta($post_id,'fixture_datetime',$fixture->fixture_datetime);
			//Now set a load of taxonomies so stuff is in the right category
			//Need to supply the slug which we can figure out by replacing spaces with -'s
			wp_set_object_terms($post_id,str_replace(" ","-",$fixture->opponent),'opponent');
			//Storing a unique week in the taxonomies as well to allow for easier filtering in the front end.
			wp_set_object_terms($post_id,"Week ".$fixture->week." ".convert_term_to_words($fixture->term,$fixture->academic_year),'week');
			//Team slug
			// Team slug format is team-name-sport e.g. u14-boys-swimming
			// Added a trim to remove trailing spaces
			$team_slug = str_replace(" ","-",trim($fixture->team))."-".$fixture->sport;
			wp_set_object_terms($post_id,$team_slug,'team');
			//Venue
			wp_set_object_terms($post_id,str_replace(" ","-",$fixture->venue),'venue');

		}
		$this->debug("Sportsdesk Sync Complete. ".count($rows)." traversed</p>");
		set_transient( 'senior_sports_sync', time(), 7 * MINUTE_IN_SECONDS );
		delete_transient( 'senior_sports_sync_running' );


		$this->log("Sync Complete - ".date("H:i:s"));



	}
	private function log($message, $die=false) {
		error_log("Sportsdesk Sync: ".$message);
		$this->slack($message);
		if ($die===true):
			$this->slack("Tried to die, but we don't do that anymore!");
		endif;
	}

	private function debug($message) {
		if ($this->debug === true) {
			return $this->log($debug);
		}
	}

	// (string) $message - message to be passed to Slack
	// (string) $room - room in which to write the message, too
	// (string) $icon - You can set up custom emoji icons to use with each message
	public function slack($message, $room = null, $icon = ":slack:") {
        if ($room===null) {
	        $room = $this->slack_room;
        }

        $data = "payload=" . json_encode(array(
                	"channel"       =>  "#{$room}",
					"text"          =>  $message,
					"icon_emoji"    =>  $icon,
					"username"		=>	$this->slack_username
				));

		// You can get your webhook endpoint from your Slack settings
        $ch = curl_init($this->slack_hook);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);


        return $result;
	}

}
