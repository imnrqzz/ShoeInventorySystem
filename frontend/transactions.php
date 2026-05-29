<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transactions</title>
<link rel="stylesheet" href="transactions_style.css">

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

<!-- LOG TRANSACTION MODAL -->

<div class="modal" id="transactionModal">

    <div class="modal-content">

        <h2>Log Transaction</h2>

        <input type="date" id="transDate">

        <input type="text" id="transItem" placeholder="Item Name">

        <select id="transType">
            <option value="Sale">Sale</option>
            <option value="Restock">Restock</option>
            <option value="Waste">Waste</option>
        </select>

        <input type="number" id="transQty" placeholder="Quantity Change">

        <input type="text" id="transBy" placeholder="By">

        <input type="text" id="transReason" placeholder="Reason">

        <div class="modal-buttons">
            <button class="save-btn" id="saveTransaction">
                Save
            </button>

            <button class="cancel-btn" id="closeModal">
                Cancel
            </button>
        </div>

    </div>

</div>

<style>

/* =========================
   MODAL
========================= */

.modal{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:1000;
}

.modal-content{
    background:white;
    width:400px;
    padding:30px;
    border-radius:18px;
    display:flex;
    flex-direction:column;
    gap:15px;
}

.modal-content h2{
    margin-bottom:10px;
}

.modal-content input,
.modal-content select{
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

.modal-buttons{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:10px;
}

.save-btn{
    background:#1976d2;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

.cancel-btn{
    background:#e0e0e0;
    border:none;
    padding:12px 18px;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

</style>

<script>

const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const modal = document.getElementById("transactionModal");
const saveBtn = document.getElementById("saveTransaction");

const tableBody = document.querySelector(".transaction-table tbody");

let transactionCount = 0;

/* OPEN MODAL */

openModalBtn.addEventListener("click", () => {
    modal.style.display = "flex";
});

/* CLOSE MODAL */

closeModalBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

/* SAVE TRANSACTION */

saveBtn.addEventListener("click", () => {

    const date = document.getElementById("transDate").value;
    const item = document.getElementById("transItem").value;
    const type = document.getElementById("transType").value;
    const qty = document.getElementById("transQty").value;
    const by = document.getElementById("transBy").value;
    const reason = document.getElementById("transReason").value;

    if(!date || !item || !qty || !by || !reason){
        alert("Please fill all fields.");
        return;
    }

    transactionCount++;

    const row = document.createElement("tr");

    row.innerHTML = `
        <td>${transactionCount}</td>
        <td>${date}</td>
        <td>${item}</td>
        <td>
            <span class="type-badge">
                ${type}
            </span>
        </td>
        <td>${qty}</td>
        <td>${by}</td>
        <td>${reason}</td>
        <td>
            <button class="delete-btn">
                ✖
            </button>
        </td>
    `;

    /* DELETE ROW */

    row.querySelector(".delete-btn").addEventListener("click", () => {
        row.remove();
    });

    tableBody.appendChild(row);

    /* CLEAR FORM */

    document.getElementById("transDate").value = "";
    document.getElementById("transItem").value = "";
    document.getElementById("transQty").value = "";
    document.getElementById("transBy").value = "";
    document.getElementById("transReason").value = "";

    modal.style.display = "none";

});

/* CLOSE WHEN CLICKING OUTSIDE */

window.addEventListener("click", (e) => {
    if(e.target === modal){
        modal.style.display = "none";
    }
});

</script>

</body>
</html>