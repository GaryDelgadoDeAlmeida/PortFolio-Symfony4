document.addEventListener("DOMContentLoaded", function() {
    var $collectionParticipateProjectsHolder, $newLinkLi;
    $collectionParticipateProjectsHolder = document.querySelector("ul.participateProjects-tags");

    // Remove Button
    $collectionParticipateProjectsHolder.querySelectorAll("li").forEach((element) => {
        let removeButton = document.createElement("button")
        removeButton.className = "btn btn-red"
        removeButton.innerText = "Delete this tag"
        element.appendChild(removeButton)

        removeButton.addEventListener("click", (event) => {
            element.remove(event);
        })
    })

    // Add Button
    let addParticipateProjectButton = document.createElement("li");
    addParticipateProjectButton.innerHTML = '<button type="button" class="add_tag_link btn btn-primary">Add a tag</button>'
    $collectionParticipateProjectsHolder.appendChild(addParticipateProjectButton)

    document.querySelectorAll(".add_tag_link").forEach((el) => {
        el.addEventListener("click", function(element) {
            let newElement = document.createElement("li")
            newElement.className = "item"
            let prototype = findSpecificParent(element.target, "participateProjects-tags").getAttribute("data-prototype")
            newElement.innerHTML = prototype.replace(/__name__/g, $collectionParticipateProjectsHolder.children.length - 1)
            $collectionParticipateProjectsHolder.insertBefore(newElement, addParticipateProjectButton)

            // Remove Button
            let removeButton = document.createElement("button")
            removeButton.className = "btn btn-red"
            removeButton.innerText = "Delete this tag"
            newElement.appendChild(removeButton)
            removeButton.addEventListener("click", (e) => {
                newElement.remove()
            })
        })
    })
});

/**
 * Get all parents by the element
 * 
 * @param {*} element
 * @returns 
 */
function findSpecificParent(element, target) {
    var els = "";
    
    while (element) {
        if( [null, undefined].indexOf(element.className) === -1 ) {
            if(element.className === target) {
                els = element;
                break;
            }
        }

        element = element.parentNode;
    }

    return els;
}