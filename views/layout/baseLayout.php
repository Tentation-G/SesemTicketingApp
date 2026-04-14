<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SesemIT — <?= htmlspecialchars($title ?? 'Tableau de bord') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Style -->
    <!-- <link rel="stylesheet" href="assets/css/main.css"> -->
    <link rel="stylesheet" href="<?= URL_CSS; ?>">
</head>
<body>

<!-- ══ Fond animé ══════════════════════════════════════════ -->
<div class="bg-scene" aria-hidden="true">
    <div class="bg-scene__blob bg-scene__blob--1"></div>
    <div class="bg-scene__blob bg-scene__blob--2"></div>
    <div class="bg-scene__blob bg-scene__blob--3"></div>
</div>

<div class="app-shell">

    <!-- ══ SIDEBAR ════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar__logo">
            <!-- <div class="sidebar__logo-icon"></div> -->
            <div class="sidebar__logo-text">Sesem<span>IT</span></div>
            <!-- Trop pixelisé, c'est affreux -->
            <!-- <img class="sidebar__logo-img" src="assets\img\logo_sesem_dark_e8eaf0.png" alt=""> -->
        </div>

        <!-- Navigation -->
        <nav class="sidebar__nav">

            <span class="sidebar__section-label">Principal</span>

            <a href="index.php?p=home" class="sidebar__item <?= (($_GET['p'] ?? '') === 'home' || ($_GET['p'] ?? '') === '') ? 'active' : '' ?>">
                <svg class="sidebar__item__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 9.5L10 3l7 6.5V17a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                    <path d="M7 18V11h6v7"/>
                </svg>
                Accueil
            </a>

            <a href="index.php?p=listTicketsView" class="sidebar__item <?= (($_GET['p'] ?? '') === 'listTicketsView') ? 'active' : '' ?>">
                <svg class="sidebar__item__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="14" height="14" rx="2"/>
                    <path d="M7 7h6M7 10h6M7 13h4"/>
                </svg>
                Tickets
            </a>

            <a href="index.php?p=addTicketView" class="sidebar__item <?= (($_GET['p'] ?? '') === 'addTicketView') ? 'active' : '' ?>">
                <svg class="sidebar__item__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="10" cy="10" r="7"/>
                    <path d="M10 7v6M7 10h6"/>
                </svg>
                Nouveau ticket
            </a>

            <?php if (!empty($_SESSION['user']) && (int)$_SESSION['user']['role'] === 1) : ?>
            <span class="sidebar__section-label">Administration</span>

            <a href="index.php?p=usersView" class="sidebar__item <?= (($_GET['p'] ?? '') === 'usersView') ? 'active' : '' ?>">
                <svg class="sidebar__item__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="10" cy="7" r="3"/>
                    <path d="M3 17c0-3.3 3.1-6 7-6s7 2.7 7 6"/>
                </svg>
                Utilisateurs
            </a>

            <a href="index.php?p=settingsView" class="sidebar__item <?= (($_GET['p'] ?? '') === 'settingsView') ? 'active' : '' ?>">
                <svg class="sidebar__item__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="10" cy="10" r="3"/>
                    <path d="M10 2v2M10 16v2M2 10h2M16 10h2M4.2 4.2l1.4 1.4M14.4 14.4l1.4 1.4M4.2 15.8l1.4-1.4M14.4 5.6l1.4-1.4"/>
                </svg>
                Paramètres
            </a>
            <?php endif; ?>

        </nav>

        <!-- Utilisateur connecté + déconnexion -->
        <div class="sidebar__footer">
            <?php if (!empty($_SESSION['user'])) :
                $initiales = strtoupper(
                    substr($_SESSION['user']['prenom'] ?? 'U', 0, 1) .
                    substr($_SESSION['user']['nom']    ?? '', 0, 1)
                );
                $roleLabel = match((int) $_SESSION['user']['role']) {
                    1 => 'Administrateur',
                    2 => 'Manager',
                    3 => 'Collaborateur',
                    default => 'Utilisateur',
                };
            ?>
            <div class="sidebar__user">
                <div class="sidebar__user-avatar"><?= $initiales ?></div>
                <div class="sidebar__user-info">
                    <div class="sidebar__user-info-name">
                        <?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?>
                    </div>
                    <div class="sidebar__user-info-role"><?= $roleLabel ?></div>
                </div>
            </div>
            <?php endif; ?>

            <form action="index.php?p=logout" method="post" style="margin-top:8px">
                <button type="submit" class="btn btn--ghost btn--sm" style="width:100%;justify-content:center;gap:7px">
                    <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M13 15l4-5-4-5"/>
                        <path d="M3 10h14M3 4v12"/>
                    </svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    </aside>

    <!-- ══ CONTENU PRINCIPAL ══════════════════════════════════ -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar__left">
                <!-- Breadcrumb dynamique -->
                <nav class="topbar__breadcrumb" aria-label="Fil d'Ariane">
                    <span class="topbar__breadcrumb-parent">SesemIT</span>
                    <span class="topbar__breadcrumb-sep">/</span>
                    <span class="topbar__breadcrumb-current"><?= htmlspecialchars($title ?? 'Accueil') ?></span>
                </nav>
            </div>

            <div class="topbar__right">
                <!-- Bouton notifications -->
                <button class="topbar__action-btn has-notif" title="Notifications" type="button">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M10 2a6 6 0 00-6 6v3.5L2.5 14h15L16 11.5V8a6 6 0 00-6-6z"/>
                        <path d="M8 14a2 2 0 004 0"/>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="page">
            <?= $content ?? '' ?>
        </main>

    </div><!-- /main-content -->
</div><!-- /app-shell -->

</body>
</html>