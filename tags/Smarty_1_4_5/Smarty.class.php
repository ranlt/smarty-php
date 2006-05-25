<?php
/*
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 * Author:      Monte Ohrt <monte@ispi.net>
 *              Andrei Zmievski <andrei@php.net>
 *
 * Version:             1.4.5
 * Copyright:           2001 ispi of Lincoln, Inc.
 *              
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to smarty-subscribe@lists.ispi.net
 *
 * You may contact the authors of Smarty by e-mail at:
 * monte@ispi.net
 * andrei@php.net
 *
 * Or, write to:
 * Monte Ohrt
 * CTO, ispi
 * 237 S. 70th suite 220
 * Lincoln, NE 68510
 *
 * The latest version of Smarty can be obtained from:
 * http://www.phpinsider.com/
 *
 */

// set SMARTY_DIR to absolute path to Smarty library files.
// if not defined, include_path will be used.

if(!defined("SMARTY_DIR")) {
define("SMARTY_DIR","");
}

require_once SMARTY_DIR.'Smarty.addons.php';

define("SMARTY_PHP_PASSTHRU",0);
define("SMARTY_PHP_QUOTE",1);
define("SMARTY_PHP_REMOVE",2);
define("SMARTY_PHP_ALLOW",3);

class Smarty
{

/**************************************************************************/
/* BEGIN SMARTY CONFIGURATION SECTION                                     */    
/* Set the following config variables to your liking.                     */
/**************************************************************************/

    // public vars
    var $template_dir    =  './templates';     // name of directory for templates  
    var $compile_dir     =  './templates_c';   // name of directory for compiled templates 
    var $config_dir      =  './configs';       // directory where config files are located

    var $debugging       =  false;             // enable debugging console true/false
    var $debug_tpl       =  'file:debug.tpl';  // path to debug console template
	var $debugging_ctrl	 =  'NONE';			   // Possible values:
											   // NONE - no debug control allowed
											   // URL - enable debugging when keyword
											   //       SMARTY_DEBUG is found in $QUERY_STRING
	        
    var $global_assign   =  array( 'HTTP_SERVER_VARS' => array( 'SCRIPT_NAME' )
                                 );     // variables from the GLOBALS array
                                        // that are implicitly assigned
                                        // to all templates
    var $undefined       =  null;       // undefined variables in $global_assign will be
                                        // created with this value
    var $compile_check   =  true;       // whether to check for compiling step or not:
                                        // This is generally set to false once the
                                        // application is entered into production and
                                        // initially compiled. Leave set to true
                                        // during development. true/false default true.

    var $force_compile   =  false;      // force templates to compile every time.
                                        // if caching is on, a cached file will
                                        // override compile_check and force_compile.
                                        // true/false. default false.

    var $caching         =  false;      // whether to use caching or not. true/false
    var $cache_dir       =  './cache';  // name of directory for template cache
    var $cache_lifetime  =  3600;       // number of seconds cached content will persist.
                                        // 0 = never expires. default is one hour (3600)
    var $insert_tag_check    = true;    // if you have caching turned on and you
                                        // don't use {insert} tags anywhere
                                        // in your templates, set this to false.
                                        // this will tell Smarty not to look for
                                        // insert tags and speed up cached page
                                        // fetches.

    var $tpl_file_ext    =  '.tpl';     // template file extention

    var $php_handling    =  SMARTY_PHP_PASSTHRU;
                                        // how smarty handles php tags in the templates
                                        // possible values:
                                        // SMARTY_PHP_PASSTHRU -> echo tags as is
                                        // SMARTY_PHP_QUOTE    -> escape tags as entities
                                        // SMARTY_PHP_REMOVE   -> remove php tags
                                        // SMARTY_PHP_ALLOW    -> execute php tags
                                        // default: SMARTY_PHP_PASSTHRU
    
    
    var $security       =   false;      // enable template security (default false)
    var $secure_dir     =   array('./templates'); // array of directories considered secure
    var $secure_ext     =   array('.tpl'); // array of file extentions considered secure
    var $security_settings  = array(
                                    'PHP_HANDLING'    => false,
                                    'IF_FUNCS'        => array('array', 'list',
                                                               'isset', 'empty',
                                                               'count', 'sizeof',
                                                               'in_array','is_array'),
                                    'INCLUDE_ANY'     => false,
                                    'PHP_TAGS'        => false,
                                    'MODIFIER_FUNCS'  => array('count')
                                   );

