<?php
/**
 * The act of installing an application.
 *
 * @see    http://schema.org/InstallAction
 * @since  1.0
*/
abstract class TypeInstallAction extends TypeConsumeAction
{
	/**
	 * The Schema.org Type Scope
	 *
	 * @var string
	 */
	protected static $scope = 'https://schema.org/InstallAction';
}
