<div class="wrap">

	<h2><?php _e('PayPal Mass Pay', 'wpam');?></h2>
	<h3><?php _e('View Existing Payments', 'wpam');?></h3>

	<table class="widefat" style="width: auto">
		<thead>
		<tr>
			<th width="50"></th>
			<th width="20"><?php _e('ID', 'wpam');?></th>
			<th width="150"><?php _e('Date Posted', 'wpam');?></th>
			<th width="130"><?php _e('PayPal ID', 'wpam');?></th>
			<th width="100"><?php _e('Amount', 'wpam');?></th>
			<th width="100"><?php _e('Fee', 'wpam');?></th>
			<th width="100"><?php _e('Total', 'wpam');?></th>
			<th width="120"><?php _e('Status', 'wpam');?></th>
		</tr>
		</thead>
		<tbody>
		<?php if (count($this->viewData['logs']) > 0) { ?>
			<?php foreach ($this->viewData['logs'] as $massPayment) { ?>
					<tr class="transaction-<?php echo $massPayment->status?>">
						<td><a class="button-secondary" href="<?php echo admin_url('admin.php?page=wpam-payments&step=view_payment_detail&id='.$massPayment->paypalLogId)?>"><?php _e('View', 'wpam');?></a></td>
						<td><?php echo $massPayment->paypalLogId?></td>
						<td><?php echo date("m/d/Y H:i:s",$massPayment->dateOccurred)?></td>
						<td><?php echo $massPayment->correlationId?></td>
						<td><?php echo $massPayment->amount?></td>
						<td><?php echo $massPayment->fee?></td>
						<td><?php echo $massPayment->totalAmount?></td>
						<td><?php echo $massPayment->status?></td>
					</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="100" style="text-align: center; vertical-align: middle; font-style: italic;">
					<?php _e('(No Mass Payments on Record)', 'wpam');?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>


</div>

