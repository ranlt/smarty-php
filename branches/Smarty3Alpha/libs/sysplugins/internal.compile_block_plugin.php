<?php
/**
* Smarty Internal Plugin Compile Block Plugin
* 
* Compiles code for the execution of block plugin
* 
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews 
*/
/**
* Smarty Internal Plugin Compile Block Plugin Class
*/
class Smarty_Internal_Compile_Block_Plugin extends Smarty_Internal_CompileBase {
    /**
    * Compiles code for the execution of block plugin
    * 
    * @param array $args array with attributes from parser
    * @param string $tag name of block function
    * @return string compiled code
    */
    public function compile($args, $tag)
    {
        if (strncmp($tag, 'end_', 4) != 0) {
            // opening tag of block plugin
            $this->required_attributes = array();
            $this->optional_attributes = array('_any'); 

            // check and get attributes
            $_attr = $this->_get_attributes($args);
            if ($_attr['nocache'] === 'true') {
                $this->compiler->_compiler_status->tag_nocache = true;
                unset($args['nocache']);
            } 
            // convert attributes into parameter array string
            $_paramsArray = array();
            foreach ($_attr as $_key => $_value) {
                $_paramsArray[] = "'$_key'=>$_value";
            } 
            $_params = 'array(' . implode(",", $_paramsArray) . ')'; 
            
            $this->_open_tag($tag, $_params);

            // compile code
            $output = '<?php $_block_repeat=true;$_smarty_tpl->smarty->block->' . $tag . '(' . $_params . ', null, $_smarty_tpl->smarty, $_block_repeat);while ($_block_repeat) { ob_start();?>';
        } else {
            // closing tag of block plugin
            $_params = $this->_close_tag(substr($tag,4));
            // This tag does create output
            $this->compiler->has_output = true;
            // compile code
            $output = '<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false; echo $_smarty_tpl->smarty->block->' . substr($tag,4) . '(' . $_params . ', $_block_content, $_smarty_tpl->smarty, $_block_repeat); }?>';
        } 
        return $output;
    } 
} 

?>