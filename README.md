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

## Exemplo de Uso

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use ReadLabelBalance\InStore;

// Codificação: 7 dígitos do código (C), 5 do peso em gramas (P), 1 filler (X)
$instore = new InStore();
$instore->setCodificacao('CCCCCCCPPPPPX');

// Callback que retorna preço por kg
$instore->setOnGetPrecoUnitario(function (string $codigo): float {
    return $codigo === '1234567' ? 19.90 : 0.0;
});

$codigo = '1234567';
$pesoG  = '00150'; // 150g
$base12 = $codigo . $pesoG;        // 12 dígitos
$dv     = $instore->calcEan13DV($base12);
$ean13  = $base12 . $dv;           // 13 dígitos

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

## Publicando no GitHub e Packagist

1. GitHub
   - Crie um repositório: `read-label-balance` em sua conta.
   - Configure o `composer.json` com `homepage` e `support` apontando para seu repositório.
   - Faça o push inicial:
     ```
     git init
     git add .
     git commit -m "feat: release inicial da biblioteca"
     git branch -M main
     git remote add origin https://github.com/seu-usuario/read-label-balance.git
     git push -u origin main
     ```

2. Criar tag de versão (exemplo 1.0.0)
   ```
   git tag -a v1.0.0 -m "Primeiro release estável"
   git push origin v1.0.0
   ```

3. Packagist
   - Acesse https://packagist.org e faça login com sua conta.
   - Clique em "Submit" e informe a URL do repositório GitHub.
   - Verifique se o `composer.json` está válido (nome, descrição, autoload PSR-4, licença, etc.).
   - Após aprovado, instalar com `composer require seu-usuario/read-label-balance:^1.0`.

4. Atualizações
   - Sempre que criar uma nova versão, crie uma nova tag (`v1.1.0`, `v1.2.0`, etc.).
   - O Packagist detecta automaticamente novas tags via webhook do GitHub.

## Licença

Distribuído sob a licença MIT. Consulte o arquivo `LICENSE` para mais detalhes.

## Créditos

Inspirado no componente **ACBrInStore** (Projeto ACBr). Lógica reimplementada em PHP para uso geral.