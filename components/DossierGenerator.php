<?php

namespace app\components;

use Yii;
use app\models\Message;

class DossierGenerator extends \yii\base\Component
{
    public function generate($messageId)
    {
        $message = Message::findOne($messageId);
        if (!$message) return false;

        $analysis = $message->getAnalysisResults()->orderBy(['analyzed_at' => SORT_DESC])->one();

        if (!class_exists('FPDF')) {
            Yii::error("FPDF class not found. Please run 'composer require setasign/fpdf'");
            return false;
        }

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 25); // leave space for footer

        // Header with floating title & logo
        $this->addHeader($pdf, $message);

        // Classification banner
        $this->addClassificationBanner($pdf);

        // Section 1: Metadata
        $this->addMetadataSection($pdf, $message);

        // Section 2: Intercepted content
        $this->addContentSection($pdf, $message);

        // Section 3: Decrypted content (always shows a placeholder if empty)
        $this->addDecryptionSection($pdf, $message);

        // Section 4: AI intelligence
        $this->addIntelligenceSection($pdf, $analysis);


        $filename = 'Dossier_' . $message->id . '_' . time() . '.pdf';
        $path = Yii::getAlias('@webroot/uploads/dossiers/');
        if (!is_dir($path)) mkdir($path, 0777, true);

        $pdf->Output('F', $path . $filename);
        return $filename;
    }

private function addHeader(&$pdf, $message)
{
    // Report title on the left
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(10, 10);
    $pdf->Cell(0, 8, 'INTELLIGENCE DOSSIER', 0, 1, 'L');

    // Logo on the right
    $logoPath = Yii::getAlias('@webroot/img/logo.png');
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 150, 10, 40);
    }

    // Report ID and generation date below title
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(10, 25);
    $pdf->Cell(0, 5, 'Report ID: #' . str_pad($message->id, 6, '0', STR_PAD_LEFT), 0, 1, 'L');

    $pdf->SetXY(10, 30);
    $pdf->Cell(0, 5, 'Generated: ' . date('d M Y H:i'), 0, 1, 'L');

    // Powered by message just below the date
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(10, 35);
    $pdf->Cell(0, 4, 'Powered by ' . Yii::$app->name, 0, 1, 'L');

    $pdf->Ln(8); // spacing before next section
}


    private function addClassificationBanner(&$pdf)
    {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->Cell(0, 5, 'CLASSIFIED - FOR OFFICIAL USE ONLY', 0, 1, 'C', true);
        $pdf->Ln(3);
    }

    private function addMetadataSection(&$pdf, $message)
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 6, '1. METADATA & INTERCEPT INFORMATION', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Arial', '', 8);
        $metadata = [
            'Intercepted At' => $message->intercepted_at,
            'File Type' => ucfirst($message->file_type ?: 'Text'),
            'Status' => ucfirst($message->status),
            'Encryption Type' => $message->encryption_type ?: 'Unknown',
            'Device ID' => $message->device_id ?: 'N/A',
        ];

        foreach ($metadata as $label => $value) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(40, 5, $label . ':', 0, 0);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 5, $value, 0, 1);
        }
        $pdf->Ln(2);
    }

    private function addContentSection(&$pdf, $message)
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, '2. INTERCEPTED CONTENT', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Courier', '', 8);
        $content = !empty($message->encrypted_content) ? $message->encrypted_content : '[No intercepted content available]';
        $pdf->MultiCell(0, 4, $content, 1);
        $pdf->Ln(2);
    }

    private function addDecryptionSection(&$pdf, $message)
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, '3. DECRYPTED CONTENT', 0, 1);
        $pdf->Ln(1);

        $pdf->SetFont('Courier', '', 8);
        $content = !empty($message->decrypted_content) ? $message->decrypted_content : '[No decrypted content available]';
        $pdf->MultiCell(0, 4, $content, 1);
        $pdf->Ln(2);
    }

    private function addIntelligenceSection(&$pdf, $analysis)
    {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, '4. AI INTELLIGENCE ANALYSIS', 0, 1);
        $pdf->Ln(1);

        $sections = $analysis ? $this->parseInsights($analysis->ai_insights) : [];
        $pdf->SetFont('Arial', '', 8);

        if (empty($sections)) {
            $pdf->MultiCell(0, 4, '[No AI intelligence analysis available]', 1);
        } else {
            foreach ($sections as $section) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(0, 5, $section['title'], 0, 1);
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(0, 4, $section['content']);
                $pdf->Ln(1);
            }

            if ($analysis) {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(0, 4, 'Confidence Score: ' . $analysis->confidence_score . '%', 0, 1);
            }
        }

        $pdf->Ln(2);
    }

    private function parseInsights($insights)
    {
        $sections = [];
        if (preg_match_all('/^(LANGUAGE|CIPHER|TRANSLATION|ANALYSIS|TRANSCRIPTION|EXTRACTED TEXT):\s*(.+?)(?=^[A-Z]+:|$)/ms', $insights, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $sections[] = ['title' => $matches[1][$i], 'content' => trim($matches[2][$i])];
            }
        } else {
            $sections[] = ['title' => 'Analysis', 'content' => $insights];
        }
        return $sections;
    }

    private function addFooter(&$pdf)
    {
        $pdf->SetY(-20);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 3, 'Powered by ' . Yii::$app->name, 0, 1, 'L');
        $pdf->Cell(0, 3, 'Page ' . $pdf->PageNo() . ' | Generated: ' . date('d M Y H:i:s'), 0, 1, 'R');
    }
}
