
<!-- filepath: d:\xampp\htdocs\mvclixo\src\App\Features\Testy\Views\dbtest.php -->
<div class="container">
    <h1><?= $title ?></h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <h3>Error:</h3>
            <p><?= htmlspecialchars($error) ?></p>
            <?php if (isset($trace)): ?>
                <pre><?= htmlspecialchars($trace) ?></pre>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h2>Connection Test</h2>
            </div>
            <div class="card-body">
                <p><?= htmlspecialchars($connectionStatus) ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2>Transaction Test</h2>
            </div>
            <div class="card-body">
                <p><?= htmlspecialchars($transactionResult) ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2>Insert Test</h2>
            </div>
            <div class="card-body">
                <p>Last inserted ID: <?= htmlspecialchars($insertId) ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2>Recent Records</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['id']) ?></td>
                            <td><?= htmlspecialchars($record['name']) ?></td>
                            <td><?= htmlspecialchars($record['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/testy" class="btn btn-primary">Back to Testy</a>
    </div>
</div>