<?php
/**
 * @package Betting Bonuses
 */
/*
Plugin Name: Betting Bonuses
Description: This Plugin is used to add Betting bonuses in admin and display in on frontend
Author: Karthikeyan Balasubramanian
Author URI: https://www.linkedin.com/in/karthikawebon/
*/

add_action( 'init', 'create_bettings');


function create_bettings() {
    register_post_type( 'bettings',
        array(
            'labels' => array(
                'name' => 'Bettings',
                'singular_name' => 'Betting',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Betting',
                'edit' => 'Edit',
                'edit_item' => 'Edit Betting',
                'new_item' => 'New Betting',
                'view' => 'View',
                'view_item' => 'View Betting',
                'search_items' => 'Search Bettings',
                'not_found' => 'No Bettings',
                'not_found_in_trash' => 'No Bettings found in Trash',
                'parent' => 'Parent Bettings'
            ),
 
            'public' => true,
            'menu_position' => 15,
            'supports' => false,
            'taxonomies' => array( 'betting_game' ),
            'has_archive' => true
        )
    );
    register_taxonomy(
        'betting_game',
        'bettings',
        array(
            'labels' => array(
                'name' => 'Game',
                'add_new_item' => 'Add New Game',
                'new_item_name' => "New Game"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
        )
    );    
}

add_action( 'admin_init', 'display_betting_meta_box' );

function display_betting_meta_box() {
    add_meta_box( 'betting_meta_box',
        'Betting Details',
        'display_betting_form',
        'bettings', 'normal', 'high'
    );
}

add_action( 'do_meta_boxes' , 'wpdocs_remove_page_excerpt_field' );
 
function wpdocs_remove_page_excerpt_field() {
    remove_meta_box( 'taqyeem_post_options' , 'bettings' , 'normal' );
    remove_meta_box( 'wpseo_meta' , 'bettings' , 'normal' ); 
    remove_meta_box( 'slugdiv' , 'bettings' , 'normal' );
}

?>
<?php
function display_betting_form( $bettings ) {
    $betting_amount = intval( get_post_meta( $bettings->ID, 'betting_amount', true ) );
    $betting_bonus = intval( get_post_meta( $bettings->ID, 'betting_bonus', true ) );
    $betting_logo_url =  get_post_meta( $bettings->ID, 'betting_logo_url', true );
    $betting_bonus_link =  get_post_meta( $bettings->ID, 'betting_bonus_link', true );
    $betting_redeem_bonus_link =  get_post_meta( $bettings->ID, 'betting_redeem_bonus_link', true );
    $betting_review_link =  get_post_meta( $bettings->ID, 'betting_review_link', true );
    wp_nonce_field(plugin_basename(__FILE__), 'wp_betting_nonce');
    ?>
    <table>
        <tr>
            <td style="width: 100%">Logo</td>
            <td><input type="file" id="betting_logo" name="betting_logo" value="" size="25" /></td>
            <td><?php if(!empty($betting_logo_url)){ ?><img src="<?php echo $betting_logo_url; ?>" width="50" height="50"/><?php } ?></td>
        </tr>    
        <tr>
            <td style="width: 100%">Amount</td>
            <td><input type="text" size="80" name="betting_amount" value="<?php echo $betting_amount; ?>" /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 100%">Bonus Link</td>
            <td><input type="text" size="80" name="betting_bonus_link" value="<?php echo $betting_bonus_link; ?>" /></td>
            <td>&nbsp;</td>
        </tr>        
        <tr>
            <td style="width: 100%">Bonus</td>
            <td><input type="text" size="80" name="betting_bonus" value="<?php echo $betting_bonus; ?>" /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 100%">Redeem Bonus Link</td>
            <td><input type="text" size="80" name="betting_redeem_bonus_link" value="<?php echo $betting_redeem_bonus_link; ?>" /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 100%">Review Link</td>
            <td><input type="text" size="80" name="betting_review_link" value="<?php echo $betting_review_link; ?>" /></td>
            <td>&nbsp;</td>
        </tr>                 
    </table>
    <?php
}

function update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action('post_edit_form_tag', 'update_edit_form');

add_action( 'save_post', 'add_betting_fields', 10, 2 );

function add_betting_fields( $betting_id, $betting ) {
	    if ( $betting->post_type == 'bettings' ) {
		    /* --- security verification --- */
		    if(!wp_verify_nonce($_POST['wp_betting_nonce'], plugin_basename(__FILE__))) {
		      return $betting_id;
		    } // end if
		       
		    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		      return $betting_id;
		    } // end if
		       
		    if('betting_bonuses' == $_POST['post_type']) {
		      if(!current_user_can('edit_page', $betting_id)) {
		        return $betting_id;
		      } // end if
		    } else {
		        if(!current_user_can('edit_page', $betting_id)) {
		            return $betting_id;
		        } // end if
		    } // end if
		    /* - end security verification - */ 

	    // Make sure the file array isn't empty
	    if(!empty($_FILES['betting_logo']['name'])) {
	         
	        // Setup the array of supported file types. In this case, it's just PDF.
	        $supported_types = array('image/png','image/jpg','image/jpeg');
	         
	        // Get the file type of the upload
	        $arr_file_type = wp_check_filetype(basename($_FILES['betting_logo']['name']));
	        $uploaded_type = $arr_file_type['type'];

	        // Check if the type is supported. If not, throw an error.
	        if(in_array($uploaded_type, $supported_types)) {
	 
	            // Use the WordPress API to upload the file
	            $upload = wp_upload_bits($_FILES['betting_logo']['name'], null, file_get_contents($_FILES['betting_logo']['tmp_name']));

	            if(isset($upload['error']) && $upload['error'] != 0) {
	                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
	            } else {
	                add_post_meta($betting_id, 'betting_logo_url', $upload['url']);
	                update_post_meta($betting_id, 'betting_logo_url', $upload['url']);     
	            } // end if/else
	 
	        } else {
	            wp_die("The file type that you've uploaded is not a PDF.");
	        } // end if/else
	         
	    } // end if		       	
        if ( isset( $_POST['betting_amount'] ) && $_POST['betting_amount'] != '' ) {
            update_post_meta( $betting_id, 'betting_amount', $_POST['betting_amount'] );
        }
        if ( isset( $_POST['betting_bonus'] ) && $_POST['betting_bonus'] != '' ) {
            update_post_meta( $betting_id, 'betting_bonus', $_POST['betting_bonus'] );
        }
        if ( isset( $_POST['betting_bonus_link'] ) && $_POST['betting_bonus_link'] != '' ) {
            update_post_meta( $betting_id, 'betting_bonus_link', $_POST['betting_bonus_link'] );
        }
        if ( isset( $_POST['betting_redeem_bonus_link'] ) && $_POST['betting_redeem_bonus_link'] != '' ) {
            update_post_meta( $betting_id, 'betting_redeem_bonus_link', $_POST['betting_redeem_bonus_link'] );
        }
        if ( isset( $_POST['betting_review_link'] ) && $_POST['betting_review_link'] != '' ) {
            update_post_meta( $betting_id, 'betting_review_link', $_POST['betting_review_link'] );
        }
        if ( isset( $_POST['betting_amount'] ) && $_POST['betting_amount'] != '' && isset( $_POST['betting_bonus'] ) && $_POST['betting_bonus'] != '' ) {
            update_post_meta( $betting_id, 'betting_total', $_POST['betting_amount'] + $_POST['betting_bonus']);
        }                                
    }
}

