<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Achievements Filter</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f4f6f9;
      color: #333;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    .filters-container {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      justify-content: space-around;
    }

    .filter-group {
      flex: 1 1 200px;
      min-width: 250px;
      margin-bottom: 20px;
      padding-left:90px;
    }

    .filter-group h4 {
      margin-bottom: 10px;
      color: #0056b3;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-size: 14px;
    }

    input[type="text"],
    input[type="date"] {
      padding: 8px;
      width: 100%;
      max-width: 100%;
      margin-top: 5px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    .achievement {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      box-shadow: 0 0 6px rgba(0,0,0,0.05);
    }

    #results {
      margin-top: 20px;
    }

    .filter-group label {
      font-size: 14px;
      color: #333;
    }

    @media (max-width: 768px) {
      .filters-container {
        flex-direction: column;
        align-items: flex-start;
      }

      .filter-group {
        flex: 1 1 100%;
        
      }
    }

    .button-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
    }

    #download-pdf {
      padding: 10px 20px;
      background-color: #0056b3;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
    }
  </style>
</head>
<body>

<h2>Filter Achievements</h2>

<!-- Search box -->
<input type="text" id="search" placeholder="Search Registration Id or Keyword..." />

<!-- Filters -->
<div class="filters-container">

  <!-- Main Category Filter -->
  <div class="filter-group">
    <h4>Main Category</h4>
    <?php
    $mainCategories = $conn->query("SELECT DISTINCT main_category FROM achievements");
    while ($row = $mainCategories->fetch_assoc()) {
      echo '<label><input type="checkbox" class="filter-checkbox" name="main_category[]" value="'.$row['main_category'].'"> '.$row['main_category'].'</label>';
    }
    ?>
  </div>
   <!-- Course Filter -->
  <div class="filter-group">
    <h4>Course</h4>
    <?php
    $courses = $conn->query("SELECT course FROM courses WHERE id in (SELECT DISTINCT course_id FROM users WHERE course_id IS NOT NULL)");
    while ($row = $courses->fetch_assoc()) {
      echo '<label><input type="checkbox" class="filter-checkbox" name="course[]" value="'.$row['course'].'">'.$row['course'].'</label>';
    }
    ?>
  </div>


  <!-- Year Filter -->
  <div class="filter-group">
    <h4>Year</h4>
    <?php
    $years = $conn->query("SELECT DISTINCT year FROM users");
    while ($row = $years->fetch_assoc()) {
      echo '<label><input type="checkbox" class="filter-checkbox" name="year[]" value="'.$row['year'].'"> '.$row['year'].'</label>';
    }
    ?>
  </div>

  <!-- Gender Filter -->
  <div class="filter-group">
    <h4>Gender</h4>
    <label><input type="checkbox" class="filter-checkbox" name="gender[]" value="male"> Male</label>
    <label><input type="checkbox" class="filter-checkbox" name="gender[]" value="female"> Female</label>
    <label><input type="checkbox" class="filter-checkbox" name="gender[]" value="other"> Other</label>
  </div>

 
  <!-- Date Range Filter -->
  <div class="filter-group">
    <h4>Date Range</h4>
    From:<br>
    <input type="date" id="from_date"><br>
    To:<br>
    <input type="date" id="to_date">
  </div>

  <!-- Column Selector -->
  <div class="filter-group">
    <h4>Select Columns</h4>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="registrationId" onclick="handleCheckboxClick(this)"> Registration ID</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="name" onclick="handleCheckboxClick(this)"> Name</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="year" onclick="handleCheckboxClick(this)"> Year</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="main_category" onclick="handleCheckboxClick(this)"> Main Category</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="sub_category" onclick="handleCheckboxClick(this)"> Sub Category</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="title" onclick="handleCheckboxClick(this)"> Title</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="description" onclick="handleCheckboxClick(this)"> Description</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="start_date" onclick="handleCheckboxClick(this)"> Start Date</label>
    <label><input type="checkbox" class="column-checkbox" name="selected_columns[]" value="end_date" onclick="handleCheckboxClick(this)"> End Date</label>
  </div>

</div>

<!-- Download Button -->
<div class="button-container">
  <button id="download-pdf">
    Download PDF
  </button>
</div>

<!-- Results Section -->
<div id="results"></div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

 let selectedOrder = [];

function handleCheckboxClick(checkbox) {
    const value = checkbox.value;
    if (checkbox.checked) {
        if (!selectedOrder.includes(value)) {
            selectedOrder.push(value);
        }
    } else {
        selectedOrder = selectedOrder.filter(col => col !== value);
    }
}

function fetchFilteredData() {
  let search = $('#search').val();
  let main_category = [];
  let year = [];

  $('input[name="main_category[]"]:checked').each(function() {
    main_category.push($(this).val());
  });
  $('input[name="year[]"]:checked').each(function() {
    year.push($(this).val());
  });

  let selected_columns = selectedOrder;
  let from_date = $('#from_date').val();
  let to_date = $('#to_date').val();

  // Debugging only
  console.log({
    search,
    main_category,
    year,
    from_date,
    to_date,
    selected_columns
  });

  let gender = [];
  let course = [];

  $('input[name="gender[]"]:checked').each(function() {
    gender.push($(this).val());
  });
  $('input[name="course[]"]:checked').each(function() {
    course.push($(this).val());
  });

  $.ajax({
    url: 'fetch_achievements.php',
    method: 'GET',
    data: {
      search: search,
      main_category: main_category,
      year: year,
      from_date: from_date,
      to_date: to_date,
      selected_columns: selected_columns,
      gender: gender,
      course: course
    },
    success: function(data) {
      $('#results').html(data);
    },
    error: function(xhr, status, error) {
      console.error("AJAX Error:", status, error);
    }
  });
}

$(document).ready(function() {
  $('#search, .filter-checkbox, .column-checkbox, #from_date, #to_date').on('input change', fetchFilteredData);
  fetchFilteredData(); // Initial load
});

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById("download-pdf").addEventListener("click", function () {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('l', 'pt', 'a4');
  
  const table = document.getElementById("achievements-table");
  if (!table) {
    alert("No data to export.");
    return;
  }

  html2canvas(table).then(canvas => {
    const imgData = canvas.toDataURL("image/png");
    const imgProps = doc.getImageProperties(imgData);
    const pdfWidth = doc.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    doc.addImage(imgData, 'PNG', 20, 20, pdfWidth - 40, pdfHeight);
    doc.save("achievements.pdf");
  });
});
</script>

</body>
</html>
