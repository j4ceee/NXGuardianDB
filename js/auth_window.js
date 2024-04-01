
window.addEventListener('load', function() { // when page is loaded
    let authOverlay = document.getElementById('auth_overlay');

    // add event listener to auth overlay to close auth window when clicked
    authOverlay.addEventListener('click', closeAuthWindow);
});

function toggleAuthWindow() {
    let authWindow = document.getElementById('auth_window');
    if (authWindow.style.display === 'flex') {
        closeAuthWindow();
    } else {
        openAuthWindow();
    }
}

function openAuthWindow() {
    let authWindow = document.getElementById('auth_window');
    let authButton = document.getElementById('auth_button');
    let authIcon = document.getElementById('auth_icon');
    let authOverlay = document.getElementById('auth_overlay');

    // show auth window
    authWindow.style.display = 'flex'; // show auth window
    // make button unclickable
    authButton.style.pointerEvents = 'none';

    setTimeout(() => {
        authWindow.style.maxHeight = '20rem'; // set max height of auth window
        authWindow.style.padding = '1rem'; // set padding of auth window
        authButton.style.pointerEvents = ''; // make button clickable again
    }, 1);

    // make background colour of auth_icon blue
    authIcon.style.transition = 'background-color 0.2s';
    authIcon.style.backgroundColor = 'var(--ryu-blue)';

    // make auth overlay visible to darken other elements
    authOverlay.style.display = 'block'

    authButton.addEventListener('click', closeAuthWindow);
}

function closeAuthWindow() {
    let authWindow = document.getElementById('auth_window');
    let authIcon = document.getElementById('auth_icon');
    let authOverlay = document.getElementById('auth_overlay');
    let authButton = document.getElementById('auth_button');

    // hide auth window
    authWindow.style.maxHeight = ''; // remove max height of auth window
    authWindow.style.padding = ''; // remove padding of auth window
    authButton.style.pointerEvents = 'none'; // make button unclickable

    // after .2s, hide auth window
    setTimeout(() => {
        authWindow.style.display = '';

        // hide auth overlay
        authOverlay.style.display = 'none';

        authButton.style.pointerEvents = ''; // make button clickable again
    }, 200);

    // remove in-line background colour style from auth_icon
    authIcon.style.backgroundColor = '';

    // remove event listener from auth button
    authButton.removeEventListener('click', closeAuthWindow);
}

function setNotRequired(name) {
    // sets the input field with the given name to not required
    let input = document.getElementById(name);
    input.required = false;
}
