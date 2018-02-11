<?
$UserID = $LoggedUser['ID'];
$TagID = \Gazelle\Util\Db::string($_GET['tagid']);
$GroupID = \Gazelle\Util\Db::string($_GET['groupid']);
$Way = \Gazelle\Util\Db::string($_GET['way']);

if (!is_number($TagID) || !is_number($GroupID)) {
	error(404);
}
if (!in_array($Way, array('up', 'down'))) {
	error(404);
}

$DB->query("
	SELECT TagID
	FROM torrents_tags_votes
	WHERE TagID = '$TagID'
		AND GroupID = '$GroupID'
		AND UserID = '$UserID'
		AND Way = '$Way'");
if (!$DB->has_results()) {
	if ($Way == 'down') {
		$Change = 'NegativeVotes = NegativeVotes + 1';
	} else {
		$Change = 'PositiveVotes = PositiveVotes + 2';
	}
	$DB->query("
		UPDATE torrents_tags
		SET $Change
		WHERE TagID = '$TagID'
			AND GroupID = '$GroupID'");
	$DB->query("
		INSERT INTO torrents_tags_votes
			(GroupID, TagID, UserID, Way)
		VALUES
			('$GroupID', '$TagID', '$UserID', '$Way')");
	$Cache->delete_value("torrents_details_$GroupID"); // Delete torrent group cache
}

$Location = (empty($_SERVER['HTTP_REFERER'])) ? "torrents.php?id={$GroupID}" : $_SERVER['HTTP_REFERER'];
header("Location: {$Location}");