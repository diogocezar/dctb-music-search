# Music Search

This is a simple script to scan a JSON of a web radio.

Every 60 seconds, the script looks to JSON and checks if there is a new song playing.

When a new song is identified, that element is stored on text file _data/musics.txt_

This script also have some goodies:

* With the *get_musics.php* you can read all files on the list, search them on youtube and automatically download them;

* With the *get_links* you can get 5 results to each line of data music.

_At this time the script are looking for Radio Rock 89FM_