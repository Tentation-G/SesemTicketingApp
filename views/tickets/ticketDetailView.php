<?php ob_start();

function initiales(string $name): string {
    $p = array_filter(explode(' ', $name));
    return count($p) >= 2
        ? strtoupper(mb_substr(reset($p), 0, 1) . mb_substr(end($p), 0, 1))
        : strtoupper(mb_substr($name, 0, 2));
}

$statutOptions   ??= [];
$prioriteOptions ??= [];
$role      = (int)($_SESSION['user']['role'] ?? 3);
$selfId    = (int)($_SESSION['user']['id']   ?? 0);

$statutId   = (int)$ticket['Statut'];
$prioriteId = (int)$ticket['Priorite'];
$statutInfo = $statutOptions[$statutId]     ?? ['label' => '?', 'color' => '#666'];
$prioLabel  = $prioriteOptions[$prioriteId] ?? '?';
$prioCls    = 'prio-' . min(5, max(1, $prioriteId));
$isClosed   = $statutId === 5;

// Créateur du ticket
$createurNom = $ticket['createurNom'] ?? ($ticket['technicienNom'] ?? '—');

// Assigné
$assigneNom  = $ticket['technicienNom'] ?? '—';
?>

<!-- ══ EN-TÊTE ════════════════════════════════════════════════ -->
<div class="page-header">
    <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
            <a href="index.php?p=listTicketsView" class="detail-back">← Tickets</a>
            <span style="color:var(--c-muted)">/</span>
            <span style="font-family:'Syne',sans-serif;font-size:13px;color:var(--c-muted)">#<?= (int)$ticket['IDTicket'] ?></span>
        </div>

        <!-- Titre + créateur -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div>
                <h1 class="page-header__title"><?= htmlspecialchars($ticket['NomClient']) ?></h1>
                <div style="display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap">
                    <span class="badge badge--<?= $prioCls ?>"><?= htmlspecialchars($prioLabel) ?></span>
                    <span class="badge" style="background:<?= htmlspecialchars($statutInfo['color']) ?>22;color:<?= htmlspecialchars($statutInfo['color']) ?>;border:1px solid <?= htmlspecialchars($statutInfo['color']) ?>44">
                        <?= htmlspecialchars($statutInfo['label']) ?>
                    </span>
                    <span style="font-size:12px;color:var(--c-muted)">
                        Créé le <?= (new DateTime($ticket['DateInsert']))->format('d/m/Y à H:i') ?>
                        par <strong style="color:var(--c-secondary)"><?= htmlspecialchars($createurNom) ?></strong>
                    </span>
                </div>
            </div>

            <!-- Assigné a -->
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
                <span style="font-size:10.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--c-muted)">Assigné à</span>
                <?php if ($assigneNom !== '—') : $initA = initiales($assigneNom); ?>
                <div style="display:flex;align-items:center;gap:7px">
                    <div class="col-tech-avatar" style="width:28px;height:28px;font-size:10px"><?= $initA ?></div>
                    <span style="font-size:13px;font-weight:500;color:var(--c-primary)"><?= htmlspecialchars($assigneNom) ?></span>
                </div>
                <?php else : ?>
                <span style="font-size:13px;color:var(--c-muted)">Non assigné</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Boutons admin -->
    <?php if ($role <= 2) : ?>
    <div style="display:flex;gap:8px;flex-shrink:0;align-self:flex-start;margin-top:4px">
        <?php if ($role === 1) : ?>
        <form method="post" action="index.php?p=deleteTicketAction"
            onsubmit="return confirm('Supprimer définitivement ce ticket ?')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="idTicket"   value="<?= (int)$ticket['IDTicket'] ?>">
            <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
        </form>
        <?php endif; ?>
        <?php if (!$isClosed) : ?>
        <form method="post" action="index.php?p=cloturerTicketAction">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="idTicket"   value="<?= (int)$ticket['IDTicket'] ?>">
            <button type="submit" class="btn btn--ghost btn--sm"
                    onclick="return confirm('Clôturer ce ticket ?')">Clôturer</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Bandeau succès -->
