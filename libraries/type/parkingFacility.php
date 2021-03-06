<?php
/**
 * A parking lot or other parking facility.
 *
 * @see    http://schema.org/ParkingFacility
 * @since  1.0
*/
abstract class TypeParkingFacility extends TypeCivicStructure
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/ParkingFacility';
}
