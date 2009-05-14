<?php
/**
* Smarty PHPunit tests for stream resources
* 
* @package PHPunit
* @author Uwe Tews 
*/


/**
* class for stream resource tests
*/
class StreamResourceTests extends PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $this->smarty = Smarty::instance();
        SmartyTests::init();
        $this->smarty->security_policy->streams = array('global');
        $this->smarty->assign('foo', 'bar');
        stream_wrapper_register("global", "ResourceStream")
        or die("Failed to register protocol");
        $fp = fopen("global://mytest", "r+");
        fwrite($fp, 'hello world {$foo}');
        fclose($fp);
    } 

    public function tearDown()
    {
        stream_wrapper_unregister("global");
    } 

    public static function isRunnable()
    {
        return true;
    } 

    /**
    * test getTemplateFilepath
    */
    public function testGetTemplateFilepath()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertEquals('global://mytest', $tpl->getTemplateFilepath());
    } 
    /**
    * test getTemplateTimestamp
    */
    public function testGetTemplateTimestamp()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getTemplateTimestamp());
    } 
    /**
    * test getTemplateSource
    */
    public function testGetTemplateSource()
    {
        $tpl = $this->smarty->createTemplate('global:mytest', $this->smarty);
        $this->assertEquals('hello world {$foo}', $tpl->getTemplateSource());
    } 
    /**
    * test usesCompiler
    */
    public function testUsesCompiler()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertTrue($tpl->usesCompiler());
    } 
    /**
    * test isEvaluated
    */
    public function testIsEvaluated()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertTrue($tpl->isEvaluated());
    } 
    /**
    * test mustCompile
    */
    public function testMustCompile()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertTrue($tpl->mustCompile());
    } 
    /**
    * test getCompiledFilepath
    */
    public function testGetCompiledFilepath()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getCompiledFilepath());
    } 
    /**
    * test getCompiledTimestamp
    */
    public function testGetCompiledTimestamp()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getCompiledTimestamp());
    } 
    /**
    * test getCompiledTemplate
    */
    public function testGetCompiledTemplate()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $result = $tpl->getCompiledTemplate();
        $this->assertContains('hello world', $result);
        $this->assertContains('<?php /* Smarty version ', $result);
    } 
    /**
    * test getCachedFilepath
    */
    public function testGetCachedFilepath()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getCachedFilepath());
    } 
    /**
    * test getCachedTimestamp
    */
    public function testGetCachedTimestamp()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getCachedTimestamp());
    } 
    /**
    * test getCachedContent
    */
    public function testGetCachedContent()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->getCachedContent());
    } 
    /**
    * test writeCachedContent
    */
    public function testWriteCachedContent()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->writeCachedContent());
    } 
    /**
    * test isCached
    */
    public function testIsCached()
    {
        $tpl = $this->smarty->createTemplate('global:mytest');
        $this->assertFalse($tpl->isCached());
    } 
    /**
    * test getRenderedTemplate
    */
    public function testGetRenderedTemplate()
    {
        $tpl = $this->smarty->createTemplate('global:mytest', $this->smarty);
        $this->assertEquals('hello world bar', $tpl->getRenderedTemplate());
    } 
    /**
    * test that no complied template and cache file was produced
    */
    public function testNoFiles()
    {
        $this->smarty->caching = true;
        $this->smarty->caching_lifetime = 20;
        $this->smarty->clear_compiled_tpl();
        $this->smarty->clear_all_cache();
        $tpl = $this->smarty->createTemplate('global:mytest', $this->smarty);
        $this->assertEquals('hello world bar', $this->smarty->fetch($tpl));
        $this->assertEquals(0, $this->smarty->clear_all_cache());
        $this->assertEquals(0, $this->smarty->clear_compiled_tpl());
    } 
    /**
    * test $smarty->is_cached
    */
    public function testSmartyIsCached()
    {
        $this->smarty->caching = true;
        $this->smarty->caching_lifetime = 20;
        $tpl = $this->smarty->createTemplate('global:mytest', $this->smarty);
        $this->assertEquals('hello world bar', $this->smarty->fetch($tpl));
        $this->assertFalse($this->smarty->is_cached($tpl));
    } 
} 

class ResourceStream {
    private $position;
    private $varname;
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url["host"];
        $this->position = 0;
        return true;
    } 
    public function stream_read($count)
    {
        $p = &$this->position;
        $ret = substr($GLOBALS[$this->varname], $p, $count);
        $p += strlen($ret);
        return $ret;
    } 
    public function stream_write($data)
    {
        $v = &$GLOBALS[$this->varname];
        $l = strlen($data);
        $p = &$this->position;
        $v = substr($v, 0, $p) . $data . substr($v, $p += $l);
        return $l;
    } 
    public function stream_tell()
    {
        return $this->position;
    } 
    public function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    } 
    public function stream_seek($offset, $whence)
    {
        $l = strlen(&$GLOBALS[$this->varname]);
        $p = &$this->position;
        switch ($whence) {
            case SEEK_SET: $newPos = $offset;
                break;
            case SEEK_CUR: $newPos = $p + $offset;
                break;
            case SEEK_END: $newPos = $l + $offset;
                break;
            default: return false;
        } 
        $ret = ($newPos >= 0 && $newPos <= $l);
        if ($ret) $p = $newPos;
        return $ret;
    } 
} 

?>