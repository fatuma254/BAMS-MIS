<?php 
ob_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
class Database {
    private $host = 'localhost';  
    private $username = 'root';  
    private $password = '';  
    private $database = 'bams_beauty_mis';  
    private $conn;

    public function __construct() {
        // Create connection
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        // Close the connection
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// New class for PDF generation, outside of AdminDashboard but in the same file
class AdminDashboardPDF extends TCPDF {
    public function Header() {
        // Logo
        $this->Image('../assets/images/logo.png', 15, 10, 30);
        
        // Company Name
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(87, 131, 157); // Blue shade for professional look
        $this->Cell(0, 15, 'BAMS Beauty MIS', 0, false, 'C', 0);
        
        // Subtitle
        $this->Ln(12);
        $this->SetFont('helvetica', 'I', 12);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Admin Dashboard Report', 0, true, 'C', 0);
        
        // Decorative line
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(87, 131, 157);
        $this->Line(15, 35, 195, 35);
        $this->Ln(20);
    }

    public function Footer() {
        $this->SetY(-25);
        // Business Info
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'BAMS Beauty Management Information System', 0, false, 'C', 0);
        $this->Ln(5);
        // Page number
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0);
    }
}

class AdminDashboard {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Key Performance Indicators
    public function getKPIs() {
        $kpis = [
            'total_talents' => $this->getTotalTalents(),
            'total_bookings' => $this->getTotalBookings(),
            'total_revenue' => $this->getTotalRevenue(),
            'total_tasks' => $this->getTotalTasks(),
            'pending_bookings' => $this->getPendingBookings(),
            'monthly_revenue' => $this->getMonthlyRevenue()
        ];
        return $kpis;
    }

    private function getTotalTalents() {
        $query = "SELECT COUNT(*) as count FROM talents";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    private function getTotalBookings() {
        $query = "SELECT COUNT(*) as count FROM bookings";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    private function getTotalRevenue() {
        $query = "SELECT SUM(amount) as total FROM transactions WHERE type = 'revenue'";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['total'];
    }

    private function getTotalTasks() {
        $query = "SELECT COUNT(*) as count FROM tasks WHERE status != 'Completed'";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    private function getPendingBookings() {
        $query = "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'";
        $result = $this->conn->query($query);
        return $result->fetch_assoc()['count'];
    }

    private function getMonthlyRevenue() {
        $query = "SELECT 
            MONTH(transaction_date) as month, 
            SUM(amount) as monthly_total 
            FROM transactions 
            WHERE type = 'revenue' 
            AND YEAR(transaction_date) = YEAR(CURRENT_DATE)
            GROUP BY MONTH(transaction_date)
            ORDER BY month";
        $result = $this->conn->query($query);
        
        $monthly_data = array_fill(1, 12, 0);
        while ($row = $result->fetch_assoc()) {
            $monthly_data[$row['month']] = $row['monthly_total'];
        }
        return $monthly_data;
    }

    public function generatePDFReport() {
        require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

        // Create new PDF instance
        $pdf = new AdminDashboardPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('BAMS Beauty MIS');
        $pdf->SetAuthor('BAMS Beauty');
        $pdf->SetTitle('Admin Dashboard Report');

        // Set margins
        $pdf->SetMargins(15, 45, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(20);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);

        // Add a page
        $pdf->AddPage();

        // Fetch KPIs and data
        $kpis = $this->getKPIs();
        $recent_activities = $this->getRecentActivities();
        $top_talents = $this->getTopTalents();

        // Report period
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(87, 131, 157);
        $pdf->Cell(0, 10, 'Dashboard Report: ' . date('F d, Y'), 0, 1, 'L');
        $pdf->Ln(5);

        // KPIs Section
        $pdf->SetFillColor(240, 248, 255); // Light blue background
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Key Performance Indicators', 0, 1, 'L');

        // Summary boxes
        $pdf->SetFont('helvetica', '', 12);
        $box_width = 58;
        $box_height = 25;
        $pdf->SetFillColor(87, 131, 157);
        $pdf->SetTextColor(255, 255, 255);

        // Total Talents Box
        $pdf->Cell($box_width, $box_height, 'Total Talents', 1, 0, 'C', true);
        $pdf->Cell(2); // spacing

        // Total Bookings Box
        $pdf->SetFillColor(46, 204, 113);
        $pdf->Cell($box_width, $box_height, 'Total Bookings', 1, 0, 'C', true);
        $pdf->Cell(2); // spacing

        // Total Revenue Box
        $pdf->SetFillColor(231, 76, 60);
        $pdf->Cell($box_width, $box_height, 'Total Revenue', 1, 1, 'C', true);

        // Amount row
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell($box_width, 10, $kpis['total_talents'], 1, 0, 'C', true);
        $pdf->Cell(2);
        $pdf->Cell($box_width, 10, $kpis['total_bookings'], 1, 0, 'C', true);
        $pdf->Cell(2);
        $pdf->Cell($box_width, 10, 'Ksh ' . number_format($kpis['total_revenue']?? 0, 2), 1, 1, 'C', true);
        $pdf->Ln(10);

        // Top Talents Section
        $pdf->SetTextColor(87, 131, 157);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Top Performing Talents', 0, 1, 'L');

        // Table header
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(87, 131, 157);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 7, 'Name', 1, 0, 'L', true);
        $pdf->Cell(50, 7, 'Specialty', 1, 0, 'L', true);
        $pdf->Cell(50, 7, 'Total Bookings', 1, 1, 'R', true);

        // Table contents
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', '', 10);
        $fill = false;
        foreach ($top_talents as $talent) {
            $fill = !$fill;
            $pdf->SetFillColor(240, 248, 255);
            
            $pdf->Cell(80, 7, htmlspecialchars($talent['name']), 1, 0, 'L', $fill);
            $pdf->Cell(50, 7, htmlspecialchars($talent['specialty']), 1, 0, 'L', $fill);
            $pdf->Cell(50, 7, $talent['total_bookings'], 1, 1, 'R', $fill);
        }

        // Recent Activities Section
        $pdf->Ln(10);
        $pdf->SetTextColor(87, 131, 157);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Recent Activities', 0, 1, 'L');

        // Table header
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(87, 131, 157);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, 'Type', 1, 0, 'L', true);
        $pdf->Cell(80, 7, 'Name', 1, 0, 'L', true);
        $pdf->Cell(50, 7, 'Date', 1, 1, 'R', true);

        // Table contents
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', '', 10);
        $fill = false;
        foreach ($recent_activities as $activity) {
            $fill = !$fill;
            $pdf->SetFillColor(240, 248, 255);
            
            $pdf->Cell(50, 7, ucfirst(htmlspecialchars($activity['type'])), 1, 0, 'L', $fill);
            $pdf->Cell(80, 7, htmlspecialchars($activity['name']), 1, 0, 'L', $fill);
            $pdf->Cell(50, 7, htmlspecialchars($activity['date']), 1, 1, 'R', $fill);
        }

        // Output the PDF
        $pdf->Output('admin_dashboard_report_' . date('Y-m-d') . '.pdf', 'D');
    }

    // Recent Activities
    public function getRecentActivities() {
        $query = "
            (SELECT 'booking' as type, client_name as name, booking_date as date FROM bookings ORDER BY booking_date DESC LIMIT 5)
            UNION
            (SELECT 'task' as type, title as name, start_date as date FROM tasks ORDER BY start_date DESC LIMIT 5)
            ORDER BY date DESC
            LIMIT 10
        ";
        $result = $this->conn->query($query);
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        return $activities;
    }

    // Top Performing Talents
    public function getTopTalents() {
        $query = "
            SELECT t.name, t.specialty, 
            COUNT(b.id) as total_bookings,
            SUM(t.hourly_rate) as total_earnings
            FROM talents t
            LEFT JOIN bookings b ON t.id = b.talent_id
            GROUP BY t.id
            ORDER BY total_bookings DESC
            LIMIT 5
        ";
        $result = $this->conn->query($query);
        $talents = [];
        while ($row = $result->fetch_assoc()) {
            $talents[] = $row;
        }
        return $talents;
    }
}

// Add export functionality
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
    $dashboard = new AdminDashboard();
    $dashboard->generatePDFReport();
    exit();
}

