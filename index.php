<?php
include 'db.php';

$error_msg = "";
// --- 1. DELETE EXPENSE LOGIC ---
if (isset($_GET['delete_expense_id'])) {
    $delete_id = $_GET['delete_expense_id'];
    $conn->query("DELETE FROM expenses WHERE id = $delete_id");
    header("Location: index.php");
    exit();
}

// --- 2. DELETE FRIEND LOGIC ---
if (isset($_GET['delete_friend_id'])) {
    $del_friend_id = $_GET['delete_friend_id'];
    $conn->query("DELETE FROM friends WHERE id = $del_friend_id");
    header("Location: index.php");
    exit();
}

// --- 3. FRIEND ADD LOGIC (WITH DUPLICATE ENTRY CHECK) ---
if (isset($_POST['add_friend_btn'])) {
    $name = trim(htmlspecialchars($_POST['friend_name']));

    if (!empty($name)) {
        // SQL query to check if name already exists
        $check_duplicate = $conn->query("SELECT * FROM friends WHERE name = '$name'");

        if ($check_duplicate->num_rows > 0) {
            $error_msg = "<strong>$name</strong> is already in the group. Duplicate entries not allowed!";
        } else {

            $conn->query("INSERT INTO friends (name) VALUES ('$name')");
            header("Location: index.php");
            exit();
        }
    }
}

// --- 4. EXPENSE ADD LOGIC ---
if (isset($_POST['add_expense_btn'])) {
    $title = trim(htmlspecialchars($_POST['expense_title']));
    $amount = $_POST['expense_amount'];
    $paid_by = $_POST['paid_by'];

    if (!empty($title) && $amount > 0 && !empty($paid_by)) {
        $conn->query("INSERT INTO expenses (title, amount, paid_by_id) VALUES ('$title', '$amount', '$paid_by')");
        header("Location: index.php");
        exit();
    }
}

$friends = $conn->query("SELECT * FROM friends");
$expenses = $conn->query("SELECT expenses.*, friends.name AS payer FROM expenses JOIN friends ON expenses.paid_by_id = friends.id");

// --- 5. SPLITWISE MATHEMATICAL ENGINE ---
$total_expense = 0;
$friend_count = $friends->num_rows;

$paid_amounts = [];
$friend_names = [];

$friends->data_seek(0);
while ($f = $friends->fetch_assoc()) {
    $paid_amounts[$f['id']] = 0;
    $friend_names[$f['id']] = $f['name'];
}

$expenses->data_seek(0);
while ($e = $expenses->fetch_assoc()) {
    $total_expense += $e['amount'];
    $paid_amounts[$e['paid_by_id']] += $e['amount'];
}

$per_head_share = ($friend_count > 0) ? ($total_expense / $friend_count) : 0;

$debtors = [];
$creditors = [];

foreach ($paid_amounts as $f_id => $amount_paid) {
    $net_balance = $amount_paid - $per_head_share;
    if ($net_balance < -0.01) {
        $debtors[$f_id] = abs($net_balance);
    } elseif ($net_balance > 0.01) {
        $creditors[$f_id] = $net_balance;
    }
}

$settlements = [];
foreach ($debtors as $d_id => $d_amount) {
    foreach ($creditors as $c_id => $c_amount) {
        if ($d_amount <= 0) break;
        if ($c_amount <= 0) continue;

        $amount_to_transfer = min($d_amount, $c_amount);
        $settlements[] = [
            'from' => $friend_names[$d_id],
            'to' => $friend_names[$c_id],
            'amount' => number_format($amount_to_transfer, 2)
        ];
        $d_amount -= $amount_to_transfer;
        $creditors[$c_id] -= $amount_to_transfer;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense Splitter 💰</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light py-5">

    <div class="container">
        <h2 class="text-center fw-bold mb-5 text-dark"><i class="fa-solid fa-wallet text-success me-2"></i>Smart Expense Splitter</h2>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4 text-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-dark text-white rounded-3 p-3">
                    <small class="text-muted text-uppercase fw-bold">Total Group Expense</small>
                    <h3 class="fw-bold mt-1 text-warning">₹<?php echo number_format($total_expense, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                    <small class="text-secondary text-uppercase fw-bold">Total Members</small>
                    <h3 class="fw-bold mt-1 text-primary"><?php echo $friend_count; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white rounded-3 p-3">
                    <small class="text-secondary text-uppercase fw-bold">Per Head Share</small>
                    <h3 class="fw-bold mt-1 text-success">₹<?php echo number_format($per_head_share, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 bg-white">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-handshake text-info me-2"></i>Final Settlement (Who owes whom)</h5>
            <div class="row">
                <?php if (!empty($settlements)): ?>
                    <?php foreach ($settlements as $set): ?>
                        <div class="col-md-6 mb-2">
                            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border-start border-4 border-info">
                                <span><strong class="text-danger"><?php echo $set['from']; ?></strong> owes <strong class="text-success"><?php echo $set['to']; ?></strong></span>
                                <span class="badge bg-info text-dark fw-bold fs-6">₹<?php echo $set['amount']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-2">
                        <i class="fa-solid fa-square-check text-success me-1"></i> Everyone is settled up! No transactions needed.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-user-plus me-2"></i>Add Group Member</h5>
                    <form action="index.php" method="POST">
                        <div class="mb-3">
                            <input type="text" name="friend_name" class="form-control" placeholder="Friend's Name" required>
                        </div>
                        <button type="submit" name="add_friend_btn" class="btn btn-primary w-100 fw-bold">Add Friend</button>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-users me-2"></i>Group Members</h5>
                    <ul class="list-group list-group-flush">
                        <?php if ($friends->num_rows > 0): ?>
                            <?php
                            $friends->data_seek(0);
                            while ($f = $friends->fetch_assoc()):
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i class="fa-regular fa-user me-2 text-primary"></i><?php echo $f['name']; ?></span>
                                    <a href="index.php?delete_friend_id=<?php echo $f['id']; ?>"
                                        class="text-danger text-decoration-none small fw-bold px-2"
                                        onclick="return confirm('Remove <?php echo $f['name']; ?> from group?');">
                                        <i class="fa-solid fa-user-minus"></i>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No friends added yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-receipt me-2"></i>Add New Expense</h5>
                    <form action="index.php" method="POST">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="expense_title" class="form-control" placeholder="What for? (e.g., Dinner)" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" name="expense_amount" class="form-control" placeholder="Amount (₹)" required>
                            </div>
                            <div class="col-md-3">
                                <select name="paid_by" class="form-select" required>
                                    <option value="">Who Paid?</option>
                                    <?php
                                    $friends->data_seek(0);
                                    while ($f = $friends->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $f['id']; ?>"><?php echo $f['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="add_expense_btn" class="btn btn-success w-100 fw-bold">Add</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-history me-2"></i>Expense History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>What For</th>
                                    <th>Amount</th>
                                    <th>Paid By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($expenses->num_rows > 0): ?>
                                    <?php
                                    $expenses->data_seek(0);
                                    while ($e = $expenses->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo $e['title']; ?></td>
                                            <td class="text-success fw-bold">₹<?php echo $e['amount']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $e['payer']; ?></span></td>
                                            <td class="text-center">
                                                <a href="index.php?delete_expense_id=<?php echo $e['id']; ?>"
                                                    class="btn btn-outline-danger btn-sm rounded-3"
                                                    onclick="return confirm('Are you sure you want to settle/delete this expense?');">
                                                    <i class="fa-solid fa-trash-can"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No expenses logged yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>