    var $left_delimiter  =  '{';        // template tag delimiters.
    var $right_delimiter =  '}';
        
    var $compiler_funcs  =  array(
                                 );

    var $custom_funcs    =  array(  'html_options'      => 'smarty_func_html_options',
                                    'html_select_date'  => 'smarty_func_html_select_date',
                                    'html_select_time'  => 'smarty_func_html_select_time',
                                    'math'              => 'smarty_func_math',
                                    'fetch'             => 'smarty_func_fetch',
                                    'counter'           => 'smarty_func_counter',
                                    'assign'            => 'smarty_func_assign',
                                    'popup_init'      	=> 'smarty_func_overlib_init',
                                    'popup'           	=> 'smarty_func_overlib',
                                    'assign_debug_info' => 'smarty_func_assign_debug_info'
                                 );
    
    var $custom_mods     =  array(  'lower'             => 'strtolower',
                                    'upper'             => 'strtoupper',
                                    'capitalize'        => 'ucwords',
                                    'escape'            => 'smarty_mod_escape',
                                    'truncate'          => 'smarty_mod_truncate',
                                    'spacify'           => 'smarty_mod_spacify',
                                    'date_format'       => 'smarty_mod_date_format',
                                    'string_format'     => 'smarty_mod_string_format',
                                    'replace'           => 'smarty_mod_replace',
                                    'regex_replace'     => 'smarty_mod_regex_replace',
                                    'strip_tags'        => 'smarty_mod_strip_tags',
                                    'default'           => 'smarty_mod_default',
                                    'count_characters'  => 'smarty_mod_count_characters',
                                    'count_words'       => 'smarty_mod_count_words',
                                    'count_sentences'   => 'smarty_mod_count_sentences',
                                    'count_paragraphs'  => 'smarty_mod_count_paragraphs'
                                 );
                                 
    var $show_info_header      =   false;     // display HTML info header at top of page output
    var $show_info_include     =   false;      // display HTML comments at top & bottom of
                                              // each included template

    var $compiler_class        =   'Smarty_Compiler'; // the compiler class used by
                                                      // Smarty to compile templates
    var $resource_funcs        =  array();  // what functions resource handlers are mapped to
    var $prefilter_funcs       =  array();  // what functions templates are prefiltered through
                                            // before being compiled

    var $request_vars_order    = "EGPCS";   // the order in which request variables are
                                            // registered, similar to variables_order
                                            // in php.ini

/**************************************************************************/
/* END SMARTY CONFIGURATION SECTION                                       */    
/* There should be no need to touch anything below this line.             */
/**************************************************************************/
 
    // internal vars
    var $_error_msg            =   false;      // error messages. true/false
    var $_tpl_vars             =   array();    // where assigned template vars are kept
    var $_sections             =   array();    // keeps track of sections
    var $_conf_obj             =   null;       // configuration object
    var $_config               =   array();    // loaded configuration settings
    var $_smarty_md5           =   'f8d698aea36fcbead2b9d5359ffca76f'; // md5 checksum of the string 'Smarty'    
    var $_version              =   '1.4.5';    // Smarty version number    
    var $_extract              =   false;      // flag for custom functions
    var $_included_tpls        =   array();    // list of run-time included templates
    var $_inclusion_depth      =   0;          // current template inclusion depth
    var $_compile_id           =   null;       // for different compiled templates
    var $_smarty_debug_id      =   'SMARTY_DEBUG'; // id in query string to turn on debug mode
    

/*======================================================================*\
    Function: Smarty
    Purpose:  Constructor
\*======================================================================*/
    function Smarty()
    {		
        foreach ($this->global_assign as $key => $var_name) {
            if (is_array($var_name)) {
                foreach ($var_name as $var) {
                    if (isset($GLOBALS[$key][$var])) {
                        $this->assign($var, $GLOBALS[$key][$var]);
                    } else {
                        $this->assign($var, $this->undefined);
                    }
                }
            } else {
                if (isset($GLOBALS[$var_name])) {
                    $this->assign($var_name, $GLOBALS[$var_name]);
                } else {
                    $this->assign($var_name, $this->undefined);
                }
            }
        }
    }


/*======================================================================*\
    Function:   assign()
    Purpose:    assigns values to template variables
\*======================================================================*/
    function assign($tpl_var, $value = NULL)
    {
        if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $val) {
                if (!empty($key))
                    $this->_tpl_vars[$key] = $val;
            }
        } else {
            if (!empty($tpl_var) && isset($value))
                $this->_tpl_vars[$tpl_var] = $value;
        }
        $this->_extract = true;
    }

    
