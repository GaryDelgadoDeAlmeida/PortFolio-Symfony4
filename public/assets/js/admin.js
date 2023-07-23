import { findSpecificParent } from "./module/function.js";

document.addEventListener("DOMContentLoaded", function() {
    const removeWitness = document.getElementById("removeWitness")
    if(removeWitness) {
        removeWitness.addEventListener("click", async (e) => {
            const witnessRemoveURL = e.target.getAttribute("data-url")

            // const parent = findSpecificParent(e.target, "witness-item");
            // const tBody = parent.parentNode
            // parent.remove()

            // if(tBody.children.length === 0) {
            //     tBody.innerHTML = `
            //         <tr>
            //             <td class="txt-center" colspan="5">No results</td>
            //         </tr>
            //     `
            // }

            console.log([
                window.location,
                witnessRemoveURL,
                typeof witnessRemoveURL,
                `${window.location.origin}${witnessRemoveURL}`,
                typeof `${window.location.origin}${witnessRemoveURL}`
            ])
            const response = await fetch({
                url: `${window.location.origin}${witnessRemoveURL}`,
                method: "DELETE",
                headers: {
                    // "Accept": "application/ld+json",
                    "Content-Type": "application/json"
                }
            })
            
            if(response.status === 404) {
                alert("Error")
            } else if(response.status === 200) {
                alert("OK")
            }
            console.log(response)
            // const responseData = await response.json()
        })
    }

    const removeContacts = document.getElementsByClassName("removeMail")
    if(removeContacts.length > 0) {
        for(let $i = 0; $i < removeContacts.length; $i++) {
            removeContacts[$i].addEventListener("click", async function(e) {
                const url = e.target.getAttribute("data-url")

                // API Call
                const [status, response] = await fetch(url, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json"
                    }
                }).then(res => [res.status, res.json()]).catch(err => console.error(err))

                response.then(res => alert(res.response.message))

                if(status === 403) {
                // if(status === 200) {
                    let parent = findSpecificParent(e.target, "email-card")
                    if(parent !== "") {
                        parent.remove()
                    }
                }
            })
        }
    }
})