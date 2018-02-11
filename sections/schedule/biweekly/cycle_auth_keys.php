<?php

//------------- Cycle auth keys -----------------------------------------//

$DB->query("
		UPDATE users_info
		SET AuthKey =
			MD5(
				CONCAT(
					AuthKey, RAND(), '".\Gazelle\Util\Db::string(Users::make_secret())."',
					SHA1(
						CONCAT(
							RAND(), RAND(), '".\Gazelle\Util\Db::string(Users::make_secret())."'
						)
					)
				)
			);"
);