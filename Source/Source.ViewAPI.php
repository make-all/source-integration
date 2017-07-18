<?php

# Copyright (c) 2012 John Reese
# Licensed under the MIT license

/**
 * Display a list of changeset objects in tabular format.
 * Assumes that a table with four columns has already been defined.
 * @param array Changeset objects
 * @param array Repository objects
 */
function Source_View_Changesets( $p_changesets, $p_repos=null, $p_show_repos=true ) {
	if ( !is_array( $p_changesets ) ) {
		return;
	}

	if ( is_null( $p_repos ) || !is_array( $p_repos ) ) {
		$t_repos = SourceRepo::load_by_changesets( $p_changesets );
	} else {
		$t_repos = $p_repos;
	}

	$t_use_porting = config_get( 'plugin_Source_enable_porting' );

	foreach( $p_changesets as $t_changeset ) {
		$t_repo = $t_repos[ $t_changeset->repo_id ];
		$t_vcs = SourceVCS::repo( $t_repo );

		$t_changeset->load_files();

		$t_author = Source_View_Author( $t_changeset, false );
		$t_committer = Source_View_Committer( $t_changeset, false );
		?>

<tr class="row-1">
<td class="category" width="25%" rowspan="<?php echo count( $t_changeset->files ) + 1 ?>">
	<a name="changeset<?php echo $t_changeset->id ?>"><?php echo string_display(
		( $p_show_repos ? $t_repo->name . ': ' : '' ) .
		$t_vcs->show_changeset( $t_repo, $t_changeset )
		) ?></a>
	<br/><span class="small"><?php echo plugin_lang_get( 'timestamp', 'Source' ), ': ', string_display_line( $t_changeset->timestamp ) ?></span>
	<br/><span class="small"><?php echo plugin_lang_get( 'author', 'Source' ), ': ', $t_author ?></span>
	<?php if ( $t_committer && $t_committer != $t_author ) { ?><br/><span class="small"><?php echo plugin_lang_get( 'committer', 'Source' ), ': ', $t_committer ?></span><?php } ?>
	<?php if ( $t_use_porting ) { ?>
	<br/><span class="small"><?php echo plugin_lang_get( 'ported', 'Source' ), ': ',
		( $t_changeset->ported ? string_display_line( $t_changeset->ported ) :
			( is_null( $t_changeset->ported ) ? plugin_lang_get( 'pending', 'Source' ) : plugin_lang_get( 'na', 'Source' ) ) ) ?></span>
	<?php } ?>
	<br/><span class="small-links">
		<?php
		print_bracket_link( plugin_page( 'view', false, 'Source' ) . '&id=' . $t_changeset->id, plugin_lang_get( 'details', 'Source' ) );
		if ( $t_url = $t_vcs->url_changeset( $t_repo, $t_changeset ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'diff', 'Source' ) );
		}
		?>
</td>
<td colspan="3"><?php
	# The commit message is manually transformed (adding href, bug and bugnote
	# links + nl2br) instead of calling string_display_links(), which avoids
	# unwanted html tags processing by the MantisCoreFormatting plugin.
	# Rationale: commit messages being plain text, any html they may contain
	# should not be considered as formatting and must be displayed as-is.
	echo string_nl2br(
			string_process_bugnote_link(
				string_process_bug_link(
					string_insert_hrefs(
						string_html_specialchars( $t_changeset->message )
		) ) ) );
?></td>
</tr>

		<?php foreach ( $t_changeset->files as $t_file ) { ?>
<tr class="row-2">
<td class="small mono" colspan="2"><?php echo string_display_line( $t_vcs->show_file( $t_repo, $t_changeset, $t_file ) ) ?></td>
<td class="center" width="12%"><span class="small-links">
		<?php
		if ( $t_url = $t_vcs->url_diff( $t_repo, $t_changeset, $t_file ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'diff', 'Source' ) );
		}
		if ( $t_url = $t_vcs->url_file( $t_repo, $t_changeset, $t_file ) ) {
			print_bracket_link( $t_url, plugin_lang_get( 'file', 'Source' ) );
		}
		?>
</span></td>
</tr>
		<?php } ?>
<tr class="spacer"></tr>
		<?php
	}
}

