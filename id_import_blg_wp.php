<?php
/*
Plugin Name: IntenseDebate XML Importer (Blogger -> Wordpress)
Plugin URI: http://www.intechgrity.com/?p=267
Description: Move your intense debate comments from blogspot to wordpress using the Intense Debate XML Export file. <a href="options-general.php?page=id_import_blg_wpitg">Click here to get started</a>. The comparison is made on the Title basis! Although it is now possible to <a href="http://devilsworkshop.org/moving-from-blogger-to-wordpress-maintaining-permalinks-traffic-seo/" target="_blank">Migrate to WP from Blogger</a> without loosing a single Permalink or SEO, I preferred the original algorithm by previous author. This is a derivative work of <a href="http://blog.intensedebate.com/2010/02/09/blogger-to-wordpress/">blogspot2wp</a> Plugin made by Josh Fraser.
Version: 1.0.2
Author: Swashata
Author URI: http://www.swashata.me/
License: GPL2
*/

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

/** Load Text Domain For Translations */
load_plugin_textdomain( 'id-xml-importer', null, dirname( __FILE__ ) . '/translations' );

/** Check PHP 5 on activation and upgrade settings (Hat tip - Ozh, YOURLS plugin) */
register_activation_hook( __FILE__, 'id_xml_importer_activate_plugin' );
function id_xml_importer_activate_plugin() {
	if ( version_compare( PHP_VERSION, '5.0.0', '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( __( 'IntenseDebate XML Importer plugin requires PHP5. Sorry!', 'id-xml-import' ) );
	}
}

/**
 * Add a page for importing comments
 */
function id_xml_importer_admin_actions() {
    add_options_page( __( 'IntenseDebate XML Importer', 'id-xml-importer' ), __( 'IntenseDebate XML Import', 'id-xml-importer' ), 'manage_options', 'id_import_blg_wpitg', 'id_xml_importer_import_comment' );
}

/**
 * The magic happens here!
 */
function id_xml_importer_import_comment() {
?>
<div class="wrap">
	<h2><?php _e( 'Import your Intense Debate Comment using the Export XML File', 'id-xml-importer' ); ?></h2>

	<p><?php printf( __( 'Plugin author: <a href="%1$s">Swashata</a> and <a href="%4$s">Gautam</a> | Donate: <a href="%2$s">Buy me some beer</a> or write about it! | FAQ: <a href="%3$s">Visit our Blog</a>', 'id-xml-importer' ), 'http://www.intechgrity.com', 'http://www.intechgrity.com/about/buy-us-some-beer/', 'http://www.intechgrity.com/?p=267', 'http://gaut.am' ); ?></p>

	<h3><?php _e( 'Instructions:', 'id-xml-importer' ); ?></h3>

	<ol>
		<li><?php printf( __( 'Login to your <a href="%s" target="_blank">Intense Debate</a> account and navigate to your Site.', 'id-xml-importer' ), 'http://www.intensedebate.com' ); ?></li>
		<li><?php _e( 'Under Tools click on XML Export [from the left sidebar] and download the complete backup.', 'id-xml-importer' ); ?></li>
		<li><?php _e( 'Upload the XML file from your pc using the form below! And rest will be taken care of.', 'id-xml-importer' ); ?></li>
	</ol>
	
<?php
	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		//$xml_data = file_get_contents($_FILES['id_xml']['tmp_name']);
		
		if( !empty($_FILES['id_xml'] ) && $_FILES['id_xml']['error'] == 0 && $_FILES['id_xml']['type'] == 'text/xml' ) {
			global $wpdb;
			
			echo '<h3>' . __( 'Import Result', 'id-xml-importer' ) . '</h3><pre style="height: 400px; overflow: scroll; border: 1px dotted #333; padding: 10px">';
			
			$post_count = $comment_count = $per_post_comment_count = $per_post_total_comment = $total_post = $total_comment = 0;
			
			$comments = simplexml_load_file( $_FILES['id_xml']['tmp_name'] );
			
			// loop through each blogpost
			foreach( $comments as $post ) {
				/*Increment the $total_post*/
				$total_post++;
				
				/*Make this post comment and valid comments 0 and find post title */
				$per_post_comment_count = $per_post_total_comment = 0;
				$post_title = $post->title;
				
				echo "\n\n\n" . sprintf( __( 'Trying to import for title %s', 'id-import-xml' ), "<strong><big>$post_title</big></strong>" ) . "\n";
				
				/* Look ahead and get the date of the first comment */
				$date_of_first_comment = strtotime( $post->comments->comment->gmt );
				
				/*
				 Lookup the post ID using the title of the blogpost
				 if there are multiple blog posts with the same title
				 choose the one with the date closest to the first comment
				*/
				$query = $wpdb->prepare( "SELECT ID, ABS(%s - UNIX_TIMESTAMP(post_date_gmt)) AS nearest_date FROM $wpdb->posts WHERE post_title = %s ORDER BY nearest_date LIMIT 1", $date_of_first_comment, $post_title );
				
				if( $results = $wpdb->get_results( $query ) )
					$comment_postID = $results[0]->ID;
				
				/* Don't store the comment unless we can match it with a post */
				if ( $comment_postID ) {
					
					/**
					 *Tell the user that we have got a post
					 *We will use the get_permalink
					 */
					
					$post_permalink = get_permalink( $comment_postID );
					
					echo "\n" . sprintf( __( 'Found the post on your blog <a href="%1$s" title="%2$s">Link to post</a>', 'id-import-xml' ), $post_permalink, $post_title ) . "\n";
					/* Loop through all comments for each blogpost */
					foreach ( $post->comments->comment as $comment ) {
						
						/* Increment the per_post_total_comment */
						$per_post_total_comment++;
						$total_comment++;
						
						/* Actually insert the comment into WordPress */
						$commentdata['user_id']			= 0;
						$commentdata['comment_agent']		= '';
						$commentdata['comment_author']		= $comment->name;
						$commentdata['comment_content']		= $comment->text;
						$commentdata['comment_author_IP']	= $comment->ip;
						$commentdata['comment_author_url']	= $comment->url;
						$commentdata['comment_author_email']	= $comment->email;
						$commentdata				= apply_filters( 'preprocess_comment', $commentdata );
						$commentdata['comment_post_ID']		= (int) $comment_postID;
						$commentdata['comment_date']		= $comment->date;
						$commentdata['comment_date_gmt']	= $comment->gmt;
						$commentdata				= wp_filter_comment( $commentdata );
						$commentdata['comment_approved']	= 1;
						
						/**
						 * Don't add duplicate comments
						 * We will check on the basis of comment_name, comment_text and comment_url for the current post
						*/
						
						if ( !id_xml_importer_dup_comment($comment->name, $comment->text, $comment->url, $comment_postID ) ) {
							$comment_ID = wp_insert_comment( $commentdata ); 
							do_action( 'comment_post', $comment_ID, $commentdata['comment_approved'] );
							$comment_count++;
							$per_post_comment_count++;
						}
					}
					
					$post_count++;
				}
				else {
					echo "\n" . _e('Sorry... Could not find a post for this title', 'id-sml-import') . "\n";
				}
				
				echo "\t" . sprintf( __ngettext( 'Imported %1$d/%2$d comment for this post', 'Imported %1$d/%2$d comments for this post', $per_post_comment_count, 'id-xml-import' ), $per_post_comment_count, $per_post_total_comment );
			}
			
			echo '</pre>';
			
			if ( $comment_count == 0 )
				echo '<div class="error fade">' . __( 'There were no new comments found to import!', 'id-xml-import' ) . '</div>';
			else
				echo '<div class="updated fade">' . sprintf( __( 'Successfully added %1$d comment(s) from %2$d blog post(s).', 'id-xml-import' ), $comment_count, $post_count ) . '</div>';
			
			echo '<div class="updated fade">' . sprintf( __( 'Found a total of %d Posts and %d Comments from your uploaded XML File', 'id-xml-import'), $total_post, $total_comment ) . '</div>';
		} else {
			if ( $_FILES['id_xml']['type'] != 'text/xml' )	echo '<p>' . __( 'Uplaoded file was not a valid XML file', 'id-xml-import' ) . '</p>';
			if ( $_FILES['id_xml']['error'] != 0 )		echo '<p>' . __( 'There was some error uploading the file', 'id-xml-import' ) . '<br />' . $_FILES['id_xml']['error'] . '</p>';
			if ( empty( $_FILES['id_xml'] ) )		echo '<p>' . __( 'No file uploaded', 'id-xml-import' ) . '</p>';
		}
	} else {
	?>

	<p><?php _e( 'Howdy! Just use the form below to upload the XML file! Rest would be done automatically!', 'id-xml-import' ); ?></p>

	<form method="post" enctype="multipart/form-data">
		<p><label style="width:200px;display:block;float:left;"><?php _e( 'The Intense Debate XML file:', 'id-xml-import' ); ?></label> <input type="file" name="id_xml" id="id_xml" /></p>
		<p><input class="button-primary" type="submit" name="sub" value="<?php esc_attr_e( 'Start Import', 'id-xml-import' ); ?>" /><br /><br />
		<small><?php printf( __( '<b>Note:</b> This may take a while (or might break!) if you have a lot of comments. Get your Intense Debate Comment Export XML file from <a href="%s" target="_blank">here</a>.', 'id-xml-import' ), 'http://intensedebate.com/' ); ?></small>
	</form>
<?php
	}
?>
</div>
<?php
}

/**
 * Checks for duplicate comments
 *
 * @param string $author Author Name
 * @param string $comment Comment Content
 * @param string $url Author's website URL
 * @param int $postid Post ID of the comment
 *
 * @global $wpdb
 *
 * @return bool Whether a duplicate comment was found or not TRUE if found FALSE if not
 */
function id_xml_importer_dup_comment( $author, $comment, $url, $postid ) {
	global $wpdb;
	
	$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND (comment_type='comment' OR comment_type='') AND comment_author = '%s' AND comment_author_url = '%s' AND comment_content = '%s' LIMIT 1", $postid, $author, $url, $comment );
	if ( $wpdb->get_var( $sql ) )
		return true;
	
	return false;
}

add_action( 'admin_menu', 'id_xml_importer_admin_actions' );