<?php

declare(strict_types=1);

?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h2>Email Verification Pending</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <p>Your account has been created, but you need to verify your email address.</p>
            <p>We've sent a verification email to your registered email address.
                Please check your inbox and click on the verification link.</p>
        </div>

        <p><strong>Didn't receive the email?</strong>
            Check your spam folder or <a href="/verify-email/resend">click here to resend the verification email</a>.
        </p>

        <div class="mt-4">
            <p>Once you verify your email, you'll be able to log in to your account.</p>
        </div>
    </div>
    <div class="card-footer">
        <a href="/login" class="btn btn-outline-primary">Back to Login</a>
    </div>
</div>