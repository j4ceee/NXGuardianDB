window.addEventListener('pageshow', function() {
    // hides spinner when a page is shown
    hideSpinner()
});


function showSpinner() {
    // hide auth overlay if it is visible
    let authOverlay = document.getElementById('auth_overlay');
    authOverlay.style.display = 'none';

    document.getElementById('loading_overlay').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loading_overlay').style.display = 'none';
}