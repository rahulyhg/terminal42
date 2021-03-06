<?php
/*
 * IMPORTANT: READ THE LICENSE AGREEMENT CAREFULLY.
 *
 * BY INSTALLING, COPYING, RUNNING, OR OTHERWISE USING THE 
 * WPSSO SCHEMA JSON-LD (WPSSO JSON) PRO APPLICATION, YOU AGREE
 * TO BE BOUND BY THE TERMS OF ITS LICENSE AGREEMENT.
 * 
 * License: Nontransferable License for a WordPress Site Address URL
 * License URI: http://surniaulula.com/wp-content/plugins/wpsso-schema-json-ld/license/pro.txt
 *
 * IF YOU DO NOT AGREE TO THE TERMS OF ITS LICENSE AGREEMENT,
 * PLEASE DO NOT INSTALL, RUN, COPY, OR OTHERWISE USE THE
 * WORDPRESS SOCIAL SHARING OPTIMIZATION (WPSSO) PRO APPLICATION.
 * 
 * Copyright 2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoJsonProHeadLocalBusiness' ) ) {

	class WpssoJsonProHeadLocalBusiness {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array(
				'json_data_http_schema_org_localbusiness' => 4,	// $json_data, $mod, $mt_og, $user_id
			) );
		}

		public function filter_json_data_http_schema_org_localbusiness( $json_data, $mod, $mt_og, $user_id ) {

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$ret = array();

			if ( ! empty( $mt_og['place:location:latitude'] ) &&
				! empty( $mt_og['place:location:longitude'] ) &&
				! empty( $mt_og['place:business:service_radius'] ) ) {

				$ret['areaServed'] = WpssoSchema::get_item_type_context( 'http://schema.org/GeoShape',
					array( 'circle' => $mt_og['place:location:latitude'].' '.
						$mt_og['place:location:longitude'].' '.
						$mt_og['place:business:service_radius'] ) );
			}

			if ( preg_grep( '/^place:business:day:/', array_keys( $mt_og ) ) ) {
				/*
				 * Array (
				 *	[place:business:day:monday:open] => 09:00
				 *	[place:business:day:monday:close] => 17:00
				 *	[place:business:day:publicholidays:open] => 09:00
				 *	[place:business:day:publicholidays:close] => 17:00
				 *	[place:business:season:from] => 2016-04-01
				 *	[place:business:season:to] => 2016-05-01
				 * )
				 */
				$opening_hours = array();
				foreach ( $this->p->cf['form']['weekdays'] as $day => $label ) {
					if ( ! empty( $mt_og['place:business:day:'.$day.':open'] ) &&
						! empty( $mt_og['place:business:day:'.$day.':close'] ) ) {
	
						$dayofweek = array(
							'@context' => 'http://schema.org',
							'@type' => 'OpeningHoursSpecification',
							'dayOfWeek' => $label,
						);
						foreach ( array(
							'opens' => 'place:business:day:'.$day.':open',
							'closes' => 'place:business:day:'.$day.':close',
							'validFrom' => 'place:business:season:from',
							'validThrough' => 'place:business:season:to',
						) as $prop_name => $mt_key )
							if ( isset( $mt_og[$mt_key] ) )
								$dayofweek[$prop_name] = $mt_og[$mt_key];
						$opening_hours[] = $dayofweek;
					}
				}
				if ( ! empty( $opening_hours ) )
					$ret['openingHoursSpecification'] = $opening_hours;
			}

			return WpssoSchema::return_data_from_filter( $json_data, $ret );
		}
	}
}

?>
