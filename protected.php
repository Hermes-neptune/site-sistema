<?php
    session_start();

    require 'vendor/autoload.php';
    require 'process/db_connect.php';
    require 'process/noticias.php';
    require 'process/pendencias.php';
    require 'process/mensagens.php';
    require 'process/creditos.php';

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $sql = "SELECT email, username, rm, photo FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die('Erro: Usuário não encontrado.');
    }
    
    $user_photo_url = !empty($usuario['photo']) ? htmlspecialchars($usuario['photo']) : 'img/user.png';

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
    <html lang="pt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Presenças</title>
        <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
        <link rel="stylesheet" href="css/protected.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/thema.css">
        <link rel="stylesheet" href="css/hamburguerBtn.css">
        <link rel="stylesheet" href="css/mobile.css">
    </head>

    <body>
        <header>
            <div class="header-container">
                <div class="header-container-pt1">
                    <div class="logo-div">
                        <img src="" alt="logo da empresa" class="logo-img" />
                    </div>
                </div>
                <div class="header-container-pt2">
                    <div class="hearder-user-img">
                        <div class="hamburger-btn" id="hamburgerBtn">
                            <?php echo '<img src="' . $user_photo_url . '" alt="Foto do perfil" class="header-img" />'; ?>
                        </div>

                        <div class="sidebar" id="sidebar">
                            <div class="slider-div">
                                <div class="sidebar-extra">
                                    <div class="sidebar-header">
                                        <img src="" alt="logo da empresa" class="logo-img" />
                                    </div>
                                    <div class="sidebar-footer">
                                        <a class="config-button" href="config.php"><i class="fas fa-cog"></i></a>

                                        <a class="sair" href="process/logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        </a>
                                    </div>
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
                        <img src="<?php echo $user_photo_url; ?>" alt="Foto do perfil" class="card-img" />
                    </div>
                    <h3 id="welcome-heading">Bem-vindo, <?php echo htmlspecialchars($usuario['username']); ?>!</h3>
                    <div class="card_text">
                        <!-- <p>Seu código: <strong><?php echo htmlspecialchars($usuario['rm']); ?></strong></p> -->
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
                    <div class="card-mask-point creditos-img">
                        <img src="https://rewards.bing.com/rewardscdn/images/rewards.png" alt="Foto do perfil" class="card-creditos" />
                        <div class="text-creditos">
                            <div><a>Seus Pontos</a></div>
                            <Span><?= $total_creditos ?></Span>

                            <button class="help-button" aria-label="Explicação sobre pontos" data-overlay-text="Esta máquina é um simulador de corrida com volante e pedais!">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 50 50" aria-hidden="true">
                                    <path d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
            </div>
        </div>

            <div class="contend">
                <section class="attendance-section" aria-labelledby="attendance-heading">
                    <div class="card presenca">
                        <h3 id="attendance-heading">Presença desta semana:</h3>
                        <div class="checklist">
                            <?php foreach ($dias_semana as $dia): ?>
                                <?php $presente = in_array($dia, $presencas); ?>
                                <div class="day">
                                    <div class="circle <?php echo $presente ? 'present' : ''; ?>" aria-label="<?php echo date('l', strtotime($dia)) . ': ' . ($presente ? 'Presente' : 'Ausente'); ?>">
                                        <?php echo $presente ? '✔️' : ''; ?>
                                    </div>
                                    <div class="day-name"><?php echo date('D', strtotime($dia)); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section class="messages-section" aria-labelledby="messages-heading">
                    <div class="card mensagens">
                        <h3 id="messages-heading">Mensagens:</h3>
                        <div class="card-interno mensagen-interna">
                            <ul class="message-list" aria-label="Lista de mensagens">
                                <?php if (count($mensagens) > 0): ?>
                                    <?php foreach ($mensagens as $mensagem): ?>
                                        <li class="message-item" tabindex="0" data-overlay-text="<?php echo htmlspecialchars($mensagem['mensagem']); ?>">
                                            <span class="message-date"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mensagem['data_criacao']))); ?></span>
                                            <span class="message-content"><?php echo htmlspecialchars($mensagem['mensagem']); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="no-messages">Nenhuma mensagem disponível.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <div class="contend">
                <section class="pending-section" aria-labelledby="pending-heading">
                        <div class="card pendencias">
                            <h3 id="pending-heading">Pendencias:</h3>
                            <div class="card-interno">
                                <ul class="pending-list" aria-label="Lista de pendências">
                                    <?php if (count($pendencias) > 0): ?>
                                        <?php foreach ($pendencias as $pendencia): ?>
                                            <li class="pending-item" tabindex="0" data-overlay-text="<?php echo htmlspecialchars($pendencia['descricao']); ?>">
                                                <?php echo htmlspecialchars($pendencia['descricao']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="no-pending">Nenhuma pendência disponível.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="news-section" aria-labelledby="news-heading">
                        <div class="card pendencias">
                            <h3 id="news-heading">Notícias:</h3>
                            <div class="card-interno noticias">
                                <ul class="news-list" aria-label="Lista de notícias">
                                    <?php if (count($noticias) > 0): ?>
                                        <?php foreach ($noticias as $noticia): ?>
                                            <li class="news-item" tabindex="0" data-overlay-text="<?php echo htmlspecialchars($noticia['detalhes']); ?>">
                                                <span class="news-date"><?php echo htmlspecialchars(date('d/m/Y', strtotime($noticia['data']))); ?></span>
                                                <span class="news-title"><?php echo htmlspecialchars($noticia['assunto']); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="no-news">Nenhuma notícia disponível.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="pagination">
                                <?php if ($pagina > 1): ?>
                                    <a href="?pagina=<?php echo $pagina - 1; ?>" aria-label="Página anterior">Anterior</a>
                                <?php endif; ?>

                                <span>Página <?php echo $pagina; ?> de <?php echo $total_paginas; ?></span>

                                <?php if ($pagina < $total_paginas): ?>
                                    <a href="?pagina=<?php echo $pagina + 1; ?>" aria-label="Próxima página">Próxima</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
            </div>
        </main>

        <div id="overlay" class="overlay" aria-hidden="true" aria-modal="true" role="dialog">
            <div class="overlay-content">
                <button class="close-btn" aria-label="Fechar diálogo">✖</button>
                <h2>Detalhes</h2>
                <p id="overlay-text"></p>
            </div>
            
        </div>
        <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>

        <script src="js/thema.js"></script>
        <script src="js/hamburguerBtn.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = {
                    element: document.getElementById("overlay"),
                    textElement: document.getElementById("overlay-text"),
                    open: function(text) {
                        this.textElement.textContent = text;
                        this.element.style.display = "flex";
                        this.element.setAttribute('aria-hidden', 'false');
                        document.body.style.overflow = 'hidden';
                        document.querySelector('.close-btn').focus();
                    },
                    close: function() {
                        this.element.style.display = "none";
                        this.element.setAttribute('aria-hidden', 'true');
                        document.body.style.overflow = 'auto';
                    }
                };
                
                document.querySelectorAll('[data-overlay-text]').forEach(el => {
                    el.addEventListener('click', function() {
                        const text = this.getAttribute('data-overlay-text');
                        overlay.open(text);
                    });
                    
                    el.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            const text = this.getAttribute('data-overlay-text');
                            overlay.open(text);
                        }
                    });
                });
                
                document.querySelector('.close-btn').addEventListener('click', () => overlay.close());
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && overlay.element.style.display === 'flex') {
                        overlay.close();
                    }
                });

                overlay.element.addEventListener('click', function(e) {
                    if (e.target === overlay.element) {
                        overlay.close();
                    }
                });
            });
        </script>
    </body>
</html>