let selectedLat = null;
let selectedLon = null;
let selectedLocationKey = "";

let loadedClubData = null;
let loadedAcatData = null;
// { [name:string] : [name, county, lat, lon]}
let loadedLocationData = null;
let loadedLocationDataKeys = null;

let searchInputEl = null;
let searchItemsContainerEl = null;
let searchButtonEl = null;

const nearestClubsCount = 6;
const maxSuggestedLocationsCount = 4;
let additionalResultClubCount = 0;

function getEntryKey(entry) {
    if (entry instanceof Array) {
        return `${entry[0]}, ${entry[1]}`;
    }
    return `${entry.name}, ${entry.county}`;
}

function hideSuggestedLocations() {
    searchItemsContainerEl.innerHTML = "";
    searchItemsContainerEl.style.display = "none";
}

function setSearchInputAndHideSuggestedLocations(key) {
    searchInputEl.value = key;
    hideSuggestedLocations();
}

function selectLocationByKey(locationKey) {
    const selectedLocationData = loadedLocationData[locationKey];
    if (selectedLocationData) {
        selectedLocationKey = getEntryKey(selectedLocationData);
        selectedLat = selectedLocationData[2];
        selectedLon = selectedLocationData[3];
    }
}

function setupControls() {
    searchInputEl = document.getElementById("search-input");
    searchInputEl.addEventListener('focus', () => searchInputEl.select());
    searchInputEl.addEventListener('change', (e) => {
        selectLocationByKey(e.target.value);
    });
    searchInputEl.addEventListener('input', function (e) {
        hideSuggestedLocations();
        selectedLat = null;
        selectedLon = null;
        selectedLocationKey = null;

        const searchValue = e.target.value;
        if (!searchValue) {
            searchButtonEl.disabled = true;
            return;
        }

        let entriesFound = 0;
        const foundBestEntries = [];
        const foundOtherEntries = [];
        const onEntryFound = (key) => {
            const foundEntry = loadedLocationData[key];
            if (entriesFound === 0) {
                selectLocationByKey(key);
            }
            foundBestEntries.push(foundEntry);
            entriesFound += 1;
        };
        for (const key of loadedLocationDataKeys) {
            const lowerKey = key.toLowerCase();
            const lowerSearchValue = e.target.value.toLowerCase();
            if (lowerKey.startsWith(lowerSearchValue)) {
                onEntryFound(key);
            } else if (lowerKey.includes(lowerSearchValue)) {
                foundOtherEntries.push(key);
            }
            if (entriesFound >= maxSuggestedLocationsCount) break;
        }
        for (let i = 0; (entriesFound < maxSuggestedLocationsCount) && (i < foundOtherEntries.length); i++) {
            onEntryFound(foundOtherEntries[i]);
        }
        for (let entry of foundBestEntries) {
            const nameSpan = document.createElement("span");
            nameSpan.innerText = entry[0] + ", ";
            const countySpan = document.createElement("span");
            countySpan.className = "search-item-county";
            countySpan.innerText = entry[1];
            const entryDiv = document.createElement("div");
            entryDiv.className = "search-item";
            entryDiv.appendChild(nameSpan);
            entryDiv.appendChild(countySpan);
            entryDiv.addEventListener('click', function () {
                const key = getEntryKey(entry);
                selectLocationByKey(key);
                setSearchInputAndHideSuggestedLocations(key);
                searchClub();
            });
            searchItemsContainerEl.appendChild(entryDiv);
        }

        searchItemsContainerEl.style.display = (entriesFound > 0) ? "flex" : "none";
        searchButtonEl.disabled = (entriesFound === 0);
    });
    searchInputEl.addEventListener('keypress', function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            searchClub();
        }
    });

    searchButtonEl = document.getElementById("search-button");
    searchButtonEl.disabled = true;
    searchButtonEl.addEventListener('click', function () {
        searchClub();
    });

    document.getElementById("show-all-button").addEventListener('click', function () {
        searchClub(true);
    });
}

