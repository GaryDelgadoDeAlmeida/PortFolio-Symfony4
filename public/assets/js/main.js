$("#btnToggler").on('click', function() {
    if($("#navToggler").hasClass("collapse")) {
        // $("#navToggler").addClass("navToggler");
        $("#navToggler").removeClass("collapse");
    } else {
        // $("#navToggler").removeClass("navToggler");
        $("#navToggler").addClass("collapse");
    }
});