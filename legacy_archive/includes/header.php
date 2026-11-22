<?php
/**
 * Dynamic Header Component
 * Used in: auth pages, user dashboard, doctor pages, etc.
 * 
 * Variables (all optional):
 * - $headerGreeting: Greeting message (e.g., "👋 Bine ai venit!")
 * - $headerLinks: Array of links [['href' => 'url', 'label' => 'text'], ...]
 * - $headerUseLogo: Show logo? (default: true)
 * - $headerTitle: Header title (default: 'MediTrust')
 * - $headerLogoPath: Path to logo (default: '../img/meditrust-logo.png')
 */

$headerGreeting = $headerGreeting ?? null;
$headerLinks = $headerLinks ?? [];
$headerUseLogo = $headerUseLogo ?? true;
$headerTitle = $headerTitle ?? 'MediTrust';
$headerLogoPath = $headerLogoPath ?? '../img/meditrust-logo.png';

// Auto-detect logout URL based on current page
if (!isset($headerLogoutUrl)) {
    $current_path = $_SERVER['PHP_SELF'];
    
    if (strpos($current_path, '/admin/') !== false) {
        $headerLogoutUrl = 'logout.php';
    } elseif (strpos($current_path, '/auth/') !== false || 
              strpos($current_path, '/medici/') !== false || 
              strpos($current_path, '/pacient/') !== false ||
              strpos($current_path, '/doctor/') !== false) {
        $headerLogoutUrl = strpos($current_path, '/auth/') !== false 
            ? 'logout.php' 
            : '../auth/logout.php';
    }
}
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

        <?php if (is_array($headerLinks) && !empty($headerLinks)): ?>
            <?php foreach ($headerLinks as $link): ?>
                <?php if (isset($link['href']) && isset($link['label'])): ?>
                    <a href="<?php echo htmlspecialchars($link['href']); ?>">
                        <?php echo htmlspecialchars($link['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</header>