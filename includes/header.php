<?php
$headerLinks = $headerLinks ?? [];
$headerGreeting = $headerGreeting ?? null;
$headerUseLogo = $headerUseLogo ?? true;
$headerTitle = $headerTitle ?? 'MediTrust';
$headerLogoPath = $headerLogoPath ?? '../img/meditrust-logo.png';
?>
<header>
    <?php if ($headerUseLogo): ?>
        <div class="header-logo">
            <img src="<?php echo htmlspecialchars($headerLogoPath); ?>" alt="MediTrust" class="logo">
            <h1><?php echo htmlspecialchars($headerTitle); ?></h1>
        </div>
    <?php else: ?>
        <h1><?php echo htmlspecialchars($headerTitle); ?></h1>
    <?php endif; ?>

    <div class="header-links">
        <?php if (!empty($headerGreeting)): ?>
            <span><?php echo htmlspecialchars($headerGreeting); ?></span>
        <?php endif; ?>

        <?php foreach ($headerLinks as $link): ?>
            <a href="<?php echo htmlspecialchars($link['href']); ?>"><?php echo htmlspecialchars($link['label']); ?></a>
        <?php endforeach; ?>
    </div>
</header>
