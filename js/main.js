$(function () {
    // FUNCTIONS //
    function initializeEmojipicker() {
        $.getJSON('assets/emoji/en/raw.json', function(objemojis) {
            $.each(objemojis, function (i, val) { 
                $('.emojipicker').append('<a class="button is-white emoji">' + val.emoji + '</a>');
            });
        });
    }

    // EVENTHANDLERS //
    $("#mainNav .navbar-burger").click(function () {
        $("#mainNav .navbar-burger").toggleClass("is-active");
        $("#mainNav .navbar-menu").toggleClass("is-active");
    });
    // $("#btnCreatePost").click(function () {
    //     $("#createPost").toggleClass("is-active");
    // });
    $("#btnCreatePost").click(function () {
        $("#postModal").addClass("is-active");
    });
    $("#btnRegister").click(function () {
        $("#registerModal").addClass("is-active");
    });
    $("#btnLogin").click(function () {
        $("#loginModal").addClass("is-active");
    });
    $(".modal-background").click(function () {
        $(".modal").removeClass("is-active");
    });
    $(".exitmodal").click(function () {
        $(".modal").removeClass("is-active");
    });
    $(document).on('click', '.emoji', function () {
        $(".emojiinput").append($(this).text());
    })

    // RUN STUFF //
    initializeEmojipicker();
});