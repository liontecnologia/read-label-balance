<?php

declare(strict_types=1);

namespace ReadLabelBalance\Tests;

use PHPUnit\Framework\TestCase;
use ReadLabelBalance\InStore;

final class InStoreTest extends TestCase
{
    private function dv(string $base12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $d = (int)$base12[$i];
            $sum += ($i % 2 === 0) ? $d : 3 * $d;
        }
        $mod = $sum % 10;
        return ($mod === 0) ? 0 : 10 - $mod;
    }

    public function testDesmembrarComPesoECallback(): void
    {
        $instore = new InStore();
        $instore->setCodificacao('CCCCCCCPPPPPX'); // 7 código, 5 peso, 1 filler
        $instore->setOnGetPrecoUnitario(function (string $codigo): float {
            return $codigo === '1234567' ? 19.90 : 0.0; // R$/kg
        });

        $codigo = '1234567'; // 7 dígitos
        $pesoG  = '00150';   // 150g => 0.150kg
        $base12 = $codigo . $pesoG; // 12 dígitos
        $dv     = $this->dv($base12);
        $ean13  = $base12 . $dv;

        $instore->desmembrar($ean13);

        $this->assertSame('', $instore->getPrefixo());
        $this->assertSame('1234567', $instore->getCodigo());
        $this->assertSame('' . $dv, $instore->getDV());
        $this->assertEquals(0.150, $instore->getPeso());
        $this->assertEquals(19.90 * 0.150, $instore->getTotal());
    }

    public function testDesmembrarComTotalSemCallback(): void
    {
        $instore = new InStore();
        $instore->setCodificacao('CCCCCCCTTTTXX'); // 7 código, 4 total, 2 filler (13 chars)
        // sem callback

        $codigo = '7654321'; // 7 dígitos
        $totalC = '1234';    // 1234 centavos => R$ 12,34 (4 dígitos)
        $filler = '0';       // 1 dígito de preenchimento
        $base12 = $codigo . $totalC . $filler; // 12 dígitos
        $dv     = $this->dv($base12);
        $ean13  = $base12 . $dv;

        $instore->desmembrar($ean13);

        $this->assertSame('7654321', $instore->getCodigo());
        $this->assertEquals(12.34, $instore->getTotal());
        $this->assertEquals(0.0, $instore->getPeso());
    }

    public function testEANInvalidoDisparaExcecao(): void
    {
        $instore = new InStore();
        $instore->setCodificacao('CCCCCCCPPPPPX');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dígito verificador do código EAN13 inválido!');
        $instore->desmembrar('1234567001509'); // dv errado (exemplo)
    }
}