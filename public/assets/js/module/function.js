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