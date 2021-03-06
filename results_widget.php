<?php
/*
**
 * Sports desk results widget
 */
class SportsDesk_Results_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'sportsdesk_results_widget', // Base ID
			'Sports Desk Team Results', // Name
			#array( 'description' => __( 'Displays latest results from sportsdesk', 'text_domain' ), ) // Args
			array( 'description' => "Displays the team dropdown widget" )
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
		
		//Team Drop Down
                $select = wp_dropdown_categories("taxonomy=team&orderby=name&order=asc&hierarchical=1&hide_empty=0&echo=0&id=teamcat&name=teamcat&class=form-control");

                $select = preg_replace_callback("#<option[^>]*>[^<]*</option>#", array($this,"replace_catid_for_slug"), $select);

                echo $select;
	
	?>

	<script type="text/javascript"><!--
    		var dropdown_team = document.getElementById("teamcat");
  		function onTeamCatChange() {
                	if ( dropdown_team.options[dropdown_team.selectedIndex].value != -1 ) {
                        	location.href = "<?php echo get_option('home');?>/team/"+dropdown_team.options[dropdown_team.selectedIndex].value+"/";
                	}
    		}
    		dropdown_team.onchange = onTeamCatChange;
	--></script>		

	<?php
		
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
		//$instance['teamname'] = strip_tags( $new_instance['teamname'] );
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
			$title = 'Team Results';
		}
		//if ( isset( $instance[ 'teamname' ] ) ) {
	//		$teamname = $instance[ 'teamname' ];
	//	}
	//	else {
	//		$teamname = 'All';
	//	}
	//	if ( isset( $instance[ 'number' ] ) ) {
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


		<?php 
	}

	//function to fix the taxonomy drop down generated by wp_dropdown_categories
	// From http://wordpress.org/support/topic/i-need-to-get-the-category-slug-from-the-category-id
	function replace_catid_for_slug($option){
		$categories = get_categories("taxonomy=team&hide_empty=0");
		preg_match('/value="(\d*)"/', $option[0], $matches);

		$id = $matches[1];

		$slug = "";

		foreach($categories as $category){
			if($category->cat_ID == $id){
				$slug = $category->slug;
			}
		}

		return preg_replace("/value=\"(\d*)\"/", "value=\"$slug\"", $option[0]);
	}	
	
} // class Foo_Widget
?>
