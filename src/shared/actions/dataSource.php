<?php
    include "connectDb.php";
    $host = "127.0.0.1";
    $port = 8000;

    session_start();

    if(isset($_GET["createPlaylist"])){
        try{
            $query = $db->prepare("INSERT INTO tblPlaylists (nome, utenteId) VALUES (:nome, :utenteId)");
            $query -> bindParam(":nome", $_GET["name"]);
            $query -> bindParam(":utenteId", $_GET["idUser"]);
            $query -> execute();
            echo '200';
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["getUserPlaylists"])){
        try{
            $query = $db->prepare("SELECT tblPlaylists.*, tblUtenti.username FROM tblPlaylists INNER JOIN tblUtenti ON tblPlaylists.utenteId = tblUtenti.idUtente WHERE tblUtenti.idUtente = :userId");
            $query->bindParam(":userId", $_GET["userId"]);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $jsonResult = json_encode($result);
            echo $jsonResult;
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["search"])){
        try{
            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            $res = socket_connect($socket, $host, $port);
            $sentString = '{"action":"search","data":"'.$_GET["titolo"].'"}';
            socket_write($socket, $sentString, strlen($sentString));
            $resultApi = "";
            while($resp = socket_read($socket, 1024)){
                $resultApi .= $resp;
                if(strpos($resultApi, "\n") !== false) break;
            }

            $resultApi = json_decode(rtrim($resultApi, "\n"), true);

            foreach ($resultApi["items"] as $itemApi){
                $titolo = strtolower($itemApi["name"]);
                error_log($titolo);
                $query = $db->prepare("SELECT * FROM tblBrani INNER JOIN tblArtisti ON tblBrani.artistaId = tblArtisti.idArtista WHERE titolo = :titolo");
                $query->bindParam(":titolo", $titolo);
                $query->execute();
                $resultDb = $query->fetchAll(PDO::FETCH_ASSOC);

                $artistNameApi = str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $itemApi["artists"][0]["name"])))));
                $artistNameDb = str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $itemDb["nome"])))));

                foreach ($resultDb as $itemDb) {
                    if(strtolower($itemApi["name"]) == $itemDb["titolo"] && $itemApi["artists"][0]["name"] == $itemDb["nome"]){        
                        $itemId = array_search($itemApi, $resultApi["items"]);     
                        $resultApi["items"][$itemId]["downloaded"] = true;
                        $resultApi["items"][$itemId]["dbId"] = $itemDb["idBrano"];
                        $resultApi["items"][$itemId]["audioPath"] = $itemDb["percorsoFile"];
                        $resultApi["items"][$itemId]["coverImage"] = $itemDb["coverImage"];
                    }  
                }          
            }

            $jsonResult = json_encode($resultApi);
            echo $jsonResult;
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["searchHP"])){
        try{
            $postJson = json_decode(file_get_contents('php://input'), true);
            $resultApi = $postJson["items"];

            foreach ($resultApi as $itemApi){
                $itemApi = $itemApi["track"];
                $titolo = strtolower($itemApi["name"]);
                $query = $db->prepare("SELECT * FROM tblBrani INNER JOIN tblArtisti ON tblBrani.artistaId = tblArtisti.idArtista WHERE titolo = :titolo");
                $query->bindParam(":titolo", $titolo);
                $query->execute();
                $resultDb = $query->fetchAll(PDO::FETCH_ASSOC);

                $artistNameApi = str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $itemApi["artists"][0]["name"])))));
                $artistNameDb = str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $itemDb["nome"])))));

                foreach ($resultDb as $itemDb) {
                    if(strtolower($itemApi["name"]) == $itemDb["titolo"] && $itemApi["artists"][0]["name"] == $itemDb["nome"]){        
                        $itemId = array_search($itemApi, $resultApi["items"]);     
                        $resultApi["items"][$itemId]["downloaded"] = true;
                        $resultApi["items"][$itemId]["dbId"] = $itemDb["idBrano"];
                        $resultApi["items"][$itemId]["audioPath"] = $itemDb["percorsoFile"];
                        $resultApi["items"][$itemId]["coverImage"] = $itemDb["coverImage"];
                    }  
                }          
            }

            exit(json_encode($resultApi));
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["download"])){
        $socket = socket_create(AF_INET, SOCK_STREAM, 0);
        $res = socket_connect($socket, $host, $port);
        $sentString = '{"action":"download","data":"'.json_decode($_GET["song"],true)["id"].'","title":"'.json_decode($_GET["song"],true)["name"].'","artists":[';
        $artists = json_decode($_GET["song"], true)["artists"];
        foreach ($artists as $artist) {
            $sentString .= '{"name":"'.str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $artist["name"]))))).'"},';
        }
        $sentString = rtrim($sentString, ",");
        $sentString .= ']}';
        socket_write($socket, $sentString, strlen($sentString));
        $resultApi = "";
        while($resp = socket_read($socket, 1024)){
            $resultApi .= $resp;
            if(strpos($resultApi, "\n") !== false) break;
        }

        try{
            $songAssoc = json_decode(urldecode($_GET["song"]), true);

            $filename = $resultApi . ".mp3";

            $nomeArtista = $songAssoc["artists"][0]["name"];

            $titolo = $songAssoc["name"];
            $durata = $songAssoc["duration_ms"];
            $percorsoFile = "tracks/" . str_replace("ù", "u", str_replace("ò", "o", str_replace("ì", "i", str_replace(array("è","é"), "e", str_replace("à", "a", $nomeArtista))))) . "/" . $filename;
            $coverImage = $songAssoc["album"]["images"][0]["url"];

            $query = $db->prepare("SELECT * FROM tblArtisti WHERE nome = :artistaNome");
            $query-> bindParam(":artistaNome", $nomeArtista);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            $artistaId = -1;

            if($result[0]["nome"] == $nomeArtista){
                $artistaId = $result[0]["idArtista"];
            }
            else{
                $query = $db->prepare("INSERT INTO tblArtisti (nome) VALUES (:nomeArtista)");
                $query->bindParam(":nomeArtista", $nomeArtista);
                $query->execute();

                $artistaId = $db->lastInsertId();
            }   

            if($artistaId != -1){
                $lowerTitolo = strtolower($titolo);
                $query = $db->prepare("INSERT INTO tblBrani (titolo, durata, percorsoFile, artistaId, coverImage) VALUES (:titolo, :durata, :percorsoFile, :artistaId, :coverImage)");
                $query->bindParam(":titolo", $lowerTitolo);
                $query->bindParam(":durata", $durata);
                $query->bindParam(":percorsoFile", $percorsoFile);
                $query->bindParam(":artistaId", $artistaId);
                $query->bindParam(":coverImage", $coverImage);
                $query->execute();
            }
            else{
                throw new Exception("Error on artist Id dataSource.php:133");
            }
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
        echo $resultApi;
    }

    if(isset($_GET["addToPlaylist"])){
        $playlistId = $_GET["idPlaylist"];
        $branoId = $_GET["idBrano"];

        try{
            $query = $db -> prepare("SELECT count(idBranoPlaylist) as nBrani FROM tblBraniPlaylist WHERE playlistId = :playlistId");
            $query -> bindParam(":playlistId", $playlistId);
            $query -> execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            if($result[0]["nBrani"] == 0){
                $query = $db -> prepare("SELECT coverImage FROM tblBrani WHERE idBrano = :idBrano");
                $query -> bindParam(":idBrano", $branoId);
                $query -> execute();
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                $cover = $result[0]["coverImage"];

                $query = $db -> prepare("UPDATE tblPlaylists SET coverImage = :cover WHERE idPlaylist = :idPlaylist");
                $query -> bindParam(":cover", $cover);
                $query -> bindParam(":idPlaylist", $playlistId);
                $query -> execute();
            }

            $query = $db -> prepare("INSERT INTO tblBraniPlaylist (playlistId, branoId) VALUES (:playlistId, :branoId)");
            $query -> bindParam(":playlistId", $playlistId);
            $query -> bindParam(":branoId", $branoId);
            $query -> execute();

            echo "200";
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["getPlaylistTracks"])){
        $playlistId = $_GET["playlistId"];

        try{
            $query = $db -> prepare("SELECT * FROM tblBraniPlaylist INNER JOIN tblBrani ON tblBraniPlaylist.branoId = tblBrani.idBrano INNER JOIN tblArtisti ON tblBrani.artistaId = tblArtisti.idArtista WHERE tblBraniPlaylist.playlistId = :playlistId;");
            $query -> bindParam(":playlistId", $playlistId);
            $query -> execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($result);
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["deleteFromPlaylist"])){
        $idBranoPlaylist = $_GET["idBranoPlaylist"];

        try{
            $query = $db -> prepare("DELETE FROM tblBraniPlaylist WHERE idBranoPlaylist = :idBranoPlaylist");
            $query -> bindParam(":idBranoPlaylist", $idBranoPlaylist);
            $query -> execute();

            echo "200";
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["deletePlaylist"])){
        error_log("DELETE");
        $idPlaylist = $_GET["idPlaylist"];

        try{
            $query = $db -> prepare("DELETE FROM tblBraniPlaylist WHERE playlistId = :idPlaylist");
            $query -> bindParam(":idPlaylist", $idPlaylist);
            $query -> execute();

            $query = $db -> prepare("DELETE FROM tblPlaylists WHERE idPlaylist = :idPlaylist");
            $query -> bindParam(":idPlaylist", $idPlaylist);
            $query -> execute();

            echo "200";
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }

    if(isset($_GET["deleteAccount"])){
        error_log("DELETE");
        $idUtente = $_SESSION["idUtente"];
        error_log("utente: " . $idUtente);

        try{
            $query  = $db -> prepare("SELECT idPlaylist FROM tblPlaylists WHERE utenteId = :utenteId");
            $query -> bindParam(":utenteId", $idUtente);
            $query -> execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $playlist) {
                $query = $db -> prepare("DELETE FROM tblBraniPlaylist WHERE playlistId = :idPlaylist");
                $query -> bindParam(":idPlaylist", $playlist["idPlaylist"]);
                $query -> execute();

                $query = $db -> prepare("DELETE FROM tblPlaylists WHERE idPlaylist = :idPlaylist");
                $query -> bindParam(":idPlaylist", $playlist["idPlaylist"]);
                $query -> execute();
            }

            $query = $db -> prepare("DELETE FROM tblUtenti WHERE idUtente = :idUtente");
            $query -> bindParam(":idUtente", $idUtente);
            $query -> execute();
            echo "200";
        }
        catch (PDOException $e){
            echo "Error: " . $e->getMessage();
        }
    }
?>