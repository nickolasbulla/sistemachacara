<?php
function buildHtml(string $titulo, string $periodo, string $corpo): string {
    global $logo_src, $pdf_css, $gerado_em, $gerado_por;

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <style><?= $pdf_css ?></style>
    </head>
    <body>

        <div class="header">
            <table>
                <tr>
                    <td style="width:120px;vertical-align:middle;padding-top:0;padding-bottom:0;padding-right:14px;">
                        <img src="<?= $logo_src ?>" style="width:120px;display:block;">
                    </td>
                    <td style="vertical-align:middle;">
                        <div class="sistema">Chácara Portal</div>
                        <div class="titulo-rel"><?= htmlspecialchars($titulo) ?></div>
                        <div class="periodo">Período: <?= htmlspecialchars($periodo) ?></div>
                    </td>
                    <td class="meta">
                        <strong>Gerado em:</strong> <?= $gerado_em ?><br>
                        <strong>Gerado por:</strong> <?= $gerado_por ?>
                    </td>
                </tr>
            </table>
        </div>

        <?= $corpo ?>

    </body>
    </html>
    <?php
    return ob_get_clean();
}
