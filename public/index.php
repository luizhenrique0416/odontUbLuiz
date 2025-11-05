<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
use App\Health;
use App\Db;
date_default_timezone_set('America/Sao_Paulo'); // Define para o fuso horário de São Paulo

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

function h(string $s): string { 
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); 
}

function json_out($data, int $code = 200): void { 
  http_response_code($code); 
  header('Content-Type: application/json; charset=utf-8'); 
  echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); 
  exit; 
}

if ($method==='GET' && $path==='/health') { 
  json_out(Health::status()+['ts'=>gmdate('c')]); 
}

if ($method==='GET' && $path==='/db-check') { 
  try { 
    $pdo=Db::conn(); 
    $one=$pdo->query('SELECT 1 AS ok')->fetch(); 
    json_out(['db'=>'ok','result'=>$one]); 
  } catch (Throwable $e) { 
    json_out(['db'=>'error','message'=>$e->getMessage()],500);
  } 
}

if ($method==='POST' && $path==='/patients') {
  $name=trim($_POST['name']??''); 
  $birth=trim($_POST['birth_date']??''); 
  $phone=trim($_POST['phone']??''); 
  $cell=trim($_POST['cellphone']??''); 
  $email=trim($_POST['email']??'');
  $err=[]; 
  $hoje = new DateTime();
  $data_atual_formatada = $hoje->format('Y-m-d');

  if (mb_strlen($name)<3) 
    $err[]='Nome deve ter ao menos 3 caracteres.'; 

  if ($name !== '' && !preg_match('/^[A-Za-zÀ-ÿ\s]+$/u', $name)) //Só aceita letras maiúsculas, minúsculas, acentuadas e espaços, não podendo ser nulo
    $err[] = 'Nome deve conter apenas letras e espaços. Sem números ou caracteres especiais.';
  
  if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) 
    $err[]='E-mail inválido.'; 
  
  if ($birth!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$birth)) 
    $err[]='Data no formato YYYY-MM-DD.';

  if ($birth > $data_atual_formatada)
    $err[] = 'Data de nascimento não pode ser no futuro.';

  if ($err){ 
    $msg='<div class="alert error">
            <strong>Erro:</strong>
            <ul>
              <li>'.implode('</li>
              <li>',array_map('h',$err)).'</li>
            </ul>
          </div>'; 

    echo page_form($msg,compact('name','birth','phone','cell','email')); 
    exit; 
  }

  try { 
    $pdo=Db::conn(); 
    $st=$pdo->prepare('INSERT INTO patients (name, birth_date, phone, cellphone, email) VALUES (:n,:b,:p,:c,:e)'); 
    $st->execute([':n'=>$name?:null,':b'=>$birth?:null,':p'=>$phone?:null,':c'=>$cell?:null,':e'=>$email?:null]); 
    echo page_form('<div class="alert success">Paciente cadastrado com sucesso.</div>'); 
    exit; 
  } catch (Throwable $e){ 
    echo page_form('<div class="alert error"><strong>Erro ao salvar:</strong> '.h($e->getMessage()).'</div>',compact('name','birth','phone','cell','email')); 
    exit; 
  }
}

if ($method==='GET' && $path==='/'){ 
  echo page_form(); 
  exit; 
}

http_response_code(404); 
header('Content-Type: text/plain; charset=utf-8'); 
echo "Not Found";

function page_form(string $flash='', array $old=[]): string{
  $name=h($old['name']??''); 
  $birth=h($old['birth']??''); 
  $phone=h($old['phone']??''); 
  $cell=h($old['cell']??''); 
  $email=h($old['email']??'');
  return <<<HTML
<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
    <title>Cadastro de Pacientes odontUb V1</title>
</head>
<body>
  <header>
    <img src="assets/img/logo.png" alt="Logo da Universidade Brasil">
  </header>
  <div class="container">
    <h1>Cadastro de Pacientes</h1>
    <p class="desc">Preencha seus dados para contato e agendamento.</p>{$flash}
    <form method="post" action="/patients" novalidate>
      <div>
        <label for="name">Nome completo *</label>
        <input type="text" id="name" name="name" value="{$name}" placeholder="Nome" required>
      </div>
      <div>
        <label for="birth_date">Data de nascimento</label>
        <input type="date" id="birth_date" name="birth_date" value="{$birth}" placeholder="YYYY-MM-DD">
      </div>
      <div class="row">
        <div>
          <label for="phone">Telefone (fixo)</label>
          <input type="tel" id="phone" class="phone" name="phone" placeholder="(00) 0000-0000" value="{$phone}">
        </div>
        <div>
          <label for="cellphone">Celular</label>
          <input type="tel" id="cellphone" class="cellphone" name="cellphone" placeholder="(00) 90000-0000" value="{$cell}">
        </div>
      </div>
      <div>
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{$email}" placeholder="voce@exemplo.com">
      </div>
      <div>
        <button class="primary" type="submit">Enviar cadastro</button>
      </div>
      <p class="muted">
        <small class="hint">Ao enviar, você concorda com o uso dos seus dados para contato e agendamento.</small>
      </p>
    </form>
    <p class="muted">Endpoints: <code>/health</code> • <code>/db-check</code> • <code>POST /patients</code></p>
  </div>

  <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-3.5.0.min.js"></script>
  <!-- jQuery Mask Plugin -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

  <script type="text/javascript" src="assets/js/script.js"></script>
</body>
</html>
HTML;
}