/*======================================================================*\
    Function: append
    Purpose:  appens values to template variables
\*======================================================================*/
    function append($tpl_var, $value = NULL)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $key => $val) {
                if (!empty($key)) {
                    if (!is_array($this->_tpl_vars[$key]))
                        settype($this->_tpl_vars[$key], 'array');
                    $this->_tpl_vars[$key][] = $val;
                }
            }
        } else {
            if (!empty($tpl_var) && isset($value)) {
                if (!is_array($this->_tpl_vars[$tpl_var]))
                    settype($this->_tpl_vars[$tpl_var], 'array');
                $this->_tpl_vars[$tpl_var][] = $value;
            }
        }
        $this->_extract = true;
    }


/*======================================================================*\
    Function:   clear_assign()
    Purpose:    clear the given assigned template variable.
\*======================================================================*/
    function clear_assign($tpl_var)
    {
        if (is_array($tpl_var))
            foreach ($tpl_var as $curr_var)
                unset($this->_tpl_vars[$curr_var]);
        else
            unset($this->_tpl_vars[$tpl_var]);
    }

    
/*======================================================================*\
    Function: register_function
    Purpose:  Registers custom function to be used in templates
\*======================================================================*/
    function register_function($function, $function_impl)
    {
        $this->custom_funcs[$function] = $function_impl;
    }

/*======================================================================*\
    Function: unregister_function
    Purpose:  Unregisters custom function
\*======================================================================*/
    function unregister_function($function)
    {
        unset($this->custom_funcs[$function]);
    }

/*======================================================================*\
    Function: register_compiler_function
    Purpose:  Registers compiler function
\*======================================================================*/
    function register_compiler_function($function, $function_impl)
    {
        $this->compiler_funcs[$function] = $function_impl;
    }

/*======================================================================*\
    Function: unregister_compiler_function
    Purpose:  Unregisters compiler function
\*======================================================================*/
    function unregister_compiler_function($function)
    {
        unset($this->compiler_funcs[$function]);
    }
        
/*======================================================================*\
    Function: register_modifier
    Purpose:  Registers modifier to be used in templates
\*======================================================================*/
    function register_modifier($modifier, $modifier_impl)
    {
        $this->custom_mods[$modifier] = $modifier_impl;
    }

/*======================================================================*\
    Function: unregister_modifier
    Purpose:  Unregisters modifier
\*======================================================================*/
    function unregister_modifier($modifier)
    {
        unset($this->custom_mods[$modifier]);
    }

/*======================================================================*\
    Function: register_resource
    Purpose:  Registers a resource to fetch a template
\*======================================================================*/
    function register_resource($name, $function_name)
    {
        $this->resource_funcs[$name] = $function_name;
    }

/*======================================================================*\
    Function: unregister_resource
    Purpose:  Unregisters a resource
\*======================================================================*/
    function unregister_resource($name)
    {
        unset($this->resource_funcs[$name]);
    }

/*======================================================================*\
    Function: register_prefilter
    Purpose:  Registers a prefilter function to apply
              to a template before compiling
\*======================================================================*/
    function register_prefilter($function_name)
    {
        $this->prefilter_funcs[] = $function_name;
    }

/*======================================================================*\
    Function: unregister_prefilter
    Purpose:  Unregisters a prefilter
\*======================================================================*/
    function unregister_prefilter($function_name)
    {
        $tmp_array = array();
        foreach($this->prefilter_funcs as $curr_func) {
            if ($curr_func != $function_name) {
                $tmp_array[] = $curr_func;
            }
        }
        $this->prefilter_funcs = $tmp_array;
    }
    
/*======================================================================*\
    Function:   clear_cache()
    Purpose:    clear cached content for the given template and cache id
\*======================================================================*/
    function clear_cache($tpl_file = null, $cache_id = null, $compile_id)
    {
        return $this->_rm_auto($this->cache_dir, $tpl_file, $compile_id . $cache_id);
    }
    
    
/*======================================================================*\
    Function:   clear_all_cache()
    Purpose:    clear the entire contents of cache (all templates)
\*======================================================================*/
    function clear_all_cache()
    {
        return $this->_rm_auto($this->cache_dir);
    }


