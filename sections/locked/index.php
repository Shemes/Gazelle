<?
enforce_login();

if (!check_perms('users_mod') && !isset(\Gazelle\G::$LoggedUser['LockedAccount'])) {
    error(404);
}

include('default.php');

