<?php
/**
 * Extensions class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use Leaves_And_Love\Plugin_Lib\Components\Extensions as Extensions_Base;
use Leaves_And_Love\Plugin_Lib\Translations\Translations_Extensions;

/**
 * Class for Extensions API
 *
 * @since 1.0.0
 */
class Extensions extends Extensions_Base {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string                  $prefix       The prefix.
	 * @param Translations_Extensions $translations Translations instance.
	 */
	public function __construct( $prefix, $translations ) {
		$this->base_class = Extension::class;

		parent::__construct( $prefix, $translations );
	}
}
