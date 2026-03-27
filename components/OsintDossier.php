<?php

namespace app\components;
use FPDF;

class OsintDossier extends FPDF
{
    public $reportTitle = 'DOSSIER REPORT - KEYWORD SEARCH';
    public $generatedAt = '';
    public $classification = 'INTERNAL';

    public function Header()
    {
        $this->SetY(10);

        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(13, 44, 84);
        $this->Cell(0, 8, $this->reportTitle, 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Classification: ' . $this->classification, 0, 1, 'L');

        $this->Ln(2);
        $this->SetDrawColor(220, 220, 220);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(6);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetDrawColor(220, 220, 220);
        $this->Line(10, $this->GetY(), 200, $this->GetY());

        $this->SetY(-12);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(95, 5, 'Generated: ' . $this->generatedAt, 0, 0, 'L');
        $this->Cell(95, 5, 'Page ' . $this->PageNo(), 0, 0, 'R');
    }

    public function SectionTitle($title)
    {
        $this->Ln(4);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(13, 44, 84);
        $this->Cell(0, 8, $title, 0, 1, 'L');

        $this->SetDrawColor(220, 220, 220);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(4);
    }

    public function LabelValueRow($label, $value)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(40, 40, 40);
        $this->Cell(45, 7, $label, 0, 0, 'L');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(60, 60, 60);
        $this->MultiCell(0, 7, $value, 0, 'L');
    }

    public function InfoBox($label, $value, $r = 245, $g = 245, $b = 245)
    {
        $this->SetFillColor($r, $g, $b);
        $this->SetDrawColor(225, 225, 225);

        $x = $this->GetX();
        $y = $this->GetY();

        $this->Rect($x, $y, 190, 18, 'DF');

        $this->SetXY($x + 3, $y + 3);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, $label, 0, 1);

        $this->SetX($x + 3);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(0, 7, $value, 0, 1);

        $this->SetY($y + 20);
    }

    public function Paragraph($text)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell(0, 6, $text);
        $this->Ln(1);
    }

    public function BulletItem($text)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(5, 6, chr(149), 0, 0);
        $this->MultiCell(0, 6, $text);
    }

    public function TableHeader($headers, $widths)
    {
        $this->SetFillColor(243, 245, 247);
        $this->SetDrawColor(220, 220, 220);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(40, 40, 40);

        foreach ($headers as $index => $header) {
            $this->Cell($widths[$index], 8, $header, 1, 0, 'L', true);
        }
        $this->Ln();
    }

    public function TableRow($row, $widths)
    {
        $startX = $this->GetX();
        $startY = $this->GetY();

        $maxHeight = 0;

        foreach ($row as $index => $text) {
            $nb = $this->NbLines($widths[$index], $text);
            $height = $nb * 6;
            if ($height > $maxHeight) {
                $maxHeight = $height;
            }
        }

        if ($this->GetY() + $maxHeight > 270) {
            $this->AddPage();
        }

        foreach ($row as $index => $text) {
            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $widths[$index], $maxHeight);
            $this->MultiCell($widths[$index], 6, $text, 0, 'L');

            $this->SetXY($x + $widths[$index], $y);
        }

        $this->SetXY($startX, $startY + $maxHeight);
    }

    public function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }

        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string) $txt);
        $nb = strlen($s);

        if ($nb > 0 && $s[$nb - 1] == "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }

            if ($c == ' ') {
                $sep = $i;
            }

            $l += $cw[$c] ?? 500;

            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }

                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }
}