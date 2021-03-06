<?php
/**
 * User interaction: Check-in at a place.
 *
 * @see    http://schema.org/UserCheckins
 * @since  1.0
*/
abstract class TypeUserCheckins extends TypeUserInteraction
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/UserCheckins';
}
