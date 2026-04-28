<?php
use Dompdf\Dompdf;
use Dompdf\Options;

$logo_path = __DIR__ . '/../../assets/images/logo2.png';
$logo_b64  = base64_encode(file_get_contents($logo_path));
$logo_src  = "data:image/png;base64,{$logo_b64}";

$css_path = __DIR__ . '/../../assets/css/pdf.css';
$pdf_css  = file_exists($css_path) ? file_get_contents($css_path) : '';

date_default_timezone_set('America/Sao_Paulo');
$gerado_em  = date('d/m/Y \à\s H:i');
$gerado_por = htmlspecialchars($_SESSION['usuario_nome']);

function fmt(float|int|string $v): string {
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function renderPdf(string $html, string $filename): void {
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', false);
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('fontCache', __DIR__ . '/../../cache/fonts');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font   = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
    $canvas->page_text(252, 820, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 8, [0.5, 0.5, 0.5]);

    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}
