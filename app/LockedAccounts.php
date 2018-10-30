<?php

namespace Gazelle;

/**
 * Class to manage locked accounts
 */
class LockedAccounts
{
    /**
     * Lock an account
     *
     * @param int $UserID The ID of the user to lock
     * @param int $Type The lock type, should be a constant value
     * @param string $Message The message to write to user notes
     * @param string $Reason The reason for the lock
     * @param int $LockedByUserID The ID of the staff member that locked $UserID's account. 0 for system
     */
    public static function lock_account($UserID, $Type, $Message, $Reason, $LockedByUserID)
    {
        if ($LockedByUserID == 0) {
            $Username = 'System';
        } else {
            \Gazelle\G::$DB->query("SELECT Username FROM users_main WHERE ID = '" . $LockedByUserID . "'");
            list($Username) = \Gazelle\G::$DB->next_record();
        }

        \Gazelle\G::$DB->query("
                INSERT INTO locked_accounts (UserID, Type)
                VALUES ('" . $UserID . "', " . $Type . ')');
        Tools::update_user_notes($UserID, \Gazelle\Util\Time::sqltime() . ' - ' . \Gazelle\Util\Db::string($Message) . " by $Username\nReason: " . \Gazelle\Util\Db::string($Reason) . "\n\n");
        \Gazelle\G::$Cache->delete_value('user_info_' . $UserID);
    }

    /**
     * Unlock an account
     *
     * @param int $UserID The ID of the user to unlock
     * @param int $Type The lock type, should be a constant value. Used for database verification
     *                  to avoid deleting the wrong lock type
     * @param string $Reason The reason for unlock
     * @param int $UnlockedByUserID The ID of the staff member unlocking $UserID's account. 0 for system
     */
    public static function unlock_account($UserID, $Type, $Message, $Reason, $UnlockedByUserID)
    {
        if ($UnlockedByUserID == 0) {
            $Username = 'System';
        } else {
            \Gazelle\G::$DB->query("SELECT Username FROM users_main WHERE ID = '" . $UnlockedByUserID . "'");
            list($Username) = \Gazelle\G::$DB->next_record();
        }

        \Gazelle\G::$DB->query("DELETE FROM locked_accounts WHERE UserID = '$UserID' AND Type = '" . $Type . "'");

        if (\Gazelle\G::$DB->affected_rows() == 1) {
            \Gazelle\G::$Cache->delete_value('user_info_' . $UserID);
            Tools::update_user_notes($UserID, \Gazelle\Util\Time::sqltime() . ' - ' . \Gazelle\Util\Db::string($Message) . " by $Username\nReason: " . \Gazelle\Util\Db::string($Reason) . "\n\n");
        }
    }
}