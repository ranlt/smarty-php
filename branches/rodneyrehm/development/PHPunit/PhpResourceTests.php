<?php
/**
* Smarty PHPunit tests for PHP resources
* 
* @package PHPunit
* @author Uwe Tews 
*/

/**
* class for PHP resource tests
*/
class PhpResourceTests extends PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
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
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertEquals('./templates/phphelloworld.php', str_replace('\\', '/', $tpl->source->filepath));
    } 
    /**
    * test getTemplateTimestamp
    */
    public function testGetTemplateTimestamp()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue(is_integer($tpl->source->timestamp));
        $this->assertEquals(10, strlen($tpl->source->timestamp));
    } 
    /**
    * test getTemplateSource
    *-/
    public function testGetTemplateSource()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $tpl->source->content);
    } 
    /**
    * test usesCompiler
    */
    public function testUsesCompiler()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($tpl->source->uncompiled);
    } 
    /**
    * test isEvaluated
    */
    public function testIsEvaluated()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->source->recompiled);
    } 
    /**
    * test getCompiledFilepath
    */
    public function testGetCompiledFilepath()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->compiled->filepath);
    } 
    /**
    * test getCompiledTimestamp
    */
    public function testGetCompiledTimestamp()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->compiled->timestamp);
    } 
    /**
    * test mustCompile
    */
    public function testMustCompile()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->mustCompile());
    } 
    /**
    * test getCompiledTemplate
    */
    public function testGetCompiledTemplate()
    {
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->getCompiledTemplate());
    } 
    /**
    * test getCachedFilepath if caching disabled
    */
    public function testGetCachedFilepathCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->cached->filepath);
    } 
    /**
    * test getCachedFilepath
    */
    public function testGetCachedFilepath()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $expected = './cache/' . sha1('./templates/phphelloworld.php') . '.phphelloworld.php.php';
        $this->assertEquals(realpath($expected), realpath($tpl->cached->filepath));
    } 
    /**
    * test create cache file used by the following tests
    */
    public function testCreateCacheFile()
    { 
        // create dummy cache file
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $this->smarty->fetch($tpl));
    } 
    /**
    * test getCachedTimestamp caching disabled
    */
    public function testGetCachedTimestampCachingDisabled()
    {
        $this->smarty->caching = false;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->cached->timestamp);
    } 
    /**
    * test getCachedTimestamp caching enabled
    */
    public function testGetCachedTimestamp()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue(is_integer($tpl->cached->timestamp));
        $this->assertEquals(10, strlen($tpl->cached->timestamp));
    } 
    /**
    * test getCachedContent caching disabled
    */
    public function testGetCachedContentCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->getCachedContent());
    } 
    /**
    * test getCachedContent
    */
    public function testGetCachedContent()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $tpl->getCachedContent());
    } 
    /**
    * test isCached
    */
    public function testIsCached()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($tpl->isCached());
        $this->assertEquals(null, $tpl->rendered_content);
    } 
    /**
    * test isCached caching disabled
    */
    public function testIsCachedCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($tpl->isCached());
    } 
    /**
    * test isCached on touched source
    */
    public function testIsCachedTouchedSource()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        sleep(1);
        touch ($tpl->source->filepath);
        $this->assertFalse($tpl->isCached());
    } 
    /**
    * test is cache file is written
    */
    public function testWriteCachedContent()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->smarty->fetch($tpl);
        $this->assertTrue(file_exists($tpl->cached->filepath));
    } 
    /**
    * test getRenderedTemplate
    */
    public function testGetRenderedTemplate()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertContains('php hello world', $tpl->getRenderedTemplate());
    } 
    /**
    * test $smarty->is_cached
    */
    public function testSmartyIsCachedPrepare()
    {
        $this->smarty->allow_php_templates = true; 
        // prepare files for next test
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000; 
        // clean up for next tests
        $this->smarty->clearAllCache();
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->smarty->fetch($tpl);
    } 
    public function testSmartyIsCached()
    {
        $this->smarty->allow_php_templates = true;
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertTrue($this->smarty->isCached($tpl));
        $this->assertEquals(null, $tpl->rendered_content);
    } 
    /**
    * test $smarty->is_cached  caching disabled
    */
    public function testSmartyIsCachedCachingDisabled()
    {
        $this->smarty->allow_php_templates = true;
        $tpl = $this->smarty->createTemplate('php:phphelloworld.php');
        $this->assertFalse($this->smarty->isCached($tpl));
    } 

    public function testGetTemplateFilepathName()
    {
        $this->smarty->template_dir['foo'] = './templates_2';
        $tpl = $this->smarty->createTemplate('php:[foo]helloworld.php');
        $this->assertEquals('./templates_2/helloworld.php', $tpl->source->filepath);
    }
    
    public function testGetCachedFilepathName()
    {
        $this->smarty->template_dir['foo'] = './templates_2';
        $this->smarty->caching = true;
        $this->smarty->cache_lifetime = 1000;
        $tpl = $this->smarty->createTemplate('php:[foo]helloworld.php');
	    $expected = './cache/'.sha1($this->smarty->template_dir['foo'].DS.'helloworld.php').'.helloworld.php.php';
        $this->assertEquals($expected, $tpl->cached->filepath);
    }
    
    /**
    * final cleanup
    */
    public function testFinalCleanup()
    {
        $this->smarty->clearAllCache();
    } 
} 

?>