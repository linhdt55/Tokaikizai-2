<?php

namespace Tribe\WME\Sitebuilder\Services;

use Tribe\WME\Sitebuilder\Contracts\ManagesDomain;
use WP_Error;

/**
 * A service for working with DNS and domains.
 */
class Domain implements ManagesDomain {

	/**
	 * Extract the domain portion of a URL.
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return string Only the domain of the given URL, or an empty string if one cannot be parsed.
	 */
	public static function parseDomain( $url ) {
		// Add a protocol if one isn't present.
		if ( false === mb_strpos( $url, '://' ) ) {
			$url = 'http://' . $url;
		}

		/** @var string|false $domain */
		$domain = wp_parse_url( $url, PHP_URL_HOST );

		return $domain ?: '';
	}

	/**
	 * Format and filter the domain name.
	 *
	 * @param null|string $domain
	 *
	 * @return string|null|false
	 */
	public static function formatDomain( $domain ) {
		if ( empty( $domain ) ) {
			return $domain;
		}

		if ( function_exists( 'idn_to_ascii' ) ) {
			$domain = idn_to_ascii( $domain, 0, INTL_IDNA_VARIANT_UTS46 );
		}

		/*
		 * Validate the domain structure.
		 *
		 * Note that FILTER_VALIDATE_DOMAIN wasn't added until PHP 7.0.
		 */
		if ( version_compare( '7.0', PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, '<=' ) && $domain ) {
			// phpcs:ignore PHPCompatibility.Constants.NewConstants.filter_validate_domainFound
			$domain = filter_var( $domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME | FILTER_NULL_ON_FAILURE );
		}

		return $domain;
	}

	/**
	 * Make a request to change the domain of the site.
	 *
	 * @param string $domain
	 *
	 * @return true|WP_Error
	 */
	public function renameDomain( $domain ) {
		$domain = $this->parseDomain( $domain );

		$siteurl_updated = update_option( 'siteurl', $domain );
		$home_updated    = update_option( 'home', $domain );

		if ( ! $siteurl_updated || ! $home_updated ) {
			return new WP_Error(
				'wme-sitebuilder-domain-failure',
				__( 'Failed to update the siteurl or home options for the site.', 'wme-sitebuilder' )
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
		return [
			'domain'                 => $domain,
			'is_registered'          => true,
			'is_pointed'             => true,
			'uses_local_nameservers' => false,
			'can_setup'              => false,
			'nameservers'            => [
				'ns1.nexcess.net',
				'ns2.nexcess.net',
				'ns3.nexcess.net',
				'ns4.nexcess.net',
			],
		];
	}
}
