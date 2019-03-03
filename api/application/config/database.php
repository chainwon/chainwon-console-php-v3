<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'account';
$query_builder = TRUE;

$db['account'] = array(
	'dsn'	=> '',
	'hostname' => '47.93.60.166',
	'username' => 'chainwon',
	'password' => '2TLnw8aA7h',
	'database' => 'chainwon',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
