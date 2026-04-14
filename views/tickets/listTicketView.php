<?php ob_start();

// Helpers pour conserver les paramètres GET dans les liens de tri / pagination
function buildUrl(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    return 'index.php?' . http_build_query($params);
}

function sortLink(string $col, string $label, string $currentSort, string $currentDir): string
{
    $newDir = ($currentSort === $col && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $arrow  = '';
    if ($currentSort === $col) {
        $arrow = $currentDir === 'ASC' ? ' ▲' : ' ▼';
    }
    $url = htmlspecialchars(buildUrl(['sort' => $col, 'dir' => $newDir, 'page' => 1]));
    return "<a href=\"$url\" class=\"sort-link\">$label<span class=\"sort-arrow\">$arrow</span></a>";
}
?>

<style>
    .tickets-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.2rem;
    }
    .btn-primary {
        background: #1976D2;
        color: #fff;
        padding: .5rem 1.1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: .9rem;
    }
    .btn-primary:hover { background: #1565C0; }

    /* Filtres */
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-bottom: 1.2rem;
        background: #f5f5f5;
        padding: .8rem 1rem;
        border-radius: 8px;
    }
    .filters input,
    .filters select {
        padding: .4rem .7rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: .88rem;
    }
    .filters input { min-width: 180px; }
    .btn-filter {
        background: #1976D2;
        color: #fff;
        border: none;
        padding: .4rem .9rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: .88rem;
    }
    .btn-reset {
        background: #e0e0e0;
        color: #333;
        border: none;
        padding: .4rem .9rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: .88rem;
        text-decoration: none;
    }

    /* Tableau */
    .table-wrapper { overflow-x: auto; }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }
    th {
        background: #1976D2;
        color: #fff;
        padding: .7rem 1rem;
        text-align: left;
        white-space: nowrap;
    }
    td {
        padding: .65rem 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f0f7ff; }

    .sort-link { color: #fff; text-decoration: none; }
    .sort-link:hover { text-decoration: underline; }
    .sort-arrow { margin-left: 4px; font-size: .75rem; }

    /* Badges */
    .badge {
        display: inline-block;
        padding: .25rem .65rem;
        border-radius: 20px;
        font-size: .78rem;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
    }

    /* Badges priorité */
    .prio-1 { background: #78909C; }
    .prio-2 { background: #4DB6AC; }
    .prio-3 { background: #FFA726; }
    .prio-4 { background: #EF5350; }
    .prio-5 { background: #B71C1C; }

    /* Lien détail */
    .btn-detail {
        background: #e3f2fd;
        color: #1565C0;
        border: 1px solid #90CAF9;
        padding: .3rem .7rem;
        border-radius: 5px;
        font-size: .82rem;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-detail:hover { background: #bbdefb; }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: .4rem;
        margin-top: 1.2rem;
        flex-wrap: wrap;
    }
    .pagination a, .pagination span {
        padding: .35rem .75rem;
        border-radius: 5px;
        font-size: .88rem;
        border: 1px solid #ccc;
        text-decoration: none;
        color: #1976D2;
        background: #fff;
    }
    .pagination span.current {
        background: #1976D2;
        color: #fff;
        border-color: #1976D2;
        font-weight: 700;
    }
    .pagination a:hover { background: #e3f2fd; }
    .pagination span.disabled { color: #bbb; pointer-events: none; }

    .total-info {
        text-align: right;
        font-size: .83rem;
        color: #666;
        margin-bottom: .5rem;
    }
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #888;
        font-style: italic;
    }
</style>

<div class="tickets-header">
    <h1>Tickets</h1>
    <a href="index.php?p=addTicketView" class="btn-primary">+ Nouveau ticket</a>
</div>

<!-- Filtres -->
<form method="GET" action="index.php" class="filters">
    <input type="hidden" name="p"    value="listTicketsView">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($filters['sort']) ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($filters['dir'])  ?>">

    <input
        type="text"
        name="search"
        placeholder="Rechercher (client, n° ticket…)"
        value="<?= htmlspecialchars($filters['search']) ?>"
    >

    <select name="statut">
        <option value="">Tous les statuts</option>
        <?php foreach ($statutOptions as $id => $s) : ?>
            <option value="<?= $id ?>" <?= ((string)$filters['statut'] === (string)$id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="type">
        <option value="">Tous les types</option>
        <?php foreach ($typeOptions as $t) : ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= ($filters['type'] === $t) ? 'selected' : '' ?>>
                <?= htmlspecialchars($t) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="priorite">
        <option value="">Toutes les priorités</option>
        <?php foreach ($prioriteOptions as $id => $label) : ?>
            <option value="<?= $id ?>" <?= ((string)$filters['priorite'] === (string)$id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn-filter">Filtrer</button>
    <a href="index.php?p=listTicketsView" class="btn-reset">Réinitialiser</a>
</form>

<!-- Total -->
<p class="total-info">
    <?= $total ?> ticket<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
</p>

<!-- Tableau -->
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th><?= sortLink('IDTicket',  '#',           $filters['sort'], $filters['dir']) ?></th>
                <th><?= sortLink('Type',      'Type',        $filters['sort'], $filters['dir']) ?></th>
                <th><?= sortLink('Priorite',  'Priorité',    $filters['sort'], $filters['dir']) ?></th>
                <th><?= sortLink('Statut',    'Statut',      $filters['sort'], $filters['dir']) ?></th>
                <th><?= sortLink('NomClient', 'Client',      $filters['sort'], $filters['dir']) ?></th>
                <th><?= sortLink('DateInsert','Date',        $filters['sort'], $filters['dir']) ?></th>
                <th>Technicien</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($tickets)) : ?>
            <tr>
                <td colspan="8" class="empty-state">Aucun ticket trouvé.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($tickets as $t) :
                $statut   = $statutOptions[$t['Statut']]   ?? ['label' => '?', 'color' => '#999'];
                $prioClass = 'prio-' . min(5, max(1, (int) $t['Priorite']));
                $prioLabel = $prioriteOptions[$t['Priorite']] ?? '?';
                $date      = $t['DateInsert']
                                ? (new DateTime($t['DateInsert']))->format('d/m/Y H:i')
                                : '—';
            ?>
            <tr>
                <td><strong>#<?= (int) $t['IDTicket'] ?></strong></td>
                <td><?= htmlspecialchars($t['Type']) ?></td>
                <td>
                    <span class="badge <?= $prioClass ?>">
                        <?= htmlspecialchars($prioLabel) ?>
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:<?= htmlspecialchars($statut['color']) ?>">
                        <?= htmlspecialchars($statut['label']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($t['NomClient']) ?></td>
                <td><?= $date ?></td>
                <td><?= htmlspecialchars($t['technicien'] ?? '—') ?></td>
                <td>
                    <a href="<?= htmlspecialchars(buildUrl(['p' => 'ticketDetailView', 'id' => $t['IDTicket']])) ?>"
                        class="btn-detail">
                        Voir →
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1) : ?>
<nav class="pagination">
    <?php if ($page > 1) : ?>
        <a href="<?= htmlspecialchars(buildUrl(['page' => $page - 1])) ?>">‹ Précédent</a>
    <?php else : ?>
        <span class="disabled">‹ Précédent</span>
    <?php endif; ?>

    <?php
    $range = 2;
    for ($i = 1; $i <= $totalPages; $i++) :
        if ($i === 1 || $i === $totalPages || abs($i - $page) <= $range) :
    ?>
        <?php if ($i === $page) : ?>
            <span class="current"><?= $i ?></span>
        <?php else : ?>
            <a href="<?= htmlspecialchars(buildUrl(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php
        elseif (abs($i - $page) === $range + 1) :
            echo '<span class="disabled">…</span>';
        endif;
    endfor;
    ?>

    <?php if ($page < $totalPages) : ?>
        <a href="<?= htmlspecialchars(buildUrl(['page' => $page + 1])) ?>">Suivant ›</a>
    <?php else : ?>
        <span class="disabled">Suivant ›</span>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title   = "Tickets";
require('views/layout/baseLayout.php');
?>