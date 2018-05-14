<?php
/**
 * Template tag handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use InvalidArgumentException;

/**
 * Class for handling template tags.
 *
 * @since 1.0.0
 */
class Template_Tag_Handler {

	/**
	 * The template tag handler slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $slug;

	/**
	 * Available template tag data for this handler.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $tags = array();

	/**
	 * Arguments to pass to the template tag callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $tag_args_definition = array();

	/**
	 * Available template tag groups for this handler.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $groups = array();

	/**
	 * Constructor.
	 *
	 * Sets the template tag handler properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug                Template tag handler slug.
	 * @param array  $tags                Template tags as an associative array of `$slug => $data` pairs.
	 * @param array  $tag_args_definition Template tag callback arguments definition as an array of scalar $type
	 *                                    values, where $type must either be a class name, or one out of
	 *                                    'string', 'int', 'float' or 'bool'.
	 * @param array  $groups              Optional. Template tag groups. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown when invalid parameters are passed.
	 */
	public function __construct( $slug, $tags, $tag_args_definition, $groups = array() ) {
		$this->slug                = $slug;
		$this->tags                = $this->validate_tags( $tags );
		$this->tag_args_definition = $this->validate_tag_args_definition( $tag_args_definition );
		$this->groups              = $groups;
	}

	/**
	 * Processes content and replaces template tags.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Input content.
	 * @param array  $args    Arguments to pass to the template tag callbacks. Must validate against the
	 *                        handler's arguments definition.
	 * @return string Content with template tags replaced.
	 */
	public function process_content( $content, $args ) {
		if ( false === strpos( $content, '{' ) ) {
			return $content;
		}

		try {
			$args = $this->validate_tag_args( $args, $this->tag_args_definition );
		} catch ( InvalidArgumentException $e ) {
			return $content;
		}

		$placeholders = array();
		$replacements = array();

		foreach ( $this->tags as $slug => $data ) {
			if ( false === strpos( $content, '{' . $slug . '}' ) ) {
				continue;
			}

			$placeholders[] = '{' . $slug . '}';
			$replacements[] = (string) call_user_func_array( $data['callback'], $args );
		}

		return str_replace( $placeholders, $replacements, $content );
	}

	/**
	 * Gets the template tag handler slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template tag handler slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Adds a new template tag.
	 *
	 * Template tag data must contain a 'label' and 'callback', and may optionally contain
	 * a 'description' and 'group'.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag slug.
	 * @param array  $data Template tag data.
	 * @return bool True on success, false on failure.
	 */
	public function add_tag( $slug, $data ) {
		if ( $this->has_tag( $slug ) ) {
			return false;
		}

		try {
			$data = $this->validate_tag_data( $data );
		} catch ( InvalidArgumentException $e ) {
			return false;
		}

		$this->tags[ $slug ] = $data;

		return true;
	}

	/**
	 * Removes an existing template tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag slug.
	 * @return bool True on success, false on failure.
	 */
	public function remove_tag( $slug ) {
		if ( ! $this->has_tag( $slug ) ) {
			return false;
		}

		unset( $this->tags[ $slug ] );

		return true;
	}

	/**
	 * Checks whether a specific template tag is available.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag slug.
	 * @return bool True if template tag is available, false otherwise.
	 */
	public function has_tag( $slug ) {
		return isset( $this->tags[ $slug ] );
	}

	/**
	 * Gets a specific template tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Template tag slug.
	 * @return array|null Template tag data, or null if not found.
	 */
	public function get_tag( $slug ) {
		if ( ! $this->has_tag( $slug ) ) {
			return null;
		}

		return $this->tags[ $slug ];
	}

	/**
	 * Gets all available template tags for the handler.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $group Optional. Group slug to only get tags of that group. Default null.
	 * @return array Array of template tags.
	 */
	public function get_tags( $group = null ) {
		if ( null !== $group ) {
			return wp_list_filter( $this->tags, array( 'group' => $group ) );
		}

		return $this->tags;
	}

	/**
	 * Gets all available template tag labels for the handler.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $group Optional. Group slug to only get tags of that group. Default null.
	 * @return array Array of template tag labels.
	 */
	public function get_tag_labels( $group = null ) {
		$labels = array();

		foreach ( $this->tags as $slug => $data ) {
			if ( null !== $group && $data['group'] !== $group ) {
				continue;
			}

			$labels[ $slug ] = $data['label'];
		}

		return $labels;
	}