$dashboard = new AdminDashboard();
$kpis = $dashboard->getKPIs();
$recent_activities = $dashboard->getRecentActivities();
$top_talents = $dashboard->getTopTalents();

require_once '../profile_check.php';
include('../includes/topbar.php');
include('../includes/sidebar.php');
?>
    <div class="content-wrapper">

    <meta charset="UTF-8">
    <title>BAMS Beauty MIS - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <!-- Main Content -->
            <main class="col-md-12 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                      <div class="btn-group me-2">
                          <a href="?export=pdf" class="btn btn-danger btn-outline-secondary">Export to PDF</a>
                      </div>
                  </div>
                </div>

                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Talents</h5>
                                <p class="card-text display-7"><?php echo $kpis['total_talents']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <p class="card-text display-7">Ksh <?php echo number_format($kpis['total_revenue']?? 0, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Pending Bookings</h5>
                                <p class="card-text display-7"><?php echo $kpis['pending_bookings']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Open Tasks</h5>
                                <p class="card-text display-7"><?php echo $kpis['total_tasks']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Detailed Analytics -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Monthly Revenue</div>
                            <div class="card-body">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Top Performing Talents</div>
                            <ul class="list-group list-group-flush">
                                <?php foreach($top_talents as $talent): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($talent['name']); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo $talent['total_bookings']; ?> Bookings</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">Recent Activities</div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_activities as $activity): ?>
                                    <tr>
                                        <td><?php echo ucfirst(htmlspecialchars($activity['type'])); ?></td>
                                        <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['date']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Monthly Revenue Chart
        const ctx = document.getElementById('revenueChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Revenue (Ksh)',
                    data: <?php echo json_encode($kpis['monthly_revenue']); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
          </div>
          <?php  include('../includes/footer.php'); ?>