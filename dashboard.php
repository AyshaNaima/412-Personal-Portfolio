<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ?");
$stmt->execute([$user_id]);
$resume = $stmt->fetch();

$personal   = json_decode($resume['personal'] ?? '[]', true) ?: [];
if (!empty($resume['photo'])) $personal['photo'] = $resume['photo'];  // Load saved photo

$education  = json_decode($resume['education'] ?? '[]', true) ?: [];
$experience = json_decode($resume['experience'] ?? '[]', true) ?: [];
$skills     = $resume['skills'] ?? '';
$currentStep = $resume['step'] ?? 1;

$page_title = "Resume Builder";
$body_class = "dashboard";
require 'header.php';
?>

<div class="card">
    <div class="card-header">
        <h1>Build Your Resume</h1>
        <p>Hi <?= htmlspecialchars($_SESSION['user_email']) ?></p>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: <?= ($currentStep / 3) * 100 ?>%"></div>
        </div>
        <div class="steps">
            <span class="step active" data-step="1">Personal</span>
            <span class="step" data-step="2">Education</span>
            <span class="step" data-step="3">Experience</span>
        </div>
    </div>

    <div class="card-body">
        <form id="multiStepForm">
            <!-- ==== STEP 1 : PERSONAL ==== -->
            <div class="step-content" id="step-1" style="display: <?= $currentStep == 1 ? 'block' : 'none' ?>">
                <h3 class="step-title">Personal Details</h3>

                <!-- Photo Uploader -->
                <div class="photo-upload-container">
                    <div class="photo-preview" id="photoPreview">
                        <?php if (!empty($personal['photo'] ?? '')): ?>
                            <img src="<?= htmlspecialchars($personal['photo']) ?>" alt="Profile">
                        <?php else: ?>
                            <div class="placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p>Click or drag photo</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <input type="file" id="photoInput" accept="image/*" style="display:none">
                    <button type="button" class="btn btn-secondary" id="photoBtn" onclick="document.getElementById('photoInput').click()">
                        <?= !empty($personal['photo']) ? 'Change Photo' : 'Upload Photo' ?>
                    </button>

                    <p class="photo-hint">Max 2MB, JPG/PNG</p>
                    <div id="photoError" class="msg error" style="display:none; margin-top:.5rem"></div>
                </div>

                <!-- Personal Fields -->
                <div class="input-group"><label>Full Name</label><input type="text" name="name" value="<?= $personal['name'] ?? '' ?>" class="modern-input"></div>
                <div class="input-group"><label>Email</label><input type="email" name="email" value="<?= $personal['email'] ?? '' ?>" class="modern-input"></div>
                <div class="input-group"><label>Phone</label><input type="text" name="phone" value="<?= $personal['phone'] ?? '' ?>" class="modern-input"></div>
                <div class="input-group"><label>Address</label><input type="text" name="address" value="<?= $personal['address'] ?? '' ?>" class="modern-input"></div>

                <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next</button>
            </div>

            <!-- ==== STEP 2 : EDUCATION ==== -->
            <div class="step-content" id="step-2" style="display: <?= $currentStep == 2 ? 'block' : 'none' ?>">
                <h3 class="step-title">Education</h3>
                <div id="educationList">
                    <?php foreach ($education as $edu): ?>
                    <div class="edu-item">
                        <div class="input-row">
                            <input type="text" placeholder="Degree" value="<?= $edu['degree'] ?? '' ?>" class="modern-input">
                            <input type="text" placeholder="Institution" value="<?= $edu['institution'] ?? '' ?>" class="modern-input">
                            <input type="text" placeholder="Year" value="<?= $edu['year'] ?? '' ?>" class="modern-input">
                        </div>
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-edu-btn" onclick="addEducation()">+ Add Education</button>

                <div class="btn-group" style="margin-top:1rem">
                    <button type="button" class="btn" onclick="goToStep(1)">Back</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next</button>
                </div>
            </div>

            <!-- ==== STEP 3 : EXPERIENCE + SKILLS ==== -->
            <div class="step-content" id="step-3" style="display: <?= $currentStep == 3 ? 'block' : 'none' ?>">
                <h3 class="step-title">Work Experience</h3>
                <div id="experienceList">
                    <?php foreach ($experience as $exp): ?>
                    <div class="exp-item">
                        <div class="input-row">
                            <input type="text" placeholder="Job Title" value="<?= $exp['title'] ?? '' ?>" class="modern-input">
                            <input type="text" placeholder="Company" value="<?= $exp['company'] ?? '' ?>" class="modern-input">
                            <input type="text" placeholder="Years" value="<?= $exp['years'] ?? '' ?>" class="modern-input">
                        </div>
                        <textarea placeholder="Description" class="modern-input"><?= $exp['desc'] ?? '' ?></textarea>
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-exp-btn" onclick="addExperience()">+ Add Job</button>

                <h3 class="step-title" style="margin-top:1.5rem">Skills</h3>
                <textarea name="skills" placeholder="e.g. PHP, MySQL, JavaScript" class="modern-input skills-input" rows="4"><?= htmlspecialchars($skills) ?></textarea>

                <div class="btn-group" style="margin-top:1rem">
                    <button type="button" class="btn" onclick="goToStep(2)">Back</button>
                    <button type="button" class="btn btn-primary" onclick="saveStep(3)">Save & Finish</button>
                    <button type="button" class="btn btn-secondary" onclick="generatePDF()">Download PDF</button>
                </div>
            </div>

            <div id="feedback" class="msg"></div>
        </form>

        <p style="text-align:center; margin-top:1.5rem;">
            <a href="logout.php">Logout</a>
        </p>
    </div>
</div>

<script>
let currentStep = <?= $currentStep ?>;

/* ---- PHOTO UPLOADER ---- */
const photoInput   = document.getElementById('photoInput');
const photoPreview = document.getElementById('photoPreview');
const photoError   = document.getElementById('photoError');
const photoBtn     = document.getElementById('photoBtn');

