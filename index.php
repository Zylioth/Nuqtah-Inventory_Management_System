<?php
session_start();
$nq_logged_in = isset($_SESSION['user_id']);
$nq_name = $nq_logged_in ? htmlspecialchars($_SESSION['full_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuqtah Inventory System</title>
    <link rel="icon" type="image/png" href="/Nuqtah_IT/assets/img/Nuqtah_logo_small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/styles.css">

    <style>
    /* ---------- Due-date slip: page tokens ---------- */
    .nq-page {
        --paper: #E4D6B8;
        --paper-deep: #D8C7A3;
        --card: #F2E8D4;
        --ink: #1C1A17;
        --ink-soft: #4A443A;
        --teal: #00796B;
        --deep: #004D40;
        --stamp: #4B3A6B;
        --alert: #A8332A;
        --rule: rgba(28, 26, 23, .22);
        background-color: var(--paper);
        color: var(--ink);
        font-family: 'Courier Prime', ui-monospace, "Courier New", monospace;
        overflow-x: hidden;
        caret-color: var(--teal);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23n)' opacity='.16'/%3E%3C/svg%3E");
    }

    .nq-page ::selection { background: var(--teal); color: var(--card); }
    .nq-page :focus-visible { outline: 2px solid var(--deep); outline-offset: 3px; }
    .nq-page .nq-bar :focus-visible { outline-color: #FFFFFF; }
    .nq-page { scrollbar-color: var(--teal) var(--paper-deep); }

    .nq-skip {
        position: absolute; left: 16px; top: -60px; z-index: 60;
        background: var(--card); color: var(--deep); padding: 10px 14px;
        font-weight: 700; text-decoration: none; border: 1px solid var(--deep);
    }
    .nq-skip:focus { top: 12px; }

    .nq-wrap { width: min(1240px, 100% - 48px); margin-inline: auto; }

    /* ---------- Bar ---------- */
    .nq-bar { background: var(--teal); border-bottom: 3px solid var(--deep); }
    .nq-bar__in {
        display: flex; align-items: center; gap: 20px;
        min-height: 64px; padding-block: 10px;
    }
    .nq-brand { display: inline-flex; align-items: center; text-decoration: none; }
    .nq-brand img { height: 30px; width: auto; display: block; }
    .nq-bar__org {
        margin: 0; padding-left: 16px; border-left: 1px solid rgba(255, 255, 255, .45);
        color: #EAF3F1; font-size: .72rem; letter-spacing: .14em; text-transform: uppercase;
        line-height: 1.3;
    }
    .nq-bar__nav { margin-left: auto; display: flex; align-items: center; gap: 18px; }
    .nq-bar__who { color: #EAF3F1; font-size: .8rem; margin: 0; max-width: 26ch; }
    .nq-bar__who strong { color: #fff; }
    .nq-link {
        color: #fff; text-decoration: none; font-size: .85rem;
        border-bottom: 1px solid rgba(255, 255, 255, .5); padding-bottom: 2px;
    }
    .nq-link:hover { border-bottom-color: #fff; color: #fff; }
    .nq-btn {
        display: inline-flex; align-items: center; justify-content: center;
        font-family: 'Archivo Black', 'Arial Black', sans-serif;
        text-transform: uppercase; letter-spacing: .1em; line-height: 1;
        text-decoration: none; border: 2px solid transparent;
        padding: 12px 18px; transition: background-color .18s ease, color .18s ease, border-color .18s ease;
    }
    .nq-btn--paper { background: var(--card); color: var(--deep); border-color: var(--card); font-size: .72rem; }
    .nq-btn--paper:hover { background: transparent; color: var(--card); border-color: var(--card); }
    .nq-btn--stamp {
        background: var(--teal); color: #F7F1E3; border-color: var(--deep);
        border-bottom-width: 4px; font-size: .84rem; padding: 17px 26px;
    }
    .nq-btn--stamp:hover { background: var(--deep); color: #F7F1E3; }

    /* ---------- Card cover ---------- */
    .nq-cover { position: relative; padding-bottom: clamp(18px, 2vw, 24px); }
    .nq-cover::before {
        content: ""; position: absolute; inset: 0 0 auto 0; height: 15px;
        background-image: radial-gradient(circle 7px at 18px 0, var(--teal) 7px, transparent 7.5px);
        background-size: 56px 15px; background-repeat: repeat-x; opacity: .9;
    }
    .nq-formno {
        position: absolute; bottom: 10px; right: max(24px, calc((100% - 1240px) / 2));
        margin: 0; font-size: .68rem; letter-spacing: .16em; text-transform: uppercase;
        color: var(--deep); opacity: .85;
    }
    .nq-cover__copy { min-width: 0; }
    .nq-cover__body {
        display: grid; grid-template-columns: 1.18fr .92fr;
        gap: clamp(28px, 4.5vw, 64px); align-items: start;
        padding-block: clamp(40px, 4vw, 52px) clamp(14px, 1.6vw, 20px);
    }
    .nq-title {
        font-size: clamp(2.45rem, 5.1vw, 4.35rem);
        line-height: .98; letter-spacing: -.015em; margin: 0 0 22px;
        font-weight: 700; text-wrap: balance;
    }
    .nq-title em { font-style: normal; color: var(--deep); }
    .nq-lede {
        font-size: 1.03rem; line-height: 1.6; max-width: 54ch; margin: 0 0 30px;
        color: var(--ink-soft);
    }
    .nq-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 18px 26px; }
    .nq-actions__alt {
        color: var(--deep); font-size: .86rem; text-decoration: none;
        border-bottom: 2px solid rgba(0, 77, 64, .4); padding-bottom: 2px;
        background: none;
    }
    .nq-actions__alt:hover { border-bottom-color: var(--deep); }
    .nq-note {
        margin: 26px 0 0; font-size: .72rem; letter-spacing: .04em; color: var(--ink-soft);
        border-top: 1px dashed var(--rule); padding-top: 12px; max-width: 46ch;
    }

    .nq-window { margin: 0; position: relative; min-width: 0; background: var(--card); padding: 10px; border: 1px solid var(--rule); }
    .nq-window__media { position: relative; overflow: hidden; aspect-ratio: 4 / 4.1; background: #0E0C0A; }
    .nq-window__media video {
        position: absolute; inset: 0; width: 100%; height: 100%;
        object-fit: cover; display: block;
        filter: sepia(.22) saturate(.82) contrast(1.04);
    }
    .nq-window figcaption {
        display: flex; gap: 10px; align-items: baseline;
        padding: 10px 2px 2px; font-size: .72rem; line-height: 1.45; color: var(--ink-soft);
    }
    .nq-fig { color: var(--deep); letter-spacing: .1em; font-weight: 700; white-space: nowrap; }
    .nq-stamp {
        display: inline-block; font-family: 'Archivo Black', 'Arial Black', sans-serif;
        text-transform: uppercase; color: var(--stamp);
        border: 3px solid currentColor; padding: 7px 11px; letter-spacing: .1em;
        font-size: .78rem; line-height: 1; background: rgba(242, 232, 212, .55);
        transform: rotate(-3.5deg);
    }
    .nq-window__media .nq-stamp { position: absolute; left: 14px; bottom: 14px; z-index: 2; background: var(--card); }

    /* ---------- Perforated rule ---------- */
    .nq-perf {
        border: 0; border-top: 2px dashed rgba(0, 77, 64, .55);
        margin: 0; height: 0; opacity: 1;
    }

    /* ---------- Section frame ---------- */
    .nq-sec { padding-block: clamp(40px, 5.2vw, 64px); }
    .nq-sec__head { display: flex; flex-wrap: wrap; align-items: end; gap: 10px 28px; margin-bottom: 30px; }
    .nq-sec__head h2 {
        font-size: clamp(1.7rem, 2.6vw, 2.35rem); line-height: 1; margin: 0;
        letter-spacing: -.01em; font-weight: 700;
    }
    .nq-sec__head p { margin: 0 0 4px; font-size: .86rem; color: var(--ink-soft); max-width: 52ch; }

    /* ---------- Date-due table ---------- */
    .nq-due { background: var(--card); border-block: 1px solid var(--rule); }
    .nq-table { width: 100%; border-collapse: collapse; text-align: left; }
    .nq-table thead th {
        font-size: .66rem; letter-spacing: .16em; text-transform: uppercase;
        font-weight: 700; color: var(--deep); padding: 0 14px 10px;
        border-bottom: 1px solid var(--rule);
    }
    .nq-table thead th:first-child { padding-left: 0; }
    .nq-table tbody tr { border-bottom: 1px solid var(--rule); transition: background-color .18s ease; }
    .nq-table tbody tr:hover { background: rgba(0, 121, 107, .07); }
    .nq-table tbody tr:last-child { border-bottom: 0; }
    .nq-table td, .nq-table tbody th { padding: 20px 14px; vertical-align: top; }
    .nq-table td:first-child, .nq-table tbody th:first-child { padding-left: 0; }
    .nq-num {
        font-family: 'Archivo Black', 'Arial Black', sans-serif;
        color: var(--stamp); font-size: .9rem; letter-spacing: .06em;
        white-space: nowrap; width: 90px;
    }
    .nq-num span {
        display: inline-block; border: 2px dashed currentColor; padding: 8px 10px;
        opacity: .9;
    }
    .nq-table tbody th[scope="row"] {
        font-size: 1.02rem; letter-spacing: .06em; text-transform: uppercase;
        width: 190px; white-space: nowrap;
    }
    .nq-table tbody td:last-child { font-size: .9rem; line-height: 1.6; color: var(--ink-soft); max-width: 58ch; }

    .nq-anim .nq-num span { opacity: 0; transform: scale(1.38); filter: blur(4px); }
    .nq-anim .nq-row.is-stamped .nq-num span { animation: nq-hit .44s cubic-bezier(.2, .9, .25, 1) forwards; }
    @keyframes nq-hit {
        0% { opacity: 0; transform: scale(1.38); filter: blur(4px); }
        62% { opacity: 1; }
        100% { opacity: .9; transform: scale(1); filter: blur(0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .nq-anim .nq-num span { opacity: .9; transform: none; filter: none; }
        .nq-anim .nq-row.is-stamped .nq-num span { animation: none; }
        .nq-btn, .nq-table tbody tr { transition: none; }
    }

    /* ---------- Specimen slip ---------- */
    .nq-specimen { display: grid; grid-template-columns: 1fr .82fr; gap: clamp(30px, 5vw, 72px); align-items: center; }
    .nq-specimen h2 { font-size: clamp(1.7rem, 2.6vw, 2.35rem); line-height: 1.02; margin: 0 0 18px; letter-spacing: -.01em; }
    .nq-specimen p { font-size: .95rem; line-height: 1.65; color: var(--ink-soft); max-width: 52ch; margin: 0 0 16px; }
    .nq-specimen .nq-small { font-size: .72rem; letter-spacing: .04em; color: var(--ink-soft); border-top: 1px dashed var(--rule); padding-top: 12px; max-width: 44ch; }

    .nq-slip {
        position: relative; background: var(--card); border: 1px solid var(--rule);
        padding: 18px 20px 20px; box-shadow: 0 22px 34px -24px rgba(28, 26, 23, .55);
    }
    .nq-slip__head {
        display: flex; justify-content: space-between; align-items: baseline;
        border-bottom: 1px solid var(--ink); padding-bottom: 8px; margin-bottom: 14px;
        font-family: 'Courier Prime', ui-monospace, monospace; font-weight: 700; font-size: .72rem;
        letter-spacing: .12em; text-transform: uppercase;
    }
    .nq-slip__head span:last-child { color: var(--deep); }
    .nq-slip__grid { display: grid; grid-template-columns: auto 1fr; gap: 6px 14px; margin: 0 0 14px; font-size: .8rem; }
    .nq-slip__grid dt { color: var(--ink-soft); letter-spacing: .04em; }
    .nq-slip__grid dd { margin: 0; font-weight: 700; }
    .nq-slip__grid .nq-due-date { color: var(--ink); }
    .nq-slip__photo { position: relative; border: 1px solid var(--rule); overflow: hidden; aspect-ratio: 16 / 9; }
    .nq-slip__photo img { width: 100%; height: 100%; object-fit: cover; display: block; filter: sepia(.16) contrast(1.02); }
    .nq-slip__photo .nq-stamp { position: absolute; left: 12px; bottom: 12px; color: var(--stamp); background: rgba(242, 232, 212, .85); }
    .nq-slip__foot {
        display: flex; justify-content: space-between; margin-top: 12px;
        font-size: .66rem; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-soft);
        border-top: 1px dashed var(--rule); padding-top: 10px;
    }

    /* ---------- Ledger list ---------- */
    .nq-ledger { padding-bottom: clamp(48px, 6vw, 84px); }
    .nq-list { display: grid; grid-template-columns: 1fr 1fr; column-gap: clamp(28px, 5vw, 72px); margin: 0; }
    .nq-list__row { display: grid; grid-template-columns: 1fr; gap: 4px; padding: 18px 0; border-top: 1px solid var(--rule); }
    .nq-list__row dt {
        font-size: .8rem; letter-spacing: .1em; text-transform: uppercase; font-weight: 700; color: var(--deep);
    }
    .nq-list__row dd { margin: 0; font-size: .88rem; line-height: 1.55; color: var(--ink-soft); max-width: 46ch; }

    /* ---------- Responsive ---------- */
    /* Shared footer: let the two brand marks shrink instead of pushing the page wide */
    .nq-page footer .d-flex { flex-wrap: wrap; }
    .nq-page footer .d-flex img { max-width: 100%; }
    @media (max-width: 480px) { .nq-page footer .d-flex > div.border-start { display: none; } }

    @media (max-width: 980px) {
        .nq-cover__body { grid-template-columns: 1fr; padding-block: 48px 8px; }
        .nq-window { order: 2; }
        .nq-window__media { aspect-ratio: 16 / 9; }
        .nq-specimen { grid-template-columns: 1fr; }
        .nq-slip { max-width: 520px; }
    }
    @media (max-width: 760px) {
        .nq-wrap { width: min(1240px, 100% - 32px); }
        .nq-bar__org { display: none; }
        .nq-bar__nav { gap: 12px; }
        .nq-bar__who { display: none; }
        .nq-table thead { display: none; }
        .nq-table tbody tr { display: grid; grid-template-columns: auto 1fr; column-gap: 16px; padding-block: 6px; }
        .nq-table td, .nq-table tbody th { padding: 8px 0; }
        .nq-num { width: auto; }
        .nq-table tbody th[scope="row"] { width: auto; white-space: normal; grid-column: 2; }
        .nq-table tbody td:last-child { grid-column: 2; padding-top: 0; }
        .nq-list { grid-template-columns: 1fr; }
        .nq-formno { bottom: 6px; right: 16px; font-size: .6rem; }
        .nq-actions { gap: 14px; }
        .nq-btn--stamp { width: 100%; justify-content: center; }
        .nq-title { font-size: clamp(1.85rem, 8.4vw, 2.35rem); }
        .nq-lede { font-size: .98rem; }
        .nq-actions__alt { font-size: .8rem; }
    }
    </style>
</head>

<body class="nq-page">
<a class="nq-skip" href="#main">Skip to content</a>

<header class="nq-bar">
    <div class="nq-wrap nq-bar__in">
        <a class="nq-brand" href="index.php" aria-label="Nuqtah home">
            <img src="assets/img/logoNuqtah_White.png" alt="Nuqtah">
        </a>
        <p class="nq-bar__org">ITQSHHB<br>IT Inventory</p>

        <nav class="nq-bar__nav" aria-label="Account">
            <?php if ($nq_logged_in): ?>
                <p class="nq-bar__who">Signed in as <strong><?php echo $nq_name; ?></strong></p>
                <a class="nq-link" href="my_requests.php">My requests</a>
                <a class="nq-btn nq-btn--paper" href="actions/logout.php">Log out</a>
            <?php else: ?>
                <a class="nq-link" href="login.php">Log in</a>
                <a class="nq-btn nq-btn--paper" href="signup.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="main">

    <!-- ---------- Cover: the card itself ---------- -->
    <section class="nq-cover" aria-labelledby="nq-title">
        <p class="nq-formno" aria-hidden="true">Form NUQ-01 · Inventory Office</p>
        <div class="nq-wrap nq-cover__body">

            <div class="nq-cover__copy">
                <h1 class="nq-title" id="nq-title">Borrow campus IT gear <em>without the paper form.</em></h1>
                <p class="nq-lede">
                    Nuqtah is the IT inventory system of Institut Tahfiz Al-Quran Sultan Haji
                    Hassanal Bolkiah. Live stock, one request, a due date that tracks itself.
                </p>

                <div class="nq-actions">
                    <?php if ($nq_logged_in): ?>
                        <a class="nq-btn nq-btn--stamp" href="inventory_list.php">Browse the shelf</a>
                        <a class="nq-actions__alt" href="cart.php">Open my basket</a>
                    <?php else: ?>
                        <a class="nq-btn nq-btn--stamp" href="login.php">Sign in to borrow</a>
                        <a class="nq-actions__alt" href="signup.php">Create an account</a>
                    <?php endif; ?>
                </div>

                <p class="nq-note">
                    <?php if ($nq_logged_in): ?>
                        Your basket becomes one request with a reference number. Collect at the IT desk.
                    <?php else: ?>
                        A request is four entries long: sign in, reserve, inspection, return.
                    <?php endif; ?>
                </p>
            </div>

            <figure class="nq-window">
                <div class="nq-window__media">
                    <video autoplay muted loop playsinline preload="metadata"
                           poster="assets/img/Tahfiz_front.jpg"
                           aria-label="The ITQSHHB building at Jalan Tutong">
                        <source src="assets/video/Front_slowfront_boomerang.mp4" type="video/mp4">
                    </video>
                    <span class="nq-stamp" aria-hidden="true">Stock live</span>
                </div>
                <figcaption>
                    <span class="nq-fig">FIG. 01</span>
                    The institute at Jalan Tutong. The IT desk sits inside; this system is what it lends from.
                </figcaption>
            </figure>

        </div>
    </section>

    <hr class="nq-perf">

    <!-- ---------- Date due: the borrowing steps ---------- -->
    <section class="nq-due nq-sec" aria-labelledby="nq-due-title">
        <div class="nq-wrap">
            <div class="nq-sec__head">
                <h2 id="nq-due-title">Date due</h2>
                <p>Four entries take a request from screen to shelf. No form, no queue at the counter.</p>
            </div>

            <table class="nq-table">
                <caption class="visually-hidden">How borrowing works</caption>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Entry</th>
                        <th scope="col">What happens</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="nq-row">
                        <td class="nq-num"><span>01</span></td>
                        <th scope="row">Sign in</th>
                        <td>Register with your institute credentials and open the central catalogue.</td>
                    </tr>
                    <tr class="nq-row">
                        <td class="nq-num"><span>02</span></td>
                        <th scope="row">Reserve</th>
                        <td>Browse live stock counts, add items to your basket, and name a pickup time.</td>
                    </tr>
                    <tr class="nq-row">
                        <td class="nq-num"><span>03</span></td>
                        <th scope="row">Inspection</th>
                        <td>Give your request reference at the desk, check the item's condition, and collect.</td>
                    </tr>
                    <tr class="nq-row">
                        <td class="nq-num"><span>04</span></td>
                        <th scope="row">Return</th>
                        <td>Bring the item back on or before its due date and the shelf count updates itself.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ---------- Specimen record ---------- -->
    <section class="nq-sec" aria-labelledby="nq-spec-title">
        <div class="nq-wrap nq-specimen">
            <div>
                <h2 id="nq-spec-title">The record keeps itself</h2>
                <p>
                    Every request becomes a row you can both read: the item, who holds it, when it
                    left, and the date it is due back. Nothing is carried on a paper slip.
                </p>
                <p>
                    The desk sees the same row, so a return clears the same record the borrower sees.
                </p>
                <p class="nq-small">The card shown is a sample record for illustration, not live data.</p>
            </div>

            <article class="nq-slip" aria-label="Sample request record">
                <div class="nq-slip__head">
                    <span>Nuqtah</span>
                    <span>Request slip</span>
                </div>
                <dl class="nq-slip__grid">
                    <dt>Reference</dt><dd>NUQ-0042</dd>
                    <dt>Item</dt><dd>HP EliteBook 840</dd>
                    <dt>Issued</dt><dd>29 Oct 2026</dd>
                    <dt>Due</dt><dd class="nq-due-date">05 Nov 2026</dd>
                    <dt>Desk</dt><dd>IT Office, Block A</dd>
                </dl>
                <div class="nq-slip__photo">
                    <img src="assets/upload/1777426207_HP_EliteBook_840.jpg" alt="HP EliteBook 840 laptop from the inventory">
                    <span class="nq-stamp" aria-hidden="true">On loan</span>
                </div>
                <div class="nq-slip__foot">
                    <span>Sample record</span>
                    <span>NUQ-01 / page 2</span>
                </div>
            </article>
        </div>
    </section>

    <hr class="nq-perf">

    <!-- ---------- Register: what the system tracks ---------- -->
    <section class="nq-ledger nq-sec" aria-labelledby="nq-reg-title">
        <div class="nq-wrap">
            <div class="nq-sec__head">
                <h2 id="nq-reg-title">On the register</h2>
                <p>The modules the IT office runs on, and the ones you touch as a borrower.</p>
            </div>

            <dl class="nq-list">
                <div class="nq-list__row">
                    <dt>Borrow &amp; return</dt>
                    <dd>Digital requests replace the paper form, and return deadlines are tracked automatically.</dd>
                </div>
                <div class="nq-list__row">
                    <dt>Shop-like basket</dt>
                    <dd>Add several items to one basket and submit the whole lot as a single request.</dd>
                </div>
                <div class="nq-list__row">
                    <dt>Live stock</dt>
                    <dd>Real-time counts, plus the current deployment location of every borrowed asset.</dd>
                </div>
                <div class="nq-list__row">
                    <dt>Asset register</dt>
                    <dd>Add, edit, tag, or retire items from one central database in seconds.</dd>
                </div>
                <div class="nq-list__row">
                    <dt>Admin dashboard</dt>
                    <dd>Statistics, handovers, and transaction logs from a single screen.</dd>
                </div>
                <div class="nq-list__row">
                    <dt>Reports</dt>
                    <dd>Inventory listings and audit logs for institutional review.</dd>
                </div>
            </dl>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script>
(function () {
    var rows = document.querySelectorAll('.nq-row');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
    var video = document.querySelector('.nq-window video');

    if (video && reduce.matches) {
        video.pause();
        video.removeAttribute('autoplay');
    }

    if (!('IntersectionObserver' in window) || reduce.matches) {
        rows.forEach(function (row) { row.classList.add('is-stamped'); });
        return;
    }

    document.documentElement.classList.add('nq-anim');
    var seen = new WeakSet();
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting || seen.has(entry.target)) return;
            seen.add(entry.target);
            entry.target.classList.add('is-stamped');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.45 });

    rows.forEach(function (row) { observer.observe(row); });

    reduce.addEventListener('change', function (event) {
        if (event.matches && video) video.pause();
    });
})();
</script>

</body>
</html>
