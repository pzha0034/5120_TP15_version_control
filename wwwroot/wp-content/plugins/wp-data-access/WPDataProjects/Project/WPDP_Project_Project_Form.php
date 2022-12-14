<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataProjects\Parent_Child\WPDP_Parent_Form;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDP_Project_Project_Form extends WPDP_Parent_Form
	 *
	 * @see WPDP_Parent_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Project_Form extends WPDP_Parent_Form {

		/**
		 * WPDP_Project_Project_Form constructor
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array             $args
		 * @param array             $relationship
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, array $args = array(), array $relationship = array() ) {
			// Add column labels.
			$args['column_headers'] = array(
				'project_id'          => __( 'Project ID', 'wp-data-access' ),
				'project_name'        => __( 'Project Name', 'wp-data-access' ),
				'project_description' => __( 'Project Description', 'wp-data-access' ),
				'add_to_menu'         => __( 'Add To Menu', 'wp-data-access' ),
				'menu_name'           => __( 'Menu Name', 'wp-data-access' ),
				'project_sequence'    => __( 'Seq#', 'wp-data-access' ),
			);

			$args['edit_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_Form';

			if ( isset( $args['mode'] ) ) {
				$mode = $args['mode'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing mode]', 'wp-data-access' ) );
			}

			if ( 'view' === $mode ) {
				$args['list_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_List_View';
			} else {
				$args['list_form_class'] = 'WPDataProjects\\Project\\WPDP_Project_Page_List';
			}

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args, $relationship );

			$this->title = 'Project';
		}

		/**
		 * Overwrites method show to add debug info
		 *
		 * This debug info can be helpful in case of project page structure errors
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			if ( 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_DEBUG ) && isset( $_REQUEST['project_id'] ) ) {
				$project_id = sanitize_text_field( wp_unslash( $_REQUEST['project_id'] ) ); // input var okay.
				global $wpdb;
				$project_page_table_name = $wpdb->prefix . 'wpda_project_page';
				$pages                   = $wpdb->get_results(
					$wpdb->prepare(
						" select * from `%1s` where project_id = %d and add_to_menu = 'Yes' order by page_sequence", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
						array(
							WPDA::remove_backticks( $project_page_table_name ),
							$project_id,
						)
					),
					'ARRAY_A'
				); // phpcs:ignore Standard.Category.SniffName.ErrorCode

				$project_info = '';
				$first_page   = true;
				foreach ( $pages as $page ) {
					$wpdp = new WPDP_Project( $project_id, $page['page_id'] );
					if ( null === $wpdp->get_project() ) {
						wp_die( __( 'Data Project page not found [need a valid project_id and page_id]', 'wp-data-access' ) );
					}
					if ( ! $first_page ) {
						$project_info .= '<br/><br/>';
					}
					$project_info .= "Project $project_id, page {$page['page_id']}:<br/>";
					$project_info .= json_encode( $wpdp->get_project() );
					$first_page    = false;
				}
				?>
				<style>
					#overlay_project {
						height: 400px;
						width: 600px;
						position: fixed;
						display: none;
						top: 50%;
						left: 50%;
						transform: translate(-50%, -50%);
						-ms-transform: translate(-50%, -50%);
						right: 0;
						bottom: 0;
						background-color: #f9f9f9;
						opacity: .95;
						border: 1px solid #ccc;
						cursor: pointer;
						z-index: 1000;
					}

					#overlay_project_text {
						height: 360px;
						width: 400px;
						padding: 10px;
						position: relative;
						top: 50%;
						left: 235px;
						transform: translate(-50%, -50%);
						-ms-transform: translate(-50%, -50%);
						color: black;
						overflow-y: auto;
						background-color: white;
						border: 1px solid #ccc;
					}
				</style>
				<div id="overlay_project">
					<div id="overlay_project_text">
						<?php echo $project_info; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<div style="position: absolute; bottom: 0; right: 0; padding-right: 5px; padding-bottom: 10px;">
						<a id="button-copy-clipboard" href="javascript:void(0)" class="button button-secondary"
						   style="text-align:center;width:145px;"
						   data-clipboard-text="<?php echo str_replace( '<br/>', "\n", str_replace( '"', '&quot;', $project_info ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>">
							<i class="fas fa-clipboard wpda_icon_on_button"></i>
							<?php echo __( 'Copy to clipboard', 'wp-data-access' ); ?>
						</a>
						<br/>
						<div style="height: 5px;"></div>
						<a href="javascript:void(0)" class="button button-secondary"
						   style="text-align:center;width:145px;"
						   onclick="jQuery('#overlay_project').hide()">
							<i class="fas fa-times-circle wpda_icon_on_button"></i>
							<?php echo __( 'Close', 'wp-data-access' ); ?>
						</a>
					</div>
				</div>
				<div style="float:right;text-align:right;">
					<a href="javascript:void(0);" onclick="jQuery('#overlay_project').show()" class="button">DEBUG</a>
				</div>
				<script type='text/javascript'>
					jQuery(function () {
						var sql_to_clipboard = new ClipboardJS('#button-copy-clipboard');
						sql_to_clipboard.on('success', function (e) {
							jQuery.notify('<?php echo __( 'Info copied to clipboard!', 'wp-data-access' ); ?>','info');
						});
						sql_to_clipboard.on('error', function (e) {
							jQuery.notify('<?php echo __( 'Could not copy info to clipboard!', 'wp-data-access' ); ?>','error');
						});
					});
				</script>
				<?php
			}

			parent::show( $allow_save, $add_param );

			if ( 'new' !== $this->action_posted || $this->child_request ) {
				?>
				<script type='text/javascript'>
					jQuery(function () {
						jQuery('#show_more_less_button').show();
					});
				</script>
				<?php
			}
		}

		/**
		 * Overwrite method
		 *
		 * @param bool $set_back_form_values
		 */
		public function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			$column_index = $this->get_item_index( 'project_description' );
			$this->form_items[ $column_index ]->set_item_class( 'row-show-less-more' );

			$column_index = $this->get_item_index( 'project_sequence' );
			$this->form_items[ $column_index ]->set_item_class( 'row-show-less-more' );
		}

	}

}
