<?php

use \Gazelle\Util\Time;

function time_ago($TimeStamp) {
	return Time::timeAgo($TimeStamp);
}


/**
 * Given a number of hours, convert it to a human readable time of
 * years, months, days, etc.
 *
 * @param $Hours
 * @param int $Levels
 * @param bool $Span
 * @return string
 */
function convert_hours($Hours,$Levels=2,$Span=true) {
	return Time::convertHours($Hours, $Levels, $Span);
}

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
