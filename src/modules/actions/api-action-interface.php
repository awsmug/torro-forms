<?php
/**
 * Interface for API actions
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

/**
 * Interface for an API action.
 *
 * @since 1.0.0
 */
interface API_Action_Interface {

	/**
	 * Returns the element mappings for a given form ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 * @return array Multidimensional array, where the first level is `$element_id => $field_slugs` pairs and
	 *               the second level is `$field_slug => $mapped_param` pairs.
	 */
	public function get_mappings( $form_id );

	/**
	 * Saves the element mappings for a given form.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $form_id     Form ID.
	 * @param array $id_mappings Array of ID mappings from the elements that have just been saved.
	 */
	public function save_mappings( $form_id, $id_mappings );

	/**
	 * Registers the API-API hook for adding the necessary configuration data.
	 *
	 * @since 1.0.0
	 */
	public function register_config_data_hook();
}
