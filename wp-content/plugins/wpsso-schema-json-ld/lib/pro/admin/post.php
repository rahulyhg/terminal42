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

if ( ! class_exists( 'WpssoJsonProAdminPost' ) ) {

	class WpssoJsonProAdminPost {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'post_header_rows' => 4,	// $table_rows, $form, $head, $mod
			) );
		}

		public function filter_post_header_rows( $table_rows, $form, $head, $mod ) {

			$schema_types = $this->p->schema->get_schema_types_select();	// $add_none = true
			$title_max_len = $this->p->options['og_title_len'];
			$desc_max_len = $this->p->options['schema_desc_len'];
			$headline_max_len = WpssoJsonConfig::$cf['schema']['article']['headline']['max_len'];
			$org_names = array( 'none' => '[None]', 'site' => _x( 'Website', 'option value', 'wpsso-schema-json-ld' ) );
			$perf_names = array( 'none' => '[None]' );
			$auto_draft_msg = sprintf( __( 'Save a draft version or publish the %s to update this value.',
				'wpsso-schema-json-ld' ), ucfirst( $mod['post_type'] ) );

			if ( ! empty( $this->p->cf['plugin']['wpssoorg'] ) &&
				empty( $this->p->cf['plugin']['wpssoorg']['version'] ) ) {

				$info = $this->p->cf['plugin']['wpssoorg'];
				$org_req_msg = ' <em><a href="'.$info['url']['download'].'" target="_blank">'.
					sprintf( _x( '%s extension required', 'option comment', 'wpsso-schema-json-ld' ),
						$info['short'] ).'</a></em>';
			} else {
				// combine arrays, preserving numeric key associations
				$org_names += WpssoOrgOrganization::get_org_names();
				$perf_names += WpssoOrgOrganization::get_org_names( 'performing.group' );
				$org_req_msg = '';
			}

			// javascript hide/show classes for schema type rows
			$tr_class = array(
				'article' => $this->p->schema->get_schema_type_css_classes( 'article' ),
				'event' => $this->p->schema->get_schema_type_css_classes( 'event' ),
				'recipe' => $this->p->schema->get_schema_type_css_classes( 'recipe' ),
				'review' => $this->p->schema->get_schema_type_css_classes( 'review' ),
			);

			foreach ( array( 'schema_desc', 'subsection_schema' ) as $key )
				if ( isset( $table_rows[$key] ) )
					unset ( $table_rows[$key] );

			$form_rows = array(
				'subsection_schema' => array(
					'td_class' => 'subsection',
					'header' => 'h4',
					'label' => _x( 'Google Structured Data / Schema Markup', 'metabox title', 'wpsso-schema-json-ld' )
				),
				/*
				 * All Schema Types
				 */
				'schema_title' => array(
					'label' => _x( 'Schema Item Name', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_title',
					'no_auto_draft' => true,
					'content' => $form->get_input( 'schema_title', 'wide', $this->p->cf['lca'].'_schema_title', 
						$title_max_len, $this->p->webpage->get_title( $title_max_len, '...', $mod ) ),
				),
				'schema_desc' => array(
					'label' => _x( 'Schema Description', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_desc',
					'no_auto_draft' => true,
					'content' => $form->get_textarea( 'schema_desc', null, $this->p->cf['lca'].'_schema_desc', 
						$desc_max_len, $this->p->webpage->get_description( $desc_max_len, '...', $mod ) ),
				),
				'schema_is_main' => array(
					'label' => _x( 'Main Entity of Page', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_is_main',
					'content' => $form->get_checkbox( 'schema_is_main' ),
				),
				'schema_type' => array(
					'label' => _x( 'Schema Item Type', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_type',
					'content' => $form->get_select( 'schema_type', $schema_types,
						'long_name', '', true, false, true, 'unhide_rows' ),
				),
				/*
				 * Schema Article
				 */
				'schema_pub_org_id' => array(
					'tr_class' => 'schema_type '.$tr_class['article'],
					'label' => _x( 'Article Publisher', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_pub_org_id',
					'content' => $form->get_select( 'schema_pub_org_id', $org_names, 'long_name', '', true, 
						( $org_req_msg ? true : false ) ).$org_req_msg,
				),
				'schema_headline' => array(
					'tr_class' => 'schema_type '.$tr_class['article'],
					'label' => _x( 'Article Headline', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_headline',
					'no_auto_draft' => true,
					'content' => $form->get_input( 'schema_headline', 'wide', $this->p->cf['lca'].'_schema_headline', 
						$headline_max_len, $this->p->webpage->get_title( $headline_max_len, '...', $mod ) ),
				),
				/*
				 * Schema Event
				 */
				'schema_event_org_id' => array(
					'tr_class' => 'schema_type '.$tr_class['event'],
					'label' => _x( 'Event Organizer', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_event_org_id',
					'content' => $form->get_select( 'schema_event_org_id', $org_names, 'long_name', '', true, 
						( $org_req_msg ? true : false ) ).$org_req_msg,
				),
				'schema_event_perf_id' => array(
					'tr_class' => 'schema_type '.$tr_class['event'],
					'label' => _x( 'Event Performer', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_event_perf_id',
					'content' => $form->get_select( 'schema_event_perf_id', $perf_names, 'long_name', '', true, 
						( $org_req_msg ? true : false ) ).$org_req_msg,
				),
				/*
				 * Schema Recipe
				 */
				'schema_recipe_prep_time' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Preperation Time', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_prep_time',
					'content' => $form->get_input( 'schema_recipe_prep_days', 'short', '', 0, 0 ).' days, '.
						$form->get_input( 'schema_recipe_prep_hours', 'short', '', 0, 0 ).' hours, '.
						$form->get_input( 'schema_recipe_prep_mins', 'short', '', 0, 0 ).' mins, '.
						$form->get_input( 'schema_recipe_prep_secs', 'short', '', 0, 0 ).' secs',
				),
				'schema_recipe_cook_time' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Cooking Time', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_cook_time',
					'content' => $form->get_input( 'schema_recipe_cook_days', 'short', '', 0, 0 ).' days, '.
						$form->get_input( 'schema_recipe_cook_hours', 'short', '', 0, 0 ).' hours, '.
						$form->get_input( 'schema_recipe_cook_mins', 'short', '', 0, 0 ).' mins, '.
						$form->get_input( 'schema_recipe_cook_secs', 'short', '', 0, 0 ).' secs',
				),
				'schema_recipe_total_time' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Total Time', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_total_time',
					'content' => $form->get_input( 'schema_recipe_total_days', 'short', '', 0, 0 ).' days, '.
						$form->get_input( 'schema_recipe_total_hours', 'short', '', 0, 0 ).' hours, '.
						$form->get_input( 'schema_recipe_total_mins', 'short', '', 0, 0 ).' mins, '.
						$form->get_input( 'schema_recipe_total_secs', 'short', '', 0, 0 ).' secs',
				),
				'schema_recipe_calories' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Total Calories', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_calories',
					'content' => $form->get_input( 'schema_recipe_calories', 'medium' ),
				),
				'schema_recipe_yield' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Quantity', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_yield',
					'content' => $form->get_input( 'schema_recipe_yield', 'long_name' ),
				),
				'schema_recipe_ingredients' => array(
					'tr_class' => 'schema_type '.$tr_class['recipe'],
					'label' => _x( 'Recipe Ingredients', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_recipe_ingredients',
					'content' => $form->get_input_multi( 'schema_recipe_ingredient', 'long_name' ),
				),
				/*
				 * Schema Review
				 */
				'schema_review_item_type' => array(
					'tr_class' => 'schema_type '.$tr_class['review'],
					'label' => _x( 'Reviewed Item Type', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_review_item_type',
					'content' => $form->get_select( 'schema_review_item_type', $schema_types, 'long_name required' ),
				),
				'schema_review_item_url' => array(
					'tr_class' => 'schema_type '.$tr_class['review'],
					'label' => _x( 'Reviewed Item URL', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_review_item_url',
					'content' => $form->get_input( 'schema_review_item_url', 'wide' ),
				),
				// used as schema.org/Rating, not schema.org/aggregateRating
				'schema_review_rating' => array(
					'tr_class' => 'schema_type '.$tr_class['review'],
					'label' => _x( 'Reviewed Item Rating', 'option label', 'wpsso-schema-json-ld' ),
					'th_class' => 'medium', 'tooltip' => 'meta-schema_review_rating',
					'content' => $form->get_input( 'schema_review_rating',
						'short required', '', 0, $form->defaults['schema_review_rating'] ).
						' '._x( 'from', 'option comment', 'wpsso-schema-json-ld' ).' '.
							$form->get_input( 'schema_review_rating_from',
								'short', '', 0, $form->defaults['schema_review_rating_from'] ).
						' '._x( 'to', 'option comment', 'wpsso-schema-json-ld' ).' '.
							$form->get_input( 'schema_review_rating_to',
								'short', '', 0, $form->defaults['schema_review_rating_to'] ),
				),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod, $auto_draft_msg );
		}
	}
}

?>
