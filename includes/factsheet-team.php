<?php

namespace WSUWP\Plugin\Graduate;

/**
 * Manages team member assignment for factsheets.
 *
 * Replaces the dependency on the Editorial Access Manager (EAM) plugin
 * for controlling which users can edit specific factsheets. The UI
 * uses an Off / Roles / Users mode selector and a WordPress-style
 * tabbed panel (matching the Program Names taxonomy box) with Chosen.js
 * multi-selects for roles and users.
 *
 * @since 1.3.0
 */
class Factsheet_Team {

	const META_KEY_MODE    = 'gsdp_team_access_mode';
	const META_KEY_ROLES   = 'gsdp_team_roles';
	const META_KEY_MEMBERS = 'gsdp_team_members';
	const NONCE_ACTION     = 'gsdp_save_team';
	const NONCE_NAME       = '_gsdp_team_nonce';
	const POST_TYPE        = 'gs-factsheet';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_team_members' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		add_filter( 'eam_post_types', array( __CLASS__, 'exclude_from_eam' ) );

		add_filter( 'map_meta_cap', array( __CLASS__, 'map_meta_cap' ), 150, 4 );
		add_action( 'transition_post_status', array( __CLASS__, 'enable_team_access_on_publish' ), 10, 3 );
		add_filter( 'manage_gs-factsheet_posts_columns', array( __CLASS__, 'add_team_column' ) );
		add_action( 'manage_gs-factsheet_posts_custom_column', array( __CLASS__, 'render_team_column' ), 10, 2 );

	}

	/**
	 * Remove factsheets from EAM's managed post types.
	 *
	 * @param array $post_types Post types managed by EAM.
	 * @return array
	 */
	public static function exclude_from_eam( $post_types ) {
		unset( $post_types[ self::POST_TYPE ] );
		return $post_types;
	}

	/**
	 * When a factsheet is published, enable team access with the post author as the only team member
	 * if EAM/team access is not already configured.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 */
	public static function enable_team_access_on_publish( $new_status, $old_status, $post ) {
		
		if ( 'publish' !== $new_status ) {
			return;
		}
		// the EAM is turned on only on the transition into publish and not on every save
		if ( $old_status === 'publish' ) {
			return;
		}

		if ( ! $post || self::POST_TYPE !== \get_post_type( $post ) ) {
			return;
		}

		if ( 'off' !== self::get_access_mode( $post->ID ) ) {
			return;
		}

		$author_id = (int) $post->post_author;
		$member_ids = $author_id > 0 ? array( $author_id ) : array();

		\update_post_meta( $post->ID, self::META_KEY_MODE, 'users' );
		\update_post_meta( $post->ID, 'eam_enable_custom_access', 'users' );

		\update_post_meta( $post->ID, self::META_KEY_MEMBERS, $member_ids );
		\update_post_meta( $post->ID, 'eam_allowed_users', $member_ids );
	}

	// ------------------------------------------------------------------
	// Meta box
	// ------------------------------------------------------------------

	public static function add_meta_box() {
		\add_meta_box(
			'gsdp-team-members',
			__( 'Editorial Access Manager', 'wsuwp-plugin-graduate-school' ),
			array( __CLASS__, 'render_meta_box' ),
			self::POST_TYPE,
			'side',
			'default'
		);
	}
	/**
	 * Add the editorial access column to the factsheet posts list table.
	 *
	 * @param array $columns The existing columns array.
	 * @return array The modified columns array.
	 */
	public static function add_team_column( $columns ) {
		// Insert our column near the end; adjust key if you want a different label.
		$columns['gsdp_editorial_access'] = __( 'Editorial Access Manager', 'wsuwp-plugin-graduate-school' );
	
		return $columns;
	}

	public static function render_team_column( $column, $post_id ) {
		if ( 'gsdp_editorial_access' !== $column ) {
			return;
		}
	
		$mode = self::get_access_mode( $post_id );
	
		if ( 'off' === $mode ) {
			echo esc_html__( 'Open access', 'wsuwp-plugin-graduate-school' );
			return;
		}
	
		if ( 'roles' === $mode ) {
			$roles = self::get_team_role_slugs( $post_id );
	
			if ( empty( $roles ) ) {
				echo esc_html__( 'No roles assigned', 'wsuwp-plugin-graduate-school' );
				return;
			}
	
			$role_names = array();
			foreach ( $roles as $role_slug ) {
				$role = get_role( $role_slug );
				if ( $role && ! empty( $role->name ) ) {
					$role_names[] = translate_user_role( $role->name );
				}
			}
	
			if ( empty( $role_names ) ) {
				echo esc_html__( 'No roles assigned', 'wsuwp-plugin-graduate-school' );
			} else {
				printf(
					/* translators: %s: comma-separated list of roles */
					esc_html__( 'Roles: %s', 'wsuwp-plugin-graduate-school' ),
					esc_html( implode( ', ', $role_names ) )
				);
			}
	
			return;
		}
	
		if ( 'users' === $mode ) {
			$user_ids = self::get_team_member_ids( $post_id );
	
			if ( empty( $user_ids ) ) {
				echo esc_html__( 'No users assigned', 'wsuwp-plugin-graduate-school' );
				return;
			}
	
			$user_names = array();
			foreach ( $user_ids as $user_id ) {
				$user = get_userdata( $user_id );
				if ( $user ) {
					// You can switch to display_name if you prefer full names.
					$user_names[] = $user->user_login;
				}
			}
	
			if ( empty( $user_names ) ) {
				echo esc_html__( 'No users assigned', 'wsuwp-plugin-graduate-school' );
			} else {
				printf(
					/* translators: %s: comma-separated list of users */
					esc_html__( ' %s', 'wsuwp-plugin-graduate-school' ),
					esc_html( implode( ', ', $user_names ) )
				);
			}
	
			return;
		}
	
		// Fallback for unexpected modes.
		echo esc_html__( 'Unknown access mode', 'wsuwp-plugin-graduate-school' );
	}
	/**
	 * Render the meta box.
	 *
	 * Layout:
	 *   1. Status badge showing current access state
	 *   2. Segmented-control mode selector (Off / Roles / Users)
	 *   3. Tabbed panel with Chosen.js multi-selects
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( $post ) {
		global $wp_roles;

		$mode            = self::get_access_mode( $post->ID );
		$selected_roles  = self::get_team_role_slugs( $post->ID );
		$selected_users  = self::get_team_member_ids( $post->ID );
		$available_roles = self::get_editable_roles_for_post_type();
		$available_users = self::get_eligible_users();

		$roles_active = ( 'roles' === $mode );
		$users_active = ( 'users' === $mode );

		$roles_count = count( $selected_roles );
		$users_count = count( $selected_users );

		\wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div id="gsdp-eam-wrap">

			<!-- Status badge -->
			<div class="gsdp-eam-status">
				<?php if ( 'off' === $mode ) : ?>
					<span class="gsdp-eam-badge gsdp-eam-badge--off">
						<span class="dashicons dashicons-unlock"></span>
						<?php esc_html_e( 'Open access', 'wsuwp-plugin-graduate-school' ); ?>
					</span>
				<?php elseif ( 'roles' === $mode ) : ?>
					<span class="gsdp-eam-badge gsdp-eam-badge--active">
						<span class="dashicons dashicons-groups"></span>
						<?php
						printf(
							/* translators: %d: number of roles */
							esc_html( _n( '%d role assigned', '%d roles assigned', $roles_count, 'wsuwp-plugin-graduate-school' ) ),
							$roles_count
						);
						?>
					</span>
				<?php else : ?>
					<span class="gsdp-eam-badge gsdp-eam-badge--active">
						<span class="dashicons dashicons-admin-users"></span>
						<?php
						printf(
							/* translators: %d: number of users */
							esc_html( _n( '%d user assigned', '%d users assigned', $users_count, 'wsuwp-plugin-graduate-school' ) ),
							$users_count
						);
						?>
					</span>
				<?php endif; ?>
			</div>

			<!-- Segmented-control mode selector -->
			<div class="gsdp-eam-modes">
				<button type="button" class="gsdp-eam-mode-btn<?php echo 'off' === $mode ? ' active' : ''; ?>" data-mode="off">
					<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Off', 'wsuwp-plugin-graduate-school' ); ?>
				</button>
				<button type="button" class="gsdp-eam-mode-btn<?php echo 'roles' === $mode ? ' active' : ''; ?>" data-mode="roles">
					<span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Roles', 'wsuwp-plugin-graduate-school' ); ?>
				</button>
				<button type="button" class="gsdp-eam-mode-btn<?php echo 'users' === $mode ? ' active' : ''; ?>" data-mode="users">
					<span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Users', 'wsuwp-plugin-graduate-school' ); ?>
				</button>
				<input type="hidden" name="gsdp_team_access_mode" id="gsdp_team_access_mode" value="<?php echo esc_attr( $mode ); ?>" />
			</div>

			<!-- Tabbed panel -->
			<div id="gsdp-eam-panels" <?php echo 'off' === $mode ? 'style="display:none;"' : ''; ?>>

				<ul id="gsdp-eam-tabs">
					<li<?php echo $roles_active ? ' class="active"' : ''; ?>>
						<a href="#gsdp-team-panel-roles">
							<?php esc_html_e( 'Roles', 'wsuwp-plugin-graduate-school' ); ?>
							<span class="gsdp-eam-count" id="gsdp-eam-roles-count"><?php echo (int) $roles_count; ?></span>
						</a>
					</li>
					<li<?php echo $users_active ? ' class="active"' : ''; ?>>
						<a href="#gsdp-team-panel-users">
							<?php esc_html_e( 'Users', 'wsuwp-plugin-graduate-school' ); ?>
							<span class="gsdp-eam-count" id="gsdp-eam-users-count"><?php echo (int) $users_count; ?></span>
						</a>
					</li>
				</ul>

				<!-- Roles tab panel -->
				
				<div id="gsdp-team-panel-roles" class="gsdp-eam-tab-panel" <?php echo ! $roles_active ? 'style="display:none;"' : ''; ?>>
					<div class="gsdp-multiselect-loader" aria-hidden="true">
						<span class="gsdp-multiselect-loader-spinner"></span>
						<span class="gsdp-multiselect-loader-text"><?php esc_html_e( 'Loading…', 'wsuwp-plugin-graduate-school' ); ?></span>
					</div>
					<select multiple name="gsdp_team_roles[]" id="gsdp_team_roles">
						<?php foreach ( $available_roles as $role_slug => $role_name ) : ?>
							<option
								value="<?php echo esc_attr( $role_slug ); ?>"
								<?php if ( 'administrator' === $role_slug ) : ?>selected disabled
								<?php elseif ( in_array( $role_slug, $selected_roles, true ) ) : ?>selected<?php endif; ?>
							>
								<?php echo esc_html( $role_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Users tab panel -->
				<div id="gsdp-team-panel-users" class="gsdp-eam-tab-panel" <?php echo ! $users_active ? 'style="display:none;"' : ''; ?>>
					<div class="gsdp-multiselect-loader" aria-hidden="true">
						<span class="gsdp-multiselect-loader-spinner"></span>
						<span class="gsdp-multiselect-loader-text"><?php esc_html_e( 'Loading…', 'wsuwp-plugin-graduate-school' ); ?></span>
					</div>
					<select multiple name="gsdp_team_members[]" id="gsdp_team_members">
						<?php foreach ( $available_users as $user_object ) :
							$user     = new \WP_User( $user_object->ID );
							$is_admin = in_array( 'administrator', $user->roles, true );
							?>
							<option
								value="<?php echo absint( $user_object->ID ); ?>"
								<?php if ( $is_admin ) : ?>selected disabled
								<?php elseif ( in_array( (int) $user_object->ID, $selected_users, true ) ) : ?>selected<?php endif; ?>
							>
								<?php echo esc_attr( $user->user_login ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

			</div>

		</div>
		<?php
	}

	// ------------------------------------------------------------------
	// Helpers for the meta box
	// ------------------------------------------------------------------

	/**
	 * Return role_slug => translated role name for roles with edit_posts.
	 *
	 * @return array
	 */
	private static function get_editable_roles_for_post_type() {
		global $wp_roles;

		$post_type_object = \get_post_type_object( self::POST_TYPE );
		$edit_posts_cap   = $post_type_object ? $post_type_object->cap->edit_posts : 'edit_posts';
		$roles            = \get_editable_roles();
		$result           = array();

		foreach ( $roles as $role_slug => $role_data ) {
			$role = \get_role( $role_slug );
			if ( $role && $role->has_cap( $edit_posts_cap ) ) {
				$result[ $role_slug ] = \translate_user_role( $wp_roles->roles[ $role_slug ]['name'] );
			}
		}

		return $result;
	}

	/**
	 * Return all users registered in the system (for EAM user assignment).
	 *
	 * @return \WP_User[]
	 */
	private static function get_eligible_users() {
		return \get_users( array(
			'orderby' => 'user_login',
			'order'   => 'ASC',
		) );
	}

	// ------------------------------------------------------------------
	// Persist
	// ------------------------------------------------------------------

	/**
	 * Save the access mode, allowed roles, and team member IDs.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function save_team_members( $post_id, $post ) {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! \wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Access mode.
		$mode = isset( $_POST['gsdp_team_access_mode'] ) ? \sanitize_text_field( $_POST['gsdp_team_access_mode'] ) : 'off';

		if ( ! in_array( $mode, array( 'off', 'roles', 'users' ), true ) ) {
			$mode = 'off';
		}

		\update_post_meta( $post_id, self::META_KEY_MODE, $mode );

		if ( 'off' === $mode ) {
			\delete_post_meta( $post_id, 'eam_enable_custom_access' );
		} else {
			\update_post_meta( $post_id, 'eam_enable_custom_access', $mode );
		}

		// Roles — save when mode is 'roles', preserve existing when mode is 'users'.
		if ( 'roles' === $mode ) {
			$role_slugs = array();
			if ( ! empty( $_POST['gsdp_team_roles'] ) && is_array( $_POST['gsdp_team_roles'] ) ) {
				$role_slugs = array_map( 'sanitize_text_field', $_POST['gsdp_team_roles'] );
			}
			\update_post_meta( $post_id, self::META_KEY_ROLES, $role_slugs );
			\update_post_meta( $post_id, 'eam_allowed_roles', $role_slugs );
		} elseif ( 'off' === $mode ) {
			\delete_post_meta( $post_id, self::META_KEY_ROLES );
		}

		// Users — save when mode is 'users', preserve existing when mode is 'roles'.
		if ( 'users' === $mode ) {
			$member_ids = array();
			if ( ! empty( $_POST['gsdp_team_members'] ) && is_array( $_POST['gsdp_team_members'] ) ) {
				$member_ids = array_map( 'absint', $_POST['gsdp_team_members'] );
				$member_ids = array_values( array_unique( array_filter( $member_ids ) ) );
			}
			\update_post_meta( $post_id, self::META_KEY_MEMBERS, $member_ids );
			\update_post_meta( $post_id, 'eam_allowed_users', $member_ids );
		} elseif ( 'off' === $mode ) {
			\delete_post_meta( $post_id, self::META_KEY_MEMBERS );
		}
	}

	// ------------------------------------------------------------------
	// Admin assets
	// ------------------------------------------------------------------

	public static function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = \get_current_screen();

		if ( ! $screen || self::POST_TYPE !== $screen->id ) {
			return;
		}

		// Plugin styles & script (custom multi-select, no external JS).
		
		\wp_enqueue_script(
			'gsdp-team',
			Plugin::get( 'url' ) . 'js/factsheet-team.js',
			array( 'jquery' ),
			Plugin::get( 'version' ),
			true
		);

		\wp_enqueue_style(
			'gsdp-team',
			Plugin::get( 'url' ) . 'css/factsheet-team.css',
			array(),
			Plugin::get( 'version' )
		);
	}

	// ------------------------------------------------------------------
	// Public API – reading access state
	// ------------------------------------------------------------------

	/**
	 * Get the access mode for a factsheet.
	 *
	 * Falls back to EAM data when the new key has not been saved yet.
	 *
	 * @param int $post_id Post ID.
	 * @return string 'off', 'roles', or 'users'.
	 */
	public static function get_access_mode( $post_id ) {
		$mode = \get_post_meta( $post_id, self::META_KEY_MODE, true );

		if ( in_array( $mode, array( 'off', 'roles', 'users' ), true ) ) {
			return $mode;
		}

		// Backward compatibility with EAM.
		$eam = \get_post_meta( $post_id, 'eam_enable_custom_access', true );

		if ( in_array( $eam, array( 'roles', 'users' ), true ) ) {
			return $eam;
		}

		return 'off';
	}

	/**
	 * Get the allowed role slugs for a factsheet.
	 *
	 * Falls back to EAM data when the new key has not been saved yet.
	 *
	 * @param int $post_id Post ID.
	 * @return string[] Array of role slugs.
	 */
	public static function get_team_role_slugs( $post_id ) {
		$roles = \get_post_meta( $post_id, self::META_KEY_ROLES, true );

		if ( ! empty( $roles ) && is_array( $roles ) ) {
			return $roles;
		}

		// Backward compatibility with EAM.
		$eam_roles = \get_post_meta( $post_id, 'eam_allowed_roles', true );

		if ( ! empty( $eam_roles ) && is_array( $eam_roles ) ) {
			return $eam_roles;
		}

		return array();
	}

	/**
	 * Get team member IDs for a factsheet.
	 *
	 * Falls back to EAM data when the new key has not been saved yet.
	 *
	 * @param int $post_id Post ID.
	 * @return int[] Array of user IDs.
	 */
	public static function get_team_member_ids( $post_id ) {
		$members = \get_post_meta( $post_id, self::META_KEY_MEMBERS, true );

		if ( ! empty( $members ) && is_array( $members ) ) {
			return array_map( 'intval', $members );
		}

		// Backward compatibility with EAM.
		$eam_access = \get_post_meta( $post_id, 'eam_enable_custom_access', true );

		if ( 'users' === $eam_access ) {
			$eam_users = (array) \get_post_meta( $post_id, 'eam_allowed_users', true );
			return array_map( 'intval', array_filter( $eam_users ) );
		}

		return array();
	}

	/**
	 * Whether team-based access control is active for a factsheet.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function has_team_access( $post_id ) {
		return 'off' !== self::get_access_mode( $post_id );
	}

	/**
	 * Whether the given user is a team member on a factsheet
	 * (considering both 'roles' and 'users' modes).
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_team_member( $user_id, $post_id ) {
		$mode = self::get_access_mode( $post_id );

		if ( 'users' === $mode ) {
			$members = self::get_team_member_ids( $post_id );
			return in_array( (int) $user_id, $members, true );
		}

		if ( 'roles' === $mode ) {
			$allowed_roles = self::get_team_role_slugs( $post_id );
			$user          = new \WP_User( $user_id );

			return ! empty( $user->roles ) && count( array_intersect( $user->roles, $allowed_roles ) ) > 0;
		}

		return false;
	}

	// ------------------------------------------------------------------
	// Capability mapping
	// ------------------------------------------------------------------

	/**
	 * Controls edit and delete capabilities for factsheets based on
	 * team membership.
	 *
	 * When team access is active:
	 *  - Team members (non-admin) may edit but NOT delete.
	 *  - Non-team, non-admin users are denied edit access.
	 *  - Administrators always retain full access.
	 *
	 * @param string[] $caps    Primitive caps required.
	 * @param string   $cap     Capability being checked.
	 * @param int      $user_id User ID.
	 * @param array    $args    Additional arguments (post ID, etc.).
	 * @return string[]
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		$edit_caps   = array( 'edit_post', 'edit_page', 'edit_others_posts', 'edit_others_pages', 'publish_posts', 'publish_pages' );
		$delete_caps = array( 'delete_post', 'delete_page' );

		if ( ! in_array( $cap, array_merge( $edit_caps, $delete_caps ), true ) ) {
			return $caps;
		}

		$post_id = isset( $args[0] ) ? (int) $args[0] : null;

		if ( ! $post_id && ! empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id = (int) $_GET['post'];
		}

		if ( ! $post_id && ! empty( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id = (int) $_POST['post_ID'];
		}

		if ( ! $post_id ) {
			return $caps;
		}

		if ( self::POST_TYPE !== \get_post_type( $post_id ) ) {
			return $caps;
		}

		if ( ! self::has_team_access( $post_id ) ) {
			return $caps;
		}

		$user = new \WP_User( $user_id );

		if ( in_array( 'administrator', $user->roles, true ) ) {
			return $caps;
		}

		$is_member = self::is_team_member( $user_id, $post_id );

		if ( in_array( $cap, $delete_caps, true ) ) {
			if ( $is_member ) {
				$caps[] = 'do_not_allow';
			}
		} else {
			if ( $is_member ) {
				$caps = array();
			} else {
				$caps[] = 'do_not_allow';
			}
		}

		return $caps;
	}
}

Factsheet_Team::init();
