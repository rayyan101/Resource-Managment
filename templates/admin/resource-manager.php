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
			$designation = get_post_meta($resource_id,"resource_position",true);
			$Level = get_post_meta($resource_id,"level",true);
			echo "<option value={$resource_id}>{$resource_name} &nbsp &nbsp &nbsp ($designation) &nbsp &nbsp &nbsp ($Level)</option>";
			
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
      <input type="number" min="1" placeholder="allocation%" name="allocation">
      <input type="hidden" name="action" value="assign_project">
    </fieldset>
  
    <fieldset>
      <button name="submit" type="submit" id="contact-submit">Submit</button>
    </fieldset>
  </form>
</div>

<div class="container-rm">
	<?php
		if(current_user_can('administrator')){
	?>
	<div class="main_title">
		<h1> Panel Options</h1>
	</div>


	<div class="main_heading">
		<a href="<?php echo admin_url( 'post-new.php?post_type=project' ); ?>" id="add_project" class="btn-custom"> Add Project </a>
		<a id="assign_project" style="padding: 5px; border-radius: 5px;" class="btn-custom"> Assign a Project </a>
		<a href="<?php echo admin_url( 'post-new.php?post_type=resource' ); ?>" id="add_resource" class="btn-custom"> Add Resource </a>	
	</div>
	<?php
		}
	?>
	<div class="main_title" style="margin-top:30px">
		<h1> Resources Details </h1>
	</div>

		
	<div class="main_filter">
		
		<div class="filters">
			<select class="select-dataa" id="designation" name="resource">
				<option value="">Search By Role</option>
				<option value="pm">Project Manager</option>
				<option value="bd">Backend Developer</option>
				<option value="fd">Frontend Developer</option>
				<option value="sqa">Software Quality Assurance</option>
		</select>
		</div> 
		<div class="filters"> 		
			<input type="text"  placeholder="Resource Name" id="resource_name"  name="resource_name">
		</div> 
		<div class="filters"> 
			<input  type="text"  id="project_name" placeholder="Project Name" name="project_name">
		</div> 
		<div class="filters">  
			<label>
			<input type="checkbox" id="availability" checked name="availability">Availability
			</label>
		</div>
		<div class="filters">
		<a id="searching_button" style="cursor: pointer;" class="btn-custom"> Search </a>
		</div>
		
		<div class="filters"> 
			<select class="select-dataa" id="status"  name="resource">
				<option value="1">Select Un Assigned</option>
				<option value="0">Un Assigned</option>
				
		</select>
		</div>
	</div>
	<div class="datatable">
		<table class ="resource-data-table" id="resource_allocation_table" >
			<thead>
				<th class="resource-column"> 
					<h3> Resource Name </h3>
				</th>
				<th class="resource-column"> 
					<h3> Designation </h3>
				</th>
				<th class="resource-column"> 
					<h3> Manager Name </h3>
				</th>
				<th class="resource-column"> 
					<h3> Allocation % </h3> 
				</th>
				<th class="resource-column"> 
					<h3> Availability % </h3> 
				</th>
			</thead>
			<tbody>
				<?php
					 
					global $wpdb;
					$resources_allocation    = $wpdb->prefix.'resources_allocation';
					$quary = "SELECT * FROM $resources_allocation"; 
					$resources_allocation_results = $wpdb->get_results($quary);
					
					foreach($resources_allocation_results as $key => $resources_allocation_result){
						$resource_id = $resources_allocation_result->resource_id; 
						$allocation = $resources_allocation_result->allocation;
						$resource_name = get_the_title($resource_id);
						$designation = get_post_meta($resource_id,"resource_position",true);
						$level = get_post_meta($resource_id,"level",true);
						$manager_name = get_post_meta($resource_id,"manager_name",true);
						
						if($allocation >= 100) { 
							$availability = $allocation - $allocation;
						}
						if($allocation < 100){
							$availability = 100 - $allocation;
						}                    
	
						if($availability >= 75){
							$availability_class = 'avail-green ';
						}
						if($availability <= 75 && $availability >= 50){
							$availability_class = 'avail-orange';
						}
						if($availability <= 50 && $availability >= 0){
							$availability_class = 'avail-red';
						}
	
						if($allocation >= 75){
							$allocation_class = 'avail-red ';
						}
						if($allocation <= 75 && $allocation >= 50){
							$allocation_class = 'avail-orange';
						}
						if($allocation <= 50 && $allocation >= 0){
							$allocation_class = 'avail-green';
						}   
						
						?>
						<tr>
							<td> <?php echo $resource_name; ?> </td>
							<td> <?php echo $designation; echo "&nbsp"; echo '('.$level.')'; ?> </td>
							<td> <?php echo $manager_name; ?> </td>
							<td class="<?php echo $allocation_class; ?>"> <?php echo $allocation.' % Allocated'; ?> </td>
							<td class="<?php echo $availability_class; ?>"> <?php echo $availability.' % Available'; ?> </td>
						</tr>
						<?php
					}

				?>
			</tbody>
		</table>
		<table style="display:none;" class ="resource-data-table" id="project_resources_table">
			<thead>
				<tr>
					<th class="resource-column">
						<h3> Resource Name </h3>
					</th>
					<th class="resource-column">
						<h3> Designation </h3>
					</th>
					<th class="resource-column">
						<h3> Manager Name </h3>
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
					$items_per_page = 100;
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
						$level = get_post_meta($resource_id,"level",true);
						$manager_name = get_post_meta($resource_id,"manager_name",true);
						?>
						<tr>
							<td class="resource-column" >
								<?php echo $resourse_name;  ?>	
							</td>
							<td class="resource-column" > 
								<?php echo $designation; echo"</br>"; echo '('.$level.')'; ?>
							</td>
							<td class="resource-column" > 
								<?php echo $manager_name; ?>
							</td>
							<td class="resource-column" > 
								<?php echo $project_name; ?>
							</td>
							<td class="resource-column" > 
								<?php  echo date('m-d-Y ',strtotime($deadline)); ?>
							</td>
							
							<td class="resource-column" >  
								<?php echo "Assigned"   ?>
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


</div>