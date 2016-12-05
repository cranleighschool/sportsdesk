<?php
/*
**
 * Sports desk upcoming Fixtures widget
 */
class SportsDesk_Fixtures_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'sportsdesk_fixtures_widget', // Base ID
			'Sports Desk Upcoming Fixtures', // Name
			#array( 'description' => __( 'Displays latest results from sportsdesk', 'text_domain' ), ) // Args
			array( 'description' => "Displays upcoming fixtures from sportsdesk" )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		/*if($instance['teamname'] == "All"){
		                $query_arr = array (
				    "taxonomy" => "team",
                                    "post_type" => "match",
				    "posts_per_page" => $instance['number'],
				    "post_status" => 'future'
                                );
		} else {*/
		$query_arr = array (//"taxonomy" => "team",
				    "team" => $instance['teamname'],
				    "week" => week_tax_slug(),
				    "post_type" => "match",
				    "posts_per_page" => "40",
				    "post_status" => "any",
				    "orderby" => "post_date", 
				    //"orderby" => "post_title", 
				    "order" => "asc"
				);
		/*}*/
		$sd_fix_query = new WP_Query($query_arr);
		if($sd_fix_query->have_posts()){
			print "<ul>";
			while ( $sd_fix_query->have_posts() ) : $sd_fix_query->the_post();
			    $opponent = array_shift(get_the_terms(get_the_ID(),'opponent'));
		            $team = array_shift(get_the_terms(get_the_ID(),'team'));
			    $datetime = strtotime(get_post_meta(get_the_ID(),'fixture_datetime',true));
			    print "<li><a href='".get_permalink()."'>";
			    //print date('d/m/y G:i',$datetime). " ". 
			    print $team->name . " Vs. " . $opponent->name;
			    print "</a></li>";
			endwhile;
			print "</ul>";
		} else {
			print "No Fixtures this week";
		}
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['teamname'] = strip_tags( $new_instance['teamname'] );
		//$instance['number'] = strip_tags( $new_instance['number'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = 'Fixtures This Week';
		}
		if ( isset( $instance[ 'teamname' ] ) ) {
			$teamname = $instance[ 'teamname' ];
		}
		else {
			$teamname = 'All';
		}
		//if ( isset( $instance[ 'number' ] ) ) {
	//		$number = $instance[ 'number' ];
	//	}
	//	else {
	//		$number = '5';
	//	}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php print "Widget Title"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<!--<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php print "Max. Number of Fixtures to Display"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" />
		</p>-->
		<p>
		<select id="<?php echo $this->get_field_id( 'teamname' );?>" name="<?php echo $this->get_field_name( 'teamname' );?>">
		<option value="All">All</option>
		<?php 
		//$teams = get_terms('team');
		$teams = get_terms( 'team', array( 'parent' => 0, 'hide_empty' => 0 ) );
		foreach($teams as $team){
			if($teamname == $team->slug){
				print "<option selected='selected' value='".$team->slug."'>".$team->name."</option>";
			} else {
				print "<option value='".$team->slug."'>".$team->name."</option>";
			}
		}	
		?>
		</select>
		</p>



		<?php 
	}

} // class Foo_Widget
?>
