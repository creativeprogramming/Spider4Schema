<?php
/**
 *
 */

/**
 * Class for interacting with Microdata semantics.
 *
 * @since  1.0
 */
class MicrodataJSON
{
	/**
	 * Array with all available Types and Properties
	 *
	 * @var     array
	 * @since   1.0
	 */
	protected static $types = null;

	/**
	 * The Schema.org Type
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $type;

	/**
	 * The Property
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $property = null;

	/**
	 * The Human value or Machine value
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $content = '';

	/**
	 * Fallback Type
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $fallbackType = null;

	/**
	 * Fallback Property
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $fallbackProperty = null;

	/**
	 * Used to check if a Fallback must be used
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $fallback = false;

	/**
	 * Used to check if the Microdata semantics output are enabled or disabled
	 *
	 * @var 	boolean
	 * @since	1.0
	 */
	protected $enabled = true;

	/**
	 * Initialize the class and setup the default Type
	 *
	 * @param   string  $type  Optional, Fallback to Thing Type
	 */
	public function __construct($type = '')
	{
		// Load the JSON file
		if (!self::$types)
		{
			// TODO cache somewhere
			self::$types = json_decode(file_get_contents("libraries/types.json"), true);
		}

		// Fallback to Thing Type
		if (!$type)
		{
			$type = 'Thing';
		}

		$this->setType($type);
	}

	/**
	 * Enable or Disable Microdata semantics output
	 *
	 * @param   boolean  $flag  Enable or disable
	 *
	 * @return	object
	 */
	public function enable($flag = true)
	{
		$this->enabled = (boolean) $flag;

		return $this;
	}

	/**
	 * Set a new Schema.org Type
	 *
	 * @param   string  $type  The Type to be setup
	 *
	 * @return	object
	 */
	public function setType($type)
	{
		// Sanitize the Type
		$this->type = self::sanitizeType($type);

		// If the given Type isn't available, fallback to Thing
		if ( !self::isTypeAvailable($this->type) )
		{
			$this->type	= 'Thing';
		}

		return $this;
	}

	/**
	 * Return the current Type name
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Setup a Property
	 *
	 * @param   string  $property  The Property
	 *
	 * @return	object
	 */
	public function property($property)
	{
		// Sanitize the Property
		$property = self::sanitizeProperty($property);

		// Control if the Property exist in the given Type and setup it, if not leave NULL
		if ( self::isPropertyInType($this->type, $property) )
		{
			$this->property = $property;
		}
		else
		{
			$this->fallback = true;
		}

		return $this;
	}

	/**
	 * Return the property variable
	 *
	 * @return	string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Setup a Text value or Content value for the Microdata
	 *
	 * @param   string  $value  The human value or marchine value to be used
	 *
	 * @return	object
	 */
	public function content($value)
	{
		$this->content = $value;

		return $this;
	}

	/**
	 * Return the content variable
	 *
	 * @return	string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Setup a Fallback Type and Property
	 *
	 * @param   string  $type      The Fallback Type
	 * @param   string  $property  The Fallback Property
	 *
	 * @return	object
	 */
	public function fallback($type, $property)
	{
		// Sanitize the Type
		$this->fallbackType = self::sanitizeType($type);

		// If the given Type isn't available, fallback to Thing
		if (!self::isTypeAvailable($this->fallbackType))
		{
			$this->fallbackType = 'Thing';
		}

		// Control if the Property exist in the given Type and setup it, if not leave NULL
		if (self::isPropertyInType($this->fallbackType, $property))
		{
			$this->fallbackProperty = $property;
		}
		else
		{
			$this->fallbackProperty = null;
		}

		return $this;
	}

	/**
	 * Return the fallbackType variable
	 *
	 * @return	string
	 */
	public function getFallbackType()
	{
		return $this->fallbackType;
	}

	/**
	 * Return the fallbackProperty variable
	 *
	 * @return	string
	 */
	public function getFallbackProperty()
	{
		return $this->fallbackProperty;
	}

