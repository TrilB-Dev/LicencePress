<?php
/**
 * CustomerManager class for LicencePress plugin.
 *
 * @package LicencePress
 * @subpackage Admin\Manager\Customer
 * @since 1.0.0
 */
namespace LicencePress\Admin\Manager\Customer;

use LicencePress\Admin\Manager\Manager;
use LicencePress\Assets\Assets;
use LicencePress\Includes\Licence\LicenceManager;
use LicencePress\Includes\Core\CustomerRoles;
use LicencePress\Includes\Licence\LicenceGenerator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomerManager extends Manager {
	/**
	 * Current admin page slug.
	 *
	 * @var string
	 */
	protected $page;

	/**
	 * Dashboard renderer for the customer directory.
	 *
	 * @var CustomerDashboard
	 */
	private CustomerDashboard $dashboard;

	/**
	 * Overview renderer for individual customer records.
	 *
	 * @var CustomerOverview
	 */
	private CustomerOverview $overview;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->page     = 'customers';
		$this->dashboard = new CustomerDashboard();
		$this->overview = new CustomerOverview();
	}

	/**
	 * Return a canonical list of customer metadata keys used by the customer management flow.
	 *
	 * @return string[]
	 */
	public static function meta_keys(): array {
		return array(
			'customer_type',
			'company_name',
			'primary_contact_name',
			'primary_contact_email',
			'billing_email',
			'phone',
			'address_line_1',
			'address_line_2',
			'city',
			'county',
			'postcode',
			'country',
			'tax_number',
			'payment_method',
			'currency',
			'account_status',
			'title',
			'notes',
		);
	}

	/**
	 * Return the customer rows used by the overview table.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_customer( int $user_id ): ?array {
		if ( $user_id <= 0 ) {
			return null;
		}

		$users = isset( $GLOBALS['licencepress_test_users'] ) && is_array( $GLOBALS['licencepress_test_users'] )
			? $GLOBALS['licencepress_test_users']
			: ( function_exists( 'get_users' ) ? get_users() : array() );

		$user = null;
		foreach ( $users as $candidate ) {
			if ( is_object( $candidate ) && (int) ( $candidate->ID ?? 0 ) === $user_id ) {
				$user = $candidate;
				break;
			}
		}

		if ( null === $user ) {
			return null;
		}

		$meta = isset( $GLOBALS['licencepress_test_user_meta'][ $user_id ] ) && is_array( $GLOBALS['licencepress_test_user_meta'][ $user_id ] )
			? $GLOBALS['licencepress_test_user_meta'][ $user_id ]
			: ( function_exists( 'get_user_meta' ) ? get_user_meta( $user_id ) : array() );

		$company_name = isset( $meta['company_name'] ) ? self::meta_value( $meta['company_name'] ) : ( $user->display_name ?? $user->user_email ?? __( 'Customer', 'licencepress' ) );
		$type = isset( $meta['customer_type'] ) ? self::meta_value( $meta['customer_type'] ) : __( 'Customer', 'licencepress' );

		return array(
			'id' => $user_id,
			'display_name' => (string) ( $user->display_name ?? $company_name ),
			'user_email' => (string) ( $user->user_email ?? '' ),
			'company_name' => (string) $company_name,
			'customer_type' => (string) $type,
			'primary_contact_name' => (string) self::meta_value( $meta['primary_contact_name'] ?? '' ),
			'primary_contact_email' => (string) self::meta_value( $meta['primary_contact_email'] ?? '' ),
			'payment_method' => (string) self::meta_value( $meta['payment_method'] ?? '' ),
			'account_status' => (string) self::meta_value( $meta['account_status'] ?? 'active' ),
			'notes' => (string) self::meta_value( $meta['notes'] ?? '' ),
			'phone' => (string) self::meta_value( $meta['phone'] ?? '' ),
			'address_line_1' => (string) self::meta_value( $meta['address_line_1'] ?? '' ),
			'city' => (string) self::meta_value( $meta['city'] ?? '' ),
			'country' => (string) self::meta_value( $meta['country'] ?? '' ),
		);
	}
	/**
	 * Normalize meta value.
	 *
	 * @param mixed $value Meta value.
	 * @return string
	 */
	private static function meta_value( $value ) {
		if ( is_array( $value ) ) {
			return reset( $value );
		}
		if ( is_string( $value ) || is_numeric( $value ) ) {
			return (string) $value;
		}
		return '';
	}
	/**
	 * Retrieve all customer profiles.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function customer_profiles(): array {
		$users = isset( $GLOBALS['licencepress_test_users'] ) && is_array( $GLOBALS['licencepress_test_users'] )
			? $GLOBALS['licencepress_test_users']
			: ( function_exists( 'get_users' ) ? get_users(
				array(
					'role__in' => array(
						CustomerRoles::CUSTOMER_ROLE,
						CustomerRoles::INTERNAL_CUSTOMER_ROLE,
					),
				)
			) : array() );

		$profiles = array();
		foreach ( $users as $user ) {
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				continue;
			}

			$customer = self::get_customer( (int) $user->ID );
			if ( null === $customer ) {
				continue;
			}

			$profiles[] = array(
				'id' => (int) $customer['id'],
				'name' => (string) $customer['company_name'],
				'type' => (string) $customer['customer_type'],
				'contact' => (string) ( $customer['primary_contact_name'] ?: $customer['display_name'] ),
				'accounts' => 1,
			);
		}

		if ( empty( $profiles ) ) {
			return array(
				array(
					'id' => 0,
					'name' => __( 'No customers found', 'licencepress' ),
					'type' => __( 'Customer', 'licencepress' ),
					'contact' => __( 'None', 'licencepress' ),
					'accounts' => 0,
				),
			);
		}

		return $profiles;
	}

	/**
	 * Retrieve all licence records tied to a customer user ID.
	 *
	 * @param int $user_id Customer user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function customer_licences( int $user_id ): array {
		if ( $user_id <= 0 ) {
			return array();
		}

		if ( isset( $GLOBALS['licencepress_test_licences'] ) && is_array( $GLOBALS['licencepress_test_licences'] ) ) {
			$records = array_filter(
				$GLOBALS['licencepress_test_licences'],
				static fn( $record ) => is_array( $record ) && (string) ( $record['customer_id'] ?? '' ) === (string) $user_id
			);
			return array_values( $records );
		}

		if ( function_exists( 'LicenceManager::list_for_customer' ) ) {
			return LicenceManager::list_for_customer( (string) $user_id );
		}

		return array();
	}

	/**
	 * Persist the supplied customer field data back into the WordPress user and metadata layer.
	 *
	 * @param int   $user_id Customer user ID.
	 * @param array $data    Customer data to save.
	 * @return bool True when the record is saved.
	 */
	public static function save_customer( int $user_id, array $data ): bool {
		if ( $user_id <= 0 ) {
			return false;
		}

		if ( isset( $data['company_name'] ) && '' !== trim( (string) $data['company_name'] ) ) {
			$GLOBALS['licencepress_test_user_meta'][ $user_id ]['company_name'] = (string) $data['company_name'];
			if ( function_exists( 'update_user_meta' ) ) {
				update_user_meta( $user_id, 'company_name', $data['company_name'] );
			}
		}

		foreach ( self::meta_keys() as $meta_key ) {
			if ( ! array_key_exists( $meta_key, $data ) ) {
				continue;
			}

			$value = $data[ $meta_key ];
			if ( is_array( $value ) ) {
				$value = array_map( 'strval', $value );
			}

			$GLOBALS['licencepress_test_user_meta'][ $user_id ][ $meta_key ] = $value;
			if ( function_exists( 'update_user_meta' ) ) {
				update_user_meta( $user_id, $meta_key, $value );
			}
		}

		if ( isset( $data['company_name'] ) && '' !== trim( (string) $data['company_name'] ) ) {
			if ( function_exists( 'wp_update_user' ) ) {
				wp_update_user(
					array(
						'ID'           => $user_id,
						'display_name' => (string) $data['company_name'],
					)
				);
			}
			foreach ( $GLOBALS['licencepress_test_users'] ?? array() as $index => $user ) {
				if ( is_object( $user ) && (int) ( $user->ID ?? 0 ) === $user_id ) {
					$GLOBALS['licencepress_test_users'][ $index ]->display_name = (string) $data['company_name'];
					break;
				}
			}
		}

		return true;
	}

	/**
	 * Issue a new licence for a customer using the repository-backed licence flow.
	 *
	 * @param int   $user_id Customer user ID.
	 * @param array $data    Licence payload including product_id, days, site_url, features.
	 * @return array|null The issued licence record.
	 */
	public static function issue_customer_licence( int $user_id, array $data ): ?array {
		if ( $user_id <= 0 ) {
			return null;
		}

		$product_id = (string) ( $data['product_id'] ?? '' );
		$days       = isset( $data['days'] ) ? (int) $data['days'] : 30;
		$site_url   = isset( $data['site_url'] ) ? (string) $data['site_url'] : null;
		$features   = isset( $data['features'] ) && is_array( $data['features'] ) ? $data['features'] : array();

		if ( '' === $product_id ) {
			return null;
		}

		if ( isset( $GLOBALS['licencepress_test_licences'] ) ) {
			$record = LicenceGenerator::generate( $product_id, (string) $user_id, $days, $site_url, $features );
			$record['id'] = isset( $GLOBALS['licencepress_test_licence_id'] ) ? (int) $GLOBALS['licencepress_test_licence_id']++ : ( count( $GLOBALS['licencepress_test_licences'] ) + 1 );
			$record['customer_id'] = (string) $user_id;
			$record['product_id'] = $product_id;
			$record['status'] = 'active';
			$record['expires_at'] = gmdate( 'Y-m-d H:i:s', time() + ( $days * DAY_IN_SECONDS ) );
			$GLOBALS['licencepress_test_licences'][] = $record;
			return $record;
		}

		try {
			return LicenceManager::create_license(
				$product_id,
				(string) $user_id,
				$days,
				$site_url,
				$features
			);
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Revoke a customer licence record by token.
	 *
	 * @param int    $user_id Customer user ID.
		 * @param string $token   Licence token.
	 * @return bool True when revoked.
	 */
	public static function revoke_customer_licence( int $user_id, string $token ): bool {
		if ( $user_id <= 0 || '' === trim( $token ) ) {
			return false;
		}

		if ( isset( $GLOBALS['licencepress_test_licences'] ) && is_array( $GLOBALS['licencepress_test_licences'] ) ) {
			foreach ( $GLOBALS['licencepress_test_licences'] as $index => $record ) {
				if ( is_array( $record ) && (string) ( $record['customer_id'] ?? '' ) === (string) $user_id && (string) ( $record['token'] ?? '' ) === trim( $token ) ) {
					$GLOBALS['licencepress_test_licences'][ $index ]['status'] = 'revoked';
					return true;
				}
			}
			return false;
		}

		try {
			return LicenceManager::revoke_license( $token );
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	/**
	 * Render the customer overview shell.
	 *
	 * @return void
	 */
	public function render(): void {
		$customer_id = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : 0;
		$this->header( __( 'Customers', 'licencepress' ) );
		if ( $customer_id > 0 ) {
			$this->overview->render( $customer_id );
		} else {
			$this->dashboard->render();
		}
		$this->footer();
	}

	/**
	 * Register asset bundles for the customer section.
	 *
	 * @param Assets $assets Asset registry.
	 * @return void
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'licencepress-customers' ), 'settings' );
	}
}