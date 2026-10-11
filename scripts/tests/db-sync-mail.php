<?php
/** Local-only SQL integration checks using temporary tables, without sending mail. */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	WP_CLI::error( 'Mail-preservation tests may run only on a .local WordPress site.' );
}
define( 'BVSDS_LIBRARY_ONLY', true );
require dirname( __DIR__ ) . '/wordpress-db-sync.php';
function bvsds_assert( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
global $wpdb;
$table = $wpdb->prefix . 'bvsds_test_options';
$directory = sys_get_temp_dir() . '/bvs-db-sync-test-' . wp_generate_uuid4();
mkdir( $directory, 0700 );
$before = hash( 'sha256', serialize( $wpdb->get_results( "SELECT * FROM `$wpdb->options` ORDER BY option_name", ARRAY_A ) ) );
$rules = bvsds_mail_option_rules();
$protected = $rules['exact'];
$protected[] = 'wp_mail_smtp';
foreach ( $rules['prefix'] as $prefix ) { $protected[] = $prefix . '_fixture'; }
try {
	bvsds_assert( $wpdb->query( "CREATE TEMPORARY TABLE `$table` LIKE `$wpdb->options`" ) !== false, 'Cannot create temporary options table.' );
	foreach ( array( array( 'http://bvs.local', 'https://bvs.example' ), array( 'https://bvs.example', 'http://bvs.local' ) ) as $direction ) {
		$wpdb->query( "DELETE FROM `$table`" );
		$source = array();
		$destination = array();
		foreach ( $protected as $name ) {
			$row = array( 'option_name' => $name, 'option_value' => 'destination-secret-' . $direction[0], 'autoload' => 'off' );
			$source_value = 'source-secret-' . $direction[0];
			if ( $name === 'wp_mail_smtp' ) {
				$credentials = array( 'client_id', 'client_secret', 'access_token', 'refresh_token' );
				$row['option_value'] = serialize( array( 'gmail' => array_fill_keys( $credentials, $row['option_value'] ) ) );
				$source_value = serialize( array( 'gmail' => array_fill_keys( $credentials, $source_value ) ) );
			}
			$destination[$name] = $row;
			$wpdb->insert( $table, $row );
			$source[] = array( 'option_name' => $name, 'option_value' => $source_value, 'autoload' => 'on' );
		}
		$recipients = array( 'admin_email', 'bvs_recipient' );
		foreach ( $recipients as $name ) {
			$wpdb->insert( $table, array( 'option_name' => $name, 'option_value' => 'destination@example.test', 'autoload' => 'off' ) );
			$source[] = array( 'option_name' => $name, 'option_value' => 'source@example.test', 'autoload' => 'on' );
		}
		$wpdb->insert( $table, array( 'option_name' => 'destination_only', 'option_value' => 'remove', 'autoload' => 'yes' ) );
		$payload = array( 'url' => $direction[0] . '/iekārtas/', 'text' => "Quotes ' and \\ and \n" );
		$source[] = array( 'option_name' => 'content_setting', 'option_value' => serialize( $payload ), 'autoload' => 'auto' );
		$source[] = array( 'option_name' => 'empty_setting', 'option_value' => '', 'autoload' => 'off' );
		$sql = bvsds_options_sql( $table, $source, new \WP_CLI\SearchReplacer( $direction[0], $direction[1], true ) );
		bvsds_assert( ! str_contains( $sql, bin2hex( 'source-secret-' ) ), 'Source mail secrets entered the transfer.' );
		foreach ( explode( ";\n", trim( $sql ) ) as $statement ) {
			if ( trim( $statement ) !== '' ) { bvsds_assert( $wpdb->query( $statement ) !== false, 'SQL merge failed.' ); }
		}
		$actual = array();
		foreach ( $wpdb->get_results( "SELECT option_name, option_value, autoload FROM `$table`", ARRAY_A ) as $row ) { $actual[$row['option_name']] = $row; }
		foreach ( $destination as $name => $row ) { bvsds_assert( $actual[$name] === $row, 'Destination mail option or autoload changed.' ); }
		foreach ( $recipients as $name ) { bvsds_assert( $actual[$name]['option_value'] === 'source@example.test' && $actual[$name]['autoload'] === 'on', 'Recipient setting did not synchronize.' ); }
		bvsds_assert( ! isset( $actual['destination_only'] ), 'Stale content setting survived the replacement.' );
		$payload['url'] = $direction[1] . '/iekārtas/';
		bvsds_assert( $actual['content_setting']['option_value'] === serialize( $payload ), 'Serialized URL or text was damaged.' );
		bvsds_assert( $actual['content_setting']['autoload'] === 'auto' && $actual['empty_setting']['option_value'] === '', 'Autoload or empty value was damaged.' );
		// An unconfigured destination must not acquire the source's connection.
		$wpdb->query( "DELETE FROM `$table`" );
		foreach ( explode( ";\n", trim( $sql ) ) as $statement ) { if ( trim( $statement ) !== '' ) { $wpdb->query( $statement ); } }
		bvsds_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$table` WHERE " . bvsds_protected_options_sql() ) === 0, 'Source mail settings appeared on an unconfigured destination.' );
		foreach ( $recipients as $name ) { bvsds_assert( $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM `$table` WHERE option_name = %s", $name ) ) === 'source@example.test', 'Recipient setting was not copied to an unconfigured destination.' ); }
	}
	$tables = array( 'bvs_options', 'bvs_posts', 'bvs_wpmailsmtp_emails_queue', 'bvs_wp_mail_smtp_logs', 'bvs_actionscheduler_actions', 'other_options' );
	bvsds_assert( bvsds_excluded_tables( 'bvs_', $tables ) === array( 'bvs_options', 'bvs_wpmailsmtp_emails_queue', 'bvs_wp_mail_smtp_logs', 'bvs_actionscheduler_actions' ), 'Table exclusions did not respect the site prefix.' );
	$tables = $wpdb->get_col( 'SHOW TABLES' );
	$excluded = bvsds_excluded_tables( $wpdb->prefix, $tables );
	$included = array_filter( array_diff( $tables, $excluded ), function ( $name ) use ( $wpdb ) { return strpos( $name, $wpdb->prefix ) === 0; } );
	// Both transfer directions use an explicit content table whitelist.
	// Do not combine tables with exclude_tables, which WP-CLI ignores.
	foreach ( array( 'push' => '--tables=' . escapeshellarg( implode( ',', $included ) ), 'pull' => '--tables=' . escapeshellarg( implode( ',', $included ) ) ) as $direction => $flags ) {
		$file = "$directory/$direction.sql";
		$result = WP_CLI::runcommand( 'db export ' . escapeshellarg( $file ) . ' ' . $flags . ' --quiet', array( 'return' => 'all', 'exit_error' => false ) );
		bvsds_assert( $result->return_code === 0, 'Read-only database export failed.' );
		preg_match_all( '/CREATE TABLE `([^`]+)`/', file_get_contents( $file ), $matches );
		bvsds_assert( in_array( $wpdb->prefix . 'posts', $matches[1], true ), 'Ordinary content tables are missing from the export.' );
		bvsds_assert( ! array_intersect( $excluded, $matches[1] ), 'Protected options or mail/runtime tables entered the export.' );
		unlink( $file );
	}
	bvsds_assert( $before === hash( 'sha256', serialize( $wpdb->get_results( "SELECT * FROM `$wpdb->options` ORDER BY option_name", ARRAY_A ) ) ), 'Real WordPress options changed during tests.' );
	WP_CLI::success( 'Both directions synchronize recipients, preserve SMTP/OAuth credentials, exclude source secrets and queues, and correctly replace serialized content settings. Real site options are unchanged.' );
} finally {
	$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS `$table`" );
	foreach ( glob( "$directory/*.sql" ) as $file ) { unlink( $file ); }
	rmdir( $directory );
}
