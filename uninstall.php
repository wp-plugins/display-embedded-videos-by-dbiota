<?php


global $wpdb;
$table_name = get_option( "displayembeddedvideosbydb_tb_name" );
$wpdb->query('DROP TABLE IF EXISTS '.$table_name);

delete_option( "displayembeddedvideosbydb_db_version" );
delete_option( "displayembeddedvideosbydb_tb_name" );
delete_option( "displayembeddedvideosbydb_flag_recreation" );



?>