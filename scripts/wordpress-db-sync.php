<?php
/** Full database sync policy. Run with WP-CLI, skipping plugins and themes. */

function bvsds_mail_option_rules() {
	return array(
		'exact' => array( 'new_admin_email', 'adminhash', 'mailer', 'cron' ),
		'prefix' => array( 'wp_mail_smtp', 'wpmailsmtp', 'smtp_', 'pepipost_', 'mail_from', 'mail_set_return_path', 'mailserver_', '_transient_wp_mail_smtp', '_transient_timeout_wp_mail_smtp', '_transient_wpmailsmtp', '_transient_timeout_wpmailsmtp', 'action_scheduler_', 'schema-ActionScheduler_' ),
	);
}

function bvsds_protected_options_sql() {
	$rules = bvsds_mail_option_rules();
	$parts = array();
	foreach ( $rules['exact'] as $name ) {
		$parts[] = "option_name = UNHEX('" . bin2hex( $name ) . "')";
	}
	foreach ( $rules['prefix'] as $prefix ) {
		$parts[] = 'LEFT(option_name, ' . strlen( $prefix ) . ") = UNHEX('" . bin2hex( $prefix ) . "')";
	}
	return '(' . implode( ' OR ', $parts ) . ')';
}

function bvsds_excluded_tables( $prefix, $tables ) {
	return array_values( array_filter( $tables, function ( $table ) use ( $prefix ) {
		return $table === $prefix . 'options' || preg_match( '/^' . preg_quote( $prefix, '/' ) . '(?:wpmailsmtp_|wp_mail_smtp_|actionscheduler_)/', $table );
	} ) );
}

function bvsds_options_sql( $table, $rows, $replacer ) {
	if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) { throw new RuntimeException( 'Unsupported options table name.' ); }
	// Recipients such as bvs_recipient and admin_email are ordinary synced options.
	// Keep the destination's mail configuration continuously present. Replace only
	// other options in one transaction, without transferring source credentials.
	$sql = "START TRANSACTION;\nDELETE FROM `$table` WHERE NOT " . bvsds_protected_options_sql() . ";\n";
	$rules = bvsds_mail_option_rules();
	foreach ( $rows as $row ) {
		$name = $row['option_name'];
		$protected = in_array( $name, $rules['exact'], true );
		foreach ( $rules['prefix'] as $prefix ) {
			$protected = $protected || strpos( $name, $prefix ) === 0;
		}
		if ( $protected ) { continue; }
		$value = $replacer->run( $row['option_value'] );
		$values = array_map( function ( $value ) { return "UNHEX('" . bin2hex( $value ) . "')"; }, array( $name, $value, $row['autoload'] ) );
		$sql .= "INSERT INTO `$table` (option_name, option_value, autoload) VALUES (" . implode( ', ', $values ) . ");\n";
	}
	return $sql . "COMMIT;\n";
}

if ( defined( 'BVSDS_LIBRARY_ONLY' ) ) { return; }
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit( "Run this helper through WP-CLI.\n" ); }

global $wpdb;
if ( is_multisite() ) { WP_CLI::error( 'Database sync supports single-site WordPress only.' ); }
$mode = $args[0] ?? '';
if ( in_array( $mode, array( 'excluded-tables', 'included-tables' ), true ) ) {
	$engine = $wpdb->get_var( $wpdb->prepare( 'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $wpdb->options ) );
	if ( $engine !== 'InnoDB' ) { WP_CLI::error( 'Options table must use InnoDB for an atomic settings merge.' ); }
	$tables = $wpdb->get_col( 'SHOW TABLES' );
	if ( $wpdb->last_error ) { WP_CLI::error( 'Cannot list database tables.' ); }
	foreach ( $tables as $table ) {
		if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) { WP_CLI::error( 'Unsupported database table name.' ); }
	}
	$excluded = bvsds_excluded_tables( $wpdb->prefix, $tables );
	$included = array_filter( array_diff( $tables, $excluded ), function ( $table ) use ( $wpdb ) { return strpos( $table, $wpdb->prefix ) === 0; } );
	WP_CLI::line( implode( ',', $mode === 'excluded-tables' ? $excluded : $included ) );
} elseif ( $mode === 'options-sql' && count( $args ) === 3 ) {
	$rows = $wpdb->get_results( "SELECT option_name, option_value, autoload FROM `$wpdb->options` WHERE NOT " . bvsds_protected_options_sql(), ARRAY_A );
	if ( $wpdb->last_error ) { WP_CLI::error( 'Cannot export non-mail options.' ); }
	$replacer = new \WP_CLI\SearchReplacer( $args[1], $args[2], true );
	WP_CLI::line( bvsds_options_sql( $wpdb->options, $rows, $replacer ) );
} else {
	WP_CLI::error( 'Use excluded-tables, included-tables or options-sql FROM_URL TO_URL.' );
}
