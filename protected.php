<?php
    session_start();

    require 'vendor/autoload.php';
    require 'processos/db_connect.php';
    require 'processos/noticias.php';
    require 'processos/pendencias.php';
    require 'processos/mensagens.php';
    require 'processos/creditos.php';

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $sql = "SELECT email,username, codigo_unico FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die('Erro: Usuário não encontrado.');
    }

    $data_inicio_semana = date('Y-m-d', strtotime('monday this week'));
    $data_fim_semana = date('Y-m-d', strtotime('sunday this week'));

    $sql = "SELECT data FROM presencas WHERE user_id = ? AND data BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id'], $data_inicio_semana, $data_fim_semana]);
    $presencas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dias_semana = [];
    for ($i = 0; $i < 7; $i++) {
        $dias_semana[] = date('Y-m-d', strtotime("monday this week +$i days"));
    }
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presenças</title>
    <link rel="stylesheet" href="css/protected.css">
    <link rel="stylesheet" href="css/thema.css">
    <link rel="stylesheet" href="css/hamburguerBtn.css">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="header-container-pt1">
                <div class="logo-div">
                    <img src="img/logo-removebg-preview.png" alt="logo da empresa" class="logo-img" />
                </div>
            </div>
            <div class="header-container-pt2">
                <div class="hearder-user-img">
                    <div class="hamburger-btn" id="hamburgerBtn">
                        <img src="img/user.png" alt="Foto do perfil" class="header-img" />
                    </div>

                    <div class="sidebar" id="sidebar">
                        <div class="slider-div">
                            <div class="sidebar-footer">
                                <a class="sair" href="logout.php">Sair</a>
                            </div>
                            <div class="sidebar-header">
                                <img src="img/logo-removebg-preview.png" alt="logo da empresa" class="logo-img" />
                            </div>
                            <div class="sidebar-username">
                                <h4><?php echo htmlspecialchars($usuario['username']); ?></h4>
                                <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="devider"> | </span>

                <div class="div-username">
                    <h4><?php echo htmlspecialchars($usuario['username']); ?></h4>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="contend">
            <div class="card principal">
                <div class="card-mask">
                    <img src="img/user.png" alt="Foto do perfil" class="card-img" />
                </div>
                <h3>Bem-vindo, <?php echo htmlspecialchars($usuario['username']); ?>!</h3>
                <div class="card_text">
                    <!-- <p>Seu código: <strong><?php echo htmlspecialchars($usuario['codigo_unico']); ?></strong></p> -->
                    <a href="cracha.php">Cracha</a>
                </div>
                <div>
                    <label class="theme">
                        <input class="input" type="checkbox" id="theme-toggle" />
                        <svg width="24" viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                            stroke="currentColor" height="24" fill="none" class="icon icon-sun">
                            <circle r="5" cy="12" cx="12"></circle>
                            <line y2="3" y1="1" x2="12" x1="12"></line>
                            <line y2="23" y1="21" x2="12" x1="12"></line>
                            <line y2="5.64" y1="4.22" x2="5.64" x1="4.22"></line>
                            <line y2="19.78" y1="18.36" x2="19.78" x1="18.36"></line>
                            <line y2="12" y1="12" x2="3" x1="1"></line>
                            <line y2="12" y1="12" x2="23" x1="21"></line>
                            <line y2="18.36" y1="19.78" x2="5.64" x1="4.22"></line>
                            <line y2="4.22" y1="5.64" x2="19.78" x1="18.36"></line>
                        </svg>
                        <svg viewBox="0 0 24 24" class="icon icon-moon">
                            <path
                                d="m12.3 4.9c.4-.2.6-.7.5-1.1s-.6-.8-1.1-.8c-4.9.1-8.7 4.1-8.7 9 0 5 4 9 9 9 3.8 0 7.1-2.4 8.4-5.9.2-.4 0-.9-.4-1.2s-.9-.2-1.2.1c-1 .9-2.3 1.4-3.7 1.4-3.1 0-5.7-2.5-5.7-5.7 0-1.9 1.1-3.8 2.9-4.8zm2.8 12.5c.5 0 1 0 1.4-.1-1.2 1.1-2.8 1.7-4.5 1.7-3.9 0-7-3.1-7-7 0-2.5 1.4-4.8 3.5-6-.7 1.1-1 2.4-1 3.8-.1 4.2 3.4 7.6 7.6 7.6z">
                            </path>
                        </svg>
                    </label>
                </div>
            </div>

            <div class="card creditos">
                <div class="card-mask creditos-img">
                    <img src="https://rewards.bing.com/rewardscdn/images/rewards.png" alt="Foto do perfil" class="card-creditos" />
                    <div class="text-creditos">
                        <a>Pontos disponíveis</a>
                        <Span><?= $total_creditos ?></Span>

                        <!-- Botão que abre a explicação -->
                        <button class="help-button" onclick="openOverlay('Esta máquina é um simulador de corrida com volante e pedais!')"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 50 50">
                            <path d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z"></path>
                            </svg>
                        </button>
                        <!-- Janela de explicação -->
                        <div id="overlay" class="overlay">
                            <div class="overlay-content">
                                <span class="close-btn" onclick="closeOverlay()">✖</span>
                                <h2>Detalhes</h2>
                                <p id="overlay-text">Texto aqui</p>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

        <div class="cond">
            <div class="card presenca">
                <h3>Presença desta semana:</h3>
                <div class="checklist">
                    <?php foreach ($dias_semana as $dia): ?>
                        <?php $presente = in_array($dia, $presencas); ?>
                        <div class="day">
                            <div class="circle <?php echo $presente ? 'present' : ''; ?>">
                                <?php echo $presente ? '✔️' : ''; ?>
                            </div>
                            <div class="day-name"><?php echo date('D', strtotime($dia)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mensagens">
                <h3>Mensagens:</h3>
                <div class="card-interno mensagen-interna">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Mensagem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($mensagens) > 0): ?>
                                <?php foreach ($mensagens as $mensagem): ?>
                                    <tr onclick="openOverlay('Esta máquina é um arcade clássico de luta!')">
                                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mensagem['data_criacao']))); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mensagem['mensagem']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Nenhuma mensagem disponível.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="contend">
            <div class="card pendencias">
                <h3>Pendencias:</h3>
                <div class="card-interno">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Clique para atender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pendencias) > 0): ?>
                                <?php foreach ($pendencias as $pendencia): ?>
                                    <tr onclick="openOverlay('Esta máquina é um arcade clássico de luta!')">
                                        <td><?php echo htmlspecialchars($pendencia['descricao']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="1">Nenhuma pendência disponível.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card pendencias">
                <h3>Notícias:</h3>
                <div class="card-interno noticias">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Assunto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($noticias) > 0): ?>
                                <?php foreach ($noticias as $noticia): ?>
                                    <tr onclick="openOverlay('<?php echo htmlspecialchars($noticia['detalhes']); ?>')">
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($noticia['data']))); ?></td>
                                        <td><?php echo htmlspecialchars($noticia['assunto']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Nenhuma notícia disponível.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?php echo $pagina - 1; ?>">Anterior</a>
                    <?php endif; ?>

                    <span>Página <?php echo $pagina; ?> de <?php echo $total_paginas; ?></span>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina + 1; ?>">Próxima</a>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>

    <script src="js/thema.js"></script>
    <script src="js/hamburguerBtn.js"></script>
    <script>
        function openOverlay(text) {
            document.getElementById("overlay-text").innerText = text;
            document.getElementById("overlay").style.display = "flex";
        }

        function closeOverlay() {
            document.getElementById("overlay").style.display = "none";
        }
    </script>
</body>

</html>