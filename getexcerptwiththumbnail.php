<?php
/*
Plugin Name: Get Excerpts with Thumbnail Images
Plugin URI: http://creativeslab.net/get-excerpt-with-thumb
Description: Returns thumbnail images of posts along with excerpt.
Version: 1.0
Author: Sachethan G Reddy
Author URI: http://creativeslab.net
*/

/*  Copyright 2009  Sachethan G Reddy (http://creativeslab.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307USA
*/

function getExcerptsWithThumbnail(){
	
	if(is_single()) return;
	global $wp_query, $wpdb;
	$id = $wp_query->post->ID;
	$moreText = get_option("ex_th_more_text");
		$cutLen = get_option("ex_th_cut_length");
		$postLen = get_option("ex_th_post_length");
		$imgCssClass = get_option("ex_th_img_css_class");
	$excerpt = wswwpx_content_extract($moreLink, $postLen, $cutLen);
	
	$query = "SELECT wpm.post_id, wpm.meta_value, wp.guid, wp2.post_title FROM " . $wpdb->prefix."postmeta wpm, " . $wpdb->prefix."posts wp, " . $wpdb->prefix."posts wp2  WHERE wpm.post_id IN (SELECT ID FROM " . $wpdb->prefix."posts WHERE post_parent = wp2.id and post_type like 'attachment') and wpm.meta_key like '_wp_attachment_metadata' and wp.id = wpm.post_id and wp2.id = " . $id;

	$attachments = $wpdb->get_results($query);
	
	if ($attachments) {
		foreach ($attachments as $attachment) {
			$img_url = $attachment->guid;
			$metadata = maybe_unserialize( $attachment->meta_value);
			$img_url = str_replace(basename($img_url), $metadata['sizes']['thumbnail']['file'], $img_url);
	
			$imageWidth = $metadata['sizes']['thumbnail']['width'];
			$imageHeight = $metadata['sizes']['thumbnail']['height'];
			$imgTag = '<img src="'.attribute_escape($img_url).'" width=' . $imageWidth . ' height=' . $imageHeight . ' class="' . $imgCssClass . '" alt="' . $attachment->post_title .'" />';
			$post_permalink = get_permalink();
			$post_info['post_thumbnail'] = "<a href='" . $post_permalink . "' title='" . $attachment->post_title . "'>$imgTag</a>";
			$post_info['post_title'] = "<a href='" . $post_permalink . "' title='" . $attachment->post_title . "'>" . $attachment->post_title . "</a>";
			break;
		}
	}
	$post_info['post_excerpt'] = $excerpt;
	return $post_info;
}

function excerpt_thumbnail_admin(){
    if(isset($_POST['submitted'])){
		// Get data from input fields
        $moreText = $_POST["ex_th_more_text"];
		$cutLen = $_POST["ex_th_cut_length"];
		$postLen = $_POST["ex_th_post_length"];
		$imgCssClass = $_POST["ex_th_img_css_class"];
		
		// Upload / update data to database
		update_option("ex_th_more_text", $moreText);
		update_option("ex_th_cut_length", $cutLen);
		update_option("ex_th_post_length", $postLen);
		update_option("ex_th_img_css_class", $imgCssClass);
		
		//Options updated message
        echo "<div id=\"message\" class=\"updated fade\"><p><strong>Excerpts with Thumbnail Configuration updated.</strong></p></div>";
    }
	?>
    <div class="wrap">
    <h2>Get Excerpt With Thumbnail Configuration</h2>
	<?php
		$moreText = get_option("ex_th_more_text");
		$cutLen = get_option("ex_th_cut_length");
		$postLen = get_option("ex_th_post_length");
		$imgCssClass = get_option("ex_th_img_css_class");

		//default values to be taken if none is provided
		if($moreText == "") $moreText = "(More)...";
		if($cutLen == "") $cutLen = 20;
		if($postLen == "") $postLen = 40;
		if($imgCssClass == "") $imgCssClass = "imgstyle";

		if($postLen < $cutLen)
			$postLen = $cutLen;
	
	?>
	
    <form method="post" name="options" target="_self">
	<br/>
	<table>
		<tr>
			<td><strong>Thumbnail Css Class</strong></td><td><input name="ex_th_img_css_class" type="text" value="<?php echo $imgCssClass; ?>"/></td><td colspan="2">&nbsp;&nbsp;<strong>Ex: imgstyle{margin: 5px; float: left;}</strong> ,</br>&nbsp;&nbsp;put this style into your .css file</td>
		</tr>
		<tr><td colspan=4>&nbsp;</td></tr>
		<tr>
			<td><strong>More Text</strong></td><td><input name="ex_th_more_text" type="text" value="<?php echo $moreText; ?>"/></td><td colspan="2">&nbsp;&nbsp;More link after excerpt. Ex: <strong>More...</strong> or <strong>Read More...</strong> or <strong>Click for More...</strong></td>
		</tr>
		<tr><td colspan=4>&nbsp;</td></tr>
		<tr>
			<td><strong>Post Length</strong></td><td><input name="ex_th_post_length" type="text" value="<?php echo $postLen; ?>"/></td><td colspan="2">&nbsp;&nbsp;Length of Post to read from wordpress, <strong>default value 40</strong></td>
		</tr>
		<tr><td colspan=4>&nbsp;</td></tr>
		<tr>
			<td><strong>Excerpt Length</strong></td><td><input name="ex_th_cut_length" type="text" value="<?php echo $cutLen; ?>"/></td><td colspan="2">&nbsp;&nbsp;Length of excerpt to be displayed, <strong>default value 20</strong></td>
		</tr>
		<tr>
			<td>&nbsp;</td><td colspan="3">Note: Make sure always Post Length is greater than or equal to Excerpt Length to be read from wordpress.</td>
		</tr>
		<tr><td colspan=4>&nbsp;</td></tr>
		
	</table>
	<br />
	
	
<p class="submit">
<input name="submitted" type="hidden" value="yes" />
<input type="submit" name="Submit" value="Update Options &raquo;" />
</p>
</form>

</div>

<?php } 
//Add the options page in the admin panel
function excerpt_thumbnail_add_options() {
    add_submenu_page('options-general.php', 'Get Excerpt With Thumbnail', 'Get Excerpt With Thumbnail', 10, __FILE__, 'excerpt_thumbnail_admin');
}
add_action('admin_menu', 'excerpt_thumbnail_add_options');

?>