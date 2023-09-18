<?php
require_once './vendor/autoload.php';

use test\test;
use game\jiliAuth;

$test = new jiliAuth([]);
$test->loginURL('', '', '', '');
