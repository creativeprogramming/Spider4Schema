<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Microdata
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined("JPATH_PLATFORM") or die;

/**
 * Any collection of tests commonly ordered together.
 *
 * @package     Joomla.Platform
 * @subpackage  Microdata
 *
 * @see         http://schema.org/MedicalTestPanel
 * @since       13.1
*/
abstract class TypeMedicalTestPanel extends TypeMedicalTest
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/MedicalTestPanel';

	/**
	 * A component test of the panel.
	 * Expected Type: MedicalTest
	 * 
	 * @var	array
	 */
	protected static $subTest = array('value' => 'subTest',
		'expectedTypes' => array('MedicalTest')
	);

	/**
	 * Return the 'subTest' Property value
	 *
	 * @return	string
	 */
	public static function pSubTest()
	{
		return self::getValue(self::$subTest);
	}
}
