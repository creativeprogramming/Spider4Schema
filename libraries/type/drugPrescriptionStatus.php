<?php
/**
 * Indicates whether this drug is available by prescription or over-the-counter.
 *
 * @see    http://schema.org/DrugPrescriptionStatus
 * @since  1.0
*/
abstract class TypeDrugPrescriptionStatus extends TypeMedicalEnumeration
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/DrugPrescriptionStatus';
}
