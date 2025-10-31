<?php
// ⚙️ CONFIGURAÇÕES DO SISTEMA
define('SITE_NOME', 'Portal de Vagas Profissional');
define('EMAIL_EMPREGADOR', 'recrutador@empresa.com'); // ALTERE PARA SEU EMAIL
define('EMAIL_REMETENTE', 'noreply@' . $_SERVER['HTTP_HOST']);

// 🗃️ SISTEMA DE ARMAZENAMENTO
$arquivo_vagas = 'vagas.json';
$arquivo_candidaturas = 'candidaturas.txt';

// Inicializar arquivo de vagas se não existir
if (!file_exists($arquivo_vagas)) {
    file_put_contents($arquivo_vagas, json_encode([]));
}

// 📧 FUNÇÃO PARA ENVIAR EMAIL (SALVA EM ARQUIVO PARA TESTES)
function enviarEmail($vaga, $candidato) {
    global $arquivo_candidaturas;
    
    $dados_candidatura = "
    ==================================
    📧 NOVA CANDIDATURA - " . date('d/m/Y H:i:s') . "
    ==================================
    🎯 VAGA: {$vaga['titulo']}
    🏢 EMPRESA: {$vaga['empresa']}
    📍 LOCAL: {$vaga['localidade']}
    
    👤 DADOS DO CANDIDATO:
    • Nome: {$candidato['nome']}
    • Email: {$candidato['email']}
    • Telefone: {$candidato['telefone']}
    • LinkedIn: {$candidato['linkedin']}
    
    💼 EXPERIÊNCIA:
    {$candidato['experiencia']}
    
    📝 MENSAGEM:
    {$candidato['mensagem']}
    ==================================\n\n
    ";
    
    // Salva em arquivo (para testes/demonstração)
    file_put_contents($arquivo_candidaturas, $dados_candidatura, FILE_APPEND | LOCK_EX);
    
    // ⚡ VERSÃO PARA EMAIL REAL (descomente se tiver hospedagem com email)
    /*
    $para = EMAIL_EMPREGADOR;
    $assunto = "🎯 Nova Candidatura - " . $vaga['titulo'];
    $headers = "From: " . EMAIL_REMETENTE . "\r\n";
    $headers .= "Reply-To: " . $candidato['email'] . "\r\n";
    return mail($para, $assunto, $dados_candidatura, $headers);
    */
    
    return true;
}

// 📥 PROCESSAR FORMULÁRIOS
if ($_POST) {
    // CADASTRAR VAGA
    if (isset($_POST['cadastrar_vaga'])) {
        $vagas = json_decode(file_get_contents($arquivo_vagas), true);
        $nova_vaga = [
            'id' => uniqid(),
            'titulo' => $_POST['titulo'],
            'empresa' => $_POST['empresa'],
            'localidade' => $_POST['localidade'],
            'salario' => $_POST['salario'],
            'tipo_contrato' => $_POST['tipo_contrato'],
            'descricao' => $_POST['descricao'],
            'requisitos' => $_POST['requisitos'],
            'data_publicacao' => date('d/m/Y H:i'),
            'ativa' => true
        ];
        
        $vagas[] = $nova_vaga;
        file_put_contents($arquivo_vagas, json_encode($vagas));
        $sucesso = "✅ Vaga cadastrada com sucesso!";
    }
    
    // PROCESSAR CANDIDATURA
    if (isset($_POST['candidatar'])) {
        $vagas = json_decode(file_get_contents($arquivo_vagas), true);
        $vaga_id = $_POST['vaga_id'];
        
        // Buscar vaga
        $vaga_selecionada = null;
        foreach ($vagas as $vaga) {
            if ($vaga['id'] == $vaga_id) {
                $vaga_selecionada = $vaga;
                break;
            }
        }
        
        if ($vaga_selecionada) {
            $candidato = [
                'nome' => $_POST['nome'],
                'email' => $_POST['email'],
                'telefone' => $_POST['telefone'],
                'linkedin' => $_POST['linkedin'],
                'experiencia' => $_POST['experiencia'],
                'mensagem' => $_POST['mensagem']
            ];
            
            if (enviarEmail($vaga_selecionada, $candidato)) {
                $sucesso_candidatura = "🎉 Candidatura enviada! O recrutador será notificado.";
            } else {
                $erro_candidatura = "❌ Erro ao enviar candidatura. Tente novamente.";
            }
        }
    }
}

// 📋 CARREGAR VAGAS ATIVAS
$vagas = json_decode(file_get_contents($arquivo_vagas), true);
$vagas_ativas = array_filter($vagas, function($vaga) {
    return $vaga['ativa'] ?? true;
});

