<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * assemble filepath of requested plugin
 *
 * @param string $type
 * @param string $name
 * @return string|false
 */    
function smarty_core_assemble_plugin_filepath($params, &$this)
{

    $_plugin_filename = $params['type'] . '.' . $params['name'] . '.php';
		
    foreach ((array)$this->plugins_dir as $_plugin_dir) {

        $_plugin_filepath = $_plugin_dir . DIRECTORY_SEPARATOR . $_plugin_filename;
		
        // see if path is relative
        if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $_plugin_dir)) {
            $_relative_paths[] = $_plugin_dir;
            // relative path, see if it is in the SMARTY_DIR
            if (@is_readable(SMARTY_DIR . $_plugin_filepath)) {
                $_return = SMARTY_DIR . $_plugin_filepath;
            }
        }
        // try relative to cwd (or absolute)
        if (@is_readable($_plugin_filepath)) {
            $_return = $_plugin_filepath;
        }
    }

	if($_return === false) {
        // still not found, try PHP include_path
        if(isset($_relative_paths)) {
            foreach ((array)$_relative_paths as $_plugin_dir) {

                $_plugin_filepath = $_plugin_dir . DIRECTORY_SEPARATOR . $_plugin_filename;

				$_params = array('file_path' => $_plugin_filepath);
				require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.get_include_path.php');
            	if(smarty_core_get_include_path($_params, $this)) {				
					return $_params['new_file_path'];
                }
            }
        }
	}

    return $_return;
}	

/* vim: set expandtab: */

?>
