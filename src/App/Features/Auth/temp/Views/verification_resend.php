<div class="card">
    <div class="card-header bg-primary text-white">
        <h2>Resend Verification Email</h2>
    </div>
    <div class="card-body">
        <p>Enter the email address you used during registration, and we'll send you a new verification link.</p>

        <form method="post" action="/verify-email/resend">
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Send Verification Email</button>
        </form>
    </div>
    <div class="card-footer">
        <a href="/login" class="btn btn-outline-secondary">Back to Login</a>
    </div>
</div>