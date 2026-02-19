# Ensolved

This is CLI app for soliving [Enspelled game](https://enspelled.com).

## Usage:

`php ./ensolved.php --letters NOGIDDSAO`

Note: you need to have a dictionary to make this app work. It will search it by default at `./en.dic`,
but you can use `--wordlist` or `-w` flag to set path to dictionary. Dictionary must be alphabetically sorted.

You can download dictionary with `-g` flag:

```console
php ./ensolved.php -g               # will download dictionary to ./en.dic
php ./ensolved.php -g    ./dict.txt # will download dictionary to ./dict.txt
php ./ensolved.php -g -w ./dict.txt # will download dictionary to ./dict.txt
```
