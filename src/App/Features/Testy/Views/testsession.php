<!-- filepath: src/App/Features/Testy/Views/session.php -->
<h1>Session Middleware Test</h1>

<div class="card mb-4">
    <div class="card-header">
        <h2>Visit Counter</h2>
    </div>
    <div class="card-body">
        <h3>You have visited this page <?= htmlspecialchars($visits) ?> times</h3>
        <a href="/testy/resetsession" class="btn btn-warning">Reset Counter</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h2>All Session Data</h2>
    </div>
    <div class="card-body">
        <pre><?= htmlspecialchars(print_r($sessionData, true)) ?></pre>
    </div>
</div>