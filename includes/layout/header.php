<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- para cada página definir seu título -->
    <title><?php echo $titulo_pagina; ?></title>

    <!-- se $css_pagina for um único arquivo (string), ele inclui só um <link>
    se for um array, ele faz um foreach e inclui vários <link> um pra cada CSS
    maneira que encontrei de colocar mais de um css na mesma pagina usando header-->
    <?php
    if (!empty($css_pagina)) {
        if (is_array($css_pagina)) {
            foreach ($css_pagina as $css) {
                echo '<link rel="stylesheet" href="' . $css . '">';
            }
        } else {
            echo '<link rel="stylesheet" href="' . $css_pagina . '">';
        }
    }
    ?>

</head>
<body>
        
