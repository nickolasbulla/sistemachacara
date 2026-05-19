<?php
use Mpdf\Mpdf;

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
    $mpdf = new Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'A4',
        'default_font'  => 'dejavusans',
        'margin_left'   => 0,
        'margin_right'  => 0,
        'margin_top'    => 0,
        'margin_bottom' => 10,
        'margin_footer' => 3,
    ]);

    $mpdf->SetHTMLFooter('
        <table width="100%"><tr>
            <td style="text-align:center;font-size:8pt;color:#888;">
                Página {PAGENO} de {nb}
            </td>
        </tr></table>
    ');

    $mpdf->WriteHTML($html);
    $mpdf->Output($filename, 'D');
    exit;
}
