<?php
// =============== CONFIGURAÇÃO ==================
$chave_pix = '271d14df-342a-4b21-8dad-39f6e35352fd'; // <<< BOTA TEU PIX AQUI, CPF OU EMAIL
$nome_recebedor = 'Alessandro';
$cidade_recebedor = 'teste';

// =============== PEGAR DADOS DO POST ================
$nome = htmlspecialchars($_POST['nome'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$cep = htmlspecialchars($_POST['cep'] ?? '');
$endereco = htmlspecialchars($_POST['endereco'] ?? '');
$pagamento_metodo = htmlspecialchars($_POST['pagamento_metodo'] ?? '');

$qtd_ao_leite = (int) ($_POST['qtd_ovo_ao_leite'] ?? 0);
$qtd_meio_amargo = (int) ($_POST['qtd_ovo_meio_amargo'] ?? 0);
$qtd_recheado = (int) ($_POST['qtd_ovo_recheado'] ?? 0);

$total_itens = $qtd_ao_leite + $qtd_meio_amargo + $qtd_recheado;
$preco_unitario = 39.99;
$valor_total = $total_itens * $preco_unitario;

if ($valor_total <= 0) {
    die('Erro: Nenhum item selecionado.');
}

// =============== GERAÇÃO DO CÓDIGO PIX DINÂMICO =================
function gerarPix($chave, $descricao, $valor, $nome_recebedor, $cidade_recebedor) {
    $valor = number_format($valor, 2, '.', '');

    $payload = "";
    $payload .= '000201';
    $payload .= '26580014br.gov.bcb.pix01' . sprintf('%02d', strlen($chave)) . $chave;
    $payload .= '52040000';
    $payload .= '5303986';
    $payload .= '540' . sprintf('%02d', strlen($valor)) . $valor;
    $payload .= '5802BR';
    $payload .= '590' . sprintf('%02d', strlen($nome_recebedor)) . $nome_recebedor;
    $payload .= '600' . sprintf('%02d', strlen($cidade_recebedor)) . $cidade_recebedor;
    $payload .= '62100506' . '*****'; // txid aleatorio

    $crc = crcChecksum($payload . '6304');
    $payload .= '6304' . $crc;

    return $payload;
}

function crcChecksum($str) {
    $crc = 0xFFFF;
    for ($c = 0; $c < strlen($str); $c++) {
        $crc ^= ord($str[$c]) << 8;
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc <<= 1;
            }
        }
    }
    return strtoupper(dechex($crc & 0xFFFF));
}

$descricao = 'Compra de ovos de Páscoa';
$pix = gerarPix($chave_pix, $descricao, $valor_total, $nome_recebedor, $cidade_recebedor);

// =============== MOSTRAR QR CODE PIX ==================
echo "<h1>Pagamento via PIX</h1>";
echo "<p>Valor: <strong>R$ " . number_format($valor_total, 2, ',', '.') . "</strong></p>";
echo "<p>Nome: <strong>$nome</strong></p>";
echo "<p>Escaneie o código abaixo ou copie a chave Pix:</p>";
echo "<img src='https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($pix) . "&size=300x300' alt='QR Code PIX'>";
echo "<p><textarea readonly style='width:100%; height:100px;'>$pix</textarea></p>";
echo "<p><a href='index.html'>Voltar para loja</a></p>";
?>