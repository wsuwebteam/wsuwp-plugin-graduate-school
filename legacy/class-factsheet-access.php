<?php
/**
 * Factsheet access and permissions.
 *
 * Centralizes checks for factsheet team membership (EAM) and restricted contributors.
 * Used by auth_post_meta filters and meta box display to restrict who can edit
 * certain fields (e.g. degree shortname, include in programs, taxonomies).
 *
 * @since 1.6.0
 */

class WSUWP_Factsheet_Access {

	/**
	 * Whether the user is a team member (EAM) for the given factsheet.
	 *
	 * Delegates to {@see \WSUWP\Plugin\Graduate\Factsheet_Team::is_team_member()}
	 * which also provides backward compatibility with legacy EAM post meta.
	 *
	 * @since 1.6.0
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID (factsheet).
	 *
	 * @return bool
	 */
	public static function user_is_eam_user( $user_id, $post_id ) {
		return \WSUWP\Plugin\Graduate\Factsheet_Team::is_team_member( $user_id, $post_id );
	}

	/**
	 * Determines if a user is a restricted contributor.
	 *
	 * A user is considered a restricted contributor if:
	 * - They are assigned as a factsheet team member, OR
	 * - They have a WordPress role of 'contributor', 'author', or 'editor' (only 'administrator' has full access)
	 *
	 * Restricted users cannot edit (on existing factsheets only):
	 * - Factsheet title
	 * - Factsheet display name
	 * - Include in programs list
	 * - Program Names taxonomy
	 * - Degree Types taxonomy
	 *
	 * Note: No restrictions apply when creating a NEW factsheet or editing a DRAFT - all fields are editable.
	 *
	 * @since 1.6.0
	 *
	 * @param int      $user_id The user ID to check.
	 * @param int|null $post_id Optional. The post ID for team membership check.
	 *
	 * @return bool True if the user is a restricted contributor, false otherwise.
	 */
	public static function user_is_restricted_contributor( $user_id, $post_id = null ) {
		// No restrictions for new posts or drafts - allow full access when creating/drafting a factsheet
		$post_status = $post_id ? get_post_status( $post_id ) : '';
		if ( empty( $post_id ) || in_array( $post_status, array( 'auto-draft', 'draft' ), true ) ) {
			return false;
		}

		// Check WordPress role first. Administrators are never restricted.
		$user = new WP_User( $user_id );
		$restricted_roles = array( 'subscriber', 'contributor' );
		$unrestricted_roles = array( 'administrator' );

		foreach ( $unrestricted_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return false;
			}
		}

		// Check team membership (if post_id provided) for non-admin users.
		if ( $post_id && self::user_is_eam_user( $user_id, $post_id ) ) {
			return true;
		}

		foreach ( $restricted_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Auth filter: disallow restricted contributors from changing restricted meta.
	 *
	 * Restricted contributors include factsheet team members and users with
	 * contributor/author/editor roles.
	 *
	 * @since 1.6.0
	 *
	 * @param bool   $allowed   Whether the user is allowed to update the meta.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Post ID.
	 * @param int    $user_id   User ID.
	 *
	 * @return bool
	 */
	public static function can_edit_restricted_field( $allowed, $meta_key, $object_id, $user_id ) {
		if ( self::user_is_restricted_contributor( $user_id, $object_id ) ) {
			return false;
		}

		return $allowed;
	}
}