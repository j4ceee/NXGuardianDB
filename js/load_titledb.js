let titles = {};
let keys = [];

// list of all input elements that will be filled with data from the title database and their corresponding title database keys
const gameFields = {
    'title' : 'name', // title text input
    'developer' : 'publisher', // developer list / text input
    'release' : 'releaseDate', // release date input
    'imageLink' : 'iconUrl', // image link input
    'store_link_14' : null, // store link input
    'game_id_14' : 'id' // store ID input
};

const checkboxElements = [ // list of all checkbox elements
    'platform14',
    'single_14',
    'online_mp_14',
];

// list of strings to remove from the title text (trademarks, etc.)
const removeString = ['\u00AE', '\u2120', '\u00A9', '\u2122', '\u00AE', '\u2120', '\u00A9']


window.onload = function() {

    fetch('https://raw.githubusercontent.com/blawar/titledb/master/GB.en.json')
        .then(response => response.json())
        .then(data => {
            titles = data;

            // convert object keys to array
            keys = Object.keys(data);

            // get ?index from URL
            const urlParams = new URLSearchParams(window.location.search);
            const gameIndex = parseInt(urlParams.get('index'), 10); // Convert to integer


            if (window.location.href.includes('nsall')) {
                displayGameForm(gameIndex);
            }
            else if (window.location.href.includes('nsfp')) {

            }
        });
};

function displayGameForm(gameIndex) {
    if (gameIndex < 0 || gameIndex >= keys.length) {
        console.error('Game index is out of range.');
        return; // Exit the function if gameIndex is invalid
    }

    let titledb_entry = titles[keys[gameIndex]];

    if (!titledb_entry) {
        console.error('Game not found.');
        return; // Additional check for safety
    }

    // check Nintendo Switch platform (platform ID 14)
    let platformCheckbox = document.getElementById(checkboxElements[0]);
    // check it & trigger the change event
    platformCheckbox.checked = true;
    platformCheckbox.dispatchEvent(new Event('change'));

    // if titledb_entry['numberOfPlayers'] is >1, check the multiplayer checkbox
    let singlePlayerCheckbox = document.getElementById(checkboxElements[1]);
    let onlineMPCheckbox = document.getElementById(checkboxElements[2]);

    if (titledb_entry['numberOfPlayers'] > 1) {
        onlineMPCheckbox.checked = true;
        onlineMPCheckbox.dispatchEvent(new Event('change'));

        //'online_mp_min_14' -> set to 1
        // 'online_mp_max_14', -> set to titledb_entry['numberOfPlayers']
        document.getElementById('online_mp_min_14').value = 1;
        document.getElementById('online_mp_max_14').value = titledb_entry['numberOfPlayers'];
    }
    singlePlayerCheckbox.checked = true;
    singlePlayerCheckbox.dispatchEvent(new Event('change'));


    // loop through each input element
    for (let gameInfo in gameFields) {
        const element = document.getElementById(gameInfo);
        if (element) {
            // check if the property exists in the titledb_entry object
            if (titledb_entry[gameFields[gameInfo]]) {
                // if it's the release date, format it to YYYY-MM-DD from YYYYMMDD
                if (gameInfo === 'release') {
                    let date = titledb_entry[gameFields[gameInfo]]; // format: YYYYMMDD
                    // convert to string, split every 4 characters, join with '-'
                    date = date.toString();
                    date = [date.slice(0, 4), date.slice(4, 6), date.slice(6, 8)].join('-');
                    element.value = date;
                } else {
                    let value = titledb_entry[gameFields[gameInfo]];

                    // remove any unwanted strings from the title
                    removeString.forEach(string => {
                        value = value.replace(string, '');
                    });

                    element.value = value;
                }
            }
        }
    }
}