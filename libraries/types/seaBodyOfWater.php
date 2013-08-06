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
 * A sea (for example, the Caspian sea).
 *
 * @package     Joomla.Platform
 * @subpackage  Microdata
 *
 * @see         http://schema.org/SeaBodyOfWater
 * @since       13.1
*/
abstract class TypeSeaBodyOfWater extends TypeBodyOfWater
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/SeaBodyOfWater';
}
