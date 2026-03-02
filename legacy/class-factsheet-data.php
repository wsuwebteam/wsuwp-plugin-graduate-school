<?php

class WSUWP_Factsheet_Data {

/**
	 * Returns a usable subset of data for displaying a factsheet.
	 *
	 * @since 0.4.0
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public static function get_factsheet_data( $post_id ) {
		$factsheet_data = get_registered_metadata( 'post', $post_id );

		$data = array(
			'degree_id' => 0,
			'shortname' => '',
			'description' => '',
			'accepting_applications' => 'No',
			'faculty' => array(),
			'students' => 0,
			'aided' => 0,
			'totalfac' => 0,
			'totalcorefac' => 0,
			'degree_url' => 'Not available',
			'student_learning_outcome_url' =>'', 
			'application_url' => 'https://gradschool.wsu.edu/apply/',
			'handbook_url' => '',
			'deadlines' => array(),
			'deadlines_prog' => array(),
			'requirements' => array(),
			'requirements_gre' => array(),
			'contacts' => array(),
			'locations' => array(
				'Pullman' => 'No',
				'Spokane' => 'No',
				'Tri-Cities' => 'No',
				'Vancouver' => 'No',
				'Everett' => 'No',
				'Global Campus (online)' => 'No',
			),
			'global_URL' => '',
			'admission_requirements',
			'student_opportunities',
			'career_opportunities',
			'career_placements',
			'student_learning_outcome',
			'public' => 'No',
		);

		if ( isset( $factsheet_data['gsdp_degree_description'][0] ) ) {
			$data['description'] = $factsheet_data['gsdp_degree_description'][0];
		}

		// if ( isset( $factsheet_data['gsdp_degree_id'][0] ) ) {
		// 	$data['degree_id'] = $factsheet_data['gsdp_degree_id'][0];
		// }

		if ( isset( $factsheet_data['gsdp_include_in_programs'][0] ) && 1 === absint( $factsheet_data['gsdp_include_in_programs'][0] ) ) {
			$data['public'] = 'Yes';
		}

		if ( isset( $factsheet_data['gsdp_degree_shortname'][0] ) ) {
			$data['shortname'] = $factsheet_data['gsdp_degree_shortname'][0];
		}

		if ( isset( $factsheet_data['gsdp_accepting_applications'][0] ) && 1 === absint( $factsheet_data['gsdp_accepting_applications'][0] ) ) {
			$data['accepting_applications'] = 'Yes';
		}

		if ( isset( $factsheet_data['gsdp_grad_students_total'][0] ) ) {
			$data['students'] = $factsheet_data['gsdp_grad_students_total'][0];
		}

		if ( isset( $factsheet_data['gsdp_grad_faculty_total'][0] ) ) {
			$data['totalfac'] = $factsheet_data['gsdp_grad_faculty_total'][0];
		}

		if ( isset( $factsheet_data['gsdp_grad_core_faculty_total'][0] ) ) {
			$data['totalcorefac'] = $factsheet_data['gsdp_grad_core_faculty_total'][0];
		}

		if ( isset( $factsheet_data['gsdp_grad_students_total'][0] ) ) {
			$data['students'] = $factsheet_data['gsdp_grad_students_total'][0];
		}

		if ( isset( $factsheet_data['gsdp_grad_students_aided'][0] ) ) {
			if ( 0 === absint( $data['students'] ) ) {
				$data['aided'] = '0.00';
			} else {
				$data['aided'] = $factsheet_data['gsdp_grad_students_aided'][0];
			}
		}

		if ( isset( $factsheet_data['gsdp_degree_url'][0] ) ) {
			$data['degree_url'] = $factsheet_data['gsdp_degree_url'][0];
		}

		if ( isset( $factsheet_data['gsdp_application_url'][0] ) ) {
			$data['application_url'] = $factsheet_data['gsdp_application_url'][0];
		}
		

		if ( isset( $factsheet_data['gsdp_student_learning_outcome_url'][0] ) ) {
			$data['student_learning_outcome_url'] = $factsheet_data['gsdp_student_learning_outcome_url'][0];
		}


		if ( isset( $factsheet_data['gsdp_program_handbook_url'][0] ) ) {
			$data['handbook_url'] = $factsheet_data['gsdp_program_handbook_url'][0];
		}

		if ( isset( $factsheet_data['gsdp_deadlines'][0] ) ) {
			$data['deadlines'] = maybe_unserialize( $factsheet_data['gsdp_deadlines'][0] );

			if ( ! is_array( $data['deadlines'] ) ) {
				$data['deadlines'] = array();
			}
		}

		if ( isset( $factsheet_data['gsdp_deadlines_prog'][0] ) ) {
			$data['deadlines_prog'] = maybe_unserialize( $factsheet_data['gsdp_deadlines_prog'][0] );

			if ( ! is_array( $data['deadlines_prog'] ) ) {
				$data['deadlines_prog'] = array();
			}
		}

		if ( isset( $factsheet_data['gsdp_requirements_gre'][0] ) ) {
			$data['requirements_gre'] = maybe_unserialize( $factsheet_data['gsdp_requirements_gre'][0] );

			if ( ! is_array( $data['requirements_gre'] ) ) {
				$data['requirements_gre'] = array();
			}
		}

		if ( isset( $factsheet_data['gsdp_requirements'][0] ) ) {
			$data['requirements'] = maybe_unserialize( $factsheet_data['gsdp_requirements'][0] );

			if ( ! is_array( $data['requirements'] ) ) {
				$data['requirements'] = array();
			}
		}

		if ( isset( $factsheet_data['gsdp_contacts'][0] ) ) {
			$data['gscontacts'] = maybe_unserialize( $factsheet_data['gsdp_contacts'][0] );

			if ( ! is_array( $data['gscontacts'] ) ) {
				$data['gscontacts'] = array();
			}
		}

		if ( isset( $factsheet_data['gsdp_locations'][0] ) ) {
			$locations = maybe_unserialize( $factsheet_data['gsdp_locations'][0] );
			$data['locations'] = wp_parse_args( $locations, $data['locations'] );
		}

		if ( isset( $factsheet_data['gsdp_admission_requirements'][0] ) ) {
			$data['admission_requirements'] = $factsheet_data['gsdp_admission_requirements'][0];
		}

		if ( isset( $factsheet_data['gsdp_student_opportunities'][0] ) ) {
			$data['student_opportunities'] = $factsheet_data['gsdp_student_opportunities'][0];
		}

		if ( isset( $factsheet_data['gsdp_career_opportunities'][0] ) ) {
			$data['career_opportunities'] = $factsheet_data['gsdp_career_opportunities'][0];
		}

		if ( isset( $factsheet_data['gsdp_career_placements'][0] ) ) {
			$data['career_placements'] = $factsheet_data['gsdp_career_placements'][0];
		}

		if ( isset( $factsheet_data['gsdp_global_URL'][0] ) ) {
			$data['global_URL'] = $factsheet_data['gsdp_global_URL'][0];
		}

		return $data;
	}
}