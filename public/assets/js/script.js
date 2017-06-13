$(function() {
    nameEnter();
});

function nameEnter() {
    var cookieCheck = navigator.cookieEnabled;

    if (cookieCheck === false) {
        swal("Oh No!", "Enable cookies to continue!", "error");
        $("html").text("enable cookies!");
    };

    swal({
        title: "Username",
        text: "Enter your Minecraft username to use the shop:",
        type: "input",
        showCancelButton: false,
        closeOnConfirm: false,
        allowEscapeKey: false,
        animation: "slide-from-top",
        inputPlaceholder: "Name"
    },
    function(inputValue){
        if (inputValue === false) return false;
        if (inputValue === "") {
            swal.showInputError("Enter a name!");
            return false
        }
        var name = inputValue;
        $.post('/ver',{name:name},
        function(data) {
            if (data) {
                var user_return = data;
                swal("Nice!", "You're using Vanilla BuyCraft as: "+data, "success");
                $('#result-name').html(user_return);
                $('#result-img').attr('src', 'https://visage.surgeplay.com/face/64/'+user_return);
            } else {
                nameEnter();
                swal.showInputError("Name invalid!");
            }
        });
    });
}