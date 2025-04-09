<?php
require 'vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$chave_pix = 'seuchave@pix.com.br';
$nome_recebedor = 'PAULO CÉSAR';
$cidade = 'SALVADOR';

// --- Dados enviados pelo formulário ---
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$cep = $_POST['cep'] ?? '';
$endereco = $_POST['endereco'] ?? '';

$produtos = [
    'qtd_ovo_ao_leite' => ['nome' => 'Ovo de Páscoa Premium Ao Leite 370g', 'preco' => 39.99],
    'qtd_ovo_meio_amargo' => ['nome' => 'Ovo de Páscoa Premium Meio Amargo 370g', 'preco' => 39.99],
    'qtd_ovo_recheado' => ['nome' => 'Ovo de Páscoa Premium Recheado 370g', 'preco' => 39.99],
];

$itens = [];
$total = 0;

foreach ($produtos as $campo => $info) {
    $qtd = (int)($_POST[$campo] ?? 0);
    if ($qtd > 0) {
        $subtotal = $qtd * $info['preco'];
        $total += $subtotal;
        $itens[] = [
            'nome' => $info['nome'],
            'qtd' => $qtd,
            'preco' => $info['preco'],
            'subtotal' => $subtotal
        ];
    }
}

// --- Geração do Payload Pix ---
function gerarPayloadPix($chave, $nome, $cidade, $valor, $txid = 'TX' . time()) {
    $valor_formatado = number_format($valor, 2, '.', '');
    return "00020126360014BR.GOV.BCB.PIX01" . strlen($chave) . $chave .
           "520400005303986540" . strlen($valor_formatado) . $valor_formatado .
           "5802BR5913" . $nome .
           "6009" . $cidade .
           "62070503" . $txid . "6304";
}

$payload = gerarPayloadPix($chave_pix, $nome_recebedor, $cidade, $total);

// --- Geração do QR Code ---
$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_H,
    'scale' => 5,
]);

$qrCode = base64_encode((new QRCode($options))->render($payload));
$dataPedido = date('d/m/Y H:i');
$idPedido = 'PED' . substr(md5(uniqid()), 0, 10);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #222;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #333;
            max-width: 800px;
            margin: auto;
            border-radius: 12px;
            padding: 30px;
        }
        .header {
            background: linear-gradient(to right, #ff6a00, #d84315);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #555;
            padding: 10px;
            border-radius: 8px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #444;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #666;
        }
        table tr:nth-child(even) {
            background: #555;
        }
        .pix-box {
            background: #2e7d32;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .pix-box textarea {
            width: 100%;
            height: 80px;
            margin-top: 10px;
            font-size: 14px;
        }
        .btn {
            background: #ff6a00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ Pedido Confirmado</h1>
        <p>Brasil Cacau agradece sua preferência!</p>
    </div>

    <div class="section">
        <div class="section-title">📄 Informações do Pedido</div>
        <p><strong>Número do Pedido:</strong> <?= $idPedido ?></p>
        <p><strong>Data:</strong> <?= $dataPedido ?></p>
    </div>

    <div class="section">
        <div class="section-title">👤 Dados do Cliente</div>
        <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
        <p><strong>CEP:</strong> <?= htmlspecialchars($cep) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Endereço:</strong> <?= htmlspecialchars($endereco) ?></p>
    </div>

    <div class="section">
        <div class="section-title">🧺 Resumo do Pedido</div>
        <table>
            <tr><th>Produto</th><th>Quantidade</th><th>Preço Unitário</th><th>Subtotal</th></tr>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= $item['nome'] ?></td>
                    <td><?= $item['qtd'] ?></td>
                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight:bold;">
                <td colspan="3" style="text-align:right;">Total:</td>
                <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">💳 Pagamento via Pix</div>
        <div class="pix-box">
            <p>Escaneie o QR Code ou copie o código abaixo para realizar o pagamento:</p>
            <img src="data:image/png;base64,<?= $qrCode ?>" alt="QR Code Pix">
            <textarea readonly><?= $payload ?></textarea>
            <button class="btn" onclick="navigator.clipboard.writeText(`<?= $payload ?>`)">📋 Copiar Código Pix</button>
        </div>
    </div>

    <div class="section">
        <div class="section-title">📦 Informações de Entrega</div>
        <p>Prazo de entrega: 4 a 10 dias úteis</p>
        <p>Você receberá atualizações sobre o status do pedido por e-mail.</p>
    </div>

    <div style="text-align:center;">
        <button class="btn">✅ Confirmar Pagamento</button>
        <a href="index.html"><button class="btn">↩ Voltar à Loja</button></a>
    </div>
</div>
</body>
</html>