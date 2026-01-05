<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCI Demo Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
                :root {
            --bg-light: #f4f7f6;
            --primary-blue: #0d6efd;
            --accent-orange: #ff7f50;
            --text-dark: #2c3e50;
            --card-shadow: 0 4px 10px rgba(0,0,0,0.05);
            --card-hover-shadow: 0 10px 25px rgba(0,0,0,0.1);
            --card-hover-bg: #e8f4ff;
        }

        body {
            font-family: 'Inter', sans-serif; /* Clean, modern font */
           background-color: var(--bg-light);
            color: #333;
        }



        /* Header */

        /* Hero Section */
        .hero-section {
            background: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #eef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            text-align: center;
        }

        .lab-badge {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .section-header h3 {
            font-weight: 700;
            font-size: 1.25rem;
            margin: 0;
            padding-left: 10px;
            border-left: 4px solid var(--accent-orange);
        }

        /* Navbar */
        .navbar-custom {
            background: white;
            border-bottom: 1px solid #eef;
            padding: 15px 0;
            margin-bottom: 30px;
        }

        /* Search Section */
        .search-wrapper {
            position: relative;
            max-width: 60%; /* Wider search bar */
            margin: 0 auto 40px auto;
        }
        .search-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border-radius: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: all 0.3s;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.1);
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Tight Card */
        .tight-card {
            display: block;
            text-decoration: none;
            color: #333;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px; /* Reduced padding */
            height: 100%;
            transition: border-color 0.2s, transform 0.1s;
            background: #fff;
        }

        .tight-card:hover {
            border-color: #000;
            transform: translateY(-1px);
        }

        /* Card Typography */
        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.05rem; /* Compact title size */
            margin: 0;
            color: #111;
        }

        .card-desc {
            font-size: 0.9rem; /* Smaller body text */
            color: #666;
            margin: 0;
            line-height: 1.5;
        }

        /* Micro Badges */
        .badge-minimal {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bg-basic { background-color: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
        .bg-logic { background-color: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; }
        .bg-data  { background-color: #fefce8; color: #854d0e; border: 1px solid #fef9c3; }

        /* Footer */
        footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            font-size: 0.8rem;
            color: #999;
        }
    </style>
</head>
<body>
    <header class="hero-section">
        <div class="narrow-container">
            <div class="d-flex align-items-center justify-content-center gap-3">
                <img src="logo/GCILab_Logo.png" height="55" alt="GCI Logo">
                <div class="text-start">
                    <h2 class="fw-bold m-0 text-primary" style="font-size: 1.5rem;">GCI Hub</h2>
                    <span class="lab-badge">Game, Control & Intelligence Lab</span>
                </div>
            </div>
       <br> <h3 class="fw-bold m-0"><span class="text-danger">GCI Code Library</span></h3>
        </div>
    </header>



    <div class="container pb-5" style="max-width: 80%;"> <div class="search-wrapper">
            <i class="fa-solid fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search for examples (e.g., 'loops', 'input')...">
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-3">

            <div class="col">
                <a href="demo_view.php?id=1" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Output & Print</h5>
                        <span class="badge-minimal bg-basic">Basic</span>
                    </div>
                    <p class="card-desc">Display text and variables to the console using print().</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=2" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">User Input</h5>
                        <span class="badge-minimal bg-basic">Basic</span>
                    </div>
                    <p class="card-desc">Capture keyboard input and convert data types.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=3" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Variables</h5>
                        <span class="badge-minimal bg-basic">Basic</span>
                    </div>
                    <p class="card-desc">Working with Integers, Floats, Strings, and Booleans.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=4" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Arithmetic</h5>
                        <span class="badge-minimal bg-basic">Basic</span>
                    </div>
                    <p class="card-desc">Math operations: Addition, Subtraction, Division, Modulus.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=5" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Conditionals</h5>
                        <span class="badge-minimal bg-logic">Logic</span>
                    </div>
                    <p class="card-desc">Control flow using If, Elif, and Else statements.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=6" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Find Min/Max</h5>
                        <span class="badge-minimal bg-logic">Logic</span>
                    </div>
                    <p class="card-desc">Comparing numbers to find the largest or smallest value.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=7" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Calculate Average</h5>
                        <span class="badge-minimal bg-logic">Math</span>
                    </div>
                    <p class="card-desc">Summing a list of numbers and dividing by the count.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=8" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Loops</h5>
                        <span class="badge-minimal bg-logic">Control</span>
                    </div>
                    <p class="card-desc">Repeating tasks efficiently using For and While loops.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=9" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Lists & Arrays</h5>
                        <span class="badge-minimal bg-data">Data</span>
                    </div>
                    <p class="card-desc">Storing multiple items, accessing indices, and appending.</p>
                </a>
            </div>

            <div class="col">
                <a href="demo_view.php?id=10" class="tight-card">
                    <div class="card-header-flex">
                        <h5 class="card-title text-primary">Strings</h5>
                        <span class="badge-minimal bg-basic">Text</span>
                    </div>
                    <p class="card-desc">Slicing, concatenation, formatting, and casing.</p>
                </a>
            </div>

        </div>

        <footer class="text-center">
            chgiang © 2025
        </footer>
    </div>

</body>
</html>