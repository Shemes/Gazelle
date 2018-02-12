<?
$FeaturedAlbum = $Cache->get_value('featured_album');
if ($FeaturedAlbum === false) {
	$DB->query('
		SELECT
			fa.GroupID,
			tg.Name,
			tg.WikiImage,
			fa.ThreadID,
			fa.Title
		FROM featured_albums AS fa
			JOIN torrents_group AS tg ON tg.ID = fa.GroupID
		WHERE Ended = 0');
	$FeaturedAlbum = $DB->next_record();
	$Cache->cache_value('featured_album', $FeaturedAlbum, 0);
}
if (is_number($FeaturedAlbum['GroupID'])) {
	$Artists = \Gazelle\Artists::get_artist($FeaturedAlbum['GroupID']);
?>
		<div class="box">
			<div class="head colhead_dark"><strong>Album of the Month</strong></div>
			<div class="center pad">
				<?=\Gazelle\Artists::display_artists($Artists, true, true)?><a href="torrents.php?id=<?=$FeaturedAlbum['GroupID']?>"><?=$FeaturedAlbum['Name']?></a>
			</div>
			<div class="center pad">
				<a href="torrents.php?id=<?=$FeaturedAlbum['GroupID']?>" class="tooltip" title="<?=\Gazelle\Artists::display_artists($Artists, false, false)?> - <?=$FeaturedAlbum['Name']?>">
					<img src="<?=ImageTools::process($FeaturedAlbum['WikiImage'], true)?>" alt="<?=\Gazelle\Artists::display_artists($Artists, false, false)?> - <?=$FeaturedAlbum['Name']?>" width="100%" />
				</a>
			</div>
			<div class="center pad">
				<a href="forums.php?action=viewthread&amp;threadid=<?=$FeaturedAlbum['ThreadID']?>"><em>Discuss here</em></a>
			</div>
		</div>
<?
}
?>