add_filter( 'manage_bettings_posts_columns', 'set_custom_bettings_columns' );
function set_custom_bettings_columns($columns) {
    $columns = [];
    $columns['betting_logo'] = 'Logo';
    $columns['betting_amount'] = 'Amount';
    $columns['betting_bonus'] = 'Bonus';
    $columns['betting_game'] = 'Game';

    return $columns;
}

add_action( 'manage_bettings_posts_custom_column' , 'custom_columns', 10, 2 );

function custom_columns( $column, $post_id ) {
    switch ( $column ) {
        case 'betting_logo':
            echo '<img src="'.get_post_meta( $post_id, 'betting_logo_url', true ).'" width="50" height="50"/>';
            break;
        case 'betting_amount':
            echo get_post_meta( $post_id, 'betting_amount', true );
            break;
        case 'betting_bonus':
            echo get_post_meta( $post_id, 'betting_bonus', true ); 
            break;
        case 'betting_game':
            $term_list = wp_get_post_terms($post_id, 'betting_game', array("fields" => "names"));
            if(!empty($term_list[0]))
                echo $term_list[0]; 
            break;            
    }
}

function display_game_bettngs_function(){

    $output_display .= '<div id="vergleich-filter" class="filter">
    <form id="betting-filter-form" method="post">
    <div style="width:279px; float:left; padding: 5px 15px;">';

    $terms = get_terms('betting_game',['hide_empty' => false]);
    $term_count = 0;
    foreach($terms as $term){
        if ($term_count >= 4 && $term_count % 4 == 0){
            $output_display .= '</div>';
            $output_display .= '<div style="width:279px; float:left; padding: 5px 15px;">';
        } 
        $isChecked = (isset($_POST['betting_game']) && in_array($term->slug, $_POST['betting_game']))?"checked=checked":"";
        $output_display .= '<span class="checkbox">
                            <label for="'.$term->slug.'">'.$term->name.'</label>
                            <input id="'.$term->slug.'" type="checkbox" name="'.$term->taxonomy.'[]" class="refresh-list" value="'.$term->slug.'" onclick="filterBettings()" '.$isChecked.'>
                            </span>';
                            $term_count++;
    }
    $output_display .= '</div></form></div>';
    $output_display .='<table width="100%" border="0" cellspacing="0" id="bettings-table" class="result-list" cellpadding="0">
                        <thead>
                        <tr id="test">
                        <th scope="col"><img src="'.plugins_url( '/image/stern.png', __FILE__ ).'"></th>
                        <th scope="col"><font><font>providers</font></font></th>
                        <th scope="col"><font><font>Max. Bonus</font></font></th>
                        <th scope="col"><font><font>Bonus calculator (calculate your bonus)</font></font></th>
                        <th scope="col">&nbsp;</th>
                        </tr>
                        </thead><tbody>';

    $args = array('post_type' => 'bettings','order' => 'DESC','meta_key' => 'betting_bonus','orderby'=>'meta_value');
    if(isset( $_POST['betting_game'] ) && !empty($_POST['betting_game'])){
        $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'betting_game',
                            'field' => 'slug',
                            'terms' => $_POST['betting_game']
                        ));
    }
    $the_query = new WP_Query( $args );
    $query_index = 1; 
    if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();
    if($query_index == 1){
        $output_display .= '<tr class="winner list-item item">
                                <td class="col1_2">
                                    <img src="'.plugins_url( '/image/pokal_klein.png', __FILE__ ).'">
                                </td>
                                <td class="col2_2">
                                    <span class="zulink generatedRLM  rlmPldb openJsLinkBlank rlmForced" data-zu="link/bet365"><img src="'.get_post_meta(get_the_ID(),'betting_logo_url',true).'" width="180" height="48"></span>
                                    <a href="'.esc_url(get_post_meta(get_the_ID(),'betting_bonus_link',true)).'"><font><font>
                                     Read experiences</font></font></a>
                                </td>
                                <td class="col3_2">
                                    <div class="bonus_1"><font><font>'.get_post_meta(get_the_ID(),'betting_bonus',true).' €</font></font></div>
                                    <div class="bonus_2"><font><font>
                                    100% on deposit </font></font></div>
                                </td>
                                <td class="col4_2">
                                <div class="bonuscalc_box">
                                    <div class="bonuscalc_innerbox"><font><font> deposit
                                    </font></font><input class="bonuscalc_textfield einzahlunginput" id="betting-amount-'.get_the_ID().'" type="text" value="'.get_post_meta(get_the_ID(),'betting_amount',true).'" onkeyup="updateTotalAmount('.get_the_ID().','.get_post_meta(get_the_ID(),'betting_bonus',true).')">
                                    </div>
                                    <div class="bonuscalc_border plus"><font><font>+</font></font></div>
                                    <div class="bonuscalc_innerbox"><font><font>
                                    bonus
                                    </font></font><div class="bonuscalc_textfield" id="betting-bonus-'.get_the_ID().'" >'.get_post_meta(get_the_ID(),'betting_bonus',true).'</div>
                                    </div>
                                    <div class="bonuscalc_border"></div>
                                    <div class="bonuscalc_innerbox"><font><font> betting credits
                                    </font></font><div class="bonuscalc_textfield" id="betting-total-'.get_the_ID().'" >
                                    '.get_post_meta(get_the_ID(),'betting_total',true).'</div>
                                    </div>
                                    </div>
                                </td>
                                <td class="col5">
                                     <a href="'.esc_url(get_post_meta(get_the_ID(),'betting_redeem_bonus_link',true)).'" target="_blank">
                                        <span class="button large yellow generatedRLM  rlmPldb openJsLinkBlank rlmForced" style="padding:8px;margin:8px 6px 6px 6px" data-zu="link/bet365">
                                        <span><font><font>Bonus </font></font><em class="hidden-mobile"><font><font>Redeem</font></font></em></span>
                                        </span>
                                    </a>
                                    <a href="'.esc_url(get_post_meta(get_the_ID(),'betting_review_link',true)).'" class=" mobileInfolink"><font><font> 
                                    Bonus info
                                    </font></font></a>
                                </td>
                            </tr>';
    }else{
        $output_display .='<tr class=" list-item item">
                                <td class="col1_2"><font><font>
                                '.$query_index.'</font></font></td>
                                <td class="col2_2"><span class="zulink generatedRLM  rlmPldb openJsLinkBlank"><img src="'.get_post_meta(get_the_ID(),'betting_logo_url',true).'" width="180" height="48"></span>
                                <a href="'.esc_url(get_post_meta(get_the_ID(),'betting_bonus_link',true)).'" target="_blank"><font><font>
                                Read experiences</font></font></a>
                                </td>
                                <td class="col3_2">
                                <div class="bonus_1"><font><font>'.get_post_meta(get_the_ID(),'betting_bonus',true).' €</font></font></div>
                                <div class="bonus_2"><font><font>
                                100% on deposit </font></font></div>
                                </td>
                                <td class="col4_2">
                                <div class="bonuscalc_box">
                                <div class="bonuscalc_innerbox"><font><font> deposit
                                </font></font><input class="bonuscalc_textfield einzahlunginput" id="betting-amount-'.get_the_ID().'" value="'.get_post_meta(get_the_ID(),'betting_amount',true).'" onkeyup="updateTotalAmount('.get_the_ID().','.get_post_meta(get_the_ID(),'betting_bonus',true).')">
                                </div>
                                <div class="bonuscalc_border plus"><font><font>+</font></font></div>
                                <div class="bonuscalc_innerbox"><font><font>
                                bonus
                                </font></font><div class="bonuscalc_textfield" id="betting-bonus-'.get_the_ID().'" >'.get_post_meta(get_the_ID(),'betting_bonus',true).'</div>
                                </div>
                                <div class="bonuscalc_border"></div>
                                <div class="bonuscalc_innerbox"><font><font> betting credits
                                </font></font><div class="bonuscalc_textfield" id="betting-total-'.get_the_ID().'" >
                                    '.get_post_meta(get_the_ID(),'betting_total',true).'</div>
                                </div>
                                </div>
                                </td>
                                <td class="col5">
                                <a href="'.esc_url(get_post_meta(get_the_ID(),'betting_redeem_bonus_link',true)).'" target="_blank">
                                    <span class="button large yellow generatedRLM  rlmPldb openJsLinkBlank" style="padding:8px;margin:8px 6px 6px 6px" data-zu="link/betway">
                                    <span><font><font>Bonus </font></font><em class="hidden-mobile"><font><font>Redeem</font></font></em></span>
                                    </span>
                                </a>
                                <a target="_blank" href="'.esc_url(get_post_meta(get_the_ID(),'betting_review_link',true)).'" class=" mobileInfolink"><font><font> 
                                Bonus info
                                </font></font></a>
                                </td>
                            </tr>';
        }

    $query_index++;
    endwhile;
    endif;
    wp_reset_query();
    $output_display .= '</tbody></table>';
    return $output_display;
}

function register_shortcodes(){
   add_shortcode('display-game-bettings', 'display_game_bettngs_function');
}

add_action( 'init', 'register_shortcodes');

function wptuts_styles_with_the_lot()
{
    // Register the script like this for a plugin:
    wp_register_script( 'custom-script', plugins_url( '/js/betting-bonuses.js', __FILE__ ) );
 
    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'custom-script' );

    // Register the style like this for a plugin:
    wp_register_style( 'betting-bonuses-style', plugins_url( '/css/betting-bonuses.css', __FILE__ ), array(), '', 'all' );

    // For either a plugin or a theme, you can then enqueue the style:
    wp_enqueue_style( 'betting-bonuses-style' );
}

add_action( 'wp_enqueue_scripts', 'wptuts_styles_with_the_lot' );
?>