window.onload = function() {
    // get all input elements
    const inputs = document.querySelectorAll('input');

    // loop through each input element
    inputs.forEach(input => {
        switch(input.type) {
            case 'text':
            case 'number':
            case 'url':
            case 'date':
                input.value = ''; // set value to empty string
                break;
            case 'checkbox':
                input.checked = false; // uncheck checkbox
                break;
        }
    });
}


document.addEventListener('DOMContentLoaded', function() {
    // attach event listeners to all platform checkboxes
    document.querySelectorAll('.plat_list_check').forEach(checkbox => {
        checkbox.addEventListener('change', handlePlatformSelectionChange);
    });
});

function handlePlatformSelectionChange(event) {
    const checkbox = event.target; // the checkbox that was clicked
    const platID = checkbox.value; // takes the value of the checkbox as the platform ID
    const platName = checkbox.nextElementSibling.textContent; // the label directly after the checkbox

    // Check if the platform-specific container already exists
    let platformInfoContainer = document.querySelector(`.platform_info.info_${platID}`); // gets the container with the class name of the platform ID
    if (checkbox.checked) {
        if (!platformInfoContainer) {
            // container doesn't exist, create it & add it to the DOM
            addPlatformInfoContainer(platID, platName);
        }
    } else {
        // if unchecked, delete the platform-specific container
        if (platformInfoContainer) {
            platformInfoContainer.remove();
        }
    }
}

function addPlatformInfoContainer(platID, platName) {
    // Get the template and clone it
    const template = document.getElementById('platform_template').content.cloneNode(true);

    // Replace placeholders within the cloned template
    // for platform name and image
    const legend = template.querySelector('legend'); // get the legend element
    const img = legend.querySelector('img'); // get the img element

    // update the image src
    if (img) {
        img.src = `./img/platforms/${platID}.svg`;
    } else {
        console.error('Image not found in template');
    }

    // update the legend's text content while preserving the <img>
    if (legend) {
        if (legend.lastChild) { // check if there's a text node
            legend.lastChild.textContent = " " + platName; // Add space for separation if needed
        }
    } else {
        console.error('Legend not found in template');
    }

    // for class names
    template.querySelectorAll('.platform_info, .multiplayer_info').forEach(element => {
        element.className = element.className.replace('[platID]', platID);
    });

    // for input names, for, and id
    template.querySelectorAll('input, label').forEach(element => {
        const name = element.name || element.htmlFor || element.id; // handle input, label, and id
        if (name) {
            const newName = name.replace('[platID]', platID); // replace placeholder in name, htmlFor, or id
            if (element.tagName.toLowerCase() === 'input') { // if it's an input element
                element.name = newName;
                element.id = newName;
            } else { // if it's a label element
                element.htmlFor = newName;
            }
        }
    });

    // append the modified template to the platform_spec_cont div
    const platformSpecCont = document.querySelector('.platform_spec_cont');
    platformSpecCont.appendChild(template);

    // attach event listeners to all multiplayer checkboxes
    const platformInfoContainer = platformSpecCont.querySelector(`.platform_info.info_${platID}`);
    platformInfoContainer.querySelectorAll('.mp_feature_check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const mpFeatureCountCont = this.closest('.mp_feature_check_cont').nextElementSibling; // get the container for the multiplayer feature count (siblings of the checkbox)
            if (mpFeatureCountCont.classList.contains('mp_feature_count_cont')) {
                if (this.checked) {
                    // If the checkbox is checked, set visibility to visible and required to true
                    mpFeatureCountCont.style.visibility = 'visible';
                    mpFeatureCountCont.querySelectorAll('input').forEach(input => {
                        input.required = true;
                    });
                } else {
                    // If the checkbox is not checked, set visibility to hidden and required to false
                    mpFeatureCountCont.style.visibility = 'hidden';
                    mpFeatureCountCont.querySelectorAll('input').forEach(input => {
                        input.required = false;
                    });
                }
            }
        });
    });
}