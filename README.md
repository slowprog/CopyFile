# Composer copy file

Composer script copying your files after install. Supports copying of entire directories, individual files and complex nested directories.

For example copy fonts:

```
{
    "require":{
        "twbs/bootstrap": "~3.3",
        "slowprog/composer-copy-file": "~0.1"
    },
    "scripts": {
        "post-install-cmd": [
            "SlowProg\\CopyFile\\ScriptHandler::copy"
        ],
        "post-update-cmd": [
            "SlowProg\\CopyFile\\ScriptHandler::copy"
        ]
    },
    "extra": {
        "copy-file": {
            "vendor/twbs/bootstrap/fonts/": "web/fonts/"
        }
    }
}
```