async function initMap() {
    // caricamento lista località (i borghi (hamlet) per il momento non sono caricati)
    const allLocations = [];
    const citiesResponse = await fetch("/ricerca-dei-club/city_dataset/place-city.json")
    const townsResponse = await fetch("/ricerca-dei-club/city_dataset/place-town.json")
    const villagesResponse = await fetch("/ricerca-dei-club/city_dataset/place-village.json")
    allLocations.push(...(await citiesResponse.json()));
    allLocations.push(...(await townsResponse.json()));
    allLocations.push(...(await villagesResponse.json()));

    searchItemsContainerEl = document.getElementById("search-items-container");
    hideSuggestedLocations();
    loadedLocationData = {};
    loadedLocationDataKeys = [];
    for (const location of allLocations) {
        const key = getEntryKey(location);
        loadedLocationData[key] = [location.name, location.county, ...location.gps];
        loadedLocationDataKeys.push(key);
    }
    setupControls();
}

function formatCell(content, mode) {
    switch (mode) {
        case "mail": {
            if (content) return `<a href="mailto:${content}">${content}</a>`;
            content = "-";
        } break;
        case "url": {
            if (content.startsWith("www")) content = "http://" + content;
            if (content) return `<a href="${content}">${content.replace("http://www.", "")}</a>`;
            content = "-";
        } break;
        case "facebook":
            if (content) return content.startsWith("http") ? `<a href="${content}">link</a>` : content
            content = "-";
    }
    return `<div>${content || "-"}</div>`
};

/** 
 * @param {boolean} showAllClub
 * 
*/
function searchClub(showAllClub) {
    setErrorMessage("");
    document.getElementById('search-clubs').innerHTML = "";
    document.getElementById('all-clubs').innerHTML = "";

    if (!showAllClub && !selectedLat) {
        setErrorMessage("Seleziona una città tra quelle proposte e premi il pulsante Cerca");
        return;
    }

    additionalResultClubCount = 0;
    setSearchInputAndHideSuggestedLocations(selectedLocationKey);
    if (!loadedClubData) {
        // caricamento di tutti i Club del Veneto
        fetch("/ricerca-dei-club/data_club.json").then(club_response => club_response.json()).then(json_club => {
            fetch("/ricerca-dei-club/data_acat.json").then(acat_response => acat_response.json()).then(json_acat => {
                loadedClubData = json_club;
                loadedAcatData = json_acat;
                createResult(showAllClub);
            });
        });
    } else {
        createResult(showAllClub);
    }
}

function createResult(showAllClub, loadMoreClubCount = 0) {
    const allClub = loadedClubData.slice();
    const allAcat = loadedAcatData.slice();
    if (!showAllClub) {
        for (const club of allClub) {
            const clubDist = getDistanceFromLatLonInKm(selectedLat, selectedLon, club.gps0, club.gps1);
            club.dist = clubDist;
        }
        // ordino per distanza crescente
        allClub.sort((a, b) => a.dist - b.dist);
        // prendo solo una parte
        additionalResultClubCount += loadMoreClubCount;
        let resultClub = allClub.slice(0, nearestClubsCount + additionalResultClubCount);

        // costruzione html per lista Club
        let resultClubHTML = "";
        const resultACAT = new Set();
        for (const club of resultClub) {
            resultClubHTML += getClubHTML(club, showAllClub);
            resultACAT.add(allAcat.find((item) => item.name === club.acat));
        }
        // pulsante per caricare altri Club
        resultClubHTML += `<div class="search-button" id="load-more-button" onclick="createResult(false,2)">Visualizza altri 2 Club</div>`;

        // costruzione html per lista ACAT
        let resultAcatHTML = "";
        for (const acat of resultACAT.values()) {
            resultAcatHTML += getAcatHTML(acat);
        }

        let resultHTML = "";
        resultHTML += `<div class="result-subtitle">Club più vicini a ${selectedLocationKey}</div>`;
        resultHTML += `<div class="result-club-container">${resultClubHTML}</div>`;
        resultHTML += `<div class="result-subtitle">ACAT più vicine a ${selectedLocationKey}</div>`;
        resultHTML += `<div class="result-acat-container">${resultAcatHTML}</div>`;

        document.getElementById('search-clubs').innerHTML = resultHTML;

    } else {
        // prendo tutti i Club 
        // raggruppo per provincia e poi per ACAT
        let resultInnerHTML = "";
        const districtList = new Set();
        const acatNameList = new Set();
        for (const club of allClub) {
            districtList.add(club.district);
            acatNameList.add(club.acat);
        }
        for (const district of districtList.values()) {
            resultInnerHTML += `<div class="result-subtitle">Provincia di ${district}</div>`;
            const districtAcat = allAcat.filter((item) => item.district === district);
            for (const acat of districtAcat) {
                const clubs = allClub.filter((item) => item.acat === acat.name);
                resultInnerHTML += `<div class="result-acat-container">`;
                resultInnerHTML += getAcatHTML(acat);
                resultInnerHTML += `</div>`;
                resultInnerHTML += `<div class="result-club-container">`;
                for (const club of clubs) {
                    resultInnerHTML += getClubHTML(club, showAllClub);
                }
                resultInnerHTML += `</div>`;
            }
        }
        document.getElementById('all-clubs').innerHTML = resultInnerHTML;
    }
}

