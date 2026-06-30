(function($) {
    'use strict';

    // Copy version button
    $(document).on('click', '.wum-copy-version', function() {
        var version = $(this).data('version');
        var target = $(this).data('target');
        var $input = $(target);
        if (version && $input.length) {
            $input.val(version).trigger('change');
        }
    });

    // Unfreeze version button
    $(document).on('click', '.wum-unfreeze-version', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var $input = $(target);
        if ($input.length) {
            $input.val('').trigger('change');
            var $form = $input.closest('form');
            if ($form.length) {
                $form.submit();
            }
        }
    });

    // Validate version input (only digits and dots)
    $(document).on('input', 'input[id^="wum_freeze_"]', function() {
        var val = $(this).val();
        if (!/^([0-9]+\.?)+$/.test(val) && val !== '') {
            $(this).val(val.replace(/[^0-9.]/g, ''));
        }
    });
})(jQuery);
