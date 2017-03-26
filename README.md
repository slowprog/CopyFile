# Composer copy file

Composer script copying your files after install. Supports copying of entire directories, individual files and complex nested directories.

For example copy fonts:

```
{
    "require":{
        "twbs/bootstrap": "~3.3",
        "slowprog/composer-copy-file": "~0.2"
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

## Use cases

You need to be careful when using a last slash. The file-destination is different from the directory-destination with the slash.

Source directory hierarchy:

```
dir/
    subdir/
        file1.txt
        file2.txt
```

1. Dir-to-dir:

    ```
    {
        "extra": {
            "copy-file": {
                "dir/subdir/": "web/other/"
            }
        }
    }
    ```

    Result:

    ```
    web/
        other/
            file1.txt
            file2.txt
    ```

2. File-to-dir:

    ```
    {
        "extra": {
            "copy-file": {
                "dir/subdir/file1.txt": "web/other/"
                "dir/subdir/file2.txt": "web/other/file2.txt/"
            }
        }
    }
    ```

    Result:

    ```
    web/
        other/
            file1.txt
            file2.txt/
                file2.txt
    ```

3. File-to-file:

    ```
    {
        "extra": {
            "copy-file": {
                "dir/subdir/file1.txt": "web/other/file1.txt"
                "dir/subdir/file2.txt": "web/other/file_rename.txt"
            }
        }
    }
    ```

    Result:

    ```
    web/
        other/
            file1.txt
            file_rename.txt
    ```
