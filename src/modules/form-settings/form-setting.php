<?php
/**
 * Protector base class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Modules\Submodule;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Trait;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Trait;

/**
 * Base class for a form setting.
 *
 * @since 1.1.0
 */
abstract class Form_Setting extends Submodule implements Meta_Submodule_Interface, Settings_Submodule_Interface {
	use Meta_Submodule_Trait, Settings_Submodule_Trait;
}
