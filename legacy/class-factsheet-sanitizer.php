<?php
/**
 * Sanitization for factsheet post meta (locations, deadlines, requirements, contacts, GPA).
 *
 * Used as sanitize_callback in registered meta and by save flow.
 *
 * @package WSUWP_Graduate_School
 */

class WSUWP_Factsheet_Sanitizer {

	/**
	 * Sanitizes a GPA value.
	 *
	 * @since 0.4.0
	 *
	 * @param string $gpa The unsanitized GPA.
	 * @return string The sanitized GPA.
	 */
	public static function sanitize_gpa( $gpa ) {
		$dot_count = substr_count( $gpa, '.' );

		if ( 0 === $dot_count ) {
			$gpa = absint( $gpa ) . '.0';
		} elseif ( 1 === $dot_count ) {
			$parts = explode( '.', $gpa );
			$gpa   = absint( $parts[0] ) . '.' . absint( $parts[1] );
		} else {
			$gpa = '0.0';
		}

		return $gpa;
	}

	/**
	 * Sanitizes a set of locations stored in a string.
	 *
	 * @since 0.10.0
	 *
	 * @param array $locations Raw locations array.
	 * @return array Sanitized locations.
	 */
	public static function sanitize_locations( $locations ) {
		if ( ! is_array( $locations ) || 0 === count( $locations ) ) {
			$locations = array();
		}

		$location_names = array( 'Pullman', 'Spokane', 'Tri-Cities', 'Vancouver', 'Everett', 'Global Campus (online)' );
		$clean_locations = array();

		foreach ( $location_names as $location_name ) {
			if ( ! isset( $locations[ $location_name ] ) || ! in_array( $locations[ $location_name ], array( 'No', 'Yes', 'By Exception' ), true ) ) {
				$clean_locations[ $location_name ] = 'No';
			} else {
				$clean_locations[ $location_name ] = $locations[ $location_name ];
			}
		}

		return $clean_locations;
	}

	/**
	 * Sanitizes a set of deadlines.
	 *
	 * @since 0.4.0
	 *
	 * @param array $deadlines Raw deadlines array.
	 * @return array Sanitized deadlines.
	 */
	public static function sanitize_deadlines( $deadlines ) {
		if ( ! is_array( $deadlines ) || 0 === count( $deadlines ) ) {
			return array();
		}

		$clean_deadlines = array();

		foreach ( $deadlines as $deadline ) {
			$clean_deadline = array();

			if ( isset( $deadline['semester'] ) && in_array( $deadline['semester'], array( 'None', 'Fall', 'Spring', 'Summer' ), true ) ) {
				$clean_deadline['semester'] = $deadline['semester'];
			} else {
				$clean_deadline['semester'] = 'None';
			}

			if ( isset( $deadline['deadline'] ) ) {
				$clean_deadline['deadline'] = sanitize_text_field( $deadline['deadline'] );
			} else {
				$clean_deadline['deadline'] = '';
			}

			if ( isset( $deadline['international'] ) ) {
				$clean_deadline['international'] = sanitize_text_field( $deadline['international'] );
			} else {
				$clean_deadline['international'] = '';
			}

			$clean_deadlines[] = $clean_deadline;
		}

		return $clean_deadlines;
	}

