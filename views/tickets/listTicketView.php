<?php ob_start();

function buildUrl(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    return 'index.php?' . http_build_query($params);
}

function sortLink(string $col, string $label, string $currentSort, string $currentDir): string
{
    $isActive = $currentSort === $col;
    $newDir   = ($isActive && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $arrow    = '';
    if ($isActive) {
        $arrow = '<span class="sort-icon">' . ($currentDir === 'ASC' ? '▲' : '▼') . '</span>';
    }
    $url       = htmlspecialchars(buildUrl(['sort' => $col, 'dir' => $newDir, 'page' => 1]));
    $activeClass = $isActive ? ' active' : '';
    return "<a href=\"$url\" class=\"sort-link$activeClass\">$label$arrow</a>";
}

// Mapping statuts → classe CSS + libellé
function statutClass(int $id): string
{
    return match($id) {
        1 => 'open',
        2 => 'ongoing',
        3 => 'waiting',
        4 => 'resolved',
        5 => 'closed',
        default => 'open'
    };
}

// Initiales d'un nom complet
function initiales(string $fullName): string
{
    $parts = array_filter(explode(' ', $fullName));
    if (count($parts) >= 2) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }
    return strtoupper(mb_substr($fullName, 0, 2));
}
?>

<!-- ══ EN-TÊTE DE PAGE ═════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-header__title">
            Mes <span>tickets</span>
        </h1>
        <p class="page-header__sub">
            <?= $total ?> ticket<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
            · page <?= $page ?> / <?= max(1, $totalPages) ?>
        </p>
    </div>
    <a href="index.php?p=addTicketView" class="btn btn--primary">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10 4v12M4 10h12"/>
        </svg>
        Nouveau ticket
    </a>
</div>

