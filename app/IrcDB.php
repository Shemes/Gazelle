<?php
namespace Gazelle;

class IrcDB extends DBMySQL {
	function halt($Msg) {
		global $Bot;
		$Bot->send_to($Bot->get_channel(), 'The database is currently unavailable; try again later.');
	}
}
