<?php
authorize();

$Other = (isset($_GET['Other']) && $_GET['Other'] === 'true') ? 'true' : 'false';
$Amount = (isset($_GET['Amount'])) ? intval($_GET['Amount']) : 0;

switch ($Amount) {
	case 1:
		$Option = '1_token';
		break;
	case 10:
		$Option = '10_tokens';
		break;
	case 50:
		$Option = '50_tokens';
		break;
	default:
		error('Invalid amount of tokens');
}

if ($Other === 'true') {
	$Option .= '_other';
}

$Item = \Gazelle\Bonus::$Items[$Option];
$Price = \Gazelle\Bonus::get_price($Item);
if ($Price > \Gazelle\G::$LoggedUser['BonusPoints']) {
	error('You cannot afford this item.');
}

if ($Other === 'true') {
	if (empty($_GET['user'])) {
		error('You have to enter a username to give tokens to.');
	}
	$User = urldecode($_GET['user']);
	\Gazelle\G::$DB->query("SELECT ID FROM users_main WHERE Username='".\Gazelle\Util\Db::string($User)."'");
	if (!\Gazelle\G::$DB->has_results()) {
		error('Invalid username. Please select a valid user');
	}
	list($ID) = \Gazelle\G::$DB->next_record();
	if ($ID == \Gazelle\G::$LoggedUser['ID']) {
		error('You cannot give yourself tokens.');
	}
    \Gazelle\Bonus::send_pm_to_other(\Gazelle\G::$LoggedUser['Username'], $ID, $Amount);
}
else {
	$ID = \Gazelle\G::$LoggedUser['ID'];
}

\Gazelle\G::$DB->query("UPDATE users_main SET BonusPoints = BonusPoints - {$Price} WHERE ID='".\Gazelle\G::$LoggedUser['ID']."'");
\Gazelle\G::$Cache->delete_value('user_stats_'.\Gazelle\G::$LoggedUser['ID']);

\Gazelle\G::$DB->query("UPDATE users_main SET FLTokens = FLTokens + {$Amount} WHERE ID='{$ID}'");
\Gazelle\G::$Cache->delete_value("user_info_heavy_{$ID}");
header('Location: bonus.php?complete');
