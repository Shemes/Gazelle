<?php

namespace Gazelle;

class Forums
{
    /**
     * Get information on a thread.
     *
     * @param int $ThreadID
     *        	the thread ID.
     * @param boolean $Return
     *        	indicates whether thread info should be returned.
     * @param Boolean $SelectiveCache
     *        	cache thread info/
     * @return array holding thread information.
     */
    public static function get_thread_info($ThreadID, $Return = true, $SelectiveCache = false)
    {
        if ((!$ThreadInfo = \Gazelle\G::$Cache->get_value('thread_' . $ThreadID . '_info')) || !isset($ThreadInfo['Ranking'])) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query("
				SELECT
					t.Title,
					t.ForumID,
					t.IsLocked,
					t.IsSticky,
					COUNT(fp.id) AS Posts,
					t.LastPostAuthorID,
					ISNULL(p.TopicID) AS NoPoll,
					t.StickyPostID,
					t.AuthorID as OP,
					t.Ranking,
					MAX(fp.AddedTime) as LastPostTime
				FROM forums_topics AS t
					JOIN forums_posts AS fp ON fp.TopicID = t.ID
					LEFT JOIN forums_polls AS p ON p.TopicID = t.ID
				WHERE t.ID = '$ThreadID'
				GROUP BY fp.TopicID");
            if (!\Gazelle\G::$DB->has_results()) {
                \Gazelle\G::$DB->set_query_id($QueryID);
                return null;
            }
            $ThreadInfo = \Gazelle\G::$DB->next_record(MYSQLI_ASSOC, false);
            if ($ThreadInfo['StickyPostID']) {
                $ThreadInfo['Posts']--;
                \Gazelle\G::$DB->query(
                    "SELECT
						p.ID,
						p.AuthorID,
						p.AddedTime,
						p.Body,
						p.EditedUserID,
						p.EditedTime,
						ed.Username
						FROM forums_posts AS p
							LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
						WHERE p.TopicID = '$ThreadID'
							AND p.ID = '" . $ThreadInfo['StickyPostID'] . "'"
                );
                list($ThreadInfo['StickyPost']) = \Gazelle\G::$DB->to_array(false, MYSQLI_ASSOC);
            }
            \Gazelle\G::$DB->set_query_id($QueryID);
            if (!$SelectiveCache || !$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
                \Gazelle\G::$Cache->cache_value('thread_' . $ThreadID . '_info', $ThreadInfo, 0);
            }
        }
        if ($Return) {
            return $ThreadInfo;
        }
    }

    /**
     * Checks whether user has permissions on a forum.
     *
     * @param int $ForumID
     *        	the forum ID.
     * @param string $Perm
     *        	the permissision to check, defaults to 'Read'
     * @return boolean true if user has permission
     */
    public static function check_forumperm($ForumID, $Perm = 'Read')
    {
        $Forums = self::get_forums();
        if (isset(\Gazelle\G::$LoggedUser['CustomForums'][$ForumID]) && \Gazelle\G::$LoggedUser['CustomForums'][$ForumID] == 1) {
            return true;
        }
        if ($ForumID == DONOR_FORUM && \Gazelle\Donations::has_donor_forum(\Gazelle\G::$LoggedUser['ID'])) {
            return true;
        }
        if ($Forums[$ForumID]['MinClass' . $Perm] > \Gazelle\G::$LoggedUser['Class'] && (!isset(\Gazelle\G::$LoggedUser['CustomForums'][$ForumID]) || \Gazelle\G::$LoggedUser['CustomForums'][$ForumID] == 0)) {
            return false;
        }
        if (isset(\Gazelle\G::$LoggedUser['CustomForums'][$ForumID]) && \Gazelle\G::$LoggedUser['CustomForums'][$ForumID] == 0) {
            return false;
        }
        return true;
    }

