<?php

namespace Gazelle;

class Calendar
{
    public static $Categories = [
        1 => 'IRC Meeting', 'IRC Brainstorm', 'Poll Deadline', 'Feature Release', 'Blog Post', 'Announcement', 'Featured Album', 'Product Release', 'Staff Picks', 'Forum Brainstorm', 'Forum Discussion', 'Promotion', 'Absence', 'Task', ];
    public static $Importances = [1 => 'Critical', 'Important', 'Average', 'Meh'];
    public static $Colors = [
                                    'Critical' => 'red',
                                    'Important' => 'yellow',
                                    'Average' => 'green',
                                    'Meh' => 'blue', ];

    public static $Teams = [
                                    0 => 'Everyone',
                                    1 => 'Staff',
                                    ];

    public static function can_view()
    {
        return check_perms('users_mod');
    }

    private static function get_teams_query()
    {
        $Teams = [0];
        $IsMod = check_perms('users_mod');
        if ($IsMod) {
            $Teams[] = 1;
        }

        return 'Team IN (' . implode(',', $Teams) . ') ';
    }

    public static function get_events($Month, $Year)
    {
        if (empty($Month) || empty($Year)) {
            $Date = getdate();
            $Month = $Date['mon'];
            $Year = $Date['year'];
        }
        $Month = (int)$Month;
        $Year = (int)$Year;

        $TeamsSQL = self::get_teams_query();

        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
						SELECT
							ID, Team, Title, Category, Importance, DAY(StartDate) AS StartDay, DAY(EndDate) AS EndDay
						FROM calendar
						WHERE
							MONTH(StartDate) = '$Month'
						AND
							YEAR(StartDate) = '$Year'
						AND
							$TeamsSQL");
        $Events = \Gazelle\G::$DB->to_array();
        \Gazelle\G::$DB->set_query_id($QueryID);
        return $Events;
    }

    public static function get_event($ID)
    {
        $ID = (int)$ID;
        if (empty($ID)) {
            error('Invalid ID');
        }
        $TeamsSQL = self::get_teams_query();
        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
						SELECT
							ID, Team, Title, Body, Category, Importance, AddedBy, StartDate, EndDate
						FROM calendar
						WHERE
							ID = '$ID'
						AND
							$TeamsSQL");
        $Event = \Gazelle\G::$DB->next_record(MYSQLI_ASSOC);
        \Gazelle\G::$DB->set_query_id($QueryID);
        return $Event;
    }

    public static function create_event($Title, $Body, $Category, $Importance, $Team, $UserID, $StartDate, $EndDate = null)
    {
        if (empty($Title) || empty($Body) || !is_number($Category) || !is_number($Importance) || !is_number($Team) || empty($StartDate)) {
            error('Error adding event');
        }
        $Title = \Gazelle\Util\Db::string($Title);
        $Body = \Gazelle\Util\Db::string($Body);
        $Category = (int)$Category;
        $Importance = (int)$Importance;
        $UserID = (int)$UserID;
        $Team = (int)$Team;
        $StartDate = \Gazelle\Util\Db::string($StartDate);
        $EndDate = \Gazelle\Util\Db::string($EndDate);

        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
						INSERT INTO calendar
							(Title, Body, Category, Importance, Team, StartDate, EndDate, AddedBy)
						VALUES
							('$Title', '$Body', '$Category', '$Importance', '$Team', '$StartDate', '$EndDate', '$UserID')");
        \Gazelle\G::$DB->set_query_id($QueryID);
        send_irc('PRIVMSG ' . ADMIN_CHAN . " :!mod New calendar event created! Event: $Title; Starts: $StartDate; Ends: $EndDate.");
    }

    public static function update_event($ID, $Title, $Body, $Category, $Importance, $Team, $StartDate, $EndDate = null)
    {
        if (!is_number($ID) || empty($Title) || empty($Body) || !is_number($Category) || !is_number($Importance) || !is_number($Team) || empty($StartDate)) {
            error('Error updating event');
        }
        $ID = (int)$ID;
        $Title = \Gazelle\Util\Db::string($Title);
        $Body = \Gazelle\Util\Db::string($Body);
        $Category = (int)$Category;
        $Importance = (int)$Importance;
        $Team = (int)$Team;
        $StartDate = \Gazelle\Util\Db::string($StartDate);
        $EndDate = \Gazelle\Util\Db::string($EndDate);
        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
						UPDATE calendar
						SET
							Title = '$Title',
							Body = '$Body',
							Category = '$Category',
							Importance = '$Importance',
							Team = '$Team',
							StartDate = '$StartDate',
							EndDate = '$EndDate'
						WHERE
							ID = '$ID'");
        \Gazelle\G::$DB->set_query_id($QueryID);
    }

    public static function remove_event($ID)
    {
        $ID = (int)$ID;
        if (!empty($ID)) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query("DELETE FROM calendar WHERE ID = '$ID'");
            \Gazelle\G::$DB->set_query_id($QueryID);
        }
    }
}
