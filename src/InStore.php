<?php

declare(strict_types=1);

namespace ReadLabelBalance;

use InvalidArgumentException;

/**
 * Biblioteca ReadLabelBalance
 * Port da lógica principal do ACBrInStore para PHP, com validação EAN-13.
 *
 * Padrão de codificação (FCodificacao): string com 13 caracteres onde:
 * - 'C' representa cada dígito do código do produto.
 * - 'P' representa cada dígito do peso (em gramas, dividir por 1000).
 * - 'T' representa cada dígito do total (em centavos, dividir por 100).
 * - Qualquer outro caractere é ignorado (prefixo/filler).
 * - O 13º dígito do EAN é o DV (dígito verificador) e deve ser válido.
 *
 * Exemplo prático de codificação sem total (usa callback de preço):
 *   "CCCCCCCPPPPPX"  -> 7 dígitos de código, 5 de peso, 1 filler para não ocupar o DV.
 */
class InStore
{
    private string $prefixo = '';
    private float $peso = 0.0;
    private float $total = 0.0;
    private string $codigo = '';
    private string $dv = '';
    private string $codificacao = '';
    /** @var callable|null */
    private $onGetPrecoUnitario = null;

    public function setCodificacao(string $value): void
    {
        $this->codificacao = $value;
        $pCodigo = strpos($this->codificacao, 'C');
        if ($pCodigo === false) {
            $this->prefixo = '';
        } else {
            $this->prefixo = substr($this->codificacao, 0, $pCodigo);
        }
    }

    public function setOnGetPrecoUnitario(?callable $callback): void
    {
        $this->onGetPrecoUnitario = $callback;
    }

    public function getPrefixo(): string
    {
        return $this->prefixo;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getPeso(): float
    {
        return $this->peso;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getDV(): string
    {
        return $this->dv;
    }

    public function zerarDados(): void
    {
        $this->codigo = '';
        $this->dv = '';
        $this->peso = 0.0;
        $this->total = 0.0;
    }

    /**
     * Desmembrar o código EAN-13 conforme a codificação informada.
     *
     * @param string $codigoEtiqueta 13 dígitos numéricos (EAN-13).
     */
    public function desmembrar(string $codigoEtiqueta): void
    {
        if (strlen($this->codificacao) < 13) {
            throw new InvalidArgumentException('Codificação inválida!');
        }
        if (!preg_match('/^\d{13}$/', $codigoEtiqueta)) {
            throw new InvalidArgumentException('Código EAN13 inválido!');
        }
        if (!$this->ean13Valido($codigoEtiqueta)) {
            throw new InvalidArgumentException('Dígito verificador do código EAN13 inválido!');
        }

        $this->zerarDados();
        $precoUnitario = 0.0;

        // Posições (0-based)
        $pCodigo = strpos($this->codificacao, 'C');
        $pPeso   = strpos($this->codificacao, 'P');
        $pTotal  = strpos($this->codificacao, 'T');

        // Tamanhos
        $tCodigo = substr_count($this->codificacao, 'C');
        $tPeso   = substr_count($this->codificacao, 'P');
        $tTotal  = substr_count($this->codificacao, 'T');

        // Código
        if ($pCodigo !== false && $tCodigo > 0) {
            $this->codigo = substr($codigoEtiqueta, (int)$pCodigo, $tCodigo);
        }

        // Peso (gramas -> kg)
        if ($pPeso !== false && $tPeso > 0) {
            $pesoStr = substr($codigoEtiqueta, (int)$pPeso, $tPeso);
            $pesoInt = (int)$pesoStr; // evita problemas de locale
            $this->peso = $pesoInt / 1000.0;
        }

        // Total (centavos -> reais)
        if ($pTotal !== false && $tTotal > 0) {
            $totalStr = substr($codigoEtiqueta, (int)$pTotal, $tTotal);
            $totalInt = (int)$totalStr;
            $this->total = $totalInt / 100.0;
        }

        // Callback opcional para obter preço unitário e derivar total/peso
        if (is_callable($this->onGetPrecoUnitario)) {
            $precoUnitario = (float)call_user_func($this->onGetPrecoUnitario, $this->codigo);

            if ($precoUnitario > 0 && $this->peso > 0) {
                $this->total = $precoUnitario * $this->peso;
            }
            if ($precoUnitario > 0 && $this->total > 0 && $this->peso == 0.0) {
                $this->peso = $this->total / $precoUnitario;
            }
        }

        // Dígito verificador
        $this->dv = substr($codigoEtiqueta, -1);
    }

    /**
     * Valida EAN-13.
     */
    private function ean13Valido(string $ean13): bool
    {
        if (!preg_match('/^\d{13}$/', $ean13)) {
            return false;
        }
        $base = substr($ean13, 0, 12);
        $dv   = (int)substr($ean13, -1);
        return $this->calcEan13DV($base) === $dv;
    }

    /**
     * Calcula DV do EAN-13 a partir dos 12 primeiros dígitos.
     */
    public function calcEan13DV(string $digits12): int
    {
        if (!preg_match('/^\d{12}$/', $digits12)) {
            throw new InvalidArgumentException('Base EAN-13 deve conter 12 dígitos.');
        }
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $d = (int)$digits12[$i];
            // Índices 0,2,4,... (posições 1,3,5...) peso 1; 1,3,5,... peso 3.
            $sum += ($i % 2 === 0) ? $d : 3 * $d;
        }
        $mod = $sum % 10;
        return ($mod === 0) ? 0 : (10 - $mod);
    }
}