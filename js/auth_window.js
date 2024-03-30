
window.onload = function() { // when page is loaded
    let authOverlay = document.getElementById('auth_overlay');

    // add event listener to auth overlay to close auth window when clicked
    authOverlay.addEventListener('click', closeAuthWindow);
};

function openAuthWindow() {
    // show auth window
    let authWindow = document.getElementById('auth_window');
    authWindow.style.display = 'flex'; // show auth window

    // make background colour of auth_icon blue
    let authIcon = document.getElementById('auth_icon');
    authIcon.style.transition = 'background-color 0.2s';
    authIcon.style.backgroundColor = 'var(--ryu-blue)';

    // make auth overlay visible to darken other elements
    let authOverlay = document.getElementById('auth_overlay');
    authOverlay.style.display = 'block'

    let authButton = document.getElementById('auth_button');
    authButton.addEventListener('click', closeAuthWindow);
}

function closeAuthWindow() {
    // hide auth window
    let authWindow = document.getElementById('auth_window');
    authWindow.style.display = ''; // hide auth window

    // remove in-line background colour style from auth_icon
    let authIcon = document.getElementById('auth_icon');
    authIcon.style.backgroundColor = '';

    // hide auth overlay
    let authOverlay = document.getElementById('auth_overlay');
    authOverlay.style.display = 'none';

    // remove event listener from auth button
    let authButton = document.getElementById('auth_button');
    authButton.removeEventListener('click', closeAuthWindow);
}
