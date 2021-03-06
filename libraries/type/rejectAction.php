<?php
/**
 * The act of rejecting to/adopting an object.Related actions:AcceptAction: The antagonym of RejectAction.
 *
 * @see    http://schema.org/RejectAction
 * @since  1.0
*/
abstract class TypeRejectAction extends TypeAllocateAction
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/RejectAction';
}
