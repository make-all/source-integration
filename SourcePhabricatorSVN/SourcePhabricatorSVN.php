<?php

# Copyright (c) 2012 John Reese, 2014 The Maker
#
# Based on SourceWebSVN, part of the Source Integration plugin for
# Mantis Bugtracker (https://github.com/mantisbt-plugins/source-integration)
# Modified by The Maker to use Phabricator as the Web UI for SVN.
#
# Depends on SourceSVN and Source plugins
# (from https://github.com/mantisbt-plugins/source-integration)
#
# Licensed under the MIT license

if ( false === include_once( config_get( 'plugin_path' ) . 'SourceSVN/SourceSVN.php' ) ) {
	return;
}

class SourcePhabricatorSVNPlugin extends SourceSVNPlugin {
	public function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );

		$this->version = '0.02';
		$this->requires = array(
			'MantisCore' => '1.3.0',
			'Source' => '0.16',
			'SourceSVN' => '0.16',
		);

		$this->author = 'The Maker';
		$this->contact = 'make-all@users.github.com';
		$this->url = 'https://github.com/make-all';
	}

	public $type = 'phabsvn';

	public function show_type() {
		return plugin_lang_get( 'svn' );
	}

	public function get_phabsvn_url( $p_repo ) {
		return isset( $p_repo->info['phabsvn_url'] )
			? $p_repo->info['phabsvn_url']
			: '';
	}

	public function get_phabsvn_name( $p_repo ) {
		return isset( $p_repo->info['phabsvn_name'] )
			? $p_repo->info['phabsvn_name']
			: '';
	}

	public function get_phabsvn_path( $p_repo ) {
		return isset( $p_repo->info['phabsvn_path'] )
			? $p_repo->info['phabsvn_path']
			: '';
	}

	/**
	 * Builds the Phabricator URL base string
	 * @param object $p_repo repository
	 * @param string $p_op optional Phabricator operation
	 * @param string $p_file optional filename (as absolute path from root)
	 * @param array $p_opts optional additional Phabricator URL parameters
	 * @return string Phabricator URL
	 */
    protected function url_base( $p_repo, $p_op = '/browse', $p_file = '' ) {
		$t_name = urlencode( $this->get_phabsvn_name( $p_repo ) );
		
		$t_url = $this->get_phabsvn_url( $p_repo ) . 'diffusion/' . $t_name;
		if( !is_blank( $p_file ) ) {
		    $t_url .= $p_op . $this->get_phabsvn_path( $p_repo ) . $p_file;
		}
		return $t_url;
	}

    public function url_changeset( $p_repo, $p_changeset ) {
        $t_name = urlencode( $this->get_phabsvn_name( $p_repo) );
        $t_url = $this->get_phabsvn_url( $p_repo ) . 'r' . $t_name . $p_changeset->revision;
        return $t_url;
    }

	public function url_repo( $p_repo, $p_changeset=null ) {
		if ( !is_null( $p_changeset ) ) {
                    return $this->url_changeset($p_repo, $p_changeset);
		}
		else {
		    return $this->url_base($p_repo);
        }
	}

	public function url_file( $p_repo, $p_changeset, $p_file ) {

        if (is_blank($p_file->filename)) {
            return $this->url_repo($p_repo, $p_changeset);
        }
        else {
            # if the file has been removed, it doesn't exist in current revision
            # so we generate a link to (current revision - 1)
            $t_revision = ($p_file->action == 'rm')
                ? $p_changeset->revision - 1
                : $p_changeset->revision;
            
            return $this->url_base( $p_repo, '/browse', $p_file->filename) . ';' . $t_revision;
        }
	}

	public function url_diff( $p_repo, $p_changeset, $p_file ) {
        return $this->url_base( $p_repo, '/change', $p_file->filename) . ';' . $p_changeset->revision;
	}

	public function update_repo_form( $p_repo ) {
		$t_url  = $this->get_phabsvn_url( $p_repo );
		$t_name = $this->get_phabsvn_name( $p_repo );
		$t_path = $this->get_phabsvn_path( $p_repo );

?>
<tr>
<td class="category"><?php echo plugin_lang_get( 'url' ) ?></td>
<td><input name="phabsvn_url" maxlength="250" size="40" value="<?php echo string_attribute( $t_url ) ?>"/></td>
</tr>
<tr>
<td class="category"><?php echo plugin_lang_get( 'name' ) ?></td>
<td><input name="phabsvn_name" maxlength="250" size="40" value="<?php echo string_attribute( $t_name ) ?>"/></td>
</tr>
<tr>
<td class="category"><?php echo plugin_lang_get( 'path' ) ?></td>
<td><input name="phabsvn_path" maxlength="250" size="40" value="<?php echo string_attribute( $t_path ) ?>"/></td>
</tr>
<?php

		return parent::update_repo_form( $p_repo );
	}

	public function update_repo( $p_repo ) {
		$p_repo->info['phabsvn_url'] = gpc_get_string( 'phabsvn_url' );
		$p_repo->info['phabsvn_name'] = gpc_get_string( 'phabsvn_name' );
		$p_repo->info['phabsvn_path'] = gpc_get_string( 'phabsvn_path' );

		return parent::update_repo( $p_repo );
	}
}