/*======================================================================*\
    Function:   is_cached()
    Purpose:    test to see if valid cache exists for this template
\*======================================================================*/
    function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        if (!$this->caching)
            return false;

        $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $compile_id . $cache_id); 

        if (file_exists($cache_file) &&
            ($this->cache_lifetime == 0 ||
             (time() - filemtime($cache_file) <= $this->cache_lifetime)))
            return true;
        else
            return false;
        
    }
    
        
/*======================================================================*\
    Function:   clear_all_assign()
    Purpose:    clear all the assigned template variables.
\*======================================================================*/
    function clear_all_assign()
    {
        $this->_tpl_vars = array();
    }

/*======================================================================*\
    Function:   clear_compiled_tpl()
    Purpose:    clears compiled version of specified template resource,
                or all compiled template files if one is not specified.
                This function is for advanced use only, not normally needed.
\*======================================================================*/
    function clear_compile_tpl($tpl_file = null, $compile_id = null)
    {
        return $this->_rm_auto($this->compile_dir, $tpl_file, $compile_id);
    }

/*======================================================================*\
    Function: get_template_vars
    Purpose:  Returns an array containing template variables
\*======================================================================*/
    function &get_template_vars()
    {
        return $this->_tpl_vars;
    }


/*======================================================================*\
    Function:   display()
    Purpose:    executes & displays the template results
\*======================================================================*/
    function display($tpl_file, $cache_id = null, $compile_id = null)
    {
        $this->fetch($tpl_file, $cache_id, $compile_id, true);
    }
        
/*======================================================================*\
    Function:   fetch()
    Purpose:    executes & returns or displays the template results
\*======================================================================*/
    function fetch($tpl_file, $cache_id = null, $compile_id = null, $display = false)
    {
        global $HTTP_SERVER_VARS, $QUERY_STRING, $HTTP_COOKIE_VARS;

        $this->_compile_id = $compile_id;
        $this->_inclusion_depth = 0;
        $this->_included_tpls = array();

        $this->_included_tpls[] = array('type' => 'template',
										'filename'  => $tpl_file,
                                        'depth'    => 0);
        
        if ($this->caching) {
            $cache_file = $this->_get_auto_filename($this->cache_dir, $tpl_file, $compile_id . $cache_id);
            
            if (file_exists($cache_file) &&
                ($this->cache_lifetime == 0 ||
                 (time() - filemtime($cache_file) <= $this->cache_lifetime))) {
                $results = $this->_read_file($cache_file);
                if ($this->insert_tag_check) {
                    $results = $this->_process_cached_inserts($results);
                }
                if ($display) {
                    echo $results; return;
                } else {
                    return $results;
                }
            }
        }

        $this->_assign_smarty_interface();

        if ($this->_conf_obj === null) {
            /* Prepare the configuration object. */
            if (!class_exists('Config_File'))
                include_once SMARTY_DIR.'Config_File.class.php';
            $this->_conf_obj = new Config_File($this->config_dir);
        } else
            $this->_conf_obj->set_path($this->config_dir);

        extract($this->_tpl_vars);

        /* Initialize config array. */
        $this->_config = array(array());

        if ($this->show_info_header) {
            $info_header = '<!-- Smarty '.$this->_version.' '.strftime("%Y-%m-%d %H:%M:%S %Z").' -->'."\n\n";
        } else {
            $info_header = '';          
        }
        
        // if we just need to display the results, don't perform output
        // buffering - for speed
        if ($display && !$this->caching) {
            echo $info_header;
            if($this->_process_template($tpl_file, $compile_path))
			{
            	if ($this->show_info_include) {
                	echo "\n<!-- SMARTY_BEGIN: ".$tpl_file." -->\n";
            	}
            	include($compile_path);
            	if ($this->show_info_include) {
                	echo "\n<!-- SMARTY_END: ".$tpl_file." -->\n";
            	}
			}
        } else {
            ob_start();
            echo $info_header;
            if($this->_process_template($tpl_file, $compile_path))
			{
            	if ($this->show_info_include) {
                	echo "\n<!-- SMARTY_BEGIN: ".$tpl_file." -->\n";
            	}
            	include($compile_path);
            	if ($this->show_info_include) {
                	echo "\n<!-- SMARTY_END: ".$tpl_file." -->\n";
            	}
			}
            $results = ob_get_contents();
            ob_end_clean();
        }

        if ($this->caching) {
            $this->_write_file($cache_file, $results, true);
            $results = $this->_process_cached_inserts($results);
        }
		
        if ($display) {
            if (isset($results)) { echo $results; }
            if ($this->debugging || ($this->debugging_ctrl == 'URL' && (!empty($QUERY_STRING) && strstr($QUERY_STRING,$this->_smarty_debug_id)))) { echo $this->_generate_debug_output(); }
            return;
        } else {
            if (isset($results)) { return $results; }
        }
    }

    
