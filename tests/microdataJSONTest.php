<?php
/**
 *
 */

include_once 'libraries/microdataJSON.php';

/**
 * Test class for MicrodataJSON.
 *
 * @since  1.0
 */
class MicrodataJSONTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The default fallback Type
	 *
	 * @var string
	 */
	protected $defaultType = 'Thing';

	/**
	 * Tested class handler
	 *
	 * @var object
	 */
	protected $handler;

	/**
	 * Test setup
	 *
	 * @return	void
	 */
	public function setUp()
	{
		$this->handler = new MicrodataJSON;
	}

	/**
	 * Test the default settings
	 *
	 * @return	void
	 */
	public function testDefaults()
	{
		$this->handler = new MicrodataJSON;

		// Test that the default Type is Thing
		$this->assertEquals($this->handler->getType(), $this->defaultType);

		$this->assertClassHasAttribute('types', 'MicrodataJSON');
	}

	/**
	 * Test the setType() function
	 *
	 * @return	void
	 */
	public function testSetType()
	{
		$this->handler->setType('Article');

		// Test if the current Type is Article
		$this->assertEquals($this->handler->getType(), 'Article');

		// Test if the Type fallbacks to Thing Type
		$this->handler->setType('TypeThatDoesNotExist');
		$this->assertEquals($this->handler->getType(), $this->defaultType);
	}

	/**
	 * Test the fallback() function
	 *
	 * @return	void
	 */
	public function testFallback()
	{
		// Test fallback values
		$this->handler->fallback('Article', 'articleBody');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertEquals($this->handler->getFallbackProperty(), 'articleBody');

		// Test if Fallback Property fallbacks isn't available in the Type
		$this->handler->fallback('Article', 'anUnanvailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertNull($this->handler->getFallbackProperty());

		// Test if Fallback Type fallbacks to Thing Type
		$this->handler->fallback('anUnanvailableType', 'anUnanvailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Thing');
		$this->assertNull($this->handler->getFallbackProperty());
	}

	/**
	 * Test the display() function
	 *
	 * @return	void
	 */
	public function testDisplay()
	{
		// Setup
		$content = 'anything';

		// Test display() with all null params
		$this->handler = new MicrodataJSON;

		$this->assertEquals($this->handler->display(), '');

		// Test if the params are reseted after display()
		$this->handler->setType('Article')
			->content($content)
			->property('name')
			->fallback('Thing', 'url')
			->display();

		$this->assertNull($this->handler->getFallbackProperty());
		$this->assertNull($this->handler->getFallbackType());
		$this->assertNull($this->handler->getProperty());
		$this->assertEmpty($this->handler->getContent());

		// Test for a simple display
		$responce = $this->handler
			->property('url')
			->display();

		$this->assertEquals($responce, "itemprop='url'");

		// Test for a simple display with content
		$responce = $this->handler
			->property('url')
			->content($content)
			->display();

		$this->assertEquals($responce, "<span itemprop='url'>$content</span>");

		// Test for a simple nested display
		$responce = $this->handler
			->property('author')
			->display();

		$this->assertEquals(
			$responce,
			"itemprop='author' itemscope itemtype='https://schema.org/Organization'"
		);

		// Test for a nested display with content
		$responce = $this->handler
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$responce,
			"<span itemprop='author' itemscope itemtype='https://schema.org/Organization'>$content</span>"
		);

		// Test for a meta display without content
		$responce = $this->handler
			->property('datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"itemprop='datePublished'"
		);

		// Test for a meta display with content
		$responce = $this->handler
			->property('datePublished')
			->content($content)
			->display();

		$this->assertEquals(
			$responce,
			"<meta itemprop='datePublished' content='$content'/>"
		);

		// Test if the MicrodataJSON is disabled
		$responce = $this->handler
			->content($content)
			->fallback('Article', 'about')
			->property('datePublished')
			->enable(false)
			->display();

		$this->assertEquals($responce, $content);
	}

	/**
	 * Test the display() function when fallbacks
	 *
	 * @return	void
	 */
	public function testDisplayFallbacks()
	{
		// Setup
		$this->handler->setType('Article');
		$content = 'anything';

		// Test without content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$responce,
			"itemscope itemtype='https://schema.org/Article' itemprop='about'"
		);

		// Test wit content if fallbacks, the Property isn't available in the current Type
		$responce = $this->handler
			->content($content)
			->property('anUnanvailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$responce,
			"<span itemscope itemtype='https://schema.org/Article' itemprop='about'>$content</span>"
		);

		// Test if fallbacks, the Property isn't available in the current and fallback Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'anUnanvailableProperty')
			->display();

		$this->assertEquals($responce, "");

		// Test with content if fallbacks, the Property isn't available in the current and fallback Type
		$responce = $this->handler
			->content($content)
			->property('anUnanvailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"<meta itemscope itemtype='https://schema.org/Article' itemprop='datePublished' content='$content'/>"
		);

		// Test withtout content if fallbacks, the Property isn't available in the current and fallback Type
		$responce = $this->handler
			->property('anUnanvailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$responce,
			"itemscope itemtype='https://schema.org/Article' itemprop='datePublished'"
		);
	}

	/**
	 * Test the display() function, all display types
	 *
	 * @return	void
	 */
	public function testDisplayTypes()
	{
		// Setup
		$type = 'Article';
		$content = 'microdata';
		$property = 'datePublished';

		$microdata = $this->handler;
		$microdata->enable(true)->setType($type);

		// Display Type: Inline
		$responce = $microdata->content($content)
			->property($property)
			->display('inline');

		$this->assertEquals(
			$responce,
			"itemprop='$property'"
		);

		// Display Type: div
		$responce = $microdata->content($content)
			->property($property)
			->display('div');

		$this->assertEquals(
			$responce,
			"<div itemprop='$property'>$content</div>"
		);

		// Display Type: div without $content
		$responce = $microdata->property($property)
			->display('div');

		$this->assertEquals(
			$responce,
			"<div itemprop='$property'></div>"
		);

		// Display Type: span
		$responce = $microdata->content($content)
			->property($property)
			->display('span');

		$this->assertEquals(
			$responce,
			"<span itemprop='$property'>$content</span>"
		);

		// Display Type: span without $content
		$responce = $microdata
			->property($property)
			->display('span');

		$this->assertEquals(
			$responce,
			"<span itemprop='$property'></span>"
		);

		// Display Type: meta
		$responce = $microdata->content($content)
			->property($property)
			->display('meta');

		$this->assertEquals(
			$responce,
			"<meta itemprop='$property' content='$content'/>"
		);

		// Display Type: meta without $content
		$responce = $microdata
		->property($property)
		->display('meta');

		$this->assertEquals(
			$responce,
			"<meta itemprop='$property' content=''/>"
		);
	}

	/**
	 * Test the isTypeAvailabe() function
	 *
	 * @return	void
	 */
	public function testIsTypeAvailable()
	{
		// Test if the method return true with an available Type
		$this->assertTrue(
			$this->handler->isTypeAvailable('Article')
		);

		// Test if the method return false with an unavailable Type
		$this->assertFalse(
			$this->handler->isTypeAvailable('SomethingThatDoesNotExist')
		);
	}

	/**
	 * Test the isPropertyInType() function
	 *
	 * @return	void
	 */
	public function testIsPropertyInType()
	{
		// Setup
		$type = 'Article';

		// Test a Property that is available in the Type
		$this->assertTrue(
			$this->handler->isPropertyInType($type, 'articleBody')
		);

		// Test an inherit Property that is available in the Type
		$this->assertTrue(
			$this->handler->isPropertyInType($type, 'about')
		);

		// Test a Property that is unavailable in the Type
		$this->assertFalse(
			$this->handler->isPropertyInType($type, 'aPropertyThatDoesNotExist')
		);

		// Test a Property in an unanvailable Type
		$this->assertFalse(
			$this->handler->isPropertyInType('aTypeThatDoesNotExist', 'aPropertyThatDoesNotExist')
		);
	}

	/**
	 * Test the expectedDisplayType() function
	 *
	 * @return	void
	 */
	public function testExpectedDisplayType()
	{
		// Setup
		$type = 'Article';
		$method = self::getMethod('getExpectedDisplayType');
		$obj = new MicrodataJSON();

		// Test if Display Type is 'normal'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'articleBody')),
			'normal'
		);

		// Test if Display Type is 'nested'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'about')),
			'nested'
		);

		// Test if Display Type is 'meta'
		$this->assertEquals(
			$method->invokeArgs($obj, array($type, 'datePublished')),
			'meta'
		);
	}

	/**
	 * Test the displayScope() function
	 *
	 * @return	void
	 */
	public function testDisplayScope()
	{
		// Setup
		$type = 'Article';
		$this->handler->setType($type)
			->enable(true);

		// Test a displayScope() when microdata are enabled
		$this->assertEquals(
			$this->handler->displayScope(),
			"itemscope itemtype='https://schema.org/$type'"
		);

		// Test a displayScope() when microdata are disabled
		$this->assertEquals(
			$this->handler->displayScope(),
			"itemscope itemtype='https://schema.org/$type'"
		);
	}

	/**
	 * Test the getTypes() function
	 *
	 * @return	void
	 */
	public function testGetAllTypes()
	{
		$responce = $this->handler->getTypes();

		$this->assertGreaterThan(500, count($responce));
		$this->assertNotEmpty($responce);
		$this->assertTrue(in_array('Thing', $responce));
	}

	public function testHtmlMeta()
	{
		$scope = 'Article';
		$content = 'microdata';
		$property = 'datePublished';

		// Test with all params
		$this->assertEquals(
			MicrodataJSON::htmlMeta($content, $property, $scope),
			"<meta itemscope itemtype='https://schema.org/$scope' itemprop='$property' content='$content'/>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			MicrodataJSON::htmlMeta($content, $property, $scope, true),
			"<meta itemprop='$property' itemscope itemtype='https://schema.org/$scope' content='$content'/>"
		);

		// Test without the $scope
		$this->assertEquals(
			MicrodataJSON::htmlMeta($content, $property),
			"<meta itemprop='$property' content='$content'/>"
		);
	}

	/**
	 * Test the htmlDiv() function
	 *
	 * @return	void
	 */
	public function testHtmlDiv()
	{
		// Setup
		$scope = 'Article';
		$content = 'microdata';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			MicrodataJSON::htmlDiv($content, $property, $scope),
			"<div itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</div>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			MicrodataJSON::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test without the $scope
		$this->assertEquals(
			MicrodataJSON::htmlDiv($content, $property),
			"<div itemprop='$property'>$content</div>"
		);

		// Test without the $property
		$this->assertEquals(
			MicrodataJSON::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test withoud the $scope, $property
		$this->assertEquals(
			MicrodataJSON::htmlDiv($content),
			"<div>$content</div>"
		);
	}

	/**
	 * Test the htmlSpan() function
	 *
	 * @return	void
	 */
	public function testHtmlSpan()
	{
		// Setup
		$scope = 'Article';
		$content = 'microdata';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			MicrodataJSON::htmlSpan($content, $property, $scope),
			"<span itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</span>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			MicrodataJSON::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test without the $scope
		$this->assertEquals(
			MicrodataJSON::htmlSpan($content, $property),
			"<span itemprop='$property'>$content</span>"
		);

		// Test without the $property
		$this->assertEquals(
			MicrodataJSON::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test withoud the $scope, $property
		$this->assertEquals(
			MicrodataJSON::htmlSpan($content),
			"<span>$content</span>"
		);
	}

	/**
	 * A function helper that allows to test protected functions
	 *
	 * @param  string  $name
	 */
	protected static function getMethod($name)
	{
		$class = new ReflectionClass('MicrodataJSON');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}
}
