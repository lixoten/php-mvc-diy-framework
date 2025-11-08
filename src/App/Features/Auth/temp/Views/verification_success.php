<div class="card">
    <div class="card-header bg-success text-white">
        <h2>Email Verified Successfully</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-success">
            <p>Congratulations, <?= htmlspecialchars($username) ?>! Your email address has been verified successfully.</p>
        </div>

        <div class="mt-4">
            <p>Your account is now active. You can log in and start using all features of the application.</p>
        </div>
    </div>
    <div class="card-footer">
        <a href="/login" class="btn btn-primary">Log In Now</a>
    </div>
</div>