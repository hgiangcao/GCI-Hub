<?php
    // --- Configuration & Data Loading ---
    $coursename = "Introduction to AI & Information: Lab";
    $course_code = "AIL_114";
    $logo = "line_qr.jfif";
    $line_qr = "line_qr.jfif";
    $line_link= "https://line.me/ti/g/YMhXsThCSS";
    
    $scoreboard_link = "http://chgiang.com/exam/scoreboard.php?exam=AIL_114";
    $spreadsheet_url="https://docs.google.com/spreadsheets/d/e/2PACX-1vTMNE5SAQ5n7swVYCU9llZH9i1x8nFAJ_lg2WVsZ0HV0F8-dLsSO5vvOVMb2gv4HI5fzCIm3n50H3yj/pub?output=csv";

    if(!ini_set('default_socket_timeout', 15)) echo "";
    
    $spreadsheet_data = array();
    
    if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spreadsheet_data[] = $data;
        }
        fclose($handle);

        $list_topic = array();
        foreach ($spreadsheet_data as $data){
            $list_topic[] = $data;
        }
    } else {
        die("Problem reading csv");
    }

    $nTopic = count($list_topic);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $coursename; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-bg);
            color: #333;
        }

        /* Hero Header */
        .course-header {
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
            padding: 40px 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        /* Card Styling */
        .info-card {
            background: #fff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            
            transition: transform 0.2s;
        }
        
        .flash {
          animation: blinker 1s linear infinite;
        }

        @keyframes blinker {
          50% { opacity: 0; }
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .card-header-custom {
            background-color: transparent;
            border-bottom: 2px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Quick Links Buttons */
        .btn-quick {
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }
        
        .btn-quick:hover {
            transform: translateX(5px);
        }

        /* Table Styling */
        .table-custom thead th {
            background-color: #0d6efd;
            color: white;
            border: none;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f8ff;
        }

        /* QR Image */
        .qr-img {
            max-width: 150px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 5px;
        }
        
        a { text-decoration: none; }
    </style>
</head>
<body>

    <header class="course-header text-center">
        <div class="container">
            <h1 class="fw-bold text-primary"><?php echo $coursename; ?></h1>
            <div class="mt-3 text-dark">
               
                
            </div>
        </div>
    </header>

    <div class="container pb-5">

        <div class="row g-4 mb-5">
            <div class="col-md-8">
                <div class="card info-card h-100">
                    <div class="card-header-custom"><i class="fa-solid fa-circle-info"></i> General Information</div>
                    <div class="card-body d-flex flex-column justify-content-center">
                   <div class="d-flex align-items-center mb-3">
                          
                            <div>
                                <span class="text-danger me-2 text-success"><img src="../logo/MCUT_logo.png" width="32" /><strong>  Ming Chi University of Technology</strong></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-primary me-3 text-center" style="width: 30px;">
                             <i class="fa-solid fa-chalkboard-user text-success"></i> 
                            </div>
                            <div>
                                <span class="text-secondary fw-bold me-2 text-success"><strong>Instructor:</strong></span>
                                <span class="fw-medium text-dark"> Hoang-Giang Cao (高黃江)</span>
                            </div>
                        </div>
                        
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="text-primary me-3 text-center" style="width: 30px;">
                                <i class="fa-regular fa-clock fs-5"></i>
                            </div>
                            <div>
                                <span class="text-secondary fw-bold me-2">Office Hours:</span>
                                <span class="fw-medium text-dark">Mon: 09:00 - 11:00</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="text-primary me-3 text-center" style="width: 30px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <div>
                                <span class="text-secondary fw-bold me-2">Office:</span>
                                <span class="fw-medium text-dark">AI Center | Innovation Bldg | 3F</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="text-primary me-3 text-center" style="width: 30px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <span class="text-secondary fw-bold me-2">Email:</span>
                                <a href="mailto:chgiang@mail.mcut.edu.tw" class="text-decoration-underline text-dark">chgiang@mail.mcut.edu.tw</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card info-card text-center">
                    <div class="card-header-custom"><i class="fa-brands fa-line text-success"></i> Course Group</div>
                    <div class="card-body">
                        <img src="<?php echo $line_qr; ?>" class="qr-img mb-3" alt="Line QR">
                        <br>
                        <a href="<?php echo $line_link; ?>" class="btn btn-outline-success btn-sm rounded-pill px-4">Join Group</a>
                    </div>
                </div>
            </div>

<div class="row g-2 mb-4 text-center">
    <div class="col-md-4 text-center">
        <a href="<?php echo $scoreboard_link; ?>" class="btn btn-light btn-quick shadow-sm border h-100 m-0">
            <span><i class="fa-solid fa-trophy text-warning me-2 text-center"></i> Scoreboard</span>
            <i class="fa-solid fa-chevron-right text-muted"></i>
        </a>
    </div>
    <div class="col-md-4 text-center">
        <a href="http://chgiang.com/exam" class="btn btn-light btn-quick shadow-sm border h-100 m-0">
            <span><i class="fa-solid fa-upload text-primary me-2 text-center"></i> Exam System</span>
            <i class="fa-solid fa-chevron-right text-muted"></i>
        </a>
    </div>
    <div class="col-md-4 text-center">
        <a href="http://chgiang.com/comments/index.php?course=<?php echo $course_code; ?>" class="btn btn-light btn-quick shadow-sm border h-100 m-0">
            <span><i class="fa-solid fa-comment-dots text-info me-2 text-center"></i> Suggestions</span>
            <i class="fa-solid fa-chevron-right text-muted"></i>
        </a>
    </div>
</div>

        </div>
        

        <div class="row g-4">
            <div class="col-lg-4">
                

                <h5 class="fw-bold mb-3 border-start border-4 border-danger ps-2">Assignments  & Exams </h5>
                <div class="card info-card">
                    <div class="list-group list-group-flush">
                        <?php 
                        // Error handling if file doesn't exist to prevent page break
                        if (file_exists('../exam/ultil.php')) {
                            include ('../exam/ultil.php');
                            $exam_info = get_exam_data();
                            $nExam = count($exam_info);
                            
                            $has_exams = false;
                            for ($i = 1; $i < $nExam; $i++) {
                                if (isset($exam_info[$i][2]) && strcmp($exam_info[$i][2], $course_code) == 0) {
                                    $has_exams = true;
                                    $is_new = (int)($exam_info[$i][8]) == 1;
                                    $name = convert_exam_name($exam_info[$i][1]);
                                    $desc = isset($exam_info[$i][3]) ? $exam_info[$i][3] : '';
                                    $link = $exam_info[$i][6];
                                    
                                    echo "<a href='$link' class='list-group-item list-group-item-action py-3'>";
                                    echo "<div class='d-flex w-100 justify-content-between align-items-center'>";
                                     if ($is_new) {
                                    echo "<h6 class='mb-1 text-danger fw-bold'>$name</h6>";
                                    }
                                    else  {
                                    echo "<h6 class='mb-1 text-dark '>$name</h6>";
                                    }
                                    if ($is_new) {
                                        echo "<span class='badge bg-success rounded-pill flash'>ACTIVE</span>";
                                    }
                                    else{
                                        echo "<span class='badge bg-secondary rounded-pill'>EXPIRED</span>";
                                    }
                                    echo "</div>";
                                    if ($is_new && $desc) {
                                        echo "<small class='text-muted'>$desc</small>";
                                    }
                                    echo "</a>";
                                }
                            }
                            if (!$has_exams) {
                                echo "<div class='p-3 text-muted text-center'><small>No active exams available.</small></div>";
                            }
                        } else {
                            echo "<div class='p-3 text-danger'><small>Error: Exam utility not found.</small></div>";
                        }
                        ?>
                    </div>
                </div>

                <h5 class="fw-bold mt-4 mb-3 border-start border-4 border-success ps-2">Resources</h5>
                <div class="card info-card mb-4">
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><a href="https://docs.python.org/3/" target="_blank"><i class="fa-brands fa-python"></i> Official Python 3 Docs</a></li>
                            <li class="mb-2"><a href="https://realpython.com/" target="_blank"><i class="fa-solid fa-book-open"></i> Real Python</a></li>
                            <li><a href="https://www.geeksforgeeks.org/python/" target="_blank"><i class="fa-solid fa-code"></i> GeeksforGeeks Python</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="col-lg-8">
                <h5 class="fw-bold mb-3 border-start border-4 border-primary ps-2">Course Schedule & Slides</h5>
                <div class="card info-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom table-striped mb-0">
                            <thead>
                                <tr>
                                    <th width="10%">Index</th>
                                    <th width="80%">Topic</th>
                                    <th width="10%">Materials</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($i=1; $i < $nTopic; $i++){
                                    echo "<tr>";
                                    // Index
                                    echo "<td class='text-center fw-bold text-secondary'>".$i."</td>";
                                    
                                    // Topic (Link if exists)
                                    echo "<td>";
                                    if (isset($list_topic[$i][1]) && strcmp($list_topic[$i][1],"")!=0) {
                                        echo "<a href='".$list_topic[$i][1]."' class='fw-medium text-dark text-decoration-underline'>".$list_topic[$i][0]."</a>";
                                    } else {
                                        echo "<span class='text-dark'>".$list_topic[$i][0]."</span>";
                                    }
                                    echo "</td>";
                                    
                                    // Content/Slides
                                    echo "<td class=''>";
                                    if (isset($list_topic[$i][3]) && isset($list_topic[$i][2])) {
                                        echo "<a href='".$list_topic[$i][3]."' class='btn btn-sm btn-outline-primary '><i class='fa-regular fa-file-pdf'></i> ".$list_topic[$i][2]."</a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="bg-white border-top py-4 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-2">chgiang © 2024</p>

        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>