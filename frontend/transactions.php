<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transactions</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:#edf2fb;
}

/* =========================
   NAVBAR
========================= */

.navbar{
    width:90%;
    margin:20px auto;
    background:white;
    border-radius:22px;
    padding:18px 24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

/* LEFT */

.logo-section{
    display:flex;
    align-items:center;
    gap:15px;
}

.logo-box{
    width:44px;
    height:44px;
    border-radius:14px;
    background:linear-gradient(135deg,#5b5cf0,#6b7cff);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:22px;
}

.logo-title{
    font-size:24px;
    font-weight:700;
    color:#111827;
}

/* CENTER */

.nav-links{
    display:flex;
    align-items:center;
    gap:20px;
}

.nav-links a{
    text-decoration:none;
    color:#374151;
    font-size:15px;
    transition:0.3s;
    padding:10px 16px;
    border-radius:14px;
}

.nav-links a.active{
    background:#e8ecff;
    color:#4f46e5;
    font-weight:600;
}

.nav-links a:hover{
    background:#f3f4f6;
}

/* RIGHT */

.right-section{
    display:flex;
    align-items:center;
    gap:16px;
}

.user-circle{
    width:38px;
    height:38px;
    border-radius:50%;
    background:#e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    color:#4b5563;
}

.username{
    font-size:16px;
    font-weight:600;
    color:#111827;
}

.logout-btn{
    width:42px;
    height:42px;
    border:none;
    border-radius:50%;
    background:#ff4b4b;
    color:white;
    font-size:18px;
    cursor:pointer;
}

/* =========================
   PAGE CONTENT
========================= */

.container{
    width:90%;
    margin:30px auto;
}

.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.top-header h1{
    color:black;
    font-size:55px;
    font-weight:500;
}

.log-btn{
    background:white;
    color:black;
    text-decoration:none;
    padding:16px 26px;
    border-radius:40px;
    font-weight:600;
    font-size:18px;
}

/* =========================
   SUMMARY SECTION
========================= */

.summary-container{
    display:flex;
    gap:20px;
    margin-bottom:30px;
}

.summary-card{
    flex:1;
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.summary-header{
    background:rgba(86, 71, 221, 0.63);
    color:white;
    padding:16px;
    font-weight:bold;
    font-size:15px;
}

.summary-table{
    width:100%;
    border-collapse:collapse;
}

.summary-table th{
    background:#fafafa;
    padding:14px;
    text-align:left;
    font-size:13px;
}

.summary-table td{
    padding:14px;
    border-top:1px solid #eee;
    font-size:13px;
    color:#444;
}

.blue-text{
    color:#2196f3;
    font-weight:bold;
}

.green-text{
    color:#2e7d32;
    font-weight:bold;
}

.red-text{
    color:#d32f2f;
    font-weight:bold;
}

.orange-text{
    color:#ef6c00;
    font-weight:bold;
}

/* =========================
   FILTER
========================= */

.filter-box{
    background:white;
    padding:15px;
    border-radius:12px 12px 0 0;
    display:flex;
    gap:10px;
    align-items:center;
}

.filter-box input{
    flex:1;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
}

.filter-box select{
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
}

.filter-btn{
    background:#1976d2;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.reset-btn{
    background:#e0e0e0;
    border:none;
    padding:12px 18px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

/* =========================
   TABLE
========================= */

.transaction-table{
    width:100%;
    background:white;
    border-collapse:collapse;
    border-radius:0 0 15px 15px;
    overflow:hidden;
}

.transaction-table thead{
    background:rgba(86, 71, 221, 0.63);
    color:white;
}

.transaction-table th{
    padding:16px;
    text-align:left;
    font-size:14px;
}

.transaction-table td{
    padding:16px;
    border-bottom:1px solid #eee;
    font-size:14px;
}

.transaction-table tbody tr:hover{
    background:#f7f7f7;
}

.type-badge{
    background:#e3f2fd;
    color:#1976d2;
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.delete-btn{
    background:#e53935;
    color:white;
    border:none;
    width:35px;
    height:35px;
    border-radius:8px;
    cursor:pointer;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:900px){

    .summary-container{
        flex-direction:column;
    }

    .navbar{
        flex-direction:column;
        gap:20px;
        padding:25px;
    }

    .nav-links{
        flex-wrap:wrap;
        justify-content:center;
    }

    .top-header{
        flex-direction:column;
        gap:20px;
        align-items:flex-start;
    }

}

</style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo-section">

        <div class="logo-box">
            S
        </div>

        <div class="logo-title">
            Shoes Inventory System
        </div>

    </div>

    <div class="nav-links">

        <a href="#">Dashboard</a>
        <a href="#">Items</a>
        <a href="#">Suppliers</a>
        <a href="#">Stock</a>
        <a href="#" class="active">Transactions</a>
        <a href="#">Users</a>

    </div>

    <div class="right-section">

        <div class="user-circle">
            U
        </div>

        <div class="username">
            User1
        </div>

        <button class="logout-btn">
            ⏻
        </button>

    </div>

</div>

<!-- CONTENT -->

<div class="container">

    <div class="top-header">

        <h1>Transactions</h1>

        <button class="log-btn" id="openModal">
            + Log Transaction
        </button>

    </div>

    <!-- SUMMARY SECTION -->

    <div class="summary-container">

        <!-- LEFT CARD -->

        <div class="summary-card">

            <div class="summary-header">
                📊 Transaction Summary (by Type)
            </div>

            <table class="summary-table">

                <tr>
                    <th>Type</th>
                    <th>Count</th>
                    <th>Total Qty Moved</th>
                </tr>

                <!--ITEMS-->

            </table>

        </div>

        <!-- RIGHT CARD -->

        <div class="summary-card">

            <div class="summary-header">
                🏆 Top Consumed Items (JOIN Report)
            </div>

            <table class="summary-table">

                <tr>
                    <th>Item</th>
                    <th>Total Used</th>
                    <th>Transactions</th>
                </tr>

                <!--ITEMS-->

            </table>

        </div>

    </div>

    <!-- FILTER -->

    <div class="filter-box">

        <input type="text" placeholder="Search item...">

        <select>
            <option>All Types</option>
            <option>Sale</option>
            <option>Restock</option>
            <option>Waste</option>
        </select>

        <button class="filter-btn">
            Filter
        </button>

        <button class="reset-btn">
            Reset
        </button>

    </div>

    <!-- TABLE -->

    <table class="transaction-table">

        <thead>

            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Item</th>
                <th>Type</th>
                <th>Qty Change</th>
                <th>By</th>
                <th>Reason</th>
                <th>Del</th>
            </tr>

        </thead>

        <tbody>

            <!--ITEMS-->

        </tbody>

    </table>

</div>

</body>
</html>