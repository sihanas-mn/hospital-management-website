<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ABC Hospital' : 'ABC Hospital Management System'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo View::image('logoabc.jpg'); ?>">
    
    <!-- External CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Core CSS Files -->
    <link rel="stylesheet" href="<?php echo View::css('base'); ?>">
    <link rel="stylesheet" href="<?php echo View::css('navigation'); ?>">
    <link rel="stylesheet" href="<?php echo View::css('forms'); ?>">
    <link rel="stylesheet" href="<?php echo View::css('buttons'); ?>">
    <link rel="stylesheet" href="<?php echo View::css('components'); ?>">
    <link rel="stylesheet" href="<?php echo View::css('dashboard'); ?>">
    
    <!-- Additional CSS -->
    <?php if (isset($additionalCSS) && is_array($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo View::css($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline CSS -->
    <?php if (isset($inlineCSS)): ?>
        <style><?php echo $inlineCSS; ?></style>
    <?php endif; ?>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">
    <!-- Loading Overlay -->
    <?php if (isset($showLoading) && $showLoading): ?>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <?php if (!isset($hideNavbar) || !$hideNavbar): ?>
        <?php View::partial('navbar'); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Breadcrumb -->
        <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
            <div class="container">
                <nav class="breadcrumb">
                    <ol class="breadcrumb-list">
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <li class="breadcrumb-item <?php echo $index === count($breadcrumb) - 1 ? 'active' : ''; ?>">
                                <?php if ($index === count($breadcrumb) - 1): ?>
                                    <?php echo View::escape($item['text']); ?>
                                <?php else: ?>
                                    <a href="<?php echo View::url($item['url']); ?>"><?php echo View::escape($item['text']); ?></a>
                                    <span class="breadcrumb-separator">/</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if (hasFlash('success')): ?>
            <div class="container">
                <div class="alert alert-success alert-dismissible">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo flash('success'); ?></span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasFlash('error')): ?>
            <div class="container">
                <div class="alert alert-danger alert-dismissible">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo flash('error'); ?></span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasFlash('warning')): ?>
            <div class="container">
                <div class="alert alert-warning alert-dismissible">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo flash('warning'); ?></span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasFlash('info')): ?>
            <div class="container">
                <div class="alert alert-info alert-dismissible">
                    <i class="fas fa-info-circle"></i>
                    <span><?php echo flash('info'); ?></span>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <?php if (!isset($hideFooter) || !$hideFooter): ?>
        <?php View::partial('footer'); ?>
    <?php endif; ?>

    <!-- Floating Action Button -->
    <?php if (isset($showFloatingAction) && $showFloatingAction): ?>
        <div class="btn-float btn-primary" onclick="<?php echo $floatingActionFunction ?? 'scrollToTop()'; ?>">
            <i class="<?php echo $floatingActionIcon ?? 'fas fa-arrow-up'; ?>"></i>
        </div>
    <?php endif; ?>

    <!-- External JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- Core JavaScript -->
    <script src="<?php echo View::js('main'); ?>"></script>
    
    <!-- Additional JavaScript -->
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo View::js($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript -->
    <?php if (isset($inlineJS)): ?>
        <script><?php echo $inlineJS; ?></script>
    <?php endif; ?>

    <!-- Page-specific JavaScript -->
    <?php if (isset($pageScript)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php echo $pageScript; ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
