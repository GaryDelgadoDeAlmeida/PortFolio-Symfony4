/**
 * Get all parents by the element
 * 
 * @param {*} element
 * @returns 
 */
export function findSpecificParent(element, target) {
    var els = "";
    
    while (element) {
        if( [null, undefined].indexOf(element.className) === -1 ) {
            if(element.className.includes(target)) {
                els = element;
                break;
            }
        }

        element = element.parentNode;
    }

    return els;
}

/**
 * 
 * @param {*} element 
 */
export function removeTagButton(element) {
    let removeButton = document.createElement("button")
    removeButton.className = "btn btn-red"
    removeButton.innerText = "Delete this tag"
    element.appendChild(removeButton)
    
    removeButton.addEventListener("click", (e) => {
        element.remove()
    })
}

/**
 * 
 * @param {*} targetTag 
 */
export function addTagElement(targetTag = "participateProjects-tags") {
    // Add Button
    let addParticipateProjectButton = document.createElement("li");
    addParticipateProjectButton.innerHTML = '<button type="button" class="add_tag_link btn btn-primary">Add a tag</button>'
    $collectionParticipateProjectsHolder.appendChild(addParticipateProjectButton)

    document.querySelectorAll(".add_tag_link").forEach((el) => {
        el.addEventListener("click", function(element) {
            let newElement = document.createElement("li")
            newElement.className = "item"
            let prototype = findSpecificParent(element.target, targetTag).getAttribute("data-prototype")
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
}