<!-- ══ FILTRES ═════════════════════════════════════════════════ -->
<form method="GET" action="index.php" class="tickets-filters">
    <input type="hidden" name="p"    value="listTicketsView">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($filters['sort']) ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($filters['dir'])  ?>">

    <label class="filter-input">
        <svg class="filter-icon" width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="9" r="6"/><path d="M15 15l3 3"/>
        </svg>
        <input
            type="text"
            name="search"
            placeholder="Rechercher (client, n° ticket…)"
            value="<?= htmlspecialchars($filters['search']) ?>"
        >
    </label>

    <label class="filter-input">
        <svg class="filter-icon" width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="10" cy="10" r="7"/><path d="M10 7v3l2 2"/>
        </svg>
        <select name="statut">
            <option value="">Tous les statuts</option>
            <?php foreach ($statutOptions as $id => $s) : ?>
                <option value="<?= $id ?>" <?= ((string)$filters['statut'] === (string)$id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="filter-input">
        <svg class="filter-icon" width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="14" height="14" rx="2"/><path d="M7 7h6M7 10h4"/>
        </svg>
        <select name="type">
            <option value="">Tous les types</option>
            <?php foreach ($typeOptions as $t) : ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= ($filters['type'] === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="filter-input">
        <svg class="filter-icon" width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 6h14M6 10h8M9 14h2"/>
        </svg>
        <select name="priorite">
            <option value="">Toutes les priorités</option>
            <?php foreach ($prioriteOptions as $id => $label) : ?>
                <option value="<?= $id ?>" <?= ((string)$filters['priorite'] === (string)$id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <button type="submit" class="btn btn--primary btn--sm">Filtrer</button>
    <a href="index.php?p=listTicketsView" class="btn btn--ghost btn--sm">Réinitialiser</a>
</form>

<!-- ══ TABLEAU ═════════════════════════════════════════════════ -->
<div class="card" style="overflow:visible">
    <div class="table-wrapper">
        <table class="tickets-table">
            <thead>
                <tr>
                    <th><?= sortLink('IDTicket',  '#',          $filters['sort'], $filters['dir']) ?></th>
                    <th><?= sortLink('Type',      'Type',       $filters['sort'], $filters['dir']) ?></th>
                    <th><?= sortLink('Priorite',  'Priorité',   $filters['sort'], $filters['dir']) ?></th>
                    <th><?= sortLink('Statut',    'Statut',     $filters['sort'], $filters['dir']) ?></th>
                    <th><?= sortLink('NomClient', 'Client',     $filters['sort'], $filters['dir']) ?></th>
                    <th><?= sortLink('DateInsert','Date',       $filters['sort'], $filters['dir']) ?></th>
                    <th>Technicien</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($tickets)) : ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state__icon">🎫</div>
                            <div class="empty-state__title">Aucun ticket trouvé</div>
                            <div class="empty-state__sub">Essaie d'ajuster les filtres ou crée un nouveau ticket.</div>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($tickets as $t) :
                    $statutCls = statutClass((int) $t['Statut']);
                    $statutLbl = $statutOptions[$t['Statut']]['label'] ?? '?';
                    $prioCls   = 'prio-' . min(5, max(1, (int) $t['Priorite']));
                    $prioLbl   = $prioriteOptions[$t['Priorite']] ?? '?';
                    $date      = $t['DateInsert']
                                    ? (new DateTime($t['DateInsert']))->format('d/m/Y H:i')
                                    : '—';
                    $tech      = $t['technicien'] ?? '—';
                    $init      = $tech !== '—' ? initiales($tech) : '?';
                    $detailUrl = htmlspecialchars(buildUrl(['p' => 'ticketDetailView', 'id' => $t['IDTicket']]));
                ?>
                <tr onclick="window.location='<?= $detailUrl ?>'">
                    <td><span class="col-id">#<?= (int) $t['IDTicket'] ?></span></td>
                    <td><?= htmlspecialchars($t['Type']) ?></td>
                    <td><span class="badge badge--<?= $prioCls ?>"><?= htmlspecialchars($prioLbl) ?></span></td>
                    <td><span class="badge badge--<?= $statutCls ?>"><?= htmlspecialchars($statutLbl) ?></span></td>
                    <td><span class="col-client"><?= htmlspecialchars($t['NomClient']) ?></span></td>
                    <td><span class="col-date"><?= $date ?></span></td>
                    <td>
                        <div class="col-tech">
                            <div class="col-tech-avatar"><?= $init ?></div>
                            <?= htmlspecialchars($tech) ?>
                        </div>
                    </td>
                    <td>
                        <a href="<?= $detailUrl ?>" class="btn btn--ghost btn--sm row-action"
                            onclick="event.stopPropagation()">
                            Voir →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ══ PAGINATION ══════════════════════════════════════════ -->
    <?php if ($totalPages > 1) : ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= (($page - 1) * 20) + 1 ?>–<?= min($page * 20, $total) ?> sur <?= $total ?> tickets
        </span>

        <div class="pagination__pages">
            <!-- Précédent -->
            <?php if ($page > 1) : ?>
                <a href="<?= htmlspecialchars(buildUrl(['page' => $page - 1])) ?>"
                    class="pagination__btn pagination__btn--prev">
                    ← Préc.
                </a>
            <?php else : ?>
                <span class="pagination__btn pagination__btn--prev disabled">← Préc.</span>
            <?php endif; ?>

            <!-- Pages numérotées -->
            <?php
            $range = 2;
            for ($i = 1; $i <= $totalPages; $i++) :
                if ($i === 1 || $i === $totalPages || abs($i - $page) <= $range) :
            ?>
                <?php if ($i === $page) : ?>
                    <span class="pagination__btn current"><?= $i ?></span>
                <?php else : ?>
                    <a href="<?= htmlspecialchars(buildUrl(['page' => $i])) ?>"
                        class="pagination__btn"><?= $i ?></a>
                <?php endif; ?>
            <?php
                elseif (abs($i - $page) === $range + 1) :
                    echo '<span class="pagination__btn disabled">…</span>';
                endif;
            endfor;
            ?>

            <!-- Suivant -->
            <?php if ($page < $totalPages) : ?>
                <a href="<?= htmlspecialchars(buildUrl(['page' => $page + 1])) ?>"
                    class="pagination__btn pagination__btn--next">
                    Suiv. →
                </a>
            <?php else : ?>
                <span class="pagination__btn pagination__btn--next disabled">Suiv. →</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title   = 'Tickets';
require('views/layout/baseLayout.php');
?>