/*======================================================================*\
    Function: _assign_smarty_interface
    Purpose:  assign $smarty interface variable 
\*======================================================================*/
    function _assign_smarty_interface()
    { 
        $egpcs  = array('e'        => 'env',
                        'g'        => 'get',
                        'p'        => 'post',
                        'c'        => 'cookie',
                        's'        => 'server');
        $globals_map = array('get'      => 'HTTP_GET_VARS',
                             'post'     => 'HTTP_POST_VARS',
                             'cookies'  => 'HTTP_COOKIE_VARS',
                             'session'  => 'HTTP_SESSION_VARS',
                             'server'   => 'HTTP_SERVER_VARS',
                             'env'      => 'HTTP_ENV_VARS');

        $smarty  = array('request'  => array());

        foreach ($globals_map as $key => $array) {
            $smarty[$key] = isset($GLOBALS[$array]) ? $GLOBALS[$array] : array();
        }

        foreach (preg_split('!!', strtolower($this->request_vars_order)) as $c) {
            if (isset($egpcs[$c])) {
                $smarty['request'] = array_merge($smarty['request'], $smarty[$egpcs[$c]]);
            }
        }
        $smarty['request'] = array_merge($smarty['request'], $smarty['session']);

		$smarty['now'] = time();
		
        $this->assign('smarty', $smarty);
    }


/*======================================================================*\
    Function:   _generate_debug_output()
    Purpose:    generate debug output
\*======================================================================*/

function _generate_debug_output() {
	// we must force compile the debug template in case the environment
	// changed between separate applications.
    ob_start();
	$force_compile_orig = $this->force_compile;
	$this->force_compile = true;
    if($this->_process_template($this->debug_tpl, $compile_path))
	{
		if ($this->show_info_include) {
		  echo "\n<!-- SMARTY_BEGIN: ".$this->debug_tpl." -->\n";
		}
		include($compile_path);
		if ($this->show_info_include) {
		  echo "\n<!-- SMARTY_END: ".$this->debug_tpl." -->\n";
		}
	}   
    $results = ob_get_contents();
	$this->force_compile = $force_compile_orig;
    ob_end_clean();
    return $results;
}   
     
/*======================================================================*\
    Function:   _process_template()
    Purpose:    
\*======================================================================*/
    function _process_template($tpl_file, &$compile_path)
    {
        // get path to where compiled template is (to be) saved
        $compile_path = $this->_get_auto_filename($this->compile_dir, $tpl_file, $this->_compile_id);

        // test if template needs to be compiled
        if (!$this->force_compile && $this->_compiled_template_exists($compile_path)) {
            if (!$this->compile_check) {
                // no need to check if the template needs recompiled
                return true;
            } else { 
                // get template source and timestamp
                if(!$this->_fetch_template_source($tpl_file, $template_source, $template_timestamp)) {
					return false;
				}
                if ($template_timestamp <= $this->_fetch_compiled_template_timestamp($compile_path)) {
                    // template not expired, no recompile
                    return true;
                } else {
                    // compile template
                    $this->_compile_template($tpl_file, $template_source, $template_compiled);
                    $this->_write_compiled_template($compile_path, $template_compiled);
                    return true;
                }
            }
        } else {
            // compiled template does not exist, or forced compile
			if(!$this->_fetch_template_source($tpl_file, $template_source, $template_timestamp)) {
				return false;
			}
            $this->_compile_template($tpl_file, $template_source, $template_compiled);
            $this->_write_compiled_template($compile_path, $template_compiled);
            return true;
        }
    }
    
/*======================================================================*\
    Function:   _compiled_template_exists
    Purpose:    
\*======================================================================*/
    function _compiled_template_exists($include_path)
    {
        // everything is in $compile_dir
        return file_exists($include_path);
    }    

/*======================================================================*\
    Function:   _fetch_compiled_template_timestamp
    Purpose:    
\*======================================================================*/
    function _fetch_compiled_template_timestamp($include_path)
    {
        // everything is in $compile_dir
        return filemtime($include_path);
    }    

