<?php

use AlterVision\AVDB\DB;

$config['db']['debug']   = 0;
$config['db']['type']    = 'mysql';
$config['db']['charset'] = 'utf8';
$config['db']['host']    = 'localhost';
$config['db']['user']    = 'root';
$config['db']['pass']    = '';
$config['db']['name']    = '';

db::setup($config['db']);