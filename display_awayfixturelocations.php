<?php
		$parent = get_option('cran_awayfixturepage');
		$select_loc = wp_dropdown_pages("child_of=".$parent."&echo=0");

                $select_loc = preg_replace_callback("#<option[^>]*>[^<]*</option>#", "cran_sportsdesk_replace_pageid_for_slug", $select_loc);

                echo $select_loc;
	
		function cran_sportsdesk_replace_pageid_for_slug($option){
                $pages = get_pages("child_of=".$instance['parentid']);
                //print_r($pages);
                preg_match('/value="(\d*)"/', $option[0], $matches);

                $id = $matches[1];
                $slug = "";
                foreach($pages as $page){
                        if($page->ID == $id){
                                $slug = get_permalink($page->ID);
                        }
                }
                //$slug = "123";
                return preg_replace("/value=\"(\d*)\"/", "value=\"$slug\"", $option[0]);

		}

?>
        <script type="text/javascript"><!--
                var oppdropdown = document.getElementById("page_id");
                function onOppChange() {
                        if ( oppdropdown.options[oppdropdown.selectedIndex].value != -1 ) {
                                location.href = oppdropdown.options[oppdropdown.selectedIndex].value;
                        }
                }
                oppdropdown.onchange = onOppChange;
        --></script>
