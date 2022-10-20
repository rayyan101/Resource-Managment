<div class="container" style="display:none;"> 
  <form id="assign-project" action="" method="post">
    <fieldset>
	<h3 style="text-align: center;">Assign a Project</h3>
	</fieldset>
    
    <fieldset>
      <select class="select-data" style="width:100%; padding-top: 20%;" name="resource">
		<option value="">Select Resource</option>
		<?php
		foreach(RM_Main::get_cpt_data("resource") as $resource_id => $resource_name){
			echo "<option value={$resource_id}>{$resource_name}</option>";

		}
		?>
      </select>
    </fieldset>
   <fieldset>
      <select class="select-data" style="width:100%x;"  name="project">
		<option class="select-data-value" value="">Select Project</option>
		<?php
		foreach(RM_Main::get_cpt_data("project") as $project_id => $project_name){
			echo "<option value={$project_id}>{$project_name}</option>";
		}
		?>
	</select>
    </fieldset>
   
    <fieldset>
      <input type="number" min="0" placeholder="allocation%" name="allocation">
      <input type="hidden" name="action" value="assign_project">
    </fieldset>
  
    <fieldset>
      <button name="submit" type="submit" id="contact-submit">Submit</button>
    </fieldset>
  </form>
</div>

<div class="main_heading">
	<a href="<?php echo admin_url( 'post-new.php?post_type=project' ); ?>" id="add_project" style="padding: 5px; border-radius: 5px; width:100px; margin-right: 10%;" class="button button-primary"> Add Project </a>
	<a id="assign_project" style="padding: 5px; border-radius: 5px;" class="button button-primary"> Assign a Project </a>
	<a href="<?php echo admin_url( 'post-new.php?post_type=resource' ); ?>" id="add_resource" style="padding: 5px; border-radius: 5px;  width:100px; margin-left: 10%;" class="button button-primary"> Add Resource </a>	
</div>

	
<div class="main_filter">
	
	<h1 style="margin-bottom: 20px;"> Resources Details </h1>
	<div class="filters">
		<select class="select-dataa" id="designation" style="width:130px;" name="resource">
			<option value="">Search By Role</option>
			<option value="pm">Project Manager</option>
			<option value="bd">Backend Developer</option>
			<option value="fd">Frontend Developer</option>
			<option value="sqa">Software Quality Assurance</option>
      </select>
	</div> 
	<div class="filters"> 		
		<input type="text" style="width:120px;" placeholder="Resource Name" id="resource_name"  name="resource_name">
	</div> 
	<div class="filters"> 
		<input  type="text" style="width:120px;" id="project_name" placeholder="Project Name" name="project_name">
	</div> 
	<div class="filters">  
		<label>
		<input type="checkbox" style="width:10px;" id="availability"  name="availability">Availability
		</label>
	</div>
	<div class="filters">
	<a id="searching_button" style="width:80px;" class="button button-primary"> Search </a>
	</div>
	
	<div class="filters"> 
		<select class="select-dataa" id="status" style="width:140px;" name="resource">
			<option value="">Resource Status</option>
			<option value="1">Working</option>
			<option value="0">Not Working</option>
			
      </select>
	</div>
</div>
<div class="datatable">
	<table class ="resource-data-table" id="resource_allocation_table" style="display:none;">
		<thead>
			<th class="resource-column"> 
				<h3> Resource Name </h3>
			 </th>
			 <th class="resource-column"> 
				<h3> Designation </h3>
			 </th>
			<th class="resource-column"> 
				<h3> Allocation % </h3> 
			</th>
			<th class="resource-column"> 
				<h3> Availability % </h3> 
			</th>
		</thead>
		<tbody> 
		</tbody>
	</table>
	<table class ="resource-data-table" id="project_resources_table">
		<thead>
			<tr>
				<th class="resource-column">
					<h3> Resource Name </h3>
				</th>
				<th class="resource-column">
					<h3> Designation </h3>
				</th>
				<th class="resource-column">
					<h3>Project Name</h3>
				</th>
				<th class="resource-column">
					<h3> Deadline </h3>
				</th>
				<th class="resource-column">
					<h3> Status </h3>
				</th>
				<th class="resource-column">
					<h3>Allocation %</h3>
				</th>	
			</tr>
		</thead>
		<tbody>
			<?php 
				global $wpdb;
				$projects_resources =  $wpdb->prefix . 'projects_resources';
				$total_records = $wpdb->get_var("SELECT COUNT(1) FROM $projects_resources");
				$items_per_page = 10;
				$page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
				$offset = ( $page * $items_per_page ) - $items_per_page;
				$quary = "SELECT * FROM $projects_resources where status = 1  LIMIT ${offset}, ${items_per_page}";
				$projects_resources_results = $wpdb->get_results( $quary);
					
				foreach($projects_resources_results as $key => $projects_resources_result){
					
					$resource_id = $projects_resources_result->resource_id;
					$resourse_name = $projects_resources_result->resource_name;
					$project_id = $projects_resources_result->project_id;
					$post_id  = $project_id;
					$deadline = get_post_meta($post_id,"deadline",true);
					$project_name = $projects_resources_result->project_name;
					$status = $projects_resources_result->status;
					$allocation = $projects_resources_result->allocation;
					$designation = get_post_meta($resource_id,"resource_position",true);
					// print_r($designation); die();
					?>
					<tr>
						<td class="resource-column" >
							<?php echo $resourse_name;  ?>	
						</td>
						<td class="resource-column" > 
							<?php echo $designation; ?>
						</td>
						<td class="resource-column" > 
							<?php echo $project_name; ?>
						</td>
						<td class="resource-column" > 
							<?php  echo date('m-d-Y ',strtotime($deadline)); ?>
						</td>
						
						<td class="resource-column" >  
							<?php echo "Working"   ?>
						</td>
						<td class="resource-column" >  
							<?php echo $allocation."%"; ?>
						</td>
					</tr>
					<?php
				}	
			?>	
		</tbody>
	</table>
	<div id="pr-pagination"> 
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

