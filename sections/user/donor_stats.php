<?
if (check_perms('users_mod') || $OwnProfile || \Gazelle\Donations::is_visible($UserID)) { ?>
	<div class="box box_info box_userinfo_donor_stats">
		<div class="head colhead_dark">Donor Statistics</div>
		<ul class="stats nobullet">
			<li>
				Total donor points: <?=\Gazelle\Donations::get_total_rank($UserID)?>
			</li>
			<li>
				Current donor rank: <?=\Gazelle\Donations::render_rank(\Gazelle\Donations::get_rank($UserID), true)?>
			</li>
			<li>
				Last donated: <?=\Gazelle\Util\Time::timeDiff(\Gazelle\Donations::get_donation_time($UserID))?>
			</li>
		</ul>
	</div>
<?
}