/*======================================================================*\
    Function:   _write_compiled_template
    Purpose:    
\*======================================================================*/
    function _write_compiled_template($compile_path, $template_compiled)
    {
        // we save everything into $compile_dir
        $this->_write_file($compile_path, $template_compiled, true);
        return true;
    }    

/*======================================================================*\
    Function:   _fetch_template_source()
    Purpose:    fetch the template source and timestamp
\*======================================================================*/
    function _fetch_template_source($tpl_path, &$template_source, &$template_timestamp)
    {       
        // split tpl_path by the first colon
        $tpl_path_parts = explode(':', $tpl_path, 2);
        
        if (count($tpl_path_parts) == 1) {
            // no resource type, treat as type "file"
            $resource_type = 'file';
            $resource_name = $tpl_path_parts[0];
        } else {
            $resource_type = $tpl_path_parts[0];   
            $resource_name = $tpl_path_parts[1];
        }
        
        switch ($resource_type) {
            case 'file':
                if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/",$resource_name)) {
                    // relative pathname to $template_dir
                    $resource_name = $this->template_dir.'/'.$resource_name;   
                }
                if (file_exists($resource_name) && is_readable($resource_name)) {
                    $template_source = $this->_read_file($resource_name);
                    $template_timestamp = filemtime($resource_name);
                } else {
                    $this->_trigger_error_msg("unable to read template resource: \"$tpl_path\"");
                    return false;
                }
                // if security is on, make sure template comes from a $secure_dir
				
                if ($this->security && !$this->security_settings['INCLUDE_ANY']) {
                    $resource_is_secure = false;
                    foreach ($this->secure_dir as $curr_dir) {
                        if (substr(realpath($resource_name),0,strlen(realpath($curr_dir))) == realpath($curr_dir)) {
                            $resource_is_secure = true;
                            break;
                        }
                    }
                    if (!$resource_is_secure) {
                        $this->_trigger_error_msg("(secure mode) including \"$resource_name\" is not allowed");
                        return false;
                    }               
                }
                break;
            default:
                if (isset($this->resource_funcs[$resource_type])) {
                    $funcname = $this->resource_funcs[$resource_type];
                    if (function_exists($funcname)) {
                        // call the function to fetch the template
                        $funcname($resource_name, $template_source, $template_timestamp);
                        return true;
                    } else {
                        $this->_trigger_error_msg("resource function: \"$funcname\" does not exist for resource type: \"$resource_type\".");
                        return false;
                    }
                } else {
                    $this->_trigger_error_msg("unknown resource type: \"$resource_type\". Register this resource first.");
                    return false;
                }
                break;
        }

        return true;
    }

        
/*======================================================================*\
    Function:   _compile_template()
    Purpose:    called to compile the templates
\*======================================================================*/
    function _compile_template($tpl_file, $template_source, &$template_compiled)
    {
        include_once SMARTY_DIR.$this->compiler_class . '.class.php';

        $smarty_compiler = new $this->compiler_class;

        $smarty_compiler->template_dir      = $this->template_dir;
        $smarty_compiler->compile_dir       = $this->compile_dir;
        $smarty_compiler->config_dir        = $this->config_dir;
        $smarty_compiler->force_compile     = $this->force_compile;
        $smarty_compiler->caching           = $this->caching;
        $smarty_compiler->php_handling      = $this->php_handling;
        $smarty_compiler->left_delimiter    = $this->left_delimiter;
        $smarty_compiler->right_delimiter   = $this->right_delimiter;
        $smarty_compiler->custom_funcs      = $this->custom_funcs;
        $smarty_compiler->custom_mods       = $this->custom_mods;
        $smarty_compiler->_version          = $this->_version;
        $smarty_compiler->prefilter_funcs   = $this->prefilter_funcs;
        $smarty_compiler->compiler_funcs    = $this->compiler_funcs;
        $smarty_compiler->security          = $this->security;
        $smarty_compiler->secure_dir        = $this->secure_dir;
        $smarty_compiler->secure_ext        = $this->secure_ext;
        $smarty_compiler->security_settings = $this->security_settings;

        if ($smarty_compiler->_compile_file($tpl_file, $template_source, $template_compiled))
            return true;
        else {
            $this->_trigger_error_msg($smarty_compiler->_error_msg);
            return false;
        }
    }

