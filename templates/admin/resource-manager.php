<?php 

// echo "<pre>";
// var_dump(RM_Loader::get_cpt_data("resource"));
// echo "</pre>";
// exit;
?>

<div class="container">  
  <form id="assign-project" action="" method="post">
    <h3>Assign a Project</h3>
    <fieldset>
      <select name="resource">
		<option value="">Select Resource</option>
		<?php
		foreach(RM_Main::get_cpt_data("resource") as $resource_id => $resource_name){
			echo "<option value={$resource_id}>{$resource_name}</option>";

		}
		?>
      </select>
    </fieldset>
   <fieldset>
      <select name="project">
		<option value="">Select Project</option>
		<?php
		foreach(RM_Main::get_cpt_data("project") as $project_id => $project_name){
			echo "<option value={$project_id}>{$project_name}</option>";
		}
		?>
	</select>
    </fieldset>
   
    <fieldset>
      <input type="number" placeholder="allocation%" name="allocation">
      <input type="hidden" name="action" value="assign_project">
    </fieldset>
  
    <fieldset>
      <button name="submit" type="submit" id="contact-submit">Submit</button>
    </fieldset>
  </form>
</div>

<div class="datatable">
	<div style="width: 100%; height:50px; border: 1px solid;"> 
		<label>  </label>
	</div>
	<div> 
		<h3> Resources Details </h3>
	</div>
	<table >
		<thead>
			<tr >
				<th style="width:9%">
					<h3> Resource ID </h3>
				</th>
				<th style="width:9%">
					<h3>Resource Name </h3>
				</th>
				<th style="width:9%">
					<h3>Project </h3>
				</th>
				<th style="width:9%">
					<h3>Allocation</h3>
				</th>
				
			</tr>
		</thead>
		<tbody>
			<?php 
				global $wpdb;
				$projects_resources =  $wpdb->prefix . 'projects_resources';
				
				$total_records = $wpdb->get_var("SELECT COUNT(1) FROM $projects_resources");

				$items_per_page = 5;
				$page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
				$offset = ( $page * $items_per_page ) - $items_per_page;
				$projects_resources_results = $wpdb->get_results( "SELECT * FROM $projects_resources  LIMIT ${offset}, ${items_per_page}");
	
				foreach($projects_resources_results as $key => $projects_resources_result){
					$resource_id = $projects_resources_result->resource_id;
					$project_id = $projects_resources_result->project_id;
					$allocation = $projects_resources_result->allocation;
					?>
					<tr>
						<td class="manage-column" style="width:9%">
							<?php echo $resource_id ?>
						</td>
						<?php $resourse_name = get_the_title($resource_id) ?>
						<td class="manage-column" style="width:9%"> 
							<?php echo $resourse_name; ?> 
						</td>
						<?php $project_name = get_the_title($project_id) ?>
						<td class="manage-column" style="width:9%"> 
							<?php echo $project_name; ?> 
						</td>
						<td class="manage-column" style="width:9%">  
							<?php echo $allocation."%"; ?>
						</td>
					</tr>
					<?php
				}	
			
			?>	
		</tbody>
	</table>
	<div> 
		<?php
			echo paginate_links( array(
			'base' => add_query_arg( 'cpage', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil($total_records / $items_per_page),
			'current' => $page
			)); 
		?>
	</div>
</div>