function getClubHTML(club, showAllClub) {
    let resultClubHTML = "";
    resultClubHTML += '<div class="result-club">';
    resultClubHTML += '<div class="result-entry-title">' + club.name + '</div>';
    resultClubHTML += formatCell('Presso: ') + formatCell(club.building);
    resultClubHTML += formatCell('Indirizzo: ') + formatCell(club.address);
    resultClubHTML += formatCell('CAP: ') + formatCell(club.postalcode);
    resultClubHTML += formatCell('Città: ') + formatCell(club.city);
    resultClubHTML += formatCell('Provincia: ') + formatCell(club.district);
    resultClubHTML += formatCell('ACAT:') + formatCell(`<a class="anchor-acat" onclick="onAnchorClick('#${club.acat.replaceAll(" ", "-")}')">${club.acat}</a>`);
    if (!showAllClub) {
        resultClubHTML += formatCell('Distanza: ') + formatCell(club.dist.toFixed(1) + ' km');
    }
    resultClubHTML += '</div>';
    return resultClubHTML;
}

function getAcatHTML(acat) {
    let resultAcatHTML = "";
    resultAcatHTML += `<div id="${acat.name.replaceAll(" ", "-")}" class="result-acat">`;
    resultAcatHTML += '<div class="result-entry-title">' + acat.name + '</div>';
    resultAcatHTML += formatCell('Fondazione: ') + formatCell(acat.foundingyear);
    resultAcatHTML += formatCell('Sede: ') + formatCell(acat.city);
    resultAcatHTML += formatCell('Presso: ') + formatCell(acat.building);
    resultAcatHTML += formatCell('Indirizzo: ') + formatCell(acat.address);
    resultAcatHTML += formatCell('CAP: ') + formatCell(acat.postalcode);
    resultAcatHTML += formatCell('Provincia: ') + formatCell(acat.district);
    resultAcatHTML += formatCell('Tel/Cell: ') + formatCell(acat.phonenumber);
    resultAcatHTML += formatCell('Mail: ') + formatCell(acat.email, "mail");
    resultAcatHTML += formatCell('Facebook: ') + formatCell(acat.facebook, "facebook");
    resultAcatHTML += formatCell('Sito: ') + formatCell(acat.website, "url");
    resultAcatHTML += formatCell('Presidente: ') + formatCell(acat.president);
    resultAcatHTML += formatCell('> Cell: ') + formatCell(acat.presidentphonenumber);
    resultAcatHTML += formatCell('> Mail: ') + formatCell(acat.presidentemail, "mail");
    resultAcatHTML += formatCell('Referente: ') + formatCell(acat.contact);
    resultAcatHTML += formatCell('> Cell: ') + formatCell(acat.contactphonenumber);
    resultAcatHTML += formatCell('> Mail: ') + formatCell(acat.contactemail, "mail");
    resultAcatHTML += '</div>';
    return resultAcatHTML;
}

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    var R = 6371; // Radius of the earth in km
    var dLat = deg2rad(lat2 - lat1);  // deg2rad below
    var dLon = deg2rad(lon2 - lon1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c; // Distance in km
    return d;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180)
}

function setErrorMessage(msg) {
    document.getElementById('search-error').innerHTML = msg;
}

// eslint-disable-next-line no-unused-vars
function onAnchorClick(hash) {
    window.location.href = hash;
}

initMap();

// https://www.geoapify.com/download-all-the-cities-towns-villages/