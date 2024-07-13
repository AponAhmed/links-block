jQuery(document).ready(function($) {
    function updateShortcode() {
        var col = $('#col').val();
        var hide = $('#hide').val();
        var newWindow = $('#new-window').val();
        var row = $('#row').val();
        var rand = $('#rand').val();
        var n = $('#n').val();
        var mcol = $('#mcol').val();
        var tcol = $('#tcol').val();
        var singleLine = $('#single-line').val();

        var shortcode = '[linksblock';
        shortcode += ' col="' + col + '"';
        shortcode += ' hide="' + hide + '"';
        shortcode += ' new-window="' + newWindow + '"';
        shortcode += ' row="' + row + '"';
        shortcode += ' rand="' + rand + '"';
        shortcode += ' n="' + n + '"';
        shortcode += ' mcol="' + mcol + '"';
        shortcode += ' tcol="' + tcol + '"';
        shortcode += ' single-line="' + singleLine + '"';
        shortcode += ']';

        $('#generated-shortcode').text(shortcode);

        // Update preview
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'linksblock_preview',
                shortcode: shortcode
            },
            success: function(response) {
                $('#shortcode-preview').html(response);
            }
        });
    }

    $('#shortcode-builder input, #shortcode-builder select').on('change', updateShortcode);
    updateShortcode();
});
