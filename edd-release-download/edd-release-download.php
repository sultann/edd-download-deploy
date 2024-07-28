<?php
/*
 * Plugin Name:       Easy Digital Downloads - Release Download
 * Plugin URI:        https://github.com/sultann/edd-release-download
 * Description:       A plugin to release download for Easy Digital Downloads using GitHub actions.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Sultan Nasir Uddin
 * Author URI:        https://manik.me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       edd-release-download
 * Domain Path:       /languages
 */

namespace EDDReleaseDownload;

defined( 'ABSPATH' ) || exit;

add_filter( 'edd_api_valid_query_modes', __NAMESPACE__ . '\add_query_mode' );

/**
 * Add the query mode for the GitHub release API.
 *
 * @param array $modes The valid query modes.
 *
 * @return array
 */
function add_query_mode( $modes ) {
	$modes[] = 'release-download';

	return $modes;
}

add_filter( 'edd_api_output_data', __NAMESPACE__ . '\handle_release_action', 10, 2 );

/**
 * Handle the GitHub release query.
 *
 * @param array  $data The data to output.
 * @param string $endpoint The endpoint being queried.
 *
 * @return array
 */
function handle_release_action( $data, $endpoint ) {
	if ( 'release-download' !== $endpoint ) {
		return $data;
	}

	// item_id is required.
	$item_id   = isset( $_REQUEST['item_id'] ) ? absint( $_REQUEST['item_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$version   = isset( $_REQUEST['version'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['version'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$changelog = isset( $_REQUEST['changelog'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['changelog'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$download = new \EDD_Download( $item_id );

	// if the download doesn't exist, return an error.
	if ( ! $download->ID ) {
		$data['error'] = 'Invalid download ID';

		return $data;
	}

	if ( empty( $_FILES['file'] ) || ! isset( $_FILES['file']['tmp_name'] ) || UPLOAD_ERR_OK !== $_FILES['file']['error'] ) {
		$data['error'] = 'No file uploaded';

		return $data;
	}

	// if version is not empty, we will add the version each file's name and full path if its not already.
	if ( ! empty( $version ) ) {
		$ext                    = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
		$version_string         = '-v' . $version;
		$_FILES['file']['name'] = str_replace( '.' . $ext, $version_string . '.' . $ext, $_FILES['file']['name'] );
	}

	delete_transient( 'edd_check_protection_files' );
	add_filter( 'upload_dir', 'edd_set_upload_dir' );

	// make the file name as WordPress attachment.
	$file = wp_handle_upload( $_FILES['file'], array( 'test_form' => false ) );

	// if it returns an error, return the error.
	if ( isset( $file['error'] ) ) {
		$data['error'] = $file['error'];

		return $data;
	}

	// attach the file to the download.
	$attachment_id = wp_insert_attachment(
		array(
			'post_title'     => sanitize_title( $file['file'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => sanitize_key( $file['type'] ),
		),
		$file['file'],
		$download->ID
	);

	remove_filter( 'upload_dir', 'edd_set_upload_dir' );

	// If the attachment was not created, return an error.
	if ( is_wp_error( $attachment_id ) ) {
		$data['error'] = $attachment_id->get_error_message();

		return $data;
	}

	// update the version.
	if ( ! empty( $version ) ) {
		$data['version'] = $version;
		update_post_meta( $download->ID, '_edd_sl_version', $version );
	}

	// update the changelog.
	if ( ! empty( $change_log ) ) {
		$data['changelog'] = 'Changelog updated';
		$changelog         = wp_strip_all_tags( $changelog );
		update_post_meta( $download->ID, '_edd_sl_changelog', $changelog );
	}

	// update the attachment ID.
	if ( ! empty( $attachment_id ) ) {
		$attachment = wp_get_attachment_url( $attachment_id );
		$file       = array(
			array(
				'index'          => 0,
				'attachment_id'  => $attachment_id,
				'thumbnail_size' => false,
				'name'           => basename( $attachment ),
				'file'           => $attachment,
				'condition'      => 'all',
			),
		);

		$data['file'] = basename( $attachment );
		update_post_meta( $download->ID, 'edd_download_files', $file );
	}

	// Show the success message.
	$data['success'] = 'Release uploaded successfully';

	return $data;
}
