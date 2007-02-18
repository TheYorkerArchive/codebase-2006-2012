	<span class="grey">Showing</span> <?php echo count($entries); ?>
	<span class="grey">entries from</span> <?php echo $this->uri->segment(3); ?>
	<span class="grey">ordered by</span> <?php echo $this->uri->segment(4); ?>
	<br /><br />
	<table class="ReviewList">
		<tr class="ReviewListTop">
			<td><a href="/reviews/table/food/name" name="tabletop">Name</a></td>
			<td><a href="/reviews/table/food/star"><span class="sorted_by"><img style="display: inline;" src="/images/prototype/reviews/sortarrow.gif" alt="v" /> Star Rating</span></a></td>
			<td><a href="/reviews/table/food/user">User Rating</a></td>

<?php
//Tag names at top of table
if (isset($review_tags))
{
	foreach ($review_tags as &$tag)
		{
			echo '<td><a href="/reviews/table/food/any">'.$tag.'</a></td>';
		}
}
?>
		</tr>

<?php
	//For each row in the table
	$flip=1;
	foreach ($entries as &$entry)
	{

		echo '<tr class="ReviewElement'.$flip.'">
				<td>
				<h3><a href="'.$entry['review_table_link'].'">'.$entry['review_title'].'</a></h3><br />
				<a href="'.$entry['review_website'].'">'.$entry['review_website'].'</a><br />
				<a href="#">&gt;Food</a>&nbsp;&nbsp;<a href="#">&gt;Drink</a><br />
			    </td>
			    <td>'.$entry['review_rating'].' Stars</td>
			    <td>'.$entry['review_user_rating'].'/10</td>';
	//Tag handing
	foreach ($entry['tagbox'] as &$taglist)
	{
		echo '<td>';
		foreach ($taglist as &$tag)
			{
				echo $tag.'<br />';
			}
		echo '</td>';
	}

		echo '</tr>'; //End of table
		if ($flip == 1) $flip=2;
		else $flip=1;
	}
?>

		<tr class="ReviewElementEnd">
			<td colspan="6">
				<a href="#tabletop">&gt;Go back to top</a>&nbsp;&nbsp;<a href="food">&gt;Go back to food</a>
			</td>
		</tr>
	</table>
