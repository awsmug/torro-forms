<?php
/**
 * Form upload manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Components;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use awsmug\Torro_Forms\DB_Objects\Taxonomy_Manager;

/**
 * Class for managing media files to upload for a form submission.
 *
 * @since 1.0.0
 */
class Form_Upload_Manager extends Service {
	use Container_Service_Trait;

	/**
	 * The taxonomy manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_taxonomies = Taxonomy_Manager::class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix   Instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Taxonomy_Manager $taxonomies    The taxonomy manager class instance.
	 *     @type Error_Handler    $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );
	}

	/**
	 * Checks whether the typical image sizes should be generated for form uploads.
	 *
	 * By default this will be skipped. A constant 'TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES' can be used
	 * to tweak it.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $form_id Form ID for which to check this.
	 * @return bool True if image sizes should be generated, false otherwise.
	 */
	protected function should_generate_image_sizes( $form_id ) {
		$result = false;

		if ( defined( 'TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES' ) && TORRO_CREATE_FORM_UPLOAD_IMAGE_SIZES ) {
			$result = true;
		}

		/**
		 * Filters whether the typical image sizes should be generated for form upload images.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $result  True if image sizes should be generated, false otherwise. Default depends on the constant.
		 * @param int  $form_id Form ID for which to check this.
		 */
		return apply_filters( "{$this->get_prefix()}form_uploads_should_generate_image_sizes", $result, $form_id );
	}
}