// 🎯 DETERMINAR AÇÃO ATUAL
$acao = $_GET['acao'] ?? 'lista';
$vaga_id = $_GET['vaga_id'] ?? null;
$mostrar_candidatura = ($acao === 'candidatar' && $vaga_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NOME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            border-radius: 0 0 20px 20px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        nav a {
            color: #667eea;
            text-decoration: none;
            margin-left: 20px;
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        nav a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .hero {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            margin: 20px 0;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .hero h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #333;
        }
        
        .hero p {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 25px;
        }
        
        .vaga-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            margin: 20px 0;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
            transition: transform 0.3s ease;
        }
        
        .vaga-card:hover {
            transform: translateY(-5px);
        }
        
        .vaga-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .vaga-info span {
            padding: 8px 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }
        
        .btn-success:hover {
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .alert {
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .vaga-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            nav a {
                margin: 5px;
                display: inline-block;
            }
            
            .vaga-info {
                grid-template-columns: 1fr;
            }
            
            .vaga-actions {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .hero {
                padding: 30px 20px;
            }
            
            .hero h1 {
                font-size: 2em;
            }
        }
        
        .candidatura-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        @media (max-width: 968px) {
            .candidatura-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        footer {
            background: rgba(255, 255, 255, 0.95);
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            border-radius: 20px 20px 0 0;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">🎯 <?php echo SITE_NOME; ?></div>
                <nav>
                    <a href="?acao=lista">📋 Ver Vagas</a>
                    <a href="?acao=cadastrar">📝 Cadastrar Vaga</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>
        
        <?php if (isset($sucesso_candidatura)): ?>
            <div class="alert alert-success"><?php echo $sucesso_candidatura; ?></div>
        <?php endif; ?>
        
        <?php if (isset($erro_candidatura)): ?>
            <div class="alert alert-error"><?php echo $erro_candidatura; ?></div>
        <?php endif; ?>

        <?php if ($mostrar_candidatura && $vaga_id): ?>
            <!-- 📨 FORMULÁRIO DE CANDIDATURA -->
            <?php
            $vaga_selecionada = null;
            foreach ($vagas as $vaga) {
                if ($vaga['id'] == $vaga_id) {
                    $vaga_selecionada = $vaga;
                    break;
                }
            }
            
            if (!$vaga_selecionada): ?>
                <div class="alert alert-error">❌ Vaga não encontrada!</div>
                <a href="?acao=lista" class="btn">↩️ Voltar para Vagas</a>
            <?php else: ?>
                <div class="hero">
                    <h1>Candidatar-se à Vaga</h1>
                    <p>Preencha seus dados para enviar seu currículo</p>
                </div>

                <div class="candidatura-grid">
                    <!-- Info da Vaga -->
                    <div class="info-card">
                        <h3><?php echo htmlspecialchars($vaga_selecionada['titulo']); ?></h3>
                        <div style="margin-top: 20px;">
                            <p><strong>🏢 Empresa:</strong><br><?php echo htmlspecialchars($vaga_selecionada['empresa']); ?></p>
                            <p><strong>📍 Local:</strong><br><?php echo htmlspecialchars($vaga_selecionada['localidade']); ?></p>
                            <p><strong>💰 Salário:</strong><br><?php echo htmlspecialchars($vaga_selecionada['salario']); ?></p>
                            <p><strong>📄 Contrato:</strong><br><?php echo htmlspecialchars($vaga_selecionada['tipo_contrato']); ?></p>
                        </div>
                    </div>

                    <!-- Formulário Candidatura -->
                    <div class="form-card">
                        <form method="POST">
                            <input type="hidden" name="candidatar" value="1">
                            <input type="hidden" name="vaga_id" value="<?php echo $vaga_selecionada['id']; ?>">
                            
                            <h3 style="margin-bottom: 25px; color: #333;">📝 Dados Pessoais</h3>
                            
                            <div class="form-group">
                                <label for="nome" class="required">Nome Completo</label>
                                <input type="text" id="nome" name="nome" required placeholder="Seu nome completo">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="required">E-mail</label>
                                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefone" class="required">Telefone</label>
                                    <input type="tel" id="telefone" name="telefone" required placeholder="(11) 99999-9999">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="linkedin">LinkedIn</label>
                                <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/seu-perfil">
                            </div>
                            
                            <div class="form-group">
                                <label for="experiencia" class="required">💼 Experiência Profissional</label>
                                <textarea id="experiencia" name="experiencia" required placeholder="Descreva sua experiência profissional, formação, cursos relevantes..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="mensagem">📝 Mensagem para o Recrutador</label>
                                <textarea id="mensagem" name="mensagem" placeholder="Por que você é a pessoa ideal para esta vaga?"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success">📨 Enviar Candidatura</button>
                            <a href="?acao=lista" class="btn" style="background: #6c757d; margin-left: 10px;">↩️ Voltar</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($acao == 'cadastrar'): ?>
            <!-- 📝 FORMULÁRIO CADASTRAR VAGA -->
            <div class="hero">
                <h1>Cadastrar Nova Vaga</h1>
                <p>Preencha os dados da oportunidade</p>
            </div>

            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="cadastrar_vaga" value="1">
                    
                    <div class="form-group">
                        <label for="titulo" class="required">🎯 Título da Vaga</label>
                        <input type="text" id="titulo" name="titulo" required placeholder="Ex: Desenvolvedor PHP Pleno">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="empresa" class="required">🏢 Empresa</label>
                            <input type="text" id="empresa" name="empresa" required placeholder="Nome da empresa">
                        </div>
                        
                        <div class="form-group">
                            <label for="localidade" class="required">📍 Localidade</label>
                            <input type="text" id="localidade" name="localidade" required placeholder="Ex: São Paulo - SP, Remoto">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salario">💰 Salário</label>
                            <input type="text" id="salario" name="salario" placeholder="Ex: R$ 5.000 - R$ 7.000">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_contrato">📄 Tipo de Contrato</label>
                            <select id="tipo_contrato" name="tipo_contrato">
                                <option value="CLT">CLT</option>
                                <option value="PJ">PJ</option>
                                <option value="Freelancer">Freelancer</option>
                                <option value="Estágio">Estágio</option>
                                <option value="Temporário">Temporário</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao" class="required">📋 Descrição da Vaga</label>
                        <textarea id="descricao" name="descricao" required placeholder="Descreva as atividades e responsabilidades da vaga..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="requisitos" class="required">🎯 Requisitos e Qualificações</label>
                        <textarea id="requisitos" name="requisitos" required placeholder="Liste os requisitos necessários para a vaga..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn">💾 Publicar Vaga</button>
                    <a href="?acao=lista" class="btn" style="background: #6c757d; margin-left: 10px;">↩️ Voltar</a>
                </form>
            </div>

        <?php else: ?>
            <!-- 🎯 LISTA DE VAGAS -->
            <div class="hero">
                <h1>Encontre Sua Oportunidade Ideal</h1>
                <p>Conectamos talentos a oportunidades incríveis</p>
                <a href="?acao=cadastrar" class="btn">📝 Cadastrar Nova Vaga</a>
            </div>

            <?php if (empty($vagas_ativas)): ?>
                <div class="vaga-card" style="text-align: center;">
                    <h3>📭 Nenhuma vaga disponível</h3>
                    <p>Seja o primeiro a cadastrar uma oportunidade!</p>
                    <a href="?acao=cadastrar" class="btn">Cadastrar Primeira Vaga</a>
                </div>
            <?php else: ?>
                <h2 style="margin: 30px 0; color: white; text-align: center;">🚀 Vagas Disponíveis (<?php echo count($vagas_ativas); ?>)</h2>
                
                <?php foreach ($vagas_ativas as $vaga): ?>
                    <div class="vaga-card">
                        <h3><?php echo htmlspecialchars($vaga['titulo']); ?></h3>
                        
                        <div class="vaga-info">
                            <span>🏢 <?php echo htmlspecialchars($vaga['empresa']); ?></span>
                            <span>📍 <?php echo htmlspecialchars($vaga['localidade']); ?></span>
                            <span>💰 <?php echo htmlspecialchars($vaga['salario']); ?></span>
                            <span>📄 <?php echo htmlspecialchars($vaga['tipo_contrato']); ?></span>
                        </div>
                        
                        <div class="form-group">
                            <strong>📋 Descrição:</strong>
                            <p><?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>
                        </div>
                        
                        <div class="form-group">
                            <strong>🎯 Requisitos:</strong>
                            <p><?php echo nl2br(htmlspecialchars($vaga['requisitos'])); ?></p>
                        </div>
                        
                        <div class="vaga-actions">
                            <small>📅 Publicada em: <?php echo $vaga['data_publicacao']; ?></small>
                            <a href="?acao=candidatar&vaga_id=<?php echo $vaga['id']; ?>" class="btn btn-success">
                                📨 Enviar Currículo
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NOME; ?>. Desenvolvido com PHP por Edi Rocha.</p>
        <p style="margin-top: 10px; font-size: 14px;">
            <a href="?acao=lista" style="color: #667eea; text-decoration: none;">Ver Vagas</a> • 
            <a href="?acao=cadastrar" style="color: #667eea; text-decoration: none;">Cadastrar Vaga</a>
        </p>
    </footer>
</body>
</html>
