<?php
session_start();
require 'db.php';
require 'tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ?");
$stmt->execute([$user_id]);
$resume = $stmt->fetch();

if (!$resume) {
    die('No resume data found.');
}

$p = json_decode($resume['personal'], true) ?: [];
$e = json_decode($resume['education'], true) ?: [];
$x = json_decode($resume['experience'], true) ?: [];
$skills = $resume['skills'] ?? '';
$photo = $p['photo'] ?? '';

// Create PDF
class ModernResumePDF extends TCPDF {
    public function Header() {
        // Remove default header
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Generated on ' . date('M d, Y'), 0, false, 'C');
    }
}

$pdf = new ModernResumePDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Resume Builder');
$pdf->SetAuthor($p['name'] ?? 'User');
$pdf->SetTitle(($p['name'] ?? 'Resume') . ' - Resume');
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// === START HTML ===
$html = '
<style>
    body { font-family: helvetica, sans-serif; color: #1f2937; line-height: 1.6; }
    .container { width: 100%; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h1 { font-size: 28px; font-weight: bold; color: #6366f1; margin: 8px 0; }
    .header p { font-size: 11px; color: #4b5563; margin: 4px 0; }
    .photo { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 4px solid #6366f1; margin-bottom: 12px; }
    .section { margin-top: 20px; }
    .section h2 { font-size: 16px; font-weight: bold; color: #1f2937; border-bottom: 2px solid #6366f1; padding-bottom: 4px; margin-bottom: 10px; display: flex; align-items: center; }
    .section h2 svg { width: 18px; height: 18px; margin-right: 8px; fill: #6366f1; }
    .item { margin-bottom: 12px; }
    .item h3 { font-size: 13px; font-weight: bold; color: #1f2937; margin: 0; }
    .item p { font-size: 11px; color: #4b5563; margin: 2px 0; }
    .skills { column-count: 2; column-gap: 20px; }
    .skill-item { font-size: 11px; background: #eef2ff; color: #6366f1; padding: 4px 8px; border-radius: 6px; display: inline-block; margin: 3px 0; }
    .two-col { display: flex; gap: 20px; }
    .col-left { flex: 2; }
    .col-right { flex: 1; background: #f8fafc; padding: 15px; border-radius: 10px; }
</style>

<div class="container">

    <!-- Header -->
    <div class="header">';
    
    if (!empty($photo)) {
        $html .= '<img src="' . $photo . '" class="photo">';
    }
    
    $html .= '
        <h1>' . htmlspecialchars($p['name'] ?? 'Your Name') . '</h1>
        <p>
            <strong>Email:</strong> ' . htmlspecialchars($p['email'] ?? '') . ' | 
            <strong>Phone:</strong> ' . htmlspecialchars($p['phone'] ?? '') . '<br>
            ' . htmlspecialchars($p['address'] ?? '') . '
        </p>
    </div>

    <div class="two-col">
        <div class="col-left">

            <!-- Education -->
            <div class="section">
                <h2>
                    <svg viewBox="0 0 24 24"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
                    Education
                </h2>';
                
                foreach ($e as $edu) {
                    $html .= '
                    <div class="item">
                        <h3>' . htmlspecialchars($edu['degree'] ?? '') . '</h3>
                        <p><strong>' . htmlspecialchars($edu['institution'] ?? '') . '</strong> • ' . htmlspecialchars($edu['year'] ?? '') . '</p>
                    </div>';
                }
                
            $html .= '</div>

            <!-- Experience -->
            <div class="section">
                <h2>
                    <svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                    Experience
                </h2>';
                
                foreach ($x as $exp) {
                    $html .= '
                    <div class="item">
                        <h3>' . htmlspecialchars($exp['title'] ?? '') . ' at ' . htmlspecialchars($exp['company'] ?? '') . '</h3>
                        <p><strong>' . htmlspecialchars($exp['years'] ?? '') . '</strong></p>
                        <p>' . nl2br(htmlspecialchars($exp['desc'] ?? '')) . '</p>
                    </div>';
                }
                
            $html .= '</div>

        </div>

        <div class="col-right">
            <!-- Skills -->
            <div class="section">
                <h2>
                    <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13h-2v2H9v2h2v2h2v-2h2v-2h-2V7z"/></svg>
                    Skills
                </h2>
                <div class="skills">';
                
                $skillList = array_filter(array_map('trim', explode(',', $skills)));
                foreach ($skillList as $skill) {
                    $html .= '<span class="skill-item">' . htmlspecialchars($skill) . '</span>';
                }
                
                $html .= '
                </div>
            </div>
        </div>
    </div>

</div>';

// === END HTML ===
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('resume.pdf', 'D');
?>