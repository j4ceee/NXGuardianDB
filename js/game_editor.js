window.onload = function() {
    // only continue if no gameID is present in the URL
    if (!window.location.href.includes('gameID') && !window.location.href.includes('nsall') && !window.location.href.includes('nsfp')) {

        // get all input elements
        const inputs = document.querySelectorAll('input');

        // loop through each input element
        inputs.forEach(input => {
            switch (input.type) {
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
}


document.addEventListener('DOMContentLoaded', function() {
    // attach event listeners to all platform checkboxes
    document.querySelectorAll('.plat_list_check').forEach(checkbox => {
        checkbox.addEventListener('change', handlePlatformSelectionChange);
    });

    document.querySelectorAll('.plat_list_check').forEach(checkbox => {
        let platID = checkbox.value;
        addPlatformMPListener(platID);
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
    // get the template and clone it
    const template = document.getElementById('platform_template').content.cloneNode(true);

    // replace placeholders within the cloned template
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

    // for input names, for and id
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

    // add event listeners to the multiplayer checkboxes
    addPlatformMPListener(platID);
}

function addPlatformMPListener(platID) {

    const platformSpecCont = document.querySelector('.platform_spec_cont');

    // attach event listeners to all multiplayer checkboxes
    const platformInfoContainer = platformSpecCont.querySelector(`.platform_info.info_${platID}`);
    if (!platformInfoContainer) {
        return;
    }
    platformInfoContainer.querySelectorAll('.mp_feature_check').forEach(checkbox => {
        const mpFeatureCountCont = checkbox.closest('.mp_feature_check_cont').nextElementSibling; // get the container for the multiplayer feature count (siblings of the checkbox)

        if (mpFeatureCountCont.classList.contains('mp_feature_count_cont')) {
            if (checkbox.checked) {
                showMPFeatureCount(platID, mpFeatureCountCont);
            }

            checkbox.addEventListener('change', function () {

                if (this.checked) {
                    showMPFeatureCount(platID, mpFeatureCountCont);
                } else {
                    hideMPFeatureCount(platID, mpFeatureCountCont);
                }
            });
        }
    });
}

function showMPFeatureCount(platID, mpFeatureCountCont) {
    if (mpFeatureCountCont) {
        mpFeatureCountCont.style.visibility = 'visible';
        mpFeatureCountCont.querySelectorAll('input').forEach(input => {
            input.required = true;
        });
    }
}

function hideMPFeatureCount(platID, mpFeatureCountCont) {
    if (mpFeatureCountCont) {
        mpFeatureCountCont.style.visibility = 'hidden';
        mpFeatureCountCont.querySelectorAll('input').forEach(input => {
            input.required = false;
        });
    }
}