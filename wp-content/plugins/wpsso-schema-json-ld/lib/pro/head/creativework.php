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

if ( ! class_exists( 'WpssoJsonProHeadCreativeWork' ) ) {

	class WpssoJsonProHeadCreativeWork {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array(
				'json_data_http_schema_org_creativework' => 5,	// $json_data, $use_post, $mod, $mt_og, $user_id
			) );
		}

		public function filter_json_data_http_schema_org_creativework( $json_data, $use_post, $mod, $mt_og, $user_id ) {

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];
			$ret = array();

			/*
			 * Property:
			 * 	datepublished
			 * 	datemodified
			 */
			WpssoSchema::add_data_itemprop_from_assoc( $ret, $mt_og, array(
				'datepublished' => 'article:published_time',
				'datemodified' => 'article:modified_time',
			) );

			/*
			 * Property:
			 *	inLanguage
			 */
			$ret['inLanguage'] = get_locale();

			/*
			 * Property:
			 *	publisher as http://schema.org/Organization
			 */
			if ( isset( $mt_og['schema:type:id'] ) &&
				$this->p->schema->schema_type_child_of( $mt_og['schema:type:id'], 'article' ) ) {

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'skipping publisher: schema type '.$mt_og['schema:type:id'].' is child of article (publisher added by article filter)' );

			} else {
				$org_id = is_object( $mod['obj'] ) ?
					$mod['obj']->get_options( $mod['id'], 'schema_pub_org_id' ) : false;	// null, false, 'none', 'site', or number (including 0)

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'custom publisher / organization id is '.
						( empty( $org_id ) ? 'empty' : $org_id ) );

				WpssoSchema::add_single_organization_data( $ret['publisher'], $mod, $org_id, 'org_logo_url', false );	// $list_element = false
			}

			/*
			 * Property:
			 *	author as http://schema.org/Person
			 *	contributor as http://schema.org/Person
			 */
			if ( $user_id > 0 )
				WpssoSchema::add_author_and_coauthor_data( $ret, $mod, $user_id );

			/*
			 * Property:
			 *	image as http://schema.org/ImageObject
			 *	video as http://schema.org/VideoObject
			 */
			WpssoJsonSchema::add_media_data( $ret, $use_post, $mod, $mt_og, $user_id );

			if ( empty( $ret['image'] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'creativework image is missing and required' );
				if ( is_admin() && ( ! $mod['is_post'] || $mod['post_status'] === 'publish' ) )
					$this->p->notice->err( $this->p->msgs->get( 'notice-missing-schema-image' ) );
			}

			return WpssoSchema::return_data_from_filter( $json_data, $ret );
		}
	}
}

?>