    /**
     * Gets basic info on a forum.
     *
     * @param int $ForumID
     *        	the forum ID.
     */
    public static function get_forum_info($ForumID)
    {
        $Forum = \Gazelle\G::$Cache->get_value("ForumInfo_$ForumID");
        if (!$Forum) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query("
				SELECT
					Name,
					MinClassRead,
					MinClassWrite,
					MinClassCreate,
					COUNT(forums_topics.ID) AS Topics
				FROM forums
					LEFT JOIN forums_topics ON forums_topics.ForumID = forums.ID
				WHERE forums.ID = '$ForumID'
				GROUP BY ForumID");
            if (!\Gazelle\G::$DB->has_results()) {
                return false;
            }
            // Makes an array, with $Forum['Name'], etc.
            $Forum = \Gazelle\G::$DB->next_record(MYSQLI_ASSOC);

            \Gazelle\G::$DB->set_query_id($QueryID);

            \Gazelle\G::$Cache->cache_value("ForumInfo_$ForumID", $Forum, 86400);
        }
        return $Forum;
    }

    /**
     * Get the forum categories
     * @return array ForumCategoryID => Name
     */
    public static function get_forum_categories()
    {
        $ForumCats = \Gazelle\G::$Cache->get_value('forums_categories');
        if ($ForumCats === false) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query('
				SELECT ID, Name
				FROM forums_categories
				ORDER BY Sort, Name');
            $ForumCats = [];
            while (list($ID, $Name) = \Gazelle\G::$DB->next_record()) {
                $ForumCats[$ID] = $Name;
            }
            \Gazelle\G::$DB->set_query_id($QueryID);
            \Gazelle\G::$Cache->cache_value('forums_categories', $ForumCats, 0);
        }
        return $ForumCats;
    }

