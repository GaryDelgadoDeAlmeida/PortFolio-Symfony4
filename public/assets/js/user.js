import { findSpecificParent } from "./module/function";

document.addEventListener("DOMContentLoaded", function() {
    var menu = document.getElementsByClassName("links-mobile-items")[0];
    [...menu.getElementsByTagName("a")].map((item, key) => {
        item.addEventListener("click", (element) => {
            document.getElementById("burger-menu").click()
        })
    })
})