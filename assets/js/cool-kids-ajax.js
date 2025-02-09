jQuery(document).ready(function($) {
    $('#cool-kids-login').on('submit', function(e) {
        e.preventDefault();
        let email = $('#email').val();
        let nonce = $('#cool_kids_nonce').val();

        $.ajax({
            type: 'POST',
            url: cool_kids_ajax.ajax_url,
            data: {
                action: 'cool_kids_login',
                email: email,
                nonce: nonce
            },
            success: function(response) {
                $('#login-message').html('<p style="color: green;">' + response.message + '</p>');
                if (response.success) {
                    setTimeout(function() {
                        window.location.href = cool_kids_ajax.redirect_url;
                    }, 1000);
                }
            },
            error: function(xhr) {
                $('#login-message').html('<p style="color: red;">' + xhr.responseJSON.message + '</p>');
            }
        });
    });
});