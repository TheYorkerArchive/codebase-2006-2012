<?php
if (empty($organisation['reviews_untyped']) && empty($organisation['reviews_by_type'])) {
?>
	<div align='center'>
		<b>This organisation has not been reviewed yet.</b>
	</div>
<?php
}

if (!empty($organisation['reviews_untyped'])) {
	foreach ($organisation['reviews_untyped'] as $review) {
		$author_links = array();
		foreach ($review['authors'] as $author) {
			$author_links[] = '<a href="mailto:'.$author['email'].'">'.$author['name'].'</a>';
		}
		$authors = implode(', ', $author_links);
?>
		<div class="WhyNotTry">
			<div class="ArticleColumn" style="width: 100%;">
				<div style='background-color: #DDDDDD;'>
					<div id='Byline'>
						<div class='RoundBoxBL'>
							<div class='RoundBoxBR'>
								<div class='RoundBoxTL'>
									<div class='RoundBoxTR'>
										<div id='BylineText'>
											Written by<br />
											<span class='name'><?php echo $authors; ?></span><br />
											<?php echo $review['publish_date']; ?><br />
											<span class='links'>
												<?php echo anchor($review['link'], 'See more...'); ?>
											</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php echo $review['content']; ?><br />
		</div>
<?php
	}
}
/*
 * Input
 * $organisation [
 *	'reviews_untyped' [review1, review2....]
 *	'reviews_by_type' [
 *		type1name => [review1, review2...],
 *		type2name => [review1, review2...]
 *	]
 * ]
 */
?>