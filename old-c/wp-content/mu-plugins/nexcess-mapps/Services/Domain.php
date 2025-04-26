<?php

namespace Nexcess\MAPPS\Services;

use Nexcess\MAPPS\Support\Branding;
use Tribe\WME\Sitebuilder\Contracts\ManagesDomain;
use Tribe\WME\Sitebuilder\Services\Domain as BaseDomainService;
use WP_Error;

class Domain extends BaseDomainService implements ManagesDomain {

	/**
	 * @var MappsApiClient
	 */
	protected $client;

	/**
	 * Construct the integration.
	 *
	 * @param MappsApiClient $client
	 */
	public function __construct( MappsApiClient $client ) {
		$this->client = $client;
	}

	/**
	 * Make a request to change the domain of the site.
	 *
	 * @param string $domain
	 *
	 * @return true|WP_Error
	 */
	public function renameDomain( $domain ) {
		if ( empty( $domain ) ) {
			return new WP_Error(
				'mapps-change-domain-failure',
				__( 'Unable to update the site with an empty domain.', 'nexcess-mapps' )
			);
		}

		try {
			$this->client->renameDomain( $domain );
		} catch ( \Exception $e ) {
			return new WP_Error(
				'mapps-change-domain-failure',
				sprintf(
				/* Translators: %1$s is the branded company name, %2$s is the API error message. */
					__( 'The %1$s API returned an error: %2$s', 'nexcess-mapps' ),
					Branding::getCompanyName(),
					$e->getMessage()
				)
			);
		}

		return true;
	}

	/**
	 * Confirm the domain is usable for the site.
	 *
	 * @param string $domain
	 *
	 * @return array Data indicating the various states of validation checks, or an empty array if unsuccessful.
	 */
	public function isDomainUsable( $domain ) {
		if ( empty( $domain ) ) {
			return [];
		}

		try {
			$response = $this->client->checkDomainUsable( $domain );

			$data = [
				'domain' => $domain,
			];

			// Return all properties from response body.
			foreach ( $response as $prop => $value ) {
				$data[ $prop ] = $value;
			}

			return $data;
		} catch ( \Exception $e ) {
			return [];
		}
	}

}
