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

$Item = Bonus::$Items[$Option];
$Price = Bonus::get_price($Item);
if ($Price > \G::$LoggedUser['BonusPoints']) {
	error('You cannot afford this item.');
}

if ($Other === 'true') {
	if (empty($_GET['user'])) {
		error('You have to enter a username to give tokens to.');
	}
	$User = urldecode($_GET['user']);
	\G::$DB->query("SELECT ID FROM users_main WHERE Username='".\Gazelle\Util\Db::string($User)."'");
	if (!\G::$DB->has_results()) {
		error('Invalid username. Please select a valid user');
	}
	list($ID) = \G::$DB->next_record();
	if ($ID == \G::$LoggedUser['ID']) {
		error('You cannot give yourself tokens.');
	}
    Bonus::send_pm_to_other(\G::$LoggedUser['Username'], $ID, $Amount);
}
else {
	$ID = \G::$LoggedUser['ID'];
}

\G::$DB->query("UPDATE users_main SET BonusPoints = BonusPoints - {$Price} WHERE ID='".\G::$LoggedUser['ID']."'");
\G::$Cache->delete_value('user_stats_'.\G::$LoggedUser['ID']);

\G::$DB->query("UPDATE users_main SET FLTokens = FLTokens + {$Amount} WHERE ID='{$ID}'");
\G::$Cache->delete_value("user_info_heavy_{$ID}");
header('Location: bonus.php?complete');