<?php if (!empty($success)) :
    $msgs = [
        'statut'  => 'Statut mis à jour.',
        'assign'  => 'Technicien assigné.',
        'comment' => 'Commentaire ajouté.',
        'edited'  => 'Commentaire modifié.',
        'cloture' => 'Ticket clôturé.',
    ];
    $msg = $msgs[$success] ?? '';
    if ($msg) : ?>
<div class="alert alert--success" style="margin-bottom:20px">
    <span class="alert__icon">✓</span> <?= htmlspecialchars($msg) ?>
</div>
    <?php endif;
endif; ?>

<!-- ══ GRILLE PRINCIPALE ══════════════════════════════════════ -->
<div class="detail-grid">

    <!-- ── Colonne gauche ────────────────────────────────────── -->
    <div class="detail-main">

        <!-- ── Ligne 1 : Client + Localisation ──────────────── -->
        <div style="display:grid;grid-template-columns:1fr 3fr;gap:16px; margin-bottom:16px">

            <div class="form-card" style="margin-bottom:0">
                <div class="form-section"><div class="form-section__title">Client</div></div>
                <div class="detail-fields" style="grid-template-columns:1fr">
                    <div class="detail-field"><span class="detail-field__label">Nom</span><span class="detail-field__value"><?= htmlspecialchars($ticket['NomClient']) ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">Type</span><span class="detail-field__value"><?= htmlspecialchars($ticket['typeClientLabel'] ?? '—') ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">N° contrat</span><span class="detail-field__value"><?= htmlspecialchars($ticket['NumContrat'] ?: '—') ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">N° site</span><span class="detail-field__value"><?= htmlspecialchars($ticket['NumSite'] ?: '—') ?></span></div>
                </div>
            </div>

            <div class="form-card" style="margin-bottom:0">
                <div class="form-section"><div class="form-section__title">Localisation</div></div>
                <div class="detail-fields" style="grid-template-columns:1fr">
                    <div class="detail-field"><span class="detail-field__label">Adresse</span><span class="detail-field__value"><?= htmlspecialchars($ticket['Adresse']) ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">Code postal</span><span class="detail-field__value"><?= htmlspecialchars($ticket['CodePostal']) ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">Ville</span><span class="detail-field__value"><?= htmlspecialchars($ticket['Ville']) ?></span></div>
                    <?php if ($ticket['AdresseChantier']) : ?>
                    <div class="detail-field"><span class="detail-field__label">Chantier</span><span class="detail-field__value"><?= nl2br(htmlspecialchars($ticket['AdresseChantier'])) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- ── Ligne 2 : Matériel + Informations ────────────── -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

            <div class="form-card" style="margin-bottom:0">
                <div class="form-section"><div class="form-section__title">Matériel</div></div>
                <div class="detail-fields" style="grid-template-columns:1fr">
                    <div class="detail-field"><span class="detail-field__label">Objet</span><span class="detail-field__value"><?= htmlspecialchars($ticket['objetLabel'] ?? '—') ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">Marque</span><span class="detail-field__value"><?= htmlspecialchars($ticket['marqueLabel'] ?? '—') ?></span></div>
                    <div class="detail-field"><span class="detail-field__label">Famille</span><span class="detail-field__value"><?= htmlspecialchars($ticket['familleLabel'] ?? '—') ?></span></div>
                </div>
            </div>

            <div class="form-card" style="margin-bottom:0">
                <div class="form-section"><div class="form-section__title">Informations</div></div>
                <div class="meta-list">
                    <div class="meta-item"><span class="meta-item__label">Type</span><span class="meta-item__value"><?= htmlspecialchars($ticket['Type']) ?></span></div>
                    <div class="meta-item"><span class="meta-item__label">Équipe</span><span class="meta-item__value"><?= htmlspecialchars($ticket['equipeLabel']  ?? '—') ?></span></div>
                    <div class="meta-item"><span class="meta-item__label">Service</span><span class="meta-item__value"><?= htmlspecialchars($ticket['serviceLabel'] ?? '—') ?></span></div>
                    <div class="meta-item"><span class="meta-item__label">Créé</span><span class="meta-item__value"><?= (new DateTime($ticket['DateInsert']))->format('d/m/Y') ?></span></div>
                    <div class="meta-item"><span class="meta-item__label">Modifié</span><span class="meta-item__value"><?= (new DateTime($ticket['DateUpdate']))->format('d/m/Y') ?></span></div>
                    <?php if ($ticket['DateCloture']) : ?>
                    <div class="meta-item"><span class="meta-item__label">Clôturé</span><span class="meta-item__value"><?= (new DateTime($ticket['DateCloture']))->format('d/m/Y') ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Description initiale -->
        <?php if ($ticket['Commentaire']) : ?>
        <div class="form-card">
            <div class="form-section"><div class="form-section__title">Description initiale</div></div>
            <p style="font-size:13.5px;color:var(--c-secondary);line-height:1.7;white-space:pre-line"><?= htmlspecialchars($ticket['Commentaire']) ?></p>
        </div>
        <?php endif; ?>

        <!-- ── Fil de commentaires ──────────────────────────── -->
        <div id="commentaires">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                <h2 style="font-family:'Syne',sans-serif;font-size:16px;font-weight:600;color:var(--c-primary)">
                    Fil de suivi
                    <span style="font-size:13px;font-weight:400;color:var(--c-muted);margin-left:6px"><?= count($commentaires) ?></span>
                </h2>
            </div>

            <!-- Liste avec hauteur limitée + scroll -->
            <div class="comments-scroll">
                <?php if (empty($commentaires)) : ?>
                <div class="empty-state" style="padding:32px 20px">
                    <div class="empty-state__icon">💬</div>
                    <div class="empty-state__title">Aucun commentaire</div>
                </div>
                <?php else : ?>
                    <?php
                    $total_c = count($commentaires);
                    foreach ($commentaires as $ci => $c) :
                        $init    = initiales($c['auteurNom']);
                        $isNote  = (bool)$c['IsNote'];
                        $dateStr = (new DateTime($c['DateInsert']))->format('d/m/Y H:i');
                        $isLast  = $ci === $total_c - 1;
                        $isOwn   = (int)$c['IDUser'] === $selfId;
                        // Modifiable seulement si c'est le dernier ET que c'est l'auteur
                        $canEdit = $isOwn && $isLast;
                    ?>
                    <div class="comment <?= $isNote ? 'comment--note' : '' ?>" id="comment-<?= (int)$c['IDCommentaire'] ?>">
                        <div class="comment__avatar"><?= $init ?></div>
                        <div class="comment__body">
                            <div class="comment__header">
                                <span class="comment__author"><?= htmlspecialchars($c['auteurNom']) ?></span>
                                <?php if ($isNote) : ?>
                                <span class="comment__note-badge">Note interne</span>
                                <?php endif; ?>
                                <span class="comment__date"><?= $dateStr ?></span>
                                <?php if ($canEdit) : ?>
                                <button class="btn btn--ghost btn--sm" style="margin-left:auto;padding:2px 8px;font-size:11px"
                                        onclick="toggleEditComment(<?= (int)$c['IDCommentaire'] ?>, this)">
                                    Modifier
                                </button>
                                <?php endif; ?>
                            </div>

                            <!-- Texte affiché -->
                            <p class="comment__text" id="text-<?= (int)$c['IDCommentaire'] ?>">
                                <?= nl2br(htmlspecialchars($c['Contenu'])) ?>
                            </p>

                            <!-- Formulaire d'édition (caché par défaut) -->
                            <?php if ($canEdit) : ?>
                            <form method="post" action="index.php?p=editCommentaireAction"
                                id="edit-form-<?= (int)$c['IDCommentaire'] ?>"
                                style="display:none;margin-top:10px">
                                <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="idCommentaire"  value="<?= (int)$c['IDCommentaire'] ?>">
                                <input type="hidden" name="idTicket"       value="<?= (int)$ticket['IDTicket'] ?>">
                                <textarea name="contenu" class="form-control" style="min-height:80px"
                                        required><?= htmlspecialchars($c['Contenu']) ?></textarea>
                                <div style="display:flex;gap:8px;margin-top:8px;justify-content:flex-end">
                                    <button type="button" class="btn btn--ghost btn--sm"
                                            onclick="toggleEditComment(<?= (int)$c['IDCommentaire'] ?>, null)">
                                        Annuler
                                    </button>
                                    <button type="submit" class="btn btn--primary btn--sm">Enregistrer</button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div><!-- /comments-scroll -->

            <!-- Formulaire nouveau commentaire -->
            <form method="post" action="index.php?p=addCommentaireAction" class="comment-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="idTicket"   value="<?= (int)$ticket['IDTicket'] ?>">
                <div class="form-group">
                    <textarea name="contenu" class="form-control" style="min-height:90px"
                            placeholder="Ajouter un commentaire ou une note de suivi…" required></textarea>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:10px;flex-wrap:wrap">
                    <?php if ($role <= 2) : ?>
                    <label class="toggle-label">
                        <input type="checkbox" name="isNote" value="1" class="toggle-input">
                        <span class="toggle-track"></span>
                        <span style="font-size:12.5px;color:var(--c-secondary)">Note interne</span>
                    </label>
                    <?php endif; ?>
                    <button type="submit" class="btn btn--primary btn--sm" style="margin-left:auto">
                        Publier
                    </button>
                </div>
            </form>

        </div><!-- /commentaires -->
    </div><!-- /detail-main -->

    <!-- ── Colonne droite ────────────────────────────────────── -->
    <aside class="detail-sidebar">

        <!-- Statut — toujours visible, même si clôturé -->
        <div class="form-card">
            <div class="form-section"><div class="form-section__title">Statut</div></div>
            <form method="post" action="index.php?p=updateStatut">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="idTicket"   value="<?= (int)$ticket['IDTicket'] ?>">
                <select name="statut" class="form-control" style="margin-bottom:10px">
                    <?php foreach ($statutOptions as $sid => $s) : ?>
                    <option value="<?= $sid ?>" <?= $statutId === $sid ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <!-- Bouton explicite au lieu de onchange -->
                <button type="submit" class="btn btn--primary btn--sm" style="width:100%">
                    Mettre à jour le statut
                </button>
            </form>
        </div>

        <!-- Assignation — toujours visible pour admin/manager -->
        <?php if ($role <= 2) : ?>
        <div class="form-card">
            <div class="form-section"><div class="form-section__title">Technicien</div></div>
            <form method="post" action="index.php?p=assignTechnicien">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="idTicket"   value="<?= (int)$ticket['IDTicket'] ?>">
                <select name="idUser" class="form-control" style="margin-bottom:10px">
                    <option value="">— Non assigné —</option>
                    <?php foreach ($techniciens as $t) : ?>
                    <option value="<?= (int)$t['id'] ?>" <?= (int)$ticket['IDUser'] === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn--ghost btn--sm" style="width:100%">
                    Assigner
                </button>
            </form>
        </div>
        <?php endif; ?>

    </aside>
</div><!-- /detail-grid -->

<script>
function toggleEditComment(id, btn) {
    const form = document.getElementById('edit-form-' + id);
    const text = document.getElementById('text-' + id);
    const isHidden = form.style.display === 'none';

    form.style.display = isHidden ? 'block' : 'none';
    text.style.display = isHidden ? 'none'  : 'block';

    if (btn) btn.textContent = isHidden ? 'Annuler' : 'Modifier';
}
</script>

<?php
$content = ob_get_clean();
$title   = 'Ticket #' . (int)$ticket['IDTicket'];
require_once('views/layout/baseLayout.php');
?>