<?php
// api/transactions.php - Transactions endpoint
// GET    /api/transactions              - List all transactions
// GET    /api/transactions/summary      - Summary by type
// POST   /api/transactions              - Log transaction
// DELETE /api/transactions/{id}         - Delete transaction

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../backend/Classes/TransactionManager.php';
require_once __DIR__ . '/../backend/Classes/Transaction.php';

$userId = requireApiAuth();
$pdo = getApiDb();

switch ($method) {
    case 'GET':
        if ($sub === 'summary') {
            $transaction = new Transaction($pdo);
            $summary = $transaction->getSummaryByType();
            jsonSuccess($summary);
        }

        $transaction = new Transaction($pdo);
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $transactions = $transaction->getAll($search, $type);
        jsonSuccess($transactions);
        break;

    case 'POST':
        $input = getInput();
        $itemId = (int) ($input['item_id'] ?? 0);
        $type = trim($input['type'] ?? '');
        $quantity = (int) ($input['quantity'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if ($itemId <= 0) {
            jsonError('item_id is required');
        }
        if (!in_array($type, ['Restock', 'Sold'], true)) {
            jsonError('type must be Restock or Sold');
        }
        if ($quantity <= 0) {
            jsonError('quantity must be a positive integer');
        }

        // Verify item exists
        $itemCheck = $pdo->prepare("SELECT id, quantity FROM items WHERE id = ?");
        $itemCheck->execute([$itemId]);
        $item = $itemCheck->fetch();
        if (!$item) {
            jsonError('Item not found', 404);
        }

        // Check sufficient stock for Sale and Waste
        if ($type !== 'Restock' && $item['quantity'] < $quantity) {
            jsonError("Insufficient stock. Available: {$item['quantity']}");
        }

        $transaction = new Transaction($pdo);
        $result = $transaction->addTransaction($itemId, $userId, $type, $quantity, $reason);

        if (!$result) {
            jsonError('Failed to log transaction', 500);
        }

        // Return the newly created transaction
        $transactions = $transaction->getAll('', $type);
        $latest = $transactions[0] ?? null;
        jsonResponse(['success' => true, 'data' => $latest, 'message' => 'Transaction logged'], 201);
        break;

    case 'DELETE':
        if ($id === null) {
            jsonError('Transaction ID is required');
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT item_id, quantity, transaction_type FROM transactions WHERE id = ?");
            $stmt->execute([$id]);
            $tx = $stmt->fetch();

            if (!$tx) {
                $pdo->rollBack();
                jsonError('Transaction not found', 404);
            }

            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt->execute([$id]);

            // Reverse the stock impact
            $typeMap = ['Restock' => '-', 'Sold' => '+'];
            $modifier = $typeMap[$tx['transaction_type']] ?? '+';
            $stmt = $pdo->prepare("UPDATE items SET quantity = quantity $modifier ? WHERE id = ?");
            $stmt->execute([$tx['quantity'], $tx['item_id']]);

            // Sync stock table
            $stmt = $pdo->prepare("UPDATE stock SET current_qty = (SELECT quantity FROM items WHERE id = ?) WHERE item_id = ?");
            $stmt->execute([$tx['item_id'], $tx['item_id']]);

            $pdo->commit();
            jsonSuccess(null, 'Transaction deleted');
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Transaction delete error: " . $e->getMessage());
            jsonError('Failed to delete transaction', 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
        break;
}
