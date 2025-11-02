<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$step = $data['step'] ?? 1;

$stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ?");
$stmt->execute([$user_id]);
$resume = $stmt->fetch();

$personal = json_decode($resume['personal'] ?? '{}', true) ?: [];
$education = json_decode($resume['education'] ?? '[]', true) ?: [];
$experience = json_decode($resume['experience'] ?? '[]', true) ?: [];
$skills = $resume['skills'] ?? '';
$photo = $resume['photo'] ?? '';

switch ($step) {
    case 1:
        $personal = array_merge($personal, $data['data']);
        if (!empty($data['photo'])) $photo = $data['photo'];
        break;
    case 2:
        $education = $data['data'];
        break;
    case 3:
        $experience = $data['data']['experience'];
        $skills = $data['data']['skills'];
        break;
}

$stmt = $pdo->prepare("
    INSERT INTO resumes (user_id, step, personal, education, experience, skills, photo) 
    VALUES (?, ?, ?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE 
    step = VALUES(step), personal = VALUES(personal), education = VALUES(education),
    experience = VALUES(experience), skills = VALUES(skills), photo = VALUES(photo)
");

$result = $stmt->execute([
    $user_id, $step, json_encode($personal), json_encode($education),
    json_encode($experience), $skills, $photo
]);

echo json_encode([
    'success' => $result,
    'message' => 'Saved!',
    'nextStep' => $step < 3 ? $step + 1 : 3
]);
?>