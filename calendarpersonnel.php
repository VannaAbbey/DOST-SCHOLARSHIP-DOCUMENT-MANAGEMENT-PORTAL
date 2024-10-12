<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            min-height: 100vh; /* Use min-height instead of height */
            display: flex;
            flex-direction: column;
        }
        .container {
            width: 90%;
            padding: 20px;
            background-color: grdient;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .navbar {
            overflow: hidden;
            background-color: #a2cfee;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            height: 50px;
        }
        .navbar a {
            display: block;
            color: black;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 15px;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            font-weight: 200;
        }
        .navbar a:hover {
            background-color: #f2f2f2;
            color: black;
            height:30px;
        }
        .navbar .title {
            flex-grow: 1;
            text-align: center;
            font-size: 20px;
            color: #2d4a7b;
        }
        .calendar-container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 60px; /* to avoid overlap with the navbar */
        }
        .calendar {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar th, .calendar td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .calendar th {
            background-color: blanchedalmond;
            color: black;
        }
        .calendar td {
            height: 50px;
        }
        .calendar td:hover {
            background-color: aliceblue;
        }
        .month-title {
            font-size: 1.5em;
            margin-bottom: 10px;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;

        }
        .calendar-wrapper {
            flex: 1; /* Allow calendar-wrapper to grow and fill the available space */
            overflow-y: auto; /* Enable vertical scrolling */
            padding-top: 20px;
            color:black;
        }
        .dark-mode-button {
            position: fixed;
            right: 20px;
            bottom: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }
        
        .dark-mode-button:hover {
            opacity: 0.8;
        }

        body.dark-mode {
            background-color: #333;
            color: #fff; /* Text color in dark mode */
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="profilepersonnel.php">Profile</a>
        <a href="personnel_dashboard.php">Dashboard</a>
        <a href="#">FAQ</a>
        <a href="logout.php" onclick="exitPage();">Exit</a>
    </div>

    <div class="calendar-wrapper">
        <?php
        function generate_calendar($year) {
            // Array of month names
            $months = array(
                1 => 'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            );

            // Generate the calendar for each month
            for ($month = 1; $month <= 12; $month++) {
                echo '<div class="calendar-container">';
                echo '<div class="month-title">' . $months[$month] . ' ' . $year . '</div>';
                echo '<table class="calendar">';
                echo '<tr>';
                echo '<th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>';
                echo '</tr><tr>';

                // Get the first day of the month
                $first_day = mktime(0, 0, 0, $month, 1, $year);
                // Get the number of days in the month
                $days_in_month = date('t', $first_day);
                // Get the day of the week for the first day of the month
                $day_of_week = date('w', $first_day);

                // Print empty cells for days before the first day of the month
                for ($blank = 0; $blank < $day_of_week; $blank++) {
                    echo '<td></td>';
                }

                // Print the days of the month
                for ($day = 1; $day <= $days_in_month; $day++) {
                    if (($blank + $day - 1) % 7 == 0) {
                        echo '</tr><tr>';
                    }
                    echo '<td>' . $day . '</td>';
                }

                // Print empty cells for the remaining days of the week
                while (($blank + $day - 1) % 7 != 0) {
                    echo '<td></td>';
                    $day++;
                }

                echo '</tr>';
                echo '</table>';
                echo '</div>';
            }
        }

        // Generate the calendar for the year 2024
        generate_calendar(2024);
        ?>
    </div>
       <!-- Dark mode toggle button -->
    <button class="dark-mode-button" onclick="toggleDarkMode()">Dark Mode</button>

<script>
    function toggleDarkMode() {
        const body = document.body;
        body.classList.toggle('dark-mode');
    }
</script>
</body>
</html>
