<?php

/**
 * Allows values to be injected into filters and actions.
 *
 * @since 1.3.1
 *
 * Class Gravity_Flow_Form_Connector_Dynamic_Hook
 */
class Gravity_Flow_Form_Connector_Dynamic_Hook {
	/**
	 * @since 1.3.1
	 *
	 * @var mixed
	 */
	private $values;

	/**
	 * @since 1.3.1
	 *
	 * @var mixed
	 */
	private $class = null;

	/**
	 * Stores the values for later use.
	 *
	 * @since 1.3.1
	 *
	 * @param mixed $values
	 * @param null  $class
	 */
	public function __construct( $values, $class = null ) {

		$this->values = $values;

		if ( $class ) {
			$this->class = $class;
		}
	}

	/**
	 * Runs the hook callback function.
	 *
	 * @since 1.3.1
	 *
	 * @param  string $callback    The name of the method.
	 * @param  array  $filter_args The args called by the filter.
	 *
	 * @return mixed
	 */
	public function __call( $callback, $filter_args ) {

		$args = array( $filter_args, $this->values );

		if ( $this->class ) {
			if ( is_callable( array( $this->class, $callback ) ) ) {
				return call_user_func_array( array( $this->class, $callback ), $args );
			}
		}
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, $args );
		}
	}
}
