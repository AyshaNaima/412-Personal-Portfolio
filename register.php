<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$page_title = "Register | Resume Builder";
$body_class = "auth";
require 'header.php';
?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join the modern resume builder</p>
    </div>
    <div class="auth-body">
        <form id="registerForm">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" class="modern-input" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" class="modern-input" required>
            </div>
            <div id="register_feedback" class="msg"></div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="auth-link">
            Already have an account? <a href="index.php">Log in</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').onsubmit = async (e) => {
    e.preventDefault();
    const form = e.target;
    const feedback = document.getElementById('register_feedback');
    const data = new FormData(form);

    feedback.textContent = 'Creating account...';
    feedback.className = 'msg';

    try {
        const res = await fetch('register_process.php', { method: 'POST', body: data });
        const json = await res.json();

        if (json.success) {
            feedback.textContent = 'Registered! Redirecting...';
            feedback.className = 'msg success';
            setTimeout(() => window.location.href = 'index.php', 1200);
        } else {
            feedback.textContent = json.message || 'Registration failed.';
            feedback.className = 'msg error';
        }
    } catch (err) {
        feedback.textContent = 'Network error.';
        feedback.className = 'msg error';
    }
};
</script>

<?php require 'footer.php'; ?>