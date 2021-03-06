<?php
/**
 * Enumerations related to health and the practice of medicine.
 *
 * @see    http://schema.org/MedicalEnumeration
 * @since  1.0
*/
abstract class TypeMedicalEnumeration extends TypeMedicalIntangible
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/MedicalEnumeration';
}
