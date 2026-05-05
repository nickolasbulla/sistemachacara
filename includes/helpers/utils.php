<?php

function parse_data(string $d): ?string {
    $dt = DateTime::createFromFormat('d/m/Y', trim($d));
    return $dt ? $dt->format('Y-m-d') : null;
}

function fmt_data(string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '';
}
