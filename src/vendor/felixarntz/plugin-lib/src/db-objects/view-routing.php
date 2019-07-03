<?php
/**
 * View_Routing manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hooks_Trait;
use Leaves_And_Love\Plugin_Lib\Router;
use Leaves_And_Love\Plugin_Lib\Template;
use Leaves_And_Love\Plugin_Lib\Error_Handler;
use Leaves_And_Love\Plugin_Lib\Fixes;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\View_Routing' ) ) :

	/**
	 * Base class for a routing manager
	 *
	 * This class represents a general routing manager.
	 *
	 * @since 1.0.0
	 */
	abstract class View_Routing extends Service {
		use Container_Service_Trait, Hooks_Trait;

		/**
		 * The base string to use.
		 *
		 * This will be used for the archive slug as well as for the base for all
		 * singular model views.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $base = '';

		/**
		 * Permalink structure.
		 *
		 * Will be appended to the base string.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $permalink = '';

		/**
		 * Query variable name for a singular page. This will only be used if pretty permalinks are not enabled.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $singular_query_var = '';

		/**
		 * Query variable name for a preview page. This will only be used if pretty permalinks are not enabled.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $preview_query_var = '';

		/**
		 * Query variable name for an archive page. This will only be used if pretty permalinks are not enabled.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $archive_query_var = '';

		/**
		 * Name of the template file for singular views.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $singular_template_name = '';

		/**
		 * Name of the template file for archive views.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $archive_template_name = '';

		/**
		 * Name for the current model variable that is used to pass it to the singular view template.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $model_var_name = '';

		/**
		 * Name for the current collection variable that is used to pass it to the archive view template.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $collection_var_name = '';

		/**
		 * Manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager = null;

		/**
		 * Holds the current model for a request.
		 *
		 * @since 1.0.0
		 * @var Model|null
		 */
		protected $current_model = null;

		/**
		 * Holds the current collection for a request.
		 *
		 * @since 1.0.0
		 * @var Collection|null
		 */
		protected $current_collection = null;

		/**
		 * Whether the current request is for a singular model.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $is_singular = false;

		/**
		 * Whether the current request is for a preview of a singular model.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $is_preview = false;

		/**
		 * Whether the current request is for a model archive.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $is_archive = false;

		/**
		 * The page number for an archive request.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $paged = 1;

		/**
		 * The number of models to show per page.
		 *
		 * @since 1.0.0
		 * @var int
		 */
		protected $per_page = 1;

		/**
		 * Router service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_router = Router::class;

		/**
		 * Template service definition.
		 *
		 * @since 1.0.0
		 * @static
		 * @var string
		 */
		protected static $service_template = Template::class;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix   The instance prefix.
		 * @param array  $services {
		 *     Array of service instances.
		 *
		 *     @type Router        $router        The router instance.
		 *     @type Template      $template      The template instance.
		 *     @type Error_Handler $error_handler The error handler instance.
		 * }
		 */
		public function __construct( $prefix, $services ) {
			$this->set_prefix( $prefix );
			$this->set_services( $services );
		}

		/**
		 * Returns the permalink for a given model.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The model object.
		 * @return string Permalink for the model view, or empty if no permalink exists.
		 */
		public function get_model_permalink( $model ) {
			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();
				if ( ! in_array( $model->$status_property, $this->manager->statuses()->get_public(), true ) ) {
					return '';
				}
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();
				if ( ! in_array( $model->$type_property, $this->manager->types()->get_public(), true ) ) {
					return '';
				}
			}

			if ( '' !== (string) get_option( 'permalink_structure' ) ) {
				$permalink = $this->base;

				$date_property      = '';
				$special_date_parts = array();
				if ( method_exists( $this->manager, 'get_date_property' ) ) {
					$date_property = $this->manager->get_date_property();

					$special_date_parts = array(
						'year'  => 'Y',
						'month' => 'm',
						'day'   => 'd',
					);
				}

				$permalink_parts = explode( '/', $this->permalink );
				foreach ( $permalink_parts as $permalink_part ) {
					if ( preg_match( '/^%([a-z0-9_]+)%$/', $permalink_part, $matches ) ) {
						if ( ! empty( $date_property ) && isset( $special_date_parts[ $matches[1] ] ) ) {
							if ( empty( $model->$date_property ) ) {
								return '';
							}

							$permalink .= '/' . mysql2date( $special_date_parts[ $matches[1] ], $model->$date_property, false );
						} else {
							$property_name = $matches[1];
							if ( empty( $model->$property_name ) ) {
								return '';
							}

							$permalink .= '/' . $model->$property_name;
						}
					} else {
						$permalink .= '/' . $permalink_part;
					}
				}

				return $this->home_url( $permalink, $this->manager->get_prefix() . $this->manager->get_singular_slug() );
			}

			$primary_property = $this->manager->get_primary_property();
			if ( empty( $model->$primary_property ) ) {
				return '';
			}

			return add_query_arg( $this->singular_query_var, $model->$primary_property, $this->home_url() );
		}

		/**
		 * Returns the permalink for the preview of a given model.
		 *
		 * The preview data is stored for a minute, meaning the preview link is valid for that period.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The model object.
		 * @return string Permalink for the model preview, or empty if preview link could not be generated.
		 */
		public function get_model_preview_permalink( $model ) {
			$preview_data = $model->to_json();

			$preview_key = md5( serialize( $preview_data ) ); // phpcs:ignore

			$transient_name = $this->manager->get_prefix() . $this->manager->get_singular_slug() . '_preview-' . $preview_key;

			$result = set_transient( $transient_name, $preview_data, MINUTE_IN_SECONDS );
			if ( ! $result ) {
				return '';
			}

			if ( '' !== (string) get_option( 'permalink_structure' ) ) {
				$permalink = $this->base . '/preview/' . $preview_key;

				return $this->home_url( $permalink, $this->manager->get_prefix() . $this->manager->get_singular_slug() );
			}

			$query_args = array(
				$this->preview_query_var => '1',
				'preview'                => $preview_key,
			);

			return add_query_arg( $query_args, $this->home_url() );
		}

		/**
		 * Returns the permalink for the model archive.
		 *
		 * @since 1.0.0
		 *
		 * @param int $page Optional. Page number to get its archive permalink. Default 1.
		 * @return string Permalink for the archive view.
		 */
		public function get_archive_permalink( $page = 1 ) {
			if ( '' !== (string) get_option( 'permalink_structure' ) ) {
				$permalink = $this->base;

				if ( $page > 1 ) {
					$permalink .= '/page/' . $page;
				}

				return $this->home_url( $permalink, $this->manager->get_prefix() . $this->manager->get_singular_slug() . '_archive' );
			}

			$query_args = array( $this->archive_query_var => '1' );
			if ( $page > 1 ) {
				$query_args['paged'] = $page;
			}

			return add_query_arg( $query_args, $this->home_url() );
		}

		/**
		 * Returns the sample permalink for a given model.
		 *
		 * @since 1.0.0
		 *
		 * @param Model  $model    The model object.
		 * @param string $property Optional. Name of a property to keep its placeholder in the
		 *                         sample permalink in order to replace it with a dynamic field.
		 *                         Default none.
		 * @return string Sample permalink for the model, or empty string if no sample permalink could be created.
		 */
		public function get_model_sample_permalink_for_property( $model, $property = '' ) {
			if ( '' === (string) get_option( 'permalink_structure' ) ) {
				return '';
			}

			$permalink = $this->base;

			$date_property      = '';
			$special_date_parts = array();
			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_property = $this->manager->get_date_property();

				$special_date_parts = array(
					'year'  => 'Y',
					'month' => 'm',
					'day'   => 'd',
				);
			}

			$permalink_parts = explode( '/', $this->permalink );
			if ( ! empty( $property ) && ! in_array( '%' . $property . '%', $permalink_parts, true ) ) {
				return '';
			}

			foreach ( $permalink_parts as $permalink_part ) {
				if ( preg_match( '/^%([a-z0-9_]+)%$/', $permalink_part, $matches ) ) {
					if ( ! empty( $property ) && $property === $matches[1] ) {
						$permalink .= '/%' . $property . '%';
					} elseif ( ! empty( $date_property ) && isset( $special_date_parts[ $matches[1] ] ) ) {
						if ( empty( $model->$date_property ) ) {
							return '';
						}

						$permalink .= '/' . mysql2date( $special_date_parts[ $matches[1] ], $model->$date_property, false );
					} else {
						$property_name = $matches[1];
						if ( empty( $model->$property_name ) ) {
							return '';
						}

						$permalink .= '/' . $model->$property_name;
					}
				} else {
					$permalink .= '/' . $permalink_part;
				}
			}

			return $this->home_url( $permalink, $this->manager->get_prefix() . $this->manager->get_singular_slug() );
		}

		/**
		 * Checks whether the current request is for a singular model.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the request is for a singular model, false otherwise.
		 */
		public function is_singular() {
			return $this->is_singular;
		}

		/**
		 * Checks whether the current request is for a preview of a singular model.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the request is for a preview of a singular model, false otherwise.
		 */
		public function is_preview() {
			return $this->is_preview;
		}

		/**
		 * Checks whether the current request is for a model archive.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if the request is for a model archive, false otherwise.
		 */
		public function is_archive() {
			return $this->is_archive;
		}

		/**
		 * Gets the page for the current request.
		 *
		 * @since 1.0.0
		 *
		 * @return int Current page.
		 */
		public function get_paged() {
			return $this->paged;
		}

		/**
		 * Gets the current model if the current request is for a singular or a preview.
		 *
		 * @since 1.0.0
		 *
		 * @return Model|null Model instance, or null if none detected.
		 */
		public function get_current_model() {
			return $this->current_model;
		}

		/**
		 * Gets the current collection if the current request is for an archive.
		 *
		 * @since 1.0.0
		 *
		 * @return Collection|null Collection instance, or null if none detected.
		 */
		public function get_current_collection() {
			return $this->current_collection;
		}

		/**
		 * Sets the manager instance.
		 *
		 * @since 1.0.0
		 *
		 * @param Manager $manager Manager instance.
		 */
		public function set_manager( $manager ) {
			$this->manager = $manager;

			$this->setup_vars();
			$this->register_routes();
		}

		/**
		 * Handles a request for a singular model.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_vars Array of query variables.
		 * @return bool True if a singular model for the query variables was found, false otherwise.
		 */
		public function handle_singular_request( $query_vars ) {
			if ( $this->is_singular ) {
				return true;
			}

			$this->is_singular = true;
			$this->paged       = 1;
			$this->per_page    = 1;

			if ( isset( $query_vars[ $this->singular_query_var ] ) ) {
				$primary_property_value = absint( $query_vars[ $this->singular_query_var ] );
				if ( 0 === $primary_property_value ) {
					return false;
				}

				$query_vars['include'] = array( $primary_property_value );
				unset( $query_vars[ $this->singular_query_var ] );
			}

			$query_params = $this->get_query_params( $query_vars );

			$collection = $this->manager->query( $query_params );

			$this->current_model = $collection->current();
			if ( null === $this->current_model ) {
				return false;
			}

			$this->setup_view();

			return true;
		}

		/**
		 * Handles a request for a preview of a singular model.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_vars Array of query variables.
		 * @return bool True if a valid preview and capabilities are met, false otherwise.
		 */
		public function handle_preview_request( $query_vars ) {
			if ( $this->is_preview ) {
				return true;
			}

			$this->is_preview  = true;
			$this->is_singular = true;
			$this->paged       = 1;
			$this->per_page    = 1;

			if ( ! is_user_logged_in() ) {
				return false;
			}

			$capabilities = $this->manager->capabilities();
			if ( ! $capabilities || ! $capabilities->user_can_edit() ) {
				return false;
			}

			$transient_name = $this->manager->get_prefix() . $this->manager->get_singular_slug() . '_preview-' . $query_vars['preview'];

			$preview_data = get_transient( $transient_name );
			if ( ! $preview_data ) {
				return false;
			}

			delete_transient( $transient_name );

			$primary_property = $this->manager->get_primary_property();

			if ( ! empty( $preview_data[ $primary_property ] ) ) {
				$model = $this->manager->get( $preview_data[ $primary_property ] );
			} else {
				$model = $this->manager->create();
			}

			foreach ( $preview_data as $key => $value ) {
				$model->$key = $value;
			}

			$this->current_model = $model;

			$this->setup_view();

			return true;
		}

		/**
		 * Handles a request for a model archive.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_vars Array of query variables.
		 * @return bool Always returns true.
		 */
		public function handle_archive_request( $query_vars ) {
			if ( $this->is_archive ) {
				return true;
			}

			$this->is_archive = true;
			$this->paged      = 1;
			$this->per_page   = absint( get_option( 'posts_per_page' ) );

			if ( isset( $query_vars[ $this->archive_query_var ] ) ) {
				unset( $query_vars[ $this->archive_query_var ] );
			}

			if ( isset( $query_vars['paged'] ) ) {
				$paged = absint( $query_vars['paged'] );
				if ( $paged > 1 ) {
					$this->paged = $paged;
				}

				unset( $query_vars['paged'] );
			}

			$query_params = $this->get_query_params( $query_vars );

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_property = $this->manager->get_date_property();

				$query_params['orderby'] = array( $date_property => 'DESC' );
			}

			/**
			 * Filters the query parameters to use for an archive request.
			 *
			 * @since 1.0.0
			 *
			 * @param array $query_params Original query parameters.
			 */
			$query_params = apply_filters( "{$this->manager->get_prefix()}{$this->manager->get_singular_slug()}_archive_query_params", $query_params );

			$this->current_collection = $this->manager->query( $query_params );

			$this->setup_view();

			return true;
		}

		/**
		 * Maps matches from a route regular expression to query variables.
		 *
		 * This method only works if the regular expression stores all dynamic parts under named array keys.
		 *
		 * @since 1.0.0
		 *
		 * @param array $matches Array of regular expression matches.
		 * @return array Associative array of query variables.
		 */
		public function map_matches_to_query_vars( $matches ) {
			$query_vars = array();

			foreach ( $matches as $key => $value ) {
				if ( is_numeric( $key ) ) {
					continue;
				}

				$query_vars[ $key ] = $value;
			}

			return $query_vars;
		}

		/**
		 * Sets up the class properties.
		 *
		 * @since 1.0.0
		 */
		protected function setup_vars() {
			$this->base = $this->manager->get_message( 'view_routing_base' );

			if ( method_exists( $this->manager, 'get_slug_property' ) ) {
				$slug_property = $this->manager->get_slug_property();

				$this->permalink = '%' . $slug_property . '%';
			} else {
				$primary_property = $this->manager->get_primary_property();

				$this->permalink = '%' . $primary_property . '%';
			}

			$singular_slug = $this->manager->get_singular_slug();
			$plural_slug   = $this->manager->get_plural_slug();

			$this->singular_query_var = $this->get_prefix() . $singular_slug . '_' . $this->manager->get_primary_property();
			$this->preview_query_var  = $this->get_prefix() . $singular_slug . '_preview';
			$this->archive_query_var  = $this->get_prefix() . $plural_slug;

			$this->singular_template_name = $singular_slug;
			$this->archive_template_name  = $plural_slug;

			$this->model_var_name      = $singular_slug;
			$this->collection_var_name = $plural_slug;
		}

		/**
		 * Registers routes for the model singular views and archives.
		 *
		 * @since 1.0.0
		 */
		protected function register_routes() {
			if ( ! empty( $this->singular_query_var ) ) {
				$slug = $this->get_prefix() . $this->manager->get_singular_slug();

				$pattern = '^' . $this->base;

				$permalink_parts = explode( '/', $this->permalink );
				foreach ( $permalink_parts as $permalink_part ) {
					if ( preg_match( '/^%([a-z0-9_]+)%$/', $permalink_part, $matches ) ) {
						$pattern .= '/(?P<' . $matches[1] . '>[^/]+)';
					} else {
						$pattern .= '/' . $permalink_part;
					}
				}

				$pattern .= '/?$';

				$query_vars = array( $this->singular_query_var );

				$this->router()->add_route( $slug, $pattern, array( $this, 'map_matches_to_query_vars' ), $query_vars, array( $this, 'handle_singular_request' ) );
			}

			if ( ! empty( $this->preview_query_var ) ) {
				$slug = $this->get_prefix() . $this->manager->get_singular_slug() . '_preview';

				$pattern    = '^' . $this->base . '/preview/(?P<preview>[\w-]+)/?$';
				$query_vars = array( $this->preview_query_var, 'preview' );

				$this->router()->add_route( $slug, $pattern, array( $this, 'map_matches_to_query_vars' ), $query_vars, array( $this, 'handle_preview_request' ) );
			}

			if ( ! empty( $this->archive_query_var ) ) {
				$slug = $this->get_prefix() . $this->manager->get_plural_slug();

				$pattern = '^' . $this->base . '(/page/?(?P<paged>[\d]+)/?)?$';

				$query_vars = array( $this->archive_query_var, 'paged' );

				$this->router()->add_route( $slug, $pattern, array( $this, 'map_matches_to_query_vars' ), $query_vars, array( $this, 'handle_archive_request' ) );
			}
		}

		/**
		 * Retrieves the array of parameters for the model query, based on query variables and defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_vars Query variables for the current request.
		 * @return array Query parameters for the model query.
		 */
		protected function get_query_params( $query_vars ) {
			$number        = $this->per_page;
			$offset        = ( $this->paged - 1 ) * $number;
			$no_found_rows = $this->is_singular() ? true : false;

			$query_params = array(
				'number'        => $number,
				'offset'        => $offset,
				'no_found_rows' => $no_found_rows,
			);

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_query = array();

				foreach ( array( 'year', 'month', 'day' ) as $date_part ) {
					if ( isset( $query_vars[ $date_part ] ) ) {
						$date_query[ $date_part ] = $query_vars[ $date_part ];
						unset( $query_vars[ $date_part ] );
					}
				}

				if ( ! empty( $date_query ) ) {
					$query_params['date_query'] = array( $date_query );
				}
			}

			$query_params = array_merge( $query_params, $query_vars );

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();

				$query_params[ $type_property ] = $this->manager->types()->get_public();
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$query_params[ $status_property ] = $this->manager->statuses()->get_public();
			}

			return $query_params;
		}

		/**
		 * Sets up the current view.
		 *
		 * This method is invoked on every successfully routed request. It adds the hooks to adjust the
		 * regular behavior of WordPress in order to correctly handle the custom model content.
		 *
		 * @since 1.0.0
		 */
		protected function setup_view() {
			$this->add_filter( 'pre_get_document_title', array( $this, 'set_document_title' ), 1, 1 );
			$this->add_filter( 'wp_head', array( $this, 'rel_canonical' ), 10, 0 );
			$this->add_action( 'template_redirect', array( $this, 'load_template' ), 1, 0 );
		}

		/**
		 * Returns the document title for a model singular or archive view.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title Original title to be overridden.
		 * @return string New document title.
		 */
		protected function set_document_title( $title ) {
			if ( ! empty( $title ) ) {
				return $title;
			}

			$title = array();

			if ( $this->is_archive() ) {
				$title['title'] = $this->manager->get_message( 'view_routing_archive_title' );
				if ( $this->paged > 1 ) {
					$title['page'] = sprintf( $this->manager->get_message( 'view_routing_archive_title_page_suffix' ), number_format_i18n( $this->paged ) );
				}
			} else {
				if ( method_exists( $this->manager, 'get_title_property' ) ) {
					$title_property = $this->manager->get_title_property();
					$title['title'] = $this->current_model->$title_property;
				} else {
					$primary_property = $this->manager->get_primary_property();
					$title['title']   = sprintf( $this->manager->get_message( 'view_routing_singular_fallback_title' ), number_format_i18n( $this->current_model->$primary_property ) );
				}
			}

			$title['site'] = get_bloginfo( 'name', 'display' );

			/** This filter is documented in wp-includes/general-template.php */
			$sep = apply_filters( 'document_title_separator', '-' );

			/** This filter is documented in wp-includes/general-template.php */
			$title = apply_filters( 'document_title_parts', $title );

			$title = implode( " $sep ", array_filter( $title ) );
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );

			return $title;
		}

		/**
		 * Prints the canonical header for a singular view.
		 *
		 * @since 1.0.0
		 */
		protected function rel_canonical() {
			$permalink = '';

			if ( $this->is_singular() ) {
				$permalink = $this->get_model_permalink( $this->current_model );
			} elseif ( $this->is_archive() ) {
				$permalink = $this->get_archive_permalink();
			}

			if ( empty( $permalink ) ) {
				return;
			}

			echo '<link rel="canonical" href="' . esc_url( $permalink ) . '">' . "\n";
		}

		/**
		 * Loads the template for a singular view.
		 *
		 * @since 1.0.0
		 *
		 * @global WP_Rewrite $wp_rewrite WordPress rewrite rules controller.
		 */
		protected function load_template() {
			global $wp_rewrite;

			$request_method = Fixes::php_filter_input( INPUT_SERVER, 'REQUEST_METHOD' );

			/** This filter is documented in wp-includes/template-loader.php */
			if ( 'HEAD' === $request_method && apply_filters( 'exit_on_http_head', true ) ) {
				exit;
			}

			if ( $this->is_archive() ) {
				$this->template()->get_partial(
					$this->archive_template_name,
					array(
						$this->collection_var_name => $this->current_collection,
						'manager'                  => $this->manager,
						'page'                     => $this->paged,
						'per_page'                 => $this->per_page,
						'max_pages'                => ceil( $this->current_collection->get_total() / $this->per_page ),
						'page_link_template'       => $this->get_archive_permalink( '%d' ),
						'pagenum_link'             => '' !== (string) get_option( 'permalink_structure' ) ? home_url( $this->base . '%_%' ) : home_url( '%_%&' . $this->archive_query_var . '=1' ),
						'pagenum_format'           => '' !== (string) get_option( 'permalink_structure' ) ? '/page/%#%' . ( $wp_rewrite->use_trailing_slashes ? '/' : '' ) : '?paged=%#%',
						'template'                 => $this->template(),
					)
				);
			} else {
				$data = array(
					$this->model_var_name => $this->current_model,
					'manager'             => $this->manager,
					'template'            => $this->template(),
				);

				if ( method_exists( $this->manager, 'get_slug_property' ) ) {
					$slug_property = $this->manager->get_slug_property();

					$data['template_suffix'] = $this->current_model->$slug_property;
				}

				$this->template()->get_partial( $this->singular_template_name, $data );
			}

			exit;
		}

		/**
		 * Prefixes a path with the home URL.
		 *
		 * @since 1.0.5
		 *
		 * @param string $path        Optional. Relative path. If empty, the home URL is returned. Default empty string.
		 * @param string $type_of_url Optional. Type of URL. Used to determine whether the URL should receive a trailing
		 *                            slash, so it should be provided if $path is not empty. Default is 'home' (which
		 *                            only makes sense when $path is empty).
		 * @return string Absolute URL.
		 */
		protected function home_url( $path = '', $type_of_url = 'home' ) {
			$url = home_url( $path );
			$url = user_trailingslashit( home_url( $path ), $type_of_url );

			return $url;
		}
	}

endif;
