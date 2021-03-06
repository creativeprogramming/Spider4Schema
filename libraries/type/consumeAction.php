<?php
/**
 * The act of ingesting information/resources/food.
 *
 * @see    http://schema.org/ConsumeAction
 * @since  1.0
*/
abstract class TypeConsumeAction extends TypeAction
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/ConsumeAction';
}