	/**
	 * This function handle the logic of a Microdata intelligent display.
	 * Check if the Type, Property are available, if not check for a Fallback,
	 * then reset all params for the next use and
	 * return the Microdata HTML
	 *
	 * @param   string  $displayType  Optional, 'inline', available ['inline'|'span'|'div'|meta]
	 *
	 * @return	string
	 */
	public function display($displayType = '')
	{
		// Initialize the HTML to output
		$html = $this->content;

		// Control if the Microdata output is enabled, otherwise return the content or empty string
		if (!$this->enabled)
		{
			return $html;
		}

		// If the property is wrong for the current Type check if Fallback available, otherwise return empty HTML
		if ($this->property && !$this->fallback)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = self::htmlSpan($this->content, $this->property);
						break;

					case 'div':
						$html = self::htmlDiv($this->content, $this->property);
						break;

					case 'meta':
						$html = self::htmlMeta($this->content, $this->property);
						break;

					default:
						// Default $displayType = 'inline'
						$html = self::htmlProperty($this->property);
						break;
				}
			}
			else
			{
				/* Process and return the HTML in an automatic way,
				 * with the Property expected Types an display the Microdata in the right way,
				* check if the Property is normal, nested or must be rendered in a metadata tag */
				switch (self::getExpectedDisplayType($this->type, $this->property))
				{
					case 'nested':
						// Retrive the expected nested Type of the Property
						$nestedType = self::getExpectedTypes($this->type, $this->property);
						$nestedType = $nestedType[0];

						/* Check if a Content is available,
						 * otherwise Fallback to an 'inline' display type */
						if ($this->content)
						{
							$html = self::htmlSpan(
								$this->content,
								$this->property,
								$nestedType,
								true
							);
						}
						else
						{
							$html = self::htmlProperty($this->property)
							. " " . self::htmlScope($nestedType);
						}
						break;

					case 'meta':
						/* Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
						if ($this->content)
						{
							$html = self::htmlMeta($this->content, $this->property);
						}
						else
						{
							$html = self::htmlProperty($this->property);
						}
						break;

					default:
						/* Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
						if ($this->content)
						{
							$html = self::htmlSpan($this->content, $this->property);
						}
						else
						{
							$html = self::htmlProperty($this->property);
						}
						break;
				}
			}
		}
		elseif ($this->fallbackProperty)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = self::htmlSpan($this->content, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'div':
						$html = self::htmlDiv($this->content, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'meta':
						$html = self::htmlMeta($this->content, $this->fallbackProperty, $this->fallbackType);
						break;

					default:
						// Default $displayType = 'inline'
						$html = self::htmlScope($type::scope())
						. ' ' . self::htmlProperty($this->fallbackProperty);
						break;
				}
			}
			else
			{
				/* Process and return the HTML in an automatic way,
				 * with the Property expected Types an display the Microdata in the right way,
				* check if the Property is nested or must be rendered in a metadata tag */
				switch (self::getExpectedDisplayType($this->fallbackType, $this->fallbackProperty))
				{
					case 'meta':
						/* Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
						if ($this->content)
						{
							$html = self::htmlMeta($this->content, $this->fallbackProperty, $this->fallbackType);
						}
						else
						{
							$html = self::htmlScope($this->fallbackType)
							. ' ' . self::htmlProperty($this->fallbackProperty);
						}
						break;

					default:
						/* Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
						if ($this->content)
						{
							$html = self::htmlSpan($this->content, $this->fallbackProperty, $this->fallbackType);
						}
						else
						{
							$html = self::htmlScope($this->fallbackType)
							. ' ' . self::htmlProperty($this->fallbackProperty);
						}
						break;
				}
			}
		}

		// Reset params
		$this->content			= '';
		$this->property			= null;
		$this->fallbackProperty	= null;
		$this->fallbackType		= null;
		$this->fallback			= false;

		return $html;
	}

	/**
	 * Return the HTML of the current Scope
	 *
	 * @return	string
	 */
	public function displayScope()
	{
		// Control if the Microdata output is enabled, otherwise return the content or empty string
		if (!$this->enabled)
		{
			return '';
		}

		return self::htmlScope($this->type);
	}

	/**
	 * Return the sanitized Type
	 *
	 * @param   string  $type  The Type to sanitize
	 *
	 * @return	string
	 */
	public static function sanitizeType($type)
	{
		return ucfirst(trim($type));
	}

	/**
	 * Return the sanitized Property
	 *
	 * @param   string  $property  The Property to sanitize
	 *
	 * @return	string
	 */
	public static function sanitizeProperty($property)
	{
		return lcfirst(trim($property));
	}

	/**
	 * Return an array with all available Types
	 *
	 * @return	array
	 */
	public static function getTypes()
	{
		return array_keys(self::$types);
	}

	/**
	 * Return the expected types of the Property
	 *
	 * @param   string  $type      The Type to process
	 * @param   string  $property  The Property to process
	 *
	 * @return  array
	 */
	public static function getExpectedTypes($type, $property)
	{
		$tmp = self::$types[$type]['properties'];

		// Check if the Property is in the Type
		if (isset($tmp[$property]))
		{
			return $tmp[$property]['expectedTypes'];
		}

		// Check if the Property is inherit
		$extendedType = self::$types[$type]['extends'];

		// Recursive
		if (!empty($extendedType))
		{
			return self::getExpectedTypes($extendedType, $property);
		}

		return array();
	}

	/**
	 * Return the expected display type of the [normal|nested|meta]
	 * In wich way to display the Property:
	 * normal -> itemprop="name"
	 * nested -> itemprop="director" itemscope itemtype="http://schema.org/Person"
	 * meta   -> <meta itemprop="datePublished" content="1991-05-01">
	 *
	 * @param   string  $type      The Type where to find the Property
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 */
	protected static function getExpectedDisplayType($type, $property)
	{
		// FIXME If the user want to use one of the expected Types, not the first Type found
		$expectedTypes = self::getExpectedTypes($type, $property);

		// Retrieve the first expected type
		$type = $expectedTypes[0];

		// Check if it's a normal display
		if ($type === 'Text' || $type === 'URL' || $type === 'Boolean' || $type === 'Number')
		{
			return 'normal';
		}

		// Check if it's a meta display
		if ($type === 'Date' || $type === 'DateTime')
		{
			return 'meta';
		}

		// Otherwise it's a nested display
		return 'nested';
	}

	/**
	 * Recursive function, control if the given Type has the given Property
	 *
	 * @param   string  $type      The Type where to check
	 * @param   string  $property  The Property to check
	 *
	 * @return	boolean
	 */
	public static function isPropertyInType($type, $property)
	{
		if (!self::isTypeAvailable($type))
		{
			return false;
		}

		// Control if the Property exists, and return true
		if (array_key_exists($property, self::$types[$type]['properties']))
		{
			return true;
		}

		// Recursive: Check if the Property is inherit
		$extendedType = self::$types[$type]['extends'];

		if (!empty($extendedType))
		{
			return self::isPropertyInType($extendedType, $property);
		}

		return false;
	}

	/**
	 * Control if the given Type class is available
	 *
	 * @param   string  $type  The Type to check
	 *
	 * @return	boolean
	 */
	public static function isTypeAvailable($type)
	{
		return ( array_key_exists($type, self::$types) ) ? true : false;
	}

	/**
	 * Return the microdata in a <meta> tag with the machine content inside.
	 *
	 * @param   string   $content   The machine content to display
	 * @param   string   $property  The Property
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return	string
	 *
	 * @since	1.0
	 */
	public static function htmlMeta($content, $property, $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (stripos($property, 'itemprop') !== 0)
		{
			$property = self::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = self::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);

		return "<meta $tmp content='$content'/>";
	}

	/**
	 * Return the microdata in an <span> tag.
	 *
	 * @param   string   $content   The human value
	 * @param   string   $property  Optional, the human value to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return	string
	 *
	 * @since	1.0
	 */
	public static function htmlSpan($content, $property = '', $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (!empty($property) && stripos($property, 'itemprop') !== 0)
		{
			$property = self::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = self::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);
		$tmp = ($tmp) ? ' ' . $tmp : '';

		return "<span$tmp>$content</span>";
	}

	/**
	 * Return the microdata in an <div> tag.
	 *
	 * @param   string   $content   The human value
	 * @param   string   $property  Optional, the human value to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return	string
	 *
	 * @since	1.0
	 */
	public static function htmlDiv($content, $property = '', $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (!empty($property) && stripos($property, 'itemprop') !== 0)
		{
			$property = self::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = self::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);
		$tmp = ($tmp) ? ' ' . $tmp : '';

		return "<div$tmp>$content</div>";
	}

	/**
	 * Return the HTML Scope
	 *
	 * @param   string  $scope  The Scope to process
	 *
	 * @return  string
	 */
	public static function htmlScope($scope)
	{
		if (stripos($scope, 'http') !== 0)
		{
			$scope = 'https://schema.org/' . ucfirst($scope);
		}

		return "itemscope itemtype='$scope'";
	}

	/**
	 * Return the HTML Property
	 *
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 */
	public static function htmlProperty($property)
	{
		return "itemprop='$property'";
	}
}
