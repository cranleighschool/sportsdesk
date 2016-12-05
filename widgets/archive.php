<?php
/*
**
 * Sports desk upcoming Fixtures widget
 */
class SportsDesk_Archive_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'sportsdesk_archive_widget', // Base ID
			'Sports Desk Archive', // Name
			#array( 'description' => __( 'Displays latest results from sportsdesk', 'text_domain' ), ) // Args
			array( 'description' => "Widget to display links to the sports desk results archive" )
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
		//Contents here
		add_filter('get_terms_orderby', array($this,"get_terms_orderby_natural_slug"),10,2);
		$select = wp_dropdown_categories( 'taxonomy=week&hierarchical=1&orderby=name&id=archivecat&echo=0' );
		remove_filter('get_terms_orderby', array($this,"get_terms_orderby_natural_slug"));
		
                $select = preg_replace_callback("#<option[^>]*>[^<]*</option>#", array($this,"replace_catid_for_slug"), $select);

                echo $select;
	
		#print_r(get_categories('taxonomy=week')); 
		?>
	     <script type="text/javascript"><!--
                var dropdown = document.getElementById("archivecat");
                function onCatChange() {
                        if ( dropdown.options[dropdown.selectedIndex].value != -1 ) {
                                location.href = "<?php echo get_option('home');?>/week/"+dropdown.options[dropdown.selectedIndex].value+"/";
                        }
                }
                dropdown.onchange = onCatChange;
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
			$title = 'Results Archive';
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php print "Widget Title"; ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php 
	}
	
	function get_terms_orderby_natural_slug($orderby, $args){
    		$orderby = "SUBSTR({$orderby} FROM 1 FOR 5), CAST(SUBSTR({$orderby} FROM 6) AS UNSIGNED), SUBSTR({$orderby} FROM 6)";
    		return $orderby;
	}
	
        // From http://wordpress.org/support/topic/i-need-to-get-the-category-slug-from-the-category-id
        function replace_catid_for_slug($option){
                $categories = get_categories("taxonomy=week&hide_empty=0");
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


} // End of widget class
