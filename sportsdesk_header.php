<?php
	#if($context=="current"){
		//If current (no parameters) we want to display all fixtures this week regardless of whether they have results
		//$this->what_week should return us teh current week of term
		query_posts('post_type=match&posts_per_page=100&post_status=all&week='.week_tax().'&order=ASC');
	#}elseif($context=="last"){
#		query_posts('post_type=match&posts_per_page=100&post_status=all&meta_key=week&meta_value='.what_week()-1);
#	}

?>
<h2><?php print week_tax(); ?></h2>

<?php
if ( have_posts() ){

?>
<table class="ResultsTable">
<thead>
<tr>
<th>Date</th>
<th>Venue</th>
<th>Sport</th>
<th>Team</th>
<th>Opponent</th>
<th>Result</th>
<th>Score</th>
</tr>
</thead>
<tbody>
<?php
                                while ( have_posts() ) : the_post();
	$id = get_the_ID();
	$opponent = array_shift(wp_get_post_terms($id,"opponent"));
	$venue = array_shift(wp_get_post_terms($id,"venue"));
	$team = array_shift(wp_get_post_terms($id,"team"));
	$sport = get_term($team->parent,"team",object);
?>
<tr class="<?php print get_post_status($id) ?>">
<td>
	<a href="<?php the_permalink() ?>">
	<?php the_time('d/m/Y g:i a'); ?>
	</a>
</td>
<td><?php print $venue->name; ?></td>
<td><?php print $sport->name; ?></td>
<td><?php print $team->name; ?></td>
<td><?php print $opponent->name; ?></td>
<td><?php print get_post_meta($id,'result',true); ?></td>
<td><?php print get_post_meta($id,'score',true); ?></td>
</tr>


<?php

                                endwhile;
	//This cruical otherwise we get the posts listed twice
	wp_reset_query();
} else {
  print "Nothing to display";
}

?>
</tbody>
</table>
</table>

<style type="text/css">
.ResultsTable a {
    color: #000000;
}
.ResultsTable tbody tr:hover {
    cursor: pointer;
    background-color: #88ACDE;
}
.pending {
    background-color: #FFEBE8;
    text-decoration: line-through; 
}
</style>
<script type="text/javascript" src="<?php print plugin_dir_url(__FILE__); ?>js/jquery.tablesorter.min.js">
</script>
<script type="text/javascript">
jQuery(document).ready(function(){
  //alert('hello world');
  jQuery('.ResultsTable tr:even').addClass('alt'); 
  jQuery('.ResultsTable tr').click(function(){
	var href = jQuery(this).find("a").attr("href");
	if(href){
		window.location = href;
	}
	});
  jQuery('.ResultsTable').tablesorter();
});
</script>
