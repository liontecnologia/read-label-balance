<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ReadLabelBalance\InStore;

// Exemplo de codificação: 7 dígitos do código, 5 do peso (gramas), 1 filler
$instore = new InStore();
$instore->setCodificacao('CCCCCCCPPPPPX');

// Callback para obter preço unitário (R$/kg)
$instore->setOnGetPrecoUnitario(function (string $codigo): float {
    $tabela = [
        '1234567' => 19.90,
        '7654321' => 29.50,
    ];
    return $tabela[$codigo] ?? 0.0;
});

// Montando EAN-13 de exemplo
$codigo = '1234567';
$pesoG  = '00150'; // 150g
$base12 = $codigo . $pesoG;

// Utiliza método público para calcular DV
$dv = $instore->calcEan13DV($base12);
$ean13 = $base12 . $dv;

$instore->desmembrar($ean13);

printf("Código: %s\n", $instore->getCodigo());
printf("Peso (kg): %.3f\n", $instore->getPeso());
printf("Total (R$): %.2f\n", $instore->getTotal());
printf("DV: %s\n", $instore->getDV());