<div class="card">
    <div class="card-header bg-danger text-white">
        <h2>Verification Failed</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <p><strong>We couldn't verify your email address.</strong></p>
            <p>Reason: <?= htmlspecialchars($error ?? 'Unknown error') ?></p>
        </div>

        <div class="mt-4">
            <h4>What went wrong?</h4>
            <ul>
                <li>The verification link may have expired</li>
                <li>The token may be invalid or already used</li>
                <li>Your account may have been deleted</li>
            </ul>

            <h4 class="mt-3">What can you do?</h4>
            <ul>
                <li><a href="/registration">Register again</a> with your email address</li>
                <li>Contact support if you believe this is an error</li>
            </ul>
        </div>
    </div>
    <div class="card-footer">
        <a href="/login" class="btn btn-outline-primary">Back to Login</a>
        <a href="/verify-email/resend" class="btn btn-primary">Request New Verification</a>
    </div>
</div>