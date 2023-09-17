import { findSpecificParent, removeTagButton, addTagElement } from "../module/function.js";

document.addEventListener("DOMContentLoaded", function() {
    var $collectionHolder, $newLinkLi;
    $collectionHolder = document.querySelector("ul.price-details-tag");

    // Remove Button
    $collectionHolder.querySelectorAll("li").forEach((element) => {
        removeTagButton(element)
    })

    // Add Button
    let addButton = document.createElement("li");
    addButton.innerHTML = '<button type="button" class="add_tag_link btn btn-primary">Add a tag</button>'
    $collectionHolder.appendChild(addButton)

    // Add element
    // addTagElement()
    document.querySelectorAll(".add_tag_link").forEach((el) => {
        el.addEventListener("click", function(element) {
            let newElement = document.createElement("li")
            newElement.className = "item"
            
            let prototype = findSpecificParent(element.target, "price-details-tag").getAttribute("data-prototype")
            newElement.innerHTML = prototype.replace(/__name__/g, $collectionHolder.children.length - 1)
            
            $collectionHolder.insertBefore(newElement, addButton)

            // Remove Button
            removeTagButton(newElement)
        })
    })
})