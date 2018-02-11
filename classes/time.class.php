<?php

use \Gazelle\Util\Time;


/* SQL utility functions */

function time_plus($Offset) {
	return Time::timePlus($Offset);
}

function time_minus($Offset, $Fuzzy = false) {
	return Time::timeMinus($Offset, $Fuzzy);
}

function sqltime($timestamp = false) {
	return Time::sqlTime($timestamp);
}

function validDate($DateString) {
	return Time::validDate($DateString);
}

function is_valid_date($Date) {
	return Time::isValidDate($Date);
}

function is_valid_time($Time) {
	return Time::isValidTime($Time);
}

function is_valid_datetime($DateTime, $Format = 'Y-m-d H:i') {
	return Time::isValidDateTime($DateTime, $Format);
}
