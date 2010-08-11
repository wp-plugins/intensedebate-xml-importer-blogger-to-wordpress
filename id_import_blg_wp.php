<?php
/*
Plugin Name: IntenseDebate XML Importer (Blogger->Wordpress)
Plugin URI: http://www.intechgrity.com/?p=267
Description: Move your intense debate comments from blogspot to wordpress using the Intense Debate XML Export file.  <a href="options-general.php?page=id_import_blg_wpitg">Click here to get started.</a><br/>The comparison is made on the Title basis! Although it is now possible to <a href="http://devilsworkshop.org/moving-from-blogger-to-wordpress-maintaining-permalinks-traffic-seo/" target="_blank">Migrate to WP from Blogger</a> without loosing a single Permalink or SEO, I preferred the original algorithm by previous author <br/>This is a derivative work of <a href="http://blog.intensedebate.com/2010/02/09/blogger-to-wordpress/">blogspot2wp</a> Plugin made by Josh Fraser<josh@eventvue.com>
Version: 1.0.1
Author: Swashta <swashata4u@gmail.com>
Author URI: http://www.swashata.me/
License: GPL2
*/
?>
<?php
/*  Copyright 2010  Swashata Ghosh  (email : swashata4u@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
function importer_admin_actions() {
    add_options_page("IntenseDebate XML Importer", "IntenseDebate XML Import", 'manage_options', "id_import_blg_wpitg", "import_comment");
}
function import_comment() {
?>
<div class="wrap">
<h2>Import your Intense Debate Comment using the Export XML File</h2>
<p>Plugin author: <a href="http://www.intechgrity.com">Swashata</a> | Donate: <a href="http://www.intechgrity.com/about/buy-us-some-beer/">Buy me some beer</a> | FAQ: <a href="http://www.intechgrity.com/?p=267">Visit our Blog</a></p>
    <h3>Instruction:</h3>
    <ol>
    <li>Login to your <a href='http://www.intensedebate.com' target='_blank'>Intense Debate</a> account and navigate to your Site</li>
    <li>Under Tools click on XML Export [from the left sidebar] and download the complete backup</li>
    <li>Upload the XML file from your pc using the form below! And rest will be taken care of</li>
    </ol>
<?php
    if($_SERVER['REQUEST_METHOD']== 'POST') :
        //$xml_data = file_get_contents($_FILES['id_xml']['tmp_name']);
        if((!empty($_FILES['id_xml'])) && ($_FILES['id_xml']['error']==0) && ($_FILES['id_xml']['type'] == 'text/xml')) :
            echo '<h3>Import Result</h3><pre style="height: 400px; overflow: scroll; border: 1px dotted #333">';
            global $wpdb;
	    $post_count = $comment_count = $per_post_comment_count = 0;
            
            $comments = simplexml_load_file($_FILES['id_xml']['tmp_name']);
            // loop through each blogpost
            foreach($comments as $post) :
		$per_post_comment_count = 0;
                $post_title = $post->title;
                echo "\n\n\nTrying to import for title <strong><big>".$post_title."</big></strong>\n";
                // look ahead and get the date of the first comment
                $date_of_first_comment = strtotime($post->comments->comment->gmt);
                
                // lookup the post ID using the title of the blogpost
                // if there are multiple blog posts with the same title
                // choose the one with the date closest to the first comment
                $query = "SELECT ID, ABS('$date_of_first_comment' - UNIX_TIMESTAMP(post_date_gmt)) AS nearest_date FROM ".$wpdb->prefix."posts WHERE post_title = '$post_title' ORDER BY nearest_date LIMIT 1";
                $results = $wpdb->get_results($query);
		if(isset($results))
                $comment_postID = $results[0]->ID;
                
                // don't store the comment unless we can match it with a post
                if ($comment_postID) :
                    // loop through all comments for each blogpost
                    foreach ($post->comments->comment as $comment) :																			
                        // actually insert the comment into wordpress														
                        $commentdata['user_id'] = 0;
                        $commentdata['comment_agent'] = "";
                        $commentdata['comment_author'] = addslashes($comment->name);
                        $commentdata['comment_content'] = addslashes($comment->text);
                        $commentdata['comment_author_IP'] = addslashes($comment->ip);
                        $commentdata['comment_author_url'] = addslashes($comment->url);
                        $commentdata['comment_author_email'] = addslashes($comment->email);
                        $commentdata = apply_filters('preprocess_comment', $commentdata);
                        $commentdata['comment_post_ID'] = (int) $comment_postID;
                        $commentdata['comment_date']     = $comment->date;
                        $commentdata['comment_date_gmt'] = $comment->gmt;
                        $commentdata = wp_filter_comment($commentdata);
                        $commentdata['comment_approved'] = 1;
                        
                        // don't add duplicate comments
                        if (!dup_comment($comment->name, $comment->text, $comment->ip, $comment->url, $comment_postID)) :
                            $comment_ID = wp_insert_comment($commentdata); 
                            do_action('comment_post', $comment_ID, $commentdata['comment_approved']);
                            $comment_count++;
			    $per_post_comment_count++;
                        endif;
                    endforeach;
                    $post_count++;
                endif;
		echo "\tImported {$per_post_comment_count} comment(s) for this post";
            endforeach;
            echo '</pre>';
            if ($comment_count == 0) :
		?>
		<div class="error fade">There were no new comments found to import!</div>
		<?php		
            else :
		?>
		<div class="updated fade">Successfully added <?php echo $comment_count; ?> comments from <?php echo $post_count ?> blog posts</div>
		<?php
            endif;
	    
        else :
            if($_FILES['id_xml']['type'] != 'text/xml') echo '<p>Uplaoded file was not a valid XML file</p>';
            if($_FILES['id_xml']['error']!=0) echo '<p>There was some error uploading the file <br/>'.$_FILES['id_xml']['error'].'</p>';
            if(empty($_FILES['id_xml'])) echo '<p>No file uploaded</p>';
        endif;
    else :
	?>
<p>Howdy! Just use the form below to upload the XML file! Rest would be done automatically</p>
<form method='POST' enctype='multipart/form-data'>
<p><label style='width: 200px; display:block;float:left;'>The Intense Debate XML file:</label> <input type='file' name='id_xml' id='id_xml' /></p>
<p><input class="button-primary" type='submit' name='sub' value='<?php _e('Start Import') ?>' /><br /><br />
<small><b>Note:</b> this may take a while (or might break!) if you have a lot of comments. Get your Intense Debate Comment Export XML file from <a href='http://intensedebate.com/' target='_blank'>here</a>.</small>
</form>
	<?php
    endif;
    ?>
    </div>
    <?php
}

// don't import the same comment twice

function dup_comment($author, $comment, $ip, $url, $postid) {
    global $wpdb;
    if (!isset($wpdb->comments)) {
        global $tablecomments;				
        $wpdb->comments = $tablecomments;
    }
    $strSQL = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = '".addslashes($postid)."' AND comment_approved = '1' AND (comment_type='comment' or comment_type='') AND comment_author='".addslashes($author)."' AND comment_author_url='".addslashes($url)."' AND comment_author_IP='".addslashes($ip)."' AND comment_content='".addslashes($comment)."';";
    if($wpdb->get_var($strSQL)==0)
        return false;
    else
        return true;
}
add_action('admin_menu', 'importer_admin_actions');