/*======================================================================*\
    Function:   _smarty_include()
    Purpose:    called for included templates
\*======================================================================*/
    function _smarty_include($_smarty_include_tpl_file, $_smarty_include_vars)
    {
        $this->_included_tpls[] = array('type' => 'template',
										'filename' => $_smarty_include_tpl_file,
                                        'depth'    => ++$this->_inclusion_depth);

        $this->_tpl_vars = array_merge($this->_tpl_vars, $_smarty_include_vars);
        extract($this->_tpl_vars);

        array_unshift($this->_config, $this->_config[0]);
 
        if ($this->_process_template($_smarty_include_tpl_file, $compile_path, $compile_id)) {
            if ($this->show_info_include) {
                echo "\n<!-- SMARTY_BEGIN: ".$_smarty_include_tpl_file." -->\n";
            }

            include($compile_path);

            if ($this->show_info_include) {
                echo "\n<!-- SMARTY_END: ".$_smarty_include_tpl_file." -->\n";
            }
        }

        array_shift($this->_config);
        $this->_inclusion_depth--;
    }
        
    
/*======================================================================*\
    Function: _config_load
    Purpose:  load configuration values
\*======================================================================*/
    function _config_load($file, $section, $scope)
    {
		$this->_included_tpls[] = array('type' => 'config',
								'filename' => $file,
                            	'depth'    => $this->_inclusion_depth);

		
        $this->_config[0] = array_merge($this->_config[0], $this->_conf_obj->get($file));
        if ($scope == 'parent') {
            if (count($this->_config) > 0)
                $this->_config[1] = array_merge($this->_config[1], $this->_conf_obj->get($file));
        } else if ($scope == 'global')
            for ($i = 1; $i < count($this->_config); $i++)
                $this->_config[$i] = array_merge($this->_config[$i], $this->_conf_obj->get($file));

        if (!empty($section)) {
            $this->_config[0] = array_merge($this->_config[0], $this->_conf_obj->get($file, $section));
            if ($scope == 'parent') {
                if (count($this->_config) > 0)
                    $this->_config[1] = array_merge($this->_config[1], $this->_conf_obj->get($file, $section));
            } else if ($scope == 'global')
                for ($i = 1; $i < count($this->_config); $i++)
                    $this->_config[$i] = array_merge($this->_config[$i], $this->_conf_obj->get($file, $section));
        }
    }


/*======================================================================*\
    Function: _process_cached_inserts
    Purpose:  Replace cached inserts with the actual results
\*======================================================================*/
    function _process_cached_inserts($results)
    {
        preg_match_all('!'.$this->_smarty_md5.'{insert_cache (.*)}'.$this->_smarty_md5.'!Uis',
                       $results, $match);
        list($cached_inserts, $insert_args) = $match;

        for ($i = 0; $i < count($cached_inserts); $i++) {

            $args = unserialize($insert_args[$i]);

            $name = $args['name'];
            unset($args['name']);

            $function_name = 'insert_' . $name;
            $replace = $function_name($args, $this);

            $results = str_replace($cached_inserts[$i], $replace, $results);
        }

        return $results;
    } 


/*======================================================================*\
    Function: _run_insert_handler
    Purpose:  Handle insert tags
\*======================================================================*/
function _run_insert_handler($args)
{
    if ($this->caching) {
        $arg_string = serialize($args);
        return $this->_smarty_md5."{insert_cache $arg_string}".$this->_smarty_md5;
    } else {
        $function_name = 'insert_'.$args['name'];
        return $function_name($args, $this);
    }
}


/*======================================================================*\
    Function: _run_mod_handler
    Purpose:  Handle modifiers
\*======================================================================*/
function _run_mod_handler()
{
    $args = func_get_args();
    list($func_name, $map_array) = array_splice($args, 0, 2);
    $var = $args[0];

    if ($map_array && is_array($var)) {
        foreach ($var as $key => $val) {
            $args[0] = $val;
            $var[$key] = call_user_func_array($func_name, $args);
        }
        return $var;
    } else {
        return call_user_func_array($func_name, $args);
    }
}

    
/*======================================================================*\
    Function: _dequote
    Purpose:  Remove starting and ending quotes from the string
\*======================================================================*/
    function _dequote($string)
    {
        if (($string{0} == "'" || $string{0} == '"') &&
            $string{strlen($string)-1} == $string{0})
            return substr($string, 1, -1);
        else
            return $string;
    }

    
