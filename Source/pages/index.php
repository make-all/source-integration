<?php

# Copyright (c) 2012 John Reese
# Licensed under the MIT license

access_ensure_global_level( plugin_config_get( 'view_threshold' ) );
$t_can_manage = access_has_global_level( plugin_config_get( 'manage_threshold' ) );

$t_show_stats = plugin_config_get( 'show_repo_stats' );

$t_repos = SourceRepo::load_all();

html_page_top1( plugin_lang_get( 'title' ) );
html_page_top2();
?>

<br>

<div class="form-container">

	<h2><?php echo plugin_lang_get( 'repositories' ) ?></h2>

	<div class="right">
		<?php
		print_bracket_link( plugin_page( 'search_page' ), plugin_lang_get( 'search' ) );
		if( $t_can_manage ) {
			print_bracket_link( plugin_page( 'manage_config_page' ), plugin_lang_get( 'configuration' ) );
		}
		?>

	</div>

	<table>
		<thead>
			<tr class="row-category">
				<th width="30%"><?php echo plugin_lang_get( 'repository' ) ?></th>
				<th width="15%"><?php echo plugin_lang_get( 'type' ) ?></th>
<?php
	if( $t_show_stats ) {
?>
				<th width="10%"><?php echo plugin_lang_get( 'changesets' ) ?></th>
				<th width="10%"><?php echo plugin_lang_get( 'files' ) ?></th>
				<th width="10%"><?php echo plugin_lang_get( 'issues' ) ?></th>
<?php
	}
?>
				<th width="25%"><?php echo plugin_lang_get( 'actions' ) ?></th>
			</tr>
		</thead>

		<tbody>
<?php
	foreach( $t_repos as $t_repo ) {
?>
			<tr>
				<td><?php echo string_display( $t_repo->name ) ?></td>
				<td class="center"><?php echo string_display( SourceType( $t_repo->type ) ) ?></td>
<?php
		if( $t_show_stats ) {
			$t_stats = $t_repo->stats();
?>
				<td class="right"><?php echo $t_stats['changesets'] ?></td>
				<td class="right"><?php echo $t_stats['files'] ?></td>
				<td class="right"><?php echo $t_stats['bugs'] ?></td>
<?php
		}
?>
				<td class="center"><?php
					print_bracket_link( plugin_page( 'list' ) . '&id=' . $t_repo->id, plugin_lang_get( 'changesets' ) );
					if( $t_can_manage ) {
						# Import repositories can be deleted from here
						if( preg_match( '/^Import \d+-\d+\d+/', $t_repo->name ) ) {
							print_bracket_link(
								plugin_page( 'repo_delete' ) . '&id=' . $t_repo->id
									. form_security_param( 'plugin_Source_repo_delete' ),
								plugin_lang_get( 'delete' )
							);
						}
						print_bracket_link(
							plugin_page( 'repo_manage_page' ) . '&id=' . $t_repo->id,
							plugin_lang_get( 'manage' )
						);
					}
				?></td>
			</tr>
<?php
	} # foreach
?>
		</tbody>
	</table>
</div>

<?php
	if( $t_can_manage ) {
?>

<div class="width60 form-container">
<form action="<?php echo plugin_page( 'repo_create' ) ?>" method="post">
	<fieldset class="has-required">

		<legend><?php echo plugin_lang_get( 'create_repository' ) ?></legend>

		<?php echo form_security_field( 'plugin_Source_repo_create' ) ?>

		<div class="field-container">
			<label class="required" for="repo_name">
				<span><?php echo plugin_lang_get( 'name' ) ?></span>
			</label>
			<span class="input">
				<input id="repo_name" name="repo_name" type="text" maxlength="128" size="40" />
			</span>
			<span class="label-style"></span>
		</div>

		<div class="field-container">
			<label class="required" for="repo_type">
				<span><?php echo plugin_lang_get( 'type' ) ?></span>
			</label>
			<span class="select">
				<select name="repo_type">
					<option value=""><?php echo plugin_lang_get( 'select_one' ) ?></option>
<?php
		foreach( SourceTypes() as $t_type => $t_type_name ) {
?>
					<option value="<?php echo $t_type ?>"><?php echo
						string_display( $t_type_name )
					?></option>
<?php
		}
?>
				</select>
			</span>
			<span class="label-style"></span>
		</div>

		<div class="submit-button">
			<input class="button" type="submit" value="<?php echo plugin_lang_get( 'create_repository' ) ?>" />
		</div>

	</fieldset>
</form>
</div>

<?php
	} # if( $t_can_manage )
?>

<?php
html_page_bottom1( __FILE__ );