    /**
     * Get the forums
     * @return array ForumID => (various information about the forum)
     */
    public static function get_forums()
    {
        if (!$Forums = \Gazelle\G::$Cache->get_value('forums_list')) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query('
				SELECT
					f.ID,
					f.CategoryID,
					f.Name,
					f.Description,
					f.MinClassRead AS MinClassRead,
					f.MinClassWrite AS MinClassWrite,
					f.MinClassCreate AS MinClassCreate,
					f.NumTopics,
					f.NumPosts,
					f.LastPostID,
					f.LastPostAuthorID,
					f.LastPostTopicID,
					f.LastPostTime,
					0 AS SpecificRules,
					t.Title,
					t.IsLocked AS Locked,
					t.IsSticky AS Sticky
				FROM forums AS f
					JOIN forums_categories AS fc ON fc.ID = f.CategoryID
					LEFT JOIN forums_topics AS t ON t.ID = f.LastPostTopicID
				GROUP BY f.ID
				ORDER BY fc.Sort, fc.Name, f.CategoryID, f.Sort, f.Name');
            $Forums = \Gazelle\G::$DB->to_array('ID', MYSQLI_ASSOC, false);

            \Gazelle\G::$DB->query('
				SELECT ForumID, ThreadID
				FROM forums_specific_rules');
            $SpecificRules = [];
            while (list($ForumID, $ThreadID) = \Gazelle\G::$DB->next_record(MYSQLI_NUM, false)) {
                $SpecificRules[$ForumID][] = $ThreadID;
            }
            \Gazelle\G::$DB->set_query_id($QueryID);
            foreach ($Forums as $ForumID => &$Forum) {
                if (isset($SpecificRules[$ForumID])) {
                    $Forum['SpecificRules'] = $SpecificRules[$ForumID];
                } else {
                    $Forum['SpecificRules'] = [];
                }
            }
            \Gazelle\G::$Cache->cache_value('forums_list', $Forums, 0);
        }
        return $Forums;
    }

    /**
     * Get all forums that the current user has special access to ("Extra forums" in the profile)
     * @return array Array of ForumIDs
     */
    public static function get_permitted_forums()
    {
        return isset(\Gazelle\G::$LoggedUser['CustomForums']) ? array_keys(\Gazelle\G::$LoggedUser['CustomForums'], 1) : [];
    }

    /**
     * Get all forums that the current user does not have access to ("Restricted forums" in the profile)
     * @return array Array of ForumIDs
     */
    public static function get_restricted_forums()
    {
        return isset(\Gazelle\G::$LoggedUser['CustomForums']) ? array_keys(\Gazelle\G::$LoggedUser['CustomForums'], 0) : [];
    }

    /**
     * Get the last read posts for the current user
     * @param array $Forums Array of forums as returned by self::get_forums()
     * @return array TopicID => array(TopicID, PostID, Page) where PostID is the ID of the last read post and Page is the page on which that post is
     */
    public static function get_last_read($Forums)
    {
        if (isset(\Gazelle\G::$LoggedUser['PostsPerPage'])) {
            $PerPage = \Gazelle\G::$LoggedUser['PostsPerPage'];
        } else {
            $PerPage = POSTS_PER_PAGE;
        }
        $TopicIDs = [];
        foreach ($Forums as $Forum) {
            if (!empty($Forum['LastPostTopicID'])) {
                $TopicIDs[] = $Forum['LastPostTopicID'];
            }
        }
        if (!empty($TopicIDs)) {
            $QueryID = \Gazelle\G::$DB->get_query_id();
            \Gazelle\G::$DB->query("
				SELECT
					l.TopicID,
					l.PostID,
					CEIL(
						(
							SELECT
								COUNT(p.ID)
							FROM forums_posts AS p
							WHERE p.TopicID = l.TopicID
								AND p.ID <= l.PostID
						) / $PerPage
					) AS Page
				FROM forums_last_read_topics AS l
				WHERE l.TopicID IN(" . implode(',', $TopicIDs) . ") AND
					l.UserID = '" . \Gazelle\G::$LoggedUser['ID'] . "'");
            $LastRead = \Gazelle\G::$DB->to_array('TopicID', MYSQLI_ASSOC);
            \Gazelle\G::$DB->set_query_id($QueryID);
        } else {
            $LastRead = [];
        }
        return $LastRead;
    }

    /**
     * Add a note to a topic.
     * @param int $TopicID
     * @param string $Note
     * @param int|null $UserID
     * @return boolean
     */
    public static function add_topic_note($TopicID, $Note, $UserID = null)
    {
        if ($UserID === null) {
            $UserID = \Gazelle\G::$LoggedUser['ID'];
        }
        $QueryID = \Gazelle\G::$DB->get_query_id();
        \Gazelle\G::$DB->query("
			INSERT INTO forums_topic_notes
				(TopicID, AuthorID, AddedTime, Body)
			VALUES
				($TopicID, $UserID, '" . \Gazelle\Util\Time::sqltime() . "', '" . \Gazelle\Util\Db::string($Note) . "')");
        \Gazelle\G::$DB->set_query_id($QueryID);
        return (bool)\Gazelle\G::$DB->affected_rows();
    }

    /**
     * Determine if a thread is unread
     * @param bool $Locked
     * @param bool $Sticky
     * @param int $LastPostID
     * @param array $LastRead An array as returned by self::get_last_read
     * @param int $LastTopicID TopicID of the thread where the most recent post was made
     * @param string $LastTime Datetime of the last post
     * @return bool
     */
    public static function is_unread($Locked, $Sticky, $LastPostID, $LastRead, $LastTopicID, $LastTime)
    {
        return (!$Locked || $Sticky) && $LastPostID != 0 && ((empty($LastRead[$LastTopicID]) || $LastRead[$LastTopicID]['PostID'] < $LastPostID) && strtotime($LastTime) > \Gazelle\G::$LoggedUser['CatchupTime']);
    }

    /**
     * Create the part of WHERE in the sql queries used to filter forums for a
     * specific user (MinClassRead, restricted and permitted forums).
     * @return string
     */
    public static function user_forums_sql()
    {
        // I couldn't come up with a good name, please rename this if you can. -- Y
        $RestrictedForums = self::get_restricted_forums();
        $PermittedForums = self::get_permitted_forums();
        if (\Gazelle\Donations::has_donor_forum(\Gazelle\G::$LoggedUser['ID']) && !in_array(DONOR_FORUM, $PermittedForums)) {
            $PermittedForums[] = DONOR_FORUM;
        }
        $SQL = "((f.MinClassRead <= '" . \Gazelle\G::$LoggedUser['Class'] . "'";
        if (count($RestrictedForums)) {
            $SQL .= " AND f.ID NOT IN ('" . implode("', '", $RestrictedForums) . "')";
        }
        $SQL .= ')';
        if (count($PermittedForums)) {
            $SQL .= " OR f.ID IN ('" . implode("', '", $PermittedForums) . "')";
        }
        $SQL .= ')';
        return $SQL;
    }
}
