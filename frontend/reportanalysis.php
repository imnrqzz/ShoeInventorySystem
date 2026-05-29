<?php

$reports = [];

/* =========================================
   FILTER FUNCTIONALITY
========================================= */

$search = isset($_GET['search']) ? $_GET['search'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';

$filteredReports = [];

foreach ($reports as $row) {

    $matchSearch = true;
    $matchType = true;

    if ($search != '') {
        $matchSearch = stripos($row['item'], $search) !== false;
    }

    if ($typeFilter != '' && $typeFilter != 'All Types') {
        $matchType = $row['type'] == $typeFilter;
    }

    if ($matchSearch && $matchType) {
        $filteredReports[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reports Analysis</title>

<!-- LINK EXTERNAL CSS -->
<link rel="stylesheet" href="reportanalysis.css">

<script>
function downloadPDF(){

    let content = document.querySelector(".report-box").innerHTML;

    let printWindow = window.open('', '', 'width=1200,height=800');

    printWindow.document.write(`
    <html>
    <head>
        <title>Reports Analysis PDF</title>
        <style>
            body{font-family:Arial;}
            .report-header{background:#5647dd;color:#fff;padding:15px;}
            table{width:100%;border-collapse:collapse;}
            th,td{padding:12px;border-bottom:1px solid #ddd;}
            thead{background:#5647dd;color:#fff;}
            .sale{color:#2196f3;font-weight:bold;}
            .restock{color:#2e7d32;font-weight:bold;}
            .adjustment{color:#ef6c00;font-weight:bold;}
            .green{color:#2e7d32;font-weight:bold;}
            .red{color:#c62828;font-weight:bold;}
        </style>
    </head>
    <body>
        <h1>Reports Analysis</h1>
        ${content}
    </body>
    </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>

</head>

<body>

<!-- NAVBAR -->
<div class="navbar">

    <div class="logo-section">
        <div class="logo-box">S</div>
        <div class="logo-title">Shoes Inventory System</div>
    </div>

    <div class="nav-links">
        <a href="#">Dashboard</a>
        <a href="#">Items</a>
        <a href="#">Suppliers</a>
        <a href="#">Stock</a>
        <a href="#">Transactions</a>
        <a href="#" class="active">Reports</a>
        <a href="#">Users</a>
    </div>

    <div class="right-section">
        <div class="user-circle">U</div>
        <div class="username">User1</div>
        <button class="logout-btn">⏻</button>
    </div>

</div>

<!-- PAGE -->
<div class="container">

    <div class="top-header">
        <h1>Reports Analysis</h1>
        <a href="#" class="print-btn" onclick="downloadPDF()">Print Receipt</a>
    </div>

    <div class="report-box">

        <div class="report-header">
            🔍 Report 1: Transaction Search & Filter
        </div>

        <div class="filter-section">

            <form method="GET">

                <div class="filter-grid">

                    <div class="input-group">
                        <label>Item Name</label>
                        <input type="text" name="search" value="<?php echo $search; ?>">
                    </div>

                    <div class="input-group">
                        <label>Transaction Type</label>
                        <select name="type">
                            <option>All Types</option>
                            <option value="Sale" <?php if($typeFilter=='Sale') echo 'selected'; ?>>Sale</option>
                            <option value="Restock" <?php if($typeFilter=='Restock') echo 'selected'; ?>>Restock</option>
                            <option value="Adjustment" <?php if($typeFilter=='Adjustment') echo 'selected'; ?>>Adjustment</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Date From</label>
                        <input type="date">
                    </div>

                    <div class="input-group">
                        <label>Date To</label>
                        <input type="date">
                    </div>

                    <button class="filter-btn">Filter</button>

                    <a href="reports.php">
                        <button type="button" class="reset-btn">Reset</button>
                    </a>

                </div>

            </form>

            <div class="results">
                Found <?php echo count($filteredReports); ?> transaction(s)
            </div>

        </div>

        <table class="transaction-table">

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Supplier</th>
                    <th>Type</th>
                    <th>Qty Change</th>
                    <th>By</th>
                    <th>Notes</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($filteredReports as $row): ?>
                <tr>
                    <td><?php echo $row['date']; ?></td>
                    <td><strong><?php echo $row['item']; ?></strong></td>
                    <td><?php echo $row['supplier']; ?></td>
                    <td><span class="<?php echo strtolower($row['type']); ?>"><?php echo $row['type']; ?></span></td>
                    <td class="<?php echo strpos($row['qty'], '+') !== false ? 'green' : 'red'; ?>">
                        <?php echo $row['qty']; ?>
                    </td>
                    <td><?php echo $row['by']; ?></td>
                    <td><?php echo $row['notes']; ?></td>
                </tr>
                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>