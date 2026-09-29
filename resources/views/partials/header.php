<?php

declare(strict_types=1);

$headerActionUrl = $headerActionUrl ?? '/login';
$headerActionLabel = $headerActionLabel ?? 'Login';
?>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="/register" aria-label="HFM registration home">
            <span class="brand-note">Member of HF Markets Group</span>
            <img
                class="brand-logo"
                src="/assets/images/logo_hfm.svg"
                width="119"
                height="58"
                alt="HFM HF Markets"
            >
        </a>

        <a class="header-action" href="<?= htmlspecialchars($headerActionUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <?= htmlspecialchars($headerActionLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
    </div>
</header>
