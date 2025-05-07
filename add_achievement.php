<?php 
    include 'admin/db_connect.php';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['login_id'])) {
        header("Location: login.php");
        exit;
    }


    $registrationId = $_SESSION['registrationId'];
    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $main_category = $_POST['main_category'];
        // $sub_category = $_POST['sub_category'];
        $sub_category = !empty($_POST['custom_subcategory_input']) ? $_POST['custom_subcategory_input'] : $_POST['sub_category'];

        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date']; // From date
        $end_date = $_POST['end_date'];     // To date
        $location = $_POST['location'];
        $status = $_POST['status']; // Winner/Runner/Participant

        // Handle file upload
        $proof = null;
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] == 0) {
            $target_dir = "uploads/proofs/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if not exists
            }
            $file_name = uniqid() . "_" . basename($_FILES["proof"]["name"]);
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
                $proof = $target_file;
            } else {
                echo "<script>alert('Error uploading proof file.');</script>";
            }
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO achievements (registrationId, main_category, sub_category, title, description, start_date, end_date, location , status, proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->bind_param("ssssssssss", $registrationId, $main_category, $sub_category, $title, $description, $start_date, $end_date,$location, $status, $proof);
        $stmt->execute();
        
        // header("Location: index.php?page=my_profile.php");
        // exit;
        echo "<script>alert('Achievement added successfully!');window.location.href='index.php?page=my_profile';</script>";
    }
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Achievement</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            color: rgb(255, 255, 255);
            padding-top: 80px;
            margin: 10px;
        }
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
           
             width: 66%;
         
            padding: 12px;
            margin-left: 270px;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            display: flex;
            align-items:center;
            
        }
        header h1 {
            
            margin: 0;
            width: 100%;
            font-size: 24px;
        }
        .container {
            width: 70%;
            
        }
        .form-group {
            margin-bottom: 15px;
            padding-left :30px
        }
        label {
            font-size: 16px;
          
            margin-bottom: 5px;
            display: inline-block;
        }
        select, input[type="text"], input[type="date"], textarea, input[type="file"], button {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 15px;
            font-size: 16px;
            border-radius: 5px;
            width: 100%;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            background-color: #f44336;
            color: white;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .sidebar {
            width: 230px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 100px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            margin:2px;
            text-decoration: none;
            font-size: 18px;
            font-weight:bold;
            transition: background 0.3s;
             background:rgba(255, 255, 255, 0.7);
            color: rgb(60, 56, 73);
            
            border-radius: 16px;
        }

        .sidebar a:hover {
            background: #34495e;
        }
         #custom_subcategory {
            display: none; /* Hide the custom input field by default */
        }
         
    </style>
</head>
<body>

<header>
    <h1>Add New Achievement</h1>
</header>
 
<div class="sidebar">
    <h2>My Profile</h2>
    <a href="index.php?page=my_profile">Dashboard</a>
    <a href="index.php?page=add_achievement">Add Achievements</a>
 </div>
<div class="container" >
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="main_category">Main Category:</label>
            <select name="main_category" required onchange="updateSubCategories(this.value)">
                <option value="">Select</option>
                <option value="Cultural">Cultural</option>
                <option value="Technical">Technical</option>
                <option value="Sports">Sports</option>
                 <option value="Academic">Academic</option>
                <option value="Leadership">Leadership</option>
                <option value="Social Impact">Social Impact</option>
                <option value="Creativity">Creativity</option>
                <option value="Personal Development">Personal Development</option>
                <option value="Other">Other</option>
            </select>
        </div>
         <div class="form-group" id="custom_subcategory">
            <label for="custom_subcategory_input">Custom Subcategory:</label>
            <input type="text" name="custom_subcategory_input" placeholder="Enter custom subcategory" oninput="updateSubCategoryFromCustomInput()">

        </div>

        <div class="form-group">
            <label for="sub_category">Sub Category:</label>
            <select name="sub_category" id="sub_category" required>
                <option value="">Select main category first</option>
            </select>
        </div>

        
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description"></textarea>
        </div>

        <div class="form-group">
            <label for="start_date">Achievement From (Date):</label>
            <input type="date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">Achievement To (Date):</label>
            <input type="date" name="end_date" required>
        </div>
        <div class="form-group">
            <label for="location">Location/Venue:</label>
            <input type="text" name="location" required>
        </div>
      
        <div class="form-group">
            <label for="status">Status (Winner / Runner / Participant):</label>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="Winner">Winner</option>
                <option value="Runner">Runner</option>
                <option value="Participant">Participant</option>
            </select>
        </div>

        <div class="form-group">
            <label for="proof">Upload Proof (PDF/JPG/PNG):</label>
            <input type="file" name="proof" accept=".pdf, .jpg, .jpeg, .png" required>
        </div>

        <button type="submit">Submit</button>
    </form>