photoInput.addEventListener('change', handlePhoto);
photoPreview.addEventListener('click', () => photoInput.click());
photoPreview.addEventListener('dragover', e => { e.preventDefault(); photoPreview.classList.add('drag-over'); });
photoPreview.addEventListener('dragleave', () => photoPreview.classList.remove('drag-over'));
photoPreview.addEventListener('drop', e => {
    e.preventDefault();
    photoPreview.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) {
        photoInput.files = e.dataTransfer.files;
        handlePhoto();
    }
});

function handlePhoto() {
    const file = photoInput.files[0];
    photoError.style.display = 'none';
    photoError.textContent = '';

    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showPhotoError('Please upload an image');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        showPhotoError('Image must be under 2MB');
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        photoPreview.innerHTML = '';
        photoPreview.appendChild(img);
        photoBtn.textContent = 'Change Photo';
    };
    reader.readAsDataURL(file);
}

function showPhotoError(msg) {
    photoError.textContent = msg;
    photoError.style.display = 'block';
}

/* ---- RESTORE SAVED PHOTO ON PAGE LOAD ---- */
document.addEventListener('DOMContentLoaded', () => {
    if (photoPreview.querySelector('img')) {
        photoBtn.textContent = 'Change Photo';
    }
});

/* ---- Progress ---- */
function updateProgress() {
    document.getElementById('progressFill').style.width = (currentStep / 3) * 100 + '%';
    document.querySelectorAll('.step').forEach((s, i) => s.classList.toggle('active', i + 1 <= currentStep));
}

/* ---- Navigation ---- */
function goToStep(step) {
    if (step < 1 || step > 3) return;
    saveStep(currentStep).finally(() => {
        currentStep = step;
        document.querySelectorAll('.step-content').forEach(c => c.style.display = 'none');
        document.getElementById('step-' + step).style.display = 'block';
        updateProgress();
    });
}

/* ---- SAVE STEP ---- */
async function saveStep(step) {
    const feedback = document.getElementById('feedback');
    feedback.textContent = 'Saving…'; feedback.className = 'msg';

    let payload = { step };

    if (step === 1) {
        const form = document.getElementById('multiStepForm');
        payload.data = Object.fromEntries(new FormData(form));
        const photoBase64 = photoPreview.querySelector('img')?.src;
        if (photoBase64) payload.photo = photoBase64;
    }
    if (step === 2) {
        const items = [];
        document.querySelectorAll('#educationList .edu-item').forEach(el => {
            const inputs = el.querySelectorAll('input');
            items.push({ degree: inputs[0].value.trim(), institution: inputs[1].value.trim(), year: inputs[2].value.trim() });
        });
        payload.data = items;
    }
    if (step === 3) {
        const exp = [];
        document.querySelectorAll('#experienceList .exp-item').forEach(el => {
            const inputs = el.querySelectorAll('input');
            const ta = el.querySelector('textarea');
            exp.push({ title: inputs[0].value.trim(), company: inputs[1].value.trim(), years: inputs[2].value.trim(), desc: ta.value.trim() });
        });
        payload.data = { experience: exp, skills: document.querySelector('textarea[name="skills"]').value.trim() };
    }

    try {
        const res = await fetch('save_resume.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        feedback.textContent = json.message;
        feedback.className = 'msg ' + (json.success ? 'success' : 'error');
        if (json.nextStep) currentStep = json.nextStep;
        updateProgress();
    } catch (e) {
        feedback.textContent = 'Network error'; feedback.className = 'msg error';
    }
}

/* ---- ADD ROWS ---- */
function addEducation() {
    const div = document.createElement('div'); div.className = 'edu-item';
    div.innerHTML = `<div class="input-row">
        <input type="text" placeholder="Degree" class="modern-input">
        <input type="text" placeholder="Institution" class="modern-input">
        <input type="text" placeholder="Year" class="modern-input">
    </div><button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>`;
    document.getElementById('educationList').appendChild(div);
}

function addExperience() {
    const div = document.createElement('div'); div.className = 'exp-item';
    div.innerHTML = `<div class="input-row">
        <input type="text" placeholder="Job Title" class="modern-input">
        <input type="text" placeholder="Company" class="modern-input">
        <input type="text" placeholder="Years" class="modern-input">
    </div><textarea placeholder="Description" class="modern-input"></textarea>
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>`;
    document.getElementById('experienceList').appendChild(div);
}

/* ---- PDF ---- */
function generatePDF() {
    saveStep(3).finally(() => window.location.href = 'generate_pdf.php');
}

/* ---- Init ---- */
updateProgress();
</script>

<?php require 'footer.php'; ?>