$(function () {
    // FUNCTIONS //

    // EVENTHANDLERS //
    $("#mainNav .navbar-burger").click(function () {
        $("#mainNav .navbar-burger").toggleClass("is-active");
        $("#mainNav .navbar-menu").toggleClass("is-active");
    });
    // $("#btnCreatePost").click(function () {
    //     $("#createPost").toggleClass("is-active");
    // });
    $("#btnCreatePost").click(function () {
        $("#createPost").fadeToggle("fast", "linear");
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
});