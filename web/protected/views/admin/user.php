<table id="user_admin_table">
	<thead>
		<tr>
			<td>ID</td>
			<td>nickname</td>
			<td>from</td>
			<td>email</td>
			<td>tel</td>
			<td>Register Date</td>
			<td>Actions</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($list as $item): ?>
		<tr>
			<td><span><?php echo $item["user_id"]?></span></td>
			<td><span><?php echo $item["nickname"]?></span></td>
			<td><span><?php echo $item["from"]?></span></td>
			<td><span><?php echo $item["email"]?></span></td>
			<td><span><?php echo $item["tel"]?></span></td>
			<td><span><?php echo $item["datetime"]?></span></td>
			<td><a href="#" class="delete_user" data="<?php echo $item["user_id"]?>">Delete</a></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<p style="display:none" id="dialog">Are you sure to delete it ?</p>

<script type="text/javascript">
	(function ($) {
		$("#user_admin_table").dataTable();

		$(".delete_user").click(function (e) {
			var user_id = $(this).attr("data");
			e.preventDefault();
			$("#dialog").dialog({
				buttons: [{
					text: "Confirm",
					click: function () {
						$.ajax({
							url: "/index.php?r=admin/delete&user_id=" + user_id,
							success: function () {
								window.location.reload();
							}
						});
						$( this ).dialog( "close" );
					}
				}, {
					text: "Cancel",
					click: function () {
						$(this).dialog("close");
					}
				}]
			});
		});
	})(jQuery);	
</script>