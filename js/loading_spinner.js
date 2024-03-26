window.addEventListener('pageshow', function(event) {
    // hides spinner when a page is shown
    hideSpinner()
});


function showSpinner() {
    document.getElementById('loading_overlay').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loading_overlay').style.display = 'none';
}