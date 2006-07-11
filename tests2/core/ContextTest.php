<?php

// pseudo class used in test
class TestSessionStorage extends AgaviStorage
{
	public function & read($key)
	{
		$ret = null;
		return $ret;
	}
	public function & remove($key)
	{
		$ret = null;
		return $ret;
	}
	public function shutdown()
	{
	}
	public function write($key, &$data)
	{
	}
}

class CTTestActionStack extends AgaviActionStack
{
}


class ContextTest extends AgaviTestCase 
{
	public function setup()
	{
		AgaviContext::getInstance()->initialize();
	}

	public function testGetDefaultContextInstance()
	{
		$default = AgaviConfig::get('core.default_context');
		
		$this->assertNotNull(AgaviContext::getInstance());
		$this->assertType('AgaviContext', AgaviContext::getInstance());
		$a = AgaviContext::getInstance();
		$b = AgaviContext::getInstance('stdctx');
		$c = AgaviContext::getInstance('StDcTX');
		$d = AgaviContext::getInstance($default);
		$this->assertReference($a, $b);
		$this->assertReference($a, $c);
		$this->assertReference($b, $c);
		$this->assertReference($a, $d);
		
		$e = AgaviContext::getInstance('test1'); // different animal
		$this->assertNotSame($a, $e);
		$f = AgaviContext::getInstance(); // we should be getting the default (test) not the last (test1)
		$this->assertReference($a, $f);
		$this->assertNotSame($e, $f);
		
		$this->assertType('AgaviActionStack', AgaviContext::getInstance()->getController()->getActionStack());
		$this->assertType('AgaviWebRequest', AgaviContext::getInstance()->getRequest());
		$this->assertType('TestRouting', AgaviContext::getInstance()->getRouting());
	}

	public function testGetAlternateContextInstance()
	{
		$this->assertNotNull(AgaviContext::getInstance());
		$this->assertType('AgaviContext', AgaviContext::getInstance());
		$this->assertNotNull(AgaviContext::getInstance('test1'));
		$this->assertType('AgaviContext', AgaviContext::getInstance('test1'));
		$a = AgaviContext::getInstance('test1');
		$b = AgaviContext::getInstance();
		$this->assertNotSame($a, $b);
		
	}

	public function testCanReinitializeContextWithOverrides()
	{
		$context = AgaviContext::getInstance();
		$context->initialize('test1');
		$this->assertType('TestSessionStorage', $context->getStorage());
		$this->assertType('CTTestActionStack', $context->getController()->getActionStack());
	}


	public function testGetGlobalModel()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('SampleModel', $ctx->getModel('Sample'));
		$this->assertType('SingletonSampleModel', $ctx->getModel('SingletonSample'));
		$firstSingleton = $ctx->getModel('SingletonSample');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $ctx->getModel('SingletonSample');
		$this->assertReference($firstSingleton, $secondSingleton);
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testGetModel()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('Test_TestModel', $ctx->getModel('Test', 'Test'));
		$this->assertType('Test2Model', $ctx->getModel('Test2', 'Test'));
		$this->assertType('Test_SingletonTestModel', $ctx->getModel('SingletonTest', 'Test'));
		$this->assertType('SingletonTest2Model', $ctx->getModel('SingletonTest2', 'Test'));
		$firstSingleton = $ctx->getModel('SingletonTest', 'Test');
		$firstSingleton->setFoo('bar');
		$secondSingleton = $ctx->getModel('SingletonTest', 'Test');
		$this->assertReference($firstSingleton, $secondSingleton);
		$this->assertEquals($firstSingleton->getFoo(), $secondSingleton->getFoo());
	}

	public function testGetFactoryInfo()
	{
		$ctx = AgaviContext::getInstance();
		$info_ex = array('class' => 'TestResponse', 'parameters' => array());
		$this->assertSame($info_ex, $ctx->getFactoryInfo('response'));
	}

	public function testGetController()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('AgaviWebController', $ctx->getController());
	}

	public function testGetDatabaseManager()
	{
		$this->assertNull(AgaviContext::getInstance()->getDatabaseManager());

		// clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', 'stdctx'));
		AgaviConfig::set('core.use_database', true);
		AgaviContext::getInstance()->initialize();
		$this->assertType('AgaviDatabaseManager', AgaviContext::getInstance()->getDatabaseManager());
		AgaviConfig::set('core.use_database', false);
	}

	public function testGetLoggerManager()
	{
		$this->assertNull(AgaviContext::getInstance()->getLoggerManager());

		// clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', 'stdctx'));
		AgaviConfig::set('core.use_logging', true);
		AgaviContext::getInstance()->initialize();
		$this->assertType('AgaviLoggerManager', AgaviContext::getInstance()->getLoggerManager());
		AgaviConfig::set('core.use_logging', false);
	}

	public function testGetName()
	{
		$this->assertSame('stdctx', AgaviContext::getInstance()->getName());
		$this->assertSame('test1', AgaviContext::getInstance('test1')->getName());
	}

	public function testGetRequest()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('AgaviRequest', $ctx->getRequest());
	}

	public function testGetRouting()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('AgaviRouting', $ctx->getRouting());
	}

	public function testGetStorage()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('AgaviStorage', $ctx->getStorage());
	}

	public function testGetUser()
	{
		$this->assertType('AgaviUser', AgaviContext::getInstance()->getUser());

		// clear the factories cache (needed since we are changing settings which are evaluated at compile time)
		unlink(AgaviConfigCache::getCacheName(AgaviConfig::get('core.config_dir') . '/factories.xml', 'stdctx'));
		AgaviConfig::set('core.use_security', true);
		AgaviContext::getInstance()->initialize();
		$this->assertType('AgaviSecurityUser', AgaviContext::getInstance()->getUser());
	}

	public function testGetValidatorManager()
	{
		$ctx = AgaviContext::getInstance();
		$this->assertType('AgaviValidatorManager', $ctx->getValidatorManager());
	}
}


?>