/*======================================================================*\
    Function:   _read_file()
    Purpose:    read in a file
\*======================================================================*/
    function _read_file($filename)

    {
        if (!($fd = fopen($filename, 'r'))) {
            $this->_trigger_error_msg("problem reading '$filename.'");
            return false;
        }
        flock($fd, LOCK_SH);
        $contents = fread($fd, filesize($filename));
        fclose($fd);
        return $contents;
    }

/*======================================================================*\
    Function:   _write_file()
    Purpose:    write out a file
\*======================================================================*/
    function _write_file($filename, $contents, $create_dirs = false)
    {
        if ($create_dirs)
            $this->_create_dir_structure(dirname($filename));
        
        if (!($fd = fopen($filename, 'w'))) {
            $this->_trigger_error_msg("problem writing '$filename.'");
            return false;
        }
        
        // flock doesn't seem to work on several windows platforms (98, NT4, NT5, ?),
        // so we'll not use it at all in windows.
        
        if ( strtoupper(substr(PHP_OS,0,3)) == 'WIN' || (flock($fd, LOCK_EX)) ) { 
            fwrite( $fd, $contents );
            fclose($fd);
            chmod($filename,0644);
        }

        return true;
    }    

/*======================================================================*\
    Function: _get_auto_base
    Purpose:  Get a base name for automatic files creation
\*======================================================================*/
    function _get_auto_base($auto_base, $auto_source)
    {
        $source_md5 = md5($auto_source);

        $res = $auto_base . '/' . substr($source_md5, 0, 2) . '/' . $source_md5;

        return $res;
    }

/*======================================================================*\
    Function: _get_auto_filename
    Purpose:  get a concrete filename for automagically created content 
\*======================================================================*/
    function _get_auto_filename($auto_base, $auto_source, $auto_id = null)
    {
        $res = $this->_get_auto_base($auto_base, $auto_source) .
                '/' . md5($auto_id) . '.php';

        return $res;
    }

/*======================================================================*\
    Function: _rm_auto
    Purpose: delete an automagically created file by name and id 
\*======================================================================*/
    function _rm_auto($auto_base, $auto_source = null, $auto_id = null)
    {
        if (!is_dir($auto_base))
          return false;

        if (!isset($auto_source)) {
            $res = $this->_rmdir($auto_base, 0);
        } else {
            if (isset($auto_id)) {
                $tname = $this->_get_auto_filename($auto_base, $auto_source, $auto_id);
                $res = is_file($tname) && unlink( $tname);
            } else {
                $tname = $this->_get_auto_base($auto_base, $auto_source);
                $res = $this->_rmdir($tname);
            }
        }

        return $res;
    }

/*======================================================================*\
    Function: _rmdir
    Purpose: delete a dir recursively (level=0 -> keep root)
    WARNING: no security whatsoever!!
\*======================================================================*/
    function _rmdir($dirname, $level = 1)
    {
        $handle = opendir($dirname); 

        while ($entry = readdir($handle)) { 
            if ($entry != '.' && $entry != '..') { 
                if (is_dir($dirname . '/' . $entry)) { 
                    $this->_rmdir($dirname . '/' . $entry, $level + 1);
                } 
                else { 
                    unlink($dirname . '/' . $entry); 
                }
            }
        } 

        closedir($handle);

        if ($level)
            rmdir($dirname);

        return true;
    }

/*======================================================================*\
    Function: _create_dir_structure
    Purpose:  create full directory structure
\*======================================================================*/
    function _create_dir_structure($dir)
    {
        if (!file_exists($dir)) {
            $dir_parts = preg_split('!/+!', $dir, -1, PREG_SPLIT_NO_EMPTY);
            $new_dir = ($dir{0} == '/') ? '/' : '';
            foreach ($dir_parts as $dir_part) {
                $new_dir .= $dir_part;
                if (!file_exists($new_dir) && !mkdir($new_dir, 0701)) {
                    $this->_trigger_error_msg("problem creating directory \"$dir\"");
                    return false;               
                }
                $new_dir .= '/';
            }
        }
    }    
    
/*======================================================================*\
    Function:   quote_replace
    Purpose:    Quote subpattern references
\*======================================================================*/
    function quote_replace($string)
    {
        return preg_replace('![\\$]\d!', '\\\\\\0', $string);
    }


/*======================================================================*\
    Function: _trigger_error_msg
    Purpose:  trigger Smarty error
\*======================================================================*/
    function _trigger_error_msg($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Smarty error: $error_msg", $error_type);
    }

}

/* vim: set expandtab: */

?>