	/**
	 * Sanitizes a set of program deadlines.
	 *
	 * @since 0.4.0
	 *
	 * @param array $deadlines_prog Raw program deadlines array.
	 * @return array Sanitized program deadlines.
	 */
	public static function sanitize_deadlines_prog( $deadlines_prog ) {
		if ( ! is_array( $deadlines_prog ) || 0 === count( $deadlines_prog ) ) {
			return array();
		}

		$clean_deadlines_prog = array();

		foreach ( $deadlines_prog as $deadline_prog ) {
			$clean_deadline_prog = array();

			if ( isset( $deadline_prog['semester'] ) && in_array( $deadline_prog['semester'], array( 'None', 'Fall', 'Spring', 'Summer' ), true ) ) {
				$clean_deadline_prog['semester'] = $deadline_prog['semester'];
			} else {
				$clean_deadline_prog['semester'] = 'None';
			}

			if ( isset( $deadline_prog['deadline_prog'] ) ) {
				$clean_deadline_prog['deadline_prog'] = sanitize_text_field( $deadline_prog['deadline_prog'] );
			} else {
				$clean_deadline_prog['deadline_prog'] = '';
			}

			if ( isset( $deadline_prog['international'] ) ) {
				$clean_deadline_prog['international'] = sanitize_text_field( $deadline_prog['international'] );
			} else {
				$clean_deadline_prog['international'] = '';
			}

			$clean_deadlines_prog[] = $clean_deadline_prog;
		}

		return $clean_deadlines_prog;
	}

	/**
	 * Sanitizes a set of contacts.
	 *
	 * @since 0.4.0
	 *
	 * @param array $contacts Raw contacts array.
	 * @return array Sanitized contacts.
	 */
	public static function sanitize_contacts( $contacts ) {
		if ( ! is_array( $contacts ) || 0 === count( $contacts ) ) {
			return array();
		}

		$clean_contacts = array();

		foreach ( $contacts as $contact ) {
			$clean_contact = array();

			if ( isset( $contact['name'] ) ) {
				$clean_contact['name'] = sanitize_text_field( $contact['name'] );
			} else {
				$clean_contact['name'] = '';
			}

			if ( isset( $contact['email'] ) ) {
				$clean_contact['email'] = sanitize_text_field( $contact['email'] );
			} else {
				$clean_contact['email'] = '';
			}

			$clean_contacts[] = $clean_contact;
		}

		return $clean_contacts;
	}

	/**
	 * Sanitizes GRE requirements.
	 *
	 * @since 0.4.0
	 *
	 * @param array $requirements_gre Raw GRE requirements array.
	 * @return array Sanitized GRE requirements.
	 */
	public static function sanitize_requirements_gre( $requirements_gre ) {
		if ( ! is_array( $requirements_gre ) || 0 === count( $requirements_gre ) ) {
			return array();
		}

		$clean_requirements = array();

		foreach ( $requirements_gre as $requirement ) {
			$clean_requirement = array();

			if ( isset( $requirement['test'] ) ) {
				$clean_requirement['test'] = sanitize_text_field( $requirement['test'] );
			} else {
				$clean_requirement['test'] = '';
			}

			if ( isset( $requirement['required'] ) && in_array( $requirement['required'], array( 'None', 'Optional', 'Yes', 'No' ), true ) ) {
				$clean_requirement['required'] = $requirement['required'];
			} else {
				$clean_requirement['required'] = 'None';
			}

			$clean_requirements[] = $clean_requirement;
		}

		return $clean_requirements;
	}

	/**
	 * Sanitizes language test requirements.
	 *
	 * @since 0.4.0
	 *
	 * @param array $requirements Raw requirements array.
	 * @return array Sanitized requirements.
	 */
	public static function sanitize_requirements( $requirements ) {
		if ( ! is_array( $requirements ) || 0 === count( $requirements ) ) {
			return array();
		}

		$clean_requirements = array();

		foreach ( $requirements as $requirement ) {
			$clean_requirement = array();

			if ( isset( $requirement['score'] ) ) {
				$clean_requirement['score'] = sanitize_text_field( $requirement['score'] );
			} else {
				$clean_requirement['score'] = '';
			}

			if ( isset( $requirement['test'] ) ) {
				$clean_requirement['test'] = sanitize_text_field( $requirement['test'] );
			} else {
				$clean_requirement['test'] = '';
			}

			if ( isset( $requirement['description'] ) ) {
				$clean_requirement['description'] = sanitize_text_field( $requirement['description'] );
			} else {
				$clean_requirement['description'] = '';
			}

			$clean_requirements[] = $clean_requirement;
		}

		return $clean_requirements;
	}
}