    <?php if (isset($show_floating_action) && $show_floating_action): ?>
    <div class="floating-action" onclick="<?php echo isset($floating_action_function) ? $floating_action_function : 'scrollToTop()'; ?>">
        <i class="<?php echo isset($floating_action_icon) ? $floating_action_icon : 'fas fa-arrow-up'; ?>"></i>
    </div>
    <?php endif; ?>

    <!-- External JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $js; ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_js)): ?>
        <script><?php echo $inline_js; ?></script>
    <?php endif; ?>
</body>
</html>
