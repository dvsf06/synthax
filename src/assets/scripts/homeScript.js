window.onload = async () => {
    var mode = localStorage.getItem("mode");
    if(mode == "dark-mode"){
        document.body.classList.remove("light-mode");
    }
    else{
        document.body.classList.add("light-mode");
    }
    setSession("mode", mode);
    
    idUtente = getCookie("idUtente");
    console.log("Utente loggato: " + idUtente);
    var resp = await makeRequest("shared/actions/dataSource.php?getUserPlaylists=1", idUtente);
    console.log(resp);

    if(resp.length == 0){
        content = '<h1 class="mainTitle" style="text-align: center;">Suggeriti</h1><div class="row g-1 justify-content-evenly">';
        content += '<div class="col-lg-3 col-md-6 col-sm-6"><div class="p-3"><div class="card playlist-card" style="max-width: 200px !important; margin: 0 auto !important;"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRX4nv7fdXLX8cvBQJY452jOmzzPEgW4yBGCw&s" class="card-img-top"><div class="card-body"><h5 class="card-title">Nuova playlist</h5><a href="newPlaylist.php" class="btn btn-open-playlist">Crea una playlist</a></div></div></div></div>';
        content += '</div>';
        document.getElementById("mainContainer").innerHTML = content;
    }
    else{
        content = '<h1 class="mainTitle" style="text-align: center;">Suggeriti</h1><div class="row g-1 justify-content-evenly">';
        resp.forEach(element => {
            content += '<div class="col-lg-3 col-md-6 col-sm-6"><div class="p-3"><div class="card playlist-card" style="max-width: 200px !important; margin: 0 auto !important;"><img src="' + element.coverImage + '" class="card-img-top"><div class="card-body"><h5 class="card-title">' + element.nome + '</h5><p class="card-text">' + element.username + '</p><a class="btn btn-open-playlist" href="dettaglioPlaylist.php?id=' + element.idPlaylist + '&nome=' + element.nome + '">Apri playlist</a></div></div></div></div>';
        });
        content += '</div>';
        document.getElementById("mainContainer").innerHTML = content;

    }
}

async function makeRequest(url, param){
    urlFull = url + '&userId=' + param;
    const response = await fetch(urlFull);
    const data = await response.json();
    return data;
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function millisToMinutesAndSeconds(millis) {
    var minutes = Math.floor(millis / 60000);
    var seconds = ((millis % 60000) / 1000).toFixed(0);
    return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
}