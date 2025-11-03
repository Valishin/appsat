<?php global $shortname; ?>

<div id="comments">

	
	<?php if ( post_password_required() ) : ?>
		<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'avali' ); ?></p>
	</div><!-- #comments -->
	<?php return; endif; ?>

	<?php if ( have_comments() ) : ?>		
		<h4 id="comments-title"><?php comments_number(__('no comments', 'avali'), __('<span>1</span> comment', 'avali'), __('<span>%</span> comments', 'avali')); ?></h3>
		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'avali' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'avali' ) ); ?></div>
			</div> <!-- .navigation -->
		<?php endif; ?>

		<ol class="commentlist group">
			<?php wp_list_comments( array( 'avatar_size' => 60, 'type' => 'comment', 'callback' => 'mytheme_comment' ) ); ?>
		</ol>

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'avali' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'avali' ) ); ?></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

	

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>
	<!--<p class="nocomments"><?php _e( '&nbsp;', 'avali' ); ?></p>-->
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

<?php                           
	$commenter = wp_get_current_commenter();
	
	if ( is_user_logged_in() )
		$email_author = get_the_author_meta('user_email');
	else
		$email_author = $commenter['comment_author_email'];

	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$url_avatar = get_template_directory_uri() . '/images/noavatar.png';
	$fields =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name','avali' ) . '</label> ' . 
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email','avali' ) . '</label> ' . 
		            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website','avali' ) . '</label>' .
		            '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);
	
	//$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );
	$comment_args = array(
		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">'.__( 'Your comment', 'avali' ).'</label><textarea id="comment" name="comment" cols="45" rows="8"></textarea></p><div class="clear"></div>',
		'must_log_in'          => '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'avali' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'avali' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ) . '</p>',
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
		'id_form'              => 'commentform',
		'id_submit'            => 'button',
		'title_reply'          => __( 'Leave a <span>Reply</span>', 'avali' ),
		'title_reply_to'       => __( 'Leave a <span>Reply</span> to %s', 'avali' ),
		'cancel_reply_link'    => __( 'Cancel reply', 'avali' ),
		'label_submit'         => __( 'Post Comment', 'avali' ),
	);
	
	comment_form( $comment_args ); 
?>

</div><!-- #comments -->
