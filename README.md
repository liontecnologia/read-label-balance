```
   ____  _____    _    ____   _          _     _                 
  |  _ \| ____|  / \  |  _ \ | |    __ _| |__ | | ___  __ _ ___  
  | |_) |  _|   / _ \ | |_) || |   / _` | '_ \| |/ _ \/ _` / __| 
  |  _ <| |___ / ___ \|  _ < | |__| (_| | |_) | |  __/ (_| \__ \ 
  |_| \_\_____|/_/   \_\_| \_\|_____\__,_|_.__/|_|\___|\__, |___/ 
                                                     |___/        
```

# read-label-balance

Biblioteca PHP open source para leitura de etiquetas de balança (EAN‑13) utilizando um padrão de codificação simples (C/P/T) para extrair código do produto, peso e total. Inspirada na lógica do componente **ACBrInStore**.


## Instalação

Após publicar no Packagist:

```
composer require readlabelbalance/read-label-balance:^1.0
```

Clonando localmente (desenvolvimento):

```
git clone https://github.com/seu-usuario/read-label-balance.git
cd read-label-balance
composer install
composer test
```

## Códigos de balanças do mercado

- 2CCCC0TTTTTTDV
- 2CCCC00PPPPPDV
- 2CCCC00QQQQQDV
- 2CCCCCTTTTTTDV
- 2CCCCC0PPPPPDV
- 2CCCCC0QQQQQDV
- 2CCCCCCPPPPPDV
- 2CCCCCCQQQQQDV
- 2CCCCCCTTTTTDV

## Exemplo de Uso

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use ReadLabelBalance\InStore;

// Codificação: 7 dígitos do código (C), 5 do peso em gramas (P), 1 filler (X)

$instore = new InStore();
$instore->setCodificacao('2CCCC0TTTTTTDV');
$instore->desmembrar($ean13);

echo 'Código: ' . $instore->getCodigo() . PHP_EOL;
echo 'Peso (kg): ' . number_format($instore->getPeso(), 3) . PHP_EOL;
echo 'Total (R$): ' . number_format($instore->getTotal(), 2) . PHP_EOL;
echo 'DV: ' . $instore->getDV() . PHP_EOL;
```

## Como funciona a codificação

- `C`: cada ocorrência representa 1 dígito do código do produto.
- `P`: cada ocorrência representa 1 dígito do peso em gramas (dividido por 1000).
- `T`: cada ocorrência representa 1 dígito do total em centavos (dividido por 100).
- Outros caracteres: ignorados (prefixo/filler). Use um filler na posição 13 para não ocupar o DV.
- O 13º dígito do EAN é o DV (validado automaticamente).

Exemplo de codificação sem total: `CCCCCCCPPPPPX` (7 C, 5 P, 1 X filler). 7+5+1 = 13, o filler garante que peso não consuma o último dígito (DV).

## Estrutura do Projeto

```
read-label-balance/
├─ src/
│  └─ InStore.php
├─ tests/
│  └─ InStoreTest.php
├─ examples/
│  └─ example.php
├─ composer.json
├─ phpunit.xml
├─ README.md
├─ LICENSE
└─ .gitignore
```

## Testes

Executa os testes com PHPUnit:

```
composer test
```

## Licença

Distribuído sob a licença MIT. Consulte o arquivo `LICENSE` para mais detalhes.

## Créditos

Inspirado no componente **ACBrInStore** (Projeto ACBr). Lógica reimplementada em PHP para uso geral.