</div>

<script>
function updateSubCategories(category) {
    const subCategorySelect = document.getElementById('sub_category');
    
    subCategorySelect.innerHTML = ''; // Clear existing options
    const customSubCategoryInput = document.getElementById('custom_subcategory');
    customSubCategoryInput.style.display = 'none'; // Hide the custom input by default

    let options = [];
    if (category === 'Cultural') {
        options = ['Select','Dance', 'Music', 'Drama', 'Painting','Art & Craft Exhibitions', 'Theatre', 'Poetry and Short Stories','Photography' , 'Event Organization'];
    } else if (category === 'Technical') {
        options = ['Select','Hackathon','Open Source Contributions', 'Coding Contest', 'Project Exhibition', 'Technical Workshops','Conferences','Paper Presentation','Poster Competition', 'Internship','Tech Job Experience' , 'Seminar','Research Papers','Publications', 'Patent Awarded'];
    } else if (category === 'Sports') {
        options = ['Select','Football', 'Cricket', 'Athletics', 'Chess','Tennis', 'Badminton','Basketball', 'Cricket','Sports Leadership'];
    }else if (category === 'Academic') {
        options = ['Select','High Academic Grades', 'Scholarships', 'Student of the Year', 'Top Achiever Awards','Advanced Degree Achievements', 'Presentations at Academic Conferences', 'Research Assistantships' ,'Mentoring Other Students'];
    }
    else if (category === 'Professional') {
        options = ['Select','Job Promotions', 'Professional Certifications', 'Industry Recognition', 'Published Articles','Successful Product Launches'];
    }
    else if (category === 'Leadership') {
        options = ['Select','Leading Community Service Projects', 'Leadership in Clubs', 'Volunteer Initiatives', 'Managing Teams'];
    }
    else if (category === 'Social Impact') {
        options = ['Select','Volunteering for NGOs', 'Social Justice Initiatives', 'Environmental Sustainability Projects', 'Awareness Campaigns'];
    }
    else if (category === 'Creativity') {
        options = ['Select','Writing and Publishing a Book/Novel', 'Short Film', 'Documentary', 'Photography','Graphic Design ','Fashion Design ' ,'Music Composition', 'Theater Directing','Scriptwriting', 'Entrepreneurship' ];
    }else if (category === 'Personal Development') {
        options = ['Select','Financial Literacy and Budgeting Skills', 'Personal Growth', 'Public Speaking and Communication Skills', 'Volunteer '];
    }if (category === 'Other') {
        customSubCategoryInput.style.display = 'block'; // Show the custom input
        subCategorySelect.innerHTML = '<option value="">Enter custom subcategory above</option>'; 
        subCategorySelect.required = false; // Make dropdown not required when 'Other'
    } else {
        subCategorySelect.required = true; // Otherwise dropdown is required
    }


    options.forEach(sub => {
        const option = document.createElement('option');
        option.value = sub;
        option.text = sub;
        subCategorySelect.add(option);
    });


    
 }
  function updateSubCategoryFromCustomInput() {
    const customInput = document.querySelector('input[name="custom_subcategory_input"]').value;
    const subCategorySelect = document.getElementById('sub_category');
    
    if (customInput.trim() !== '') {
        subCategorySelect.innerHTML = `<option value="${customInput}" selected>${customInput}</option>`;
    }
 }


</script>

</body>
</html>

