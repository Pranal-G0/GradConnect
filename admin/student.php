<?php include('db_connect.php');?>

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
			</div>
		</div>
		<div class="row">
			<!-- Student Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>List of Students</b>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th>Avatar</th>
									<th>Name</th>
									<th>Branch</th>
									<th>Admission Year</th>
									<th>Pass-out Year</th>
									<th>Status</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$students = $conn->query("SELECT s.*, c.course, CONCAT(s.lastname, ', ', s.firstname, ' ', s.middlename) AS name 
									FROM student_bio s 
									INNER JOIN courses c ON c.id = s.course_id 
									ORDER BY name ASC");
								while($row = $students->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class="text-center">
										<div class="avatar">
											<img src="assets/uploads/<?php echo $row['avatar'] ?>" class="" alt="">
										</div>
									</td>
									<td><b><?php echo ucwords($row['name']) ?></b></td>
									<td><?php echo $row['course'] ?></td>
									<td class="text-center"><?php echo $row['admission_year'] ?></td>
									<td class="text-center"><?php echo $row['passout_year'] ?></td>
									<td class="text-center">
										<?php if($row['status'] == 1): ?>
											<span class="badge badge-primary">Verified</span>
										<?php else: ?>
											<span class="badge badge-secondary">Not Verified</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<button class="btn btn-sm btn-outline-primary view_student" type="button" data-id="<?php echo $row['id'] ?>">View</button>
										<!-- <button class="btn btn-sm btn-outline-danger delete_student" type="button" data-id="<?php echo $row['id'] ?>">Delete</button> -->
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- End Table Panel -->
		</div>
	</div>
</div>

<style>
	td {
		vertical-align: middle !important;
	}
	td p {
		margin: unset
	}
	img {
		max-width: 100px;
	}
	.avatar {
		display: flex;
		border-radius: 100%;
		width: 100px;
		height: 100px;
		align-items: center;
		justify-content: center;
		border: 3px solid;
		padding: 5px;
	}
	.avatar img {
		max-width: 100%;
		max-height: 100%;
		border-radius: 100%;
	}
</style>

<script>
	$(document).ready(function() {
		$('table').dataTable()
	})

	$('.view_student').click(function() {
		uni_modal("Student Profile", "view_student.php?id=" + $(this).attr('data-id'), 'mid-large')
	})

	$('.delete_student').click(function() {
		_conf("Are you sure to delete this student?", "delete_student", [$(this).attr('data-id')])
	})

	function delete_student($id) {
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_student',
			method: 'POST',
			data: { id: $id },
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Student record successfully deleted", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)
				}
			}
		})
	}
</script>
