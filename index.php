<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$page_title = "Login | Resume Builder";
$body_class = "auth";
require 'header.php';
?>

<div class="auth-card">
    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Log in to continue building your resume</p>
    </div>
    <div class="auth-body">
        <form id="loginForm">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" class="modern-input" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" class="modern-input" required>
            </div>
            <div id="login_feedback" class="msg"></div>
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
        <p class="auth-link">
            No account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').onsubmit = async (e) => {
    e.preventDefault();
    const form = e.target;
    const feedback = document.getElementById('login_feedback');
    const data = new FormData(form);

    feedback.textContent = 'Logging in...';
    feedback.className = 'msg';

    try {
        const res = await fetch('login.php', { method: 'POST', body: data });
        const json = await res.json();

        if (json.success) {
            feedback.textContent = 'Success! Redirecting...';
            feedback.className = 'msg success';
            setTimeout(() => window.location.href = 'dashboard.php', 800);
        } else {
            feedback.textContent = json.message || 'Login failed.';
            feedback.className = 'msg error';
        }
    } catch (err) {
        feedback.textContent = 'Network error.';
        feedback.className = 'msg error';
    }
};
</script>

<?php require 'footer.php'; ?>