/**
 * Display the author information for a changeset.
 * @param object Changeset object
 * @param boolean Echo information
 */
function Source_View_Author( $p_changeset, $p_echo=true ) {
	$t_author_name = !is_blank( $p_changeset->author ) ? string_display_line( $p_changeset->author ) : false;
	$t_author_email = !is_blank( $p_changeset->author_email ) ? string_display_line( $p_changeset->author_email ) : false;
	$t_author_username = $p_changeset->user_id > 0 ? prepare_user_name( $p_changeset->user_id ) : false;

	if ( $t_author_username ) {
		$t_output =  $t_author_username;

	} else if ( $t_author_name ) {
		$t_output =  $t_author_name;

	} else {
		$t_output =  $t_author_email;
	}

	if ( $p_echo ) {
		echo $t_output;
	} else {
		return $t_output;
	}
}

/**
 * Display the committer information for a changeset.
 * @param object Changeset object
 * @param boolean Echo information
 */
function Source_View_Committer( $p_changeset, $p_echo=true ) {
	$t_committer_name = !is_blank( $p_changeset->committer ) ? string_display_line( $p_changeset->committer ) : false;
	$t_committer_email = !is_blank( $p_changeset->committer_email ) ? string_display_line( $p_changeset->committer_email ) : false;
	$t_committer_username = $p_changeset->committer_id > 0 ? prepare_user_name( $p_changeset->committer_id ) : false;

	if ( $t_committer_username ) {
		$t_output =  $t_committer_username;

	} else if ( $t_committer_name ) {
		$t_output =  $t_committer_name;

	} else {
		$t_output =  $t_committer_email;
	}

	if ( $p_echo ) {
		echo $t_output;
	} else {
		return $t_output;
	}
}

/**
 * Display pagination links for changesets
 * @param string $p_link       URL to target page
 * @param int    $p_count      Total number of changesets
 * @param int    $p_current    Current page number
 * @param int    $p_perpage    Number of changesets per page
 */
function Source_View_Pagination( $p_link, $p_current, $p_count, $p_perpage = 25 ) {
	if( $p_count > $p_perpage ) {

		$t_pages = ceil( $p_count / $p_perpage );
		$t_block = max( 5, min( round( $t_pages / 10, -1 ), ceil( $t_pages / 6 ) ) );
		$t_page_set = array();

		$p_link .= '&offset=';

		$t_page_link = function( $p_page, $p_text = null ) use( $p_current, $p_link ) {
			if( is_null( $p_text ) ) {
				$p_text = $p_page;
			}
			if( is_null( $p_page ) ) {
				return '...';
			} elseif( $p_page == $p_current ) {
				return "<strong>$p_page</strong>";
			} else {
				return sprintf( '<a href="%s">%s</a>', $p_link . $p_page, $p_text );
			}
		};

		if( $t_pages > 15 ) {
			$t_used_page = false;
			$t_pages_per_block = 3;
			for( $i = 1; $i <= $t_pages; $i++ ) {
				if( $i <= $t_pages_per_block
				 || $i > $t_pages - $t_pages_per_block
				 || ( $i >= $p_current - $t_pages_per_block && $i <= $p_current + $t_pages_per_block )
				 || $i % $t_block == 0)
				{
					$t_page_set[] = $i;
					$t_used_page = true;
				} else if( $t_used_page ) {
					$t_page_set[] = null;
					$t_used_page = false;
				}
			}

		} else {
			$t_page_set = range( 1, $t_pages );
		}

		if( $p_current > 1 ) {
			echo $t_page_link( 1, lang_get( 'first' ) ), '&nbsp;&nbsp;';
			echo $t_page_link( $p_current - 1, lang_get( 'prev' ) ), '&nbsp;&nbsp;';
		}

		$t_page_set = array_map( $t_page_link, $t_page_set );
		echo join( ' ', $t_page_set );

		if( $p_current < $t_pages ) {
			echo '&nbsp;&nbsp;', $t_page_link( $p_current + 1, lang_get( 'next' ) );
			echo '&nbsp;&nbsp;', $t_page_link( $t_pages, lang_get( 'last' ) );
		}
	}
}
