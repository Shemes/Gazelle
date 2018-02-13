<?php

namespace Gazelle;

class Collages
{
    public static function increase_subscriptions($CollageID)
    {
        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
			UPDATE collages
			SET Subscribers = Subscribers + 1
			WHERE ID = '$CollageID'");
        \Gazelle\G::$DB->set_query_id($QueryID);
    }

    public static function decrease_subscriptions($CollageID)
    {
        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
			UPDATE collages
			SET Subscribers = IF(Subscribers < 1, 0, Subscribers - 1)
			WHERE ID = '$CollageID'");
        \Gazelle\G::$DB->set_query_id($QueryID);
    }

    public static function create_personal_collage()
    {
        \Gazelle\G::$DB->query("
			SELECT
				COUNT(ID)
			FROM collages
			WHERE UserID = '" . \Gazelle\G::$LoggedUser['ID'] . "'
				AND CategoryID = '0'
				AND Deleted = '0'");
        list($CollageCount) = \Gazelle\G::$DB->next_record();

        if ($CollageCount >= \Gazelle\G::$LoggedUser['Permissions']['MaxCollages']) {
            // TODO: fix this, the query was for COUNT(ID), so I highly doubt that this works... - Y
            list($CollageID) = \Gazelle\G::$DB->next_record();
            header('Location: collage.php?id=' . $CollageID);
            die();
        }
        $NameStr = \Gazelle\Util\Db::string(\Gazelle\G::$LoggedUser['Username'] . "'s personal collage" . ($CollageCount > 0 ? ' no. ' . ($CollageCount + 1) : ''));
        $Description = \Gazelle\Util\Db::string('Personal collage for ' . \Gazelle\G::$LoggedUser['Username'] . '. The first 5 albums will appear on his or her [url=' . site_url() . 'user.php?id= ' . \Gazelle\G::$LoggedUser['ID'] . ']profile[/url].');
        \Gazelle\G::$DB->query("
			INSERT INTO collages
				(Name, Description, CategoryID, UserID)
			VALUES
				('$NameStr', '$Description', '0', " . \Gazelle\G::$LoggedUser['ID'] . ')');
        $CollageID = \Gazelle\G::$DB->inserted_id();
        header('Location: collage.php?id=' . $CollageID);
        die();
    }
}