	/**
	 * Adds a new template tag group.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug  Group slug.
	 * @param string $label Group label.
	 * @return bool True on success, false on failure.
	 */
	public function add_group( $slug, $label ) {
		if ( isset( $this->groups[ $slug ] ) ) {
			return false;
		}

		$this->groups[ $slug ] = $label;

		return true;
	}

	/**
	 * Removes an existing template tag group.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Group slug.
	 * @return bool True on success, false on failure.
	 */
	public function remove_group( $slug ) {
		if ( ! isset( $this->groups[ $slug ] ) ) {
			return false;
		}

		unset( $this->groups[ $slug ] );

		return true;
	}

	/**
	 * Gets all available template tag groups for the handler.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of `$slug => $label` pairs.
	 */
	public function get_groups() {
		return $this->groups;
	}

	/**
	 * Validates template tags.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tags Template tags as an associative array of `$slug => $data` pairs.
	 * @return array Validated template tags.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid tag is passed.
	 */
	private function validate_tags( $tags ) {
		foreach ( $tags as $slug => &$data ) {
			$data = $this->validate_tag_data( $data );
		}

		return $tags;
	}

	/**
	 * Validates data for a template tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Template tag data to validate.
	 * @return array Validated template tag data.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid tag is passed.
	 */
	private function validate_tag_data( $data ) {
		if ( ! is_array( $data ) ) {
			/* translators: 1: template tag slug, 2: template tag handler slug */
			throw new InvalidArgumentException( sprintf( __( 'Invalid template tag %1$s for handler %2$s.', 'torro-forms' ), $slug, $this->slug ) );
		}

		if ( empty( $data['label'] ) || empty( $data['callback'] ) ) {
			/* translators: 1: template tag slug, 2: template tag handler slug */
			throw new InvalidArgumentException( sprintf( __( 'Invalid template tag %1$s for handler %2$s.', 'torro-forms' ), $slug, $this->slug ) );
		}

		if ( ! isset( $data['description'] ) ) {
			$data['description'] = '';
		}

		if ( ! isset( $data['group'] ) ) {
			$data['group'] = 'default';
		}

		return $data;
	}

	/**
	 * Validates template tag callback arguments against an arguments definition.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tag_args            Template tag callback arguments.
	 * @param array $tag_args_definition Template tag callback arguments definition as an array of scalar $type values.
	 * @return array Validated template tag callback arguments.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid tag argument is passed.
	 */
	private function validate_tag_args( $tag_args, $tag_args_definition ) {
		if ( count( $tag_args ) !== count( $tag_args_definition ) ) {
			/* translators: %s: template tag handler slug */
			throw new InvalidArgumentException( sprintf( __( 'Invalid template tag arguments passed to handler %s.', 'torro-forms' ), $this->slug ) );
		}

		$valid = true;
		foreach ( $tag_args as $index => $tag_arg ) {
			switch ( $tag_args_definition[ $index ] ) {
				case 'string':
				case 'int':
				case 'float':
				case 'bool':
					if ( ! call_user_func( 'is_' . $tag_args_definition[ $index ], $tag_arg ) ) {
						$valid = false;
					}
					break;
				default:
					if ( ! is_a( $tag_arg, $tag_args_definition[ $index ] ) ) {
						$valid = false;
					}
			}
		}

		if ( ! $valid ) {
			/* translators: %s: template tag handler slug */
			throw new InvalidArgumentException( sprintf( __( 'Invalid template tag arguments passed to handler %s.', 'torro-forms' ), $this->slug ) );
		}

		return $tag_args;
	}

	/**
	 * Validates a template tag arguments definition.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tag_args_definition Template tag callback arguments definition as an array of scalar $type values.
	 * @return array Validated template tag callback arguments definition.
	 *
	 * @throws InvalidArgumentException Thrown when an invalid $type is passed.
	 */
	private function validate_tag_args_definition( $tag_args_definition ) {
		foreach ( $tag_args_definition as $type ) {
			switch ( $type ) {
				case 'string':
				case 'int':
				case 'float':
				case 'bool':
					break;
				default:
					if ( ! class_exists( $type ) && ! interface_exists( $type ) ) {
						/* translators: %s: template tag handler slug */
						throw new InvalidArgumentException( sprintf( __( 'Invalid template tag arguments definition for handler %s.', 'torro-forms' ), $this->slug ) );
					}
			}
		}

		return $tag_args_definition;
	}
}
