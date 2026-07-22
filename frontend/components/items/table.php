            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shoe Model</th>
                                <th class="col-nowrap">Price</th>
                                <th>Supplier</th>
                                <th>Stock</th>
                                <th>Min</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $item):
                                $qty = (int)$item['quantity'];
                                $min = (int)$item['min_quantity'];
                            ?>
                            <tr>
                                <td><?= (int)$item['id'] ?></td>
                                <td><strong><?= safe($item['name']) ?></strong></td>
                                <td class="col-nowrap">$<?= number_format($item['price'], 2) ?></td>
                                <td><?= safe($item['supplier_name'] ?: '-') ?></td>
                                <td>
                                    <span class="badge <?= $qty <= $min ? 'badge-danger' : 'badge-success' ?>"><?= $qty ?></span>
                                </td>
                                <td><?= $min ?></td>
                                <td class="actions-cell">
                                    <a href="item.php?edit_id=<?= (int)$item['id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDelete('Are you sure you want to delete this item? This action cannot be undone.', 'item.php?delete_id=<?= (int)$item['id'] ?>')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>