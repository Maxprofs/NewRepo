<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>My Best Offer</th>
			<th>Best Marketplace Offer</th>
			<th>Sales Rank</th>
			<th>Units Sold(last 30 days)</th>			
		</tr>
	</thead>
	<tbody>                       				
		<tr>
			<td>
				<? 
					if (isset($details['my_best_offer'])) 
					{
						echo $details['my_best_offer'][0]['shipping_price']+$details['my_best_offer'][0]['item_price'];
					}
				?>
			</td>					
			<td>
				<?
					if (isset($details['best_marketplace_offer'])) 
					{
					    echo $details['best_marketplace_offer'][0]['shipping_price']+$details['best_marketplace_offer'][0]['item_price'];
					}
				?>
			</td>
			<td>
				<?
					if (isset($details['sales_rank'])) 
					{
						echo  $details['sales_rank']['level_0'];
					}
				?>
			</td>	
			<td>
				<?
					if (isset($details['units_sold'])) 
					{
						echo $details['units_sold']['last_30_days'];											
					}
				?>
			</td>				                        				
		</tr>	                        					                        			
	</tbody>
</table> 