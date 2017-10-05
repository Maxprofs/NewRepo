	<style>
		tr.error-row{
			background-color: #CFD8DC;
		}
		.modal-content-error {
		  margin-top: 26%;
		}
		table.table-bordered tr td {
		    margin: 0;
		}
	</style>
	<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" id="myModal" role="dialog">
	    <div class="modal-dialog">
	    
	      <!-- Modal content-->
	      <div class="modal-content modal-content-error">
	        <div class="modal-header">
	          <button type="button" class="close" data-dismiss="modal">&times;</button>
	          <h4 class="modal-title" style="text-align: center;"> Error Deatils</h4>
	        </div>
	        <div class="modal-body">
				<table class="table table-striped table-bordered">
					<tbody>
						<tr>
							<td class="value_label" width="33%">
								<span>Title</span>
							</td>
							<td class="value form-group " width="100%">
								<span><?= $data['title'] ?></span>
								
							</td>
						</tr>
						<tr class="error-row">
							<td class="value_label" width="33%">
								<span>Error</span>
							</td>
							<td class="value form-group " width="100%">
								<span><?= $data['error'] ?></span>
							</td>
						</tr>
					</tbody>
				</table>
	        </div>
	        <div class="modal-footer">
	          <button type="button" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
	        </div>
	      </div>	      
	    </div>
	  </div>	  
	</div>	