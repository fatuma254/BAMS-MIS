<?php
require_once '../config/database.php';
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get transactions - removed category join
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE transaction_date BETWEEN ? AND ?
    ORDER BY transaction_date DESC
");
$stmt->execute([$start_date, $end_date]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_revenue = 0;
$total_expenses = 0;
$type_totals = [
    'revenue' => 0,
    'expense' => 0
];

foreach($transactions as $transaction) {
    if($transaction['type'] == 'revenue') {
        $total_revenue += $transaction['amount'];
        $type_totals['revenue'] += $transaction['amount'];
    } else {
        $total_expenses += $transaction['amount'];
        $type_totals['expense'] += $transaction['amount'];
    }
}
$net_income = $total_revenue - $total_expenses;

class BAMSPDF extends TCPDF {
    public function Header() {
        // Logo
        $this->Image('../assets/images/logo.png', 15, 10, 30);
        
        // Company Name
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(157, 87, 131); // Purple shade for beauty business
        $this->Cell(0, 15, 'BAMS Beauty', 0, false, 'C', 0);
        
        // Subtitle
        $this->Ln(12);
        $this->SetFont('helvetica', 'I', 12);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Financial Management Report', 0, true, 'C', 0);
        
        // Decorative line
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(157, 87, 131);
        $this->Line(15, 35, 195, 35);
        $this->Ln(20);
    }

    public function Footer() {
        $this->SetY(-25);
        // Business Info
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'BAMS Beauty | Email: info@bamsbeauty.com | Tel: +254 787 345 765', 0, false, 'C', 0);
        $this->Ln(5);
        // Page number
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0);
    }
}

// Create new PDF instance
$pdf = new BAMSPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('BAMS Beauty System');
$pdf->SetAuthor('BAMS Beauty');
$pdf->SetTitle('BAMS Beauty Financial Report');

// Set margins
$pdf->SetMargins(15, 45, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(20);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Add a page
$pdf->AddPage();

// Report period
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(157, 87, 131);
$pdf->Cell(0, 10, 'Financial Report: ' . date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)), 0, 1, 'L');
$pdf->Ln(5);

// Financial Overview Section
$pdf->SetFillColor(250, 244, 248); // Light purple background
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Financial Overview', 0, 1, 'L');

// Summary boxes
$pdf->SetFont('helvetica', '', 12);
$box_width = 58;
$box_height = 25;
$pdf->SetFillColor(157, 87, 131);
$pdf->SetTextColor(255, 255, 255);

// Revenue Box
$pdf->Cell($box_width, $box_height, 'Total Revenue', 1, 0, 'C', true);
$pdf->Cell(2); // spacing

// Expenses Box
$pdf->SetFillColor(231, 76, 60);
$pdf->Cell($box_width, $box_height, 'Total Expenses', 1, 0, 'C', true);
$pdf->Cell(2); // spacing

// Net Income Box
$fill_color = $net_income >= 0 ? array(46, 204, 113) : array(231, 76, 60);
$pdf->SetFillColor($fill_color[0], $fill_color[1], $fill_color[2]);
$pdf->Cell($box_width, $box_height, 'Net Income', 1, 1, 'C', true);

// Amount row
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell($box_width, 10, 'Ksh ' . number_format($total_revenue, 2), 1, 0, 'C', true);
$pdf->Cell(2);
$pdf->Cell($box_width, 10, 'Ksh ' . number_format($total_expenses, 2), 1, 0, 'C', true);
$pdf->Cell(2);
$pdf->Cell($box_width, 10, 'Ksh ' . number_format($net_income, 2), 1, 1, 'C', true);
$pdf->Ln(10);

// Transaction Summary
$pdf->SetTextColor(157, 87, 131);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Transaction Summary', 0, 1, 'L');
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('helvetica', '', 10);

// Revenue vs Expense Chart
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Revenue vs Expense Breakdown', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Revenue total
$pdf->Cell(80, 7, 'Total Revenue', 1, 0, 'L');
$pdf->Cell(50, 7, 'Ksh ' . number_format($type_totals['revenue'], 2), 1, 0, 'R');
$pdf->Cell(30, 7, '100%', 1, 1, 'R');

// Expense total
$pdf->Cell(80, 7, 'Total Expenses', 1, 0, 'L');
$pdf->Cell(50, 7, 'Ksh ' . number_format($type_totals['expense'], 2), 1, 0, 'R');
$pdf->Cell(30, 7, '100%', 1, 1, 'R');
$pdf->Ln(10);

// Transactions Detail Table
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(157, 87, 131);
$pdf->Cell(0, 10, 'Detailed Transaction History', 0, 1, 'L');

// Table header
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(157, 87, 131);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 7, 'Date', 1, 0, 'L', true);
$pdf->Cell(30, 7, 'Type', 1, 0, 'L', true);
$pdf->Cell(85, 7, 'Description', 1, 0, 'L', true);
$pdf->Cell(35, 7, 'Amount', 1, 1, 'R', true);

// Table contents
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('helvetica', '', 10);
$fill = false;
foreach($transactions as $transaction) {
    if($pdf->getY() > $pdf->getPageHeight() - 30) {
        $pdf->AddPage();
        // Reprint header
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(157, 87, 131);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 7, 'Date', 1, 0, 'L', true);
        $pdf->Cell(30, 7, 'Type', 1, 0, 'L', true);
        $pdf->Cell(85, 7, 'Description', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'Amount', 1, 1, 'R', true);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', '', 10);
    }
    
    $fill = !$fill;
    $pdf->SetFillColor(250, 244, 248);
    
    $pdf->Cell(30, 7, date('Y-m-d', strtotime($transaction['transaction_date'])), 1, 0, 'L', $fill);
    $pdf->Cell(30, 7, ucfirst($transaction['type']), 1, 0, 'L', $fill);
    
    $desc = $transaction['description'];
    if(strlen($desc) > 60) {
        $desc = substr($desc, 0, 57) . '...';
    }
    $pdf->Cell(85, 7, $desc, 1, 0, 'L', $fill);
    $pdf->Cell(35, 7, 'Ksh ' . number_format($transaction['amount'], 2), 1, 1, 'R', $fill);
}

// Output the PDF
$pdf->Output('BAMS_Beauty_Financial_Report_' . date('Y-m-d') . '.pdf', 'D');