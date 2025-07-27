import subprocess
import sys # for sys.executable (The file path of the currently using python)
import shutil
import requests
import glob
import os
import socket
import json
null = None
true = True
false = False

#os.system("pipx run spotdl moneyfornothing")

def api_auth():
    CLIENT_ID = "7830ddbbbf3043a6a3ebab4273d26205"
    CLIENT_SECRET = "c5f048c8ae3c491e90d445b178d6274a"
    auth_url = "https://accounts.spotify.com/api/token"
    auth_payload = {
        "grant_type": "client_credentials",
        "client_id": CLIENT_ID,
        "client_secret": CLIENT_SECRET
    }
    resp = requests.post(auth_url, data=auth_payload)
    print(resp.status_code)
    auth_data = eval(resp.text)
    return auth_data["access_token"]

def dl_from_id(id):
    dl_link = "https://open.spotify.com/track/" + id
    os.system("spotdl " + dl_link)
    return 1

def api_query(q):
    search_url = "https://api.spotify.com/v1/search"
    search_payload = {
        "q": q,
        "type": "track",
        "limit": 10
    }
    search_headers = {
        "Authorization": "Bearer " + api_auth()
    }

    resp = requests.get(search_url, params=search_payload, headers=search_headers)
    print(resp.status_code)
    query_data = eval(resp.text)
    return query_data["tracks"]["items"]

def route_song(songName, artists):
    newFilename = songName + ".mp3"
    mp3_files = glob.glob('**/*.mp3', recursive=True)
    for mp3 in mp3_files:
        print(mp3)

    os.rename(mp3_files[0], newFilename)

    artistName = artists[0]["name"]
    if not os.path.exists("../../../tracks/" + artistName):
        os.makedirs("../../../tracks/" + artistName)

    shutil.move(newFilename, "../../../tracks/" + artists[0]["name"] + "/" + newFilename)

def handle_request(d):
    if(d["action"] == "search"):
        print("search requested")
        search_url = "https://api.spotify.com/v1/search"
        search_payload = {
            "q": d["data"],
            "type": "track",
            "limit": 10
        }
        search_headers = {
            "Authorization": "Bearer " + api_auth()
        }

        resp = requests.get(search_url, params=search_payload, headers=search_headers)
        print(resp.status_code)
        query_data = eval(resp.text)
        response_data = {
            "items": query_data["tracks"]["items"]
        }
        response_string = json.dumps(response_data)
        conn_sock.send((response_string + "\n").encode())
        conn_sock.close()
    if(d["action"] == "download"):
        print("download requested")
        print(d["data"])
        print(d["artists"])
        dl_from_id(d["data"])
        route_song(d["title"], d["artists"])
        conn_sock.send((d["title"] + "\n").encode())
        conn_sock.close()
    if(d["action"] == "login"):
        print("login requested")
        login_url = "https://api.spotify.com/v1/authorize"
        login_payload = {
            "client_id": CLIENT_ID,
            "response_type": "code",
            "redirect_uri": "http://127.0.0.1/synthax/src/callback.php"
        }
        resp = requests.get(login_url, params=login_payload)
        print(resp.status_code)
        resp_data = eval(resp.text)
        print(resp_data)


hostname = "127.0.0.1"
port = 8000

sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
sock.bind((hostname, port))
sock.listen(5)

while True:
    conn_sock, addr = sock.accept()
    print(f"Connection from {addr}")
    data = conn_sock.recv(100000)
    data = str(data).strip("b'")
    data = data.replace("\\","")
    print(data)
    data = json.loads(data)
    handle_request(data)