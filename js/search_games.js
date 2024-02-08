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
    // attach event listeners to all multiplayer checkboxes
    document.querySelectorAll('.mp_feature_check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const mpFeatureCountCont = this.closest('.mp_feature_check_cont').nextElementSibling; // get the container for the multiplayer feature count (siblings of the checkbox)
            if (mpFeatureCountCont && mpFeatureCountCont.classList.contains('mp_feature_count_cont')) {
                if (this.checked) {
                    // if the checkbox is checked, set visibility to visible
                    mpFeatureCountCont.style.visibility = 'visible';
                } else {
                    // if the checkbox is not checked, set visibility to hidden and required to false
                    mpFeatureCountCont.style.visibility = 'hidden';
                }
            }
        });
    });
});