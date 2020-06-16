[![Build Status](https://travis-ci.org/slowprog/CopyFile.svg?branch=master)](https://travis-ci.org/slowprog/CopyFile)
[![Latest Stable Version](https://poser.pugx.org/slowprog/composer-copy-file/version)](https://packagist.org/packages/slowprog/composer-copy-file)
[![Total Downloads](https://poser.pugx.org/slowprog/composer-copy-file/downloads)](https://packagist.org/packages/slowprog/composer-copy-file)

# Composer copy file

Composer script copying your files after install. Supports copying of entire directories, individual files and complex nested directories.

For example copy fonts:

```
{
    "require":{
        "twbs/bootstrap": "~3.3",
        "slowprog/composer-copy-file": "~0.3"
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

In a development you may use `-dev` suffix. For example copy non-minified in development and minified in production:

```
{
    "require":{
        "twbs/bootstrap": "~3.3",
        "slowprog/composer-copy-file": "~0.3"
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
            "vendor/twbs/bootstrap/dist/js/bootstrap.min.js": "web/js/bootstrap.js"
        },
        "copy-file-dev": {
            "vendor/twbs/bootstrap/dist/js/bootstrap.js": "web/js/bootstrap.js"
        }
    }
}
```

## Use cases

You need to be careful when using a last slash. The file-destination is different from the directory-destination with the slash.

If in destination directory already exists copy of file, then it will be override. To overwrite only older files append `?` in end of destination path.

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
                "dir/subdir/file1.txt": "web/other/",
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
                "dir/subdir/file1.txt": "web/other/file1.txt",
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

4. Override only older files:

    ```
    {
        "extra": {
            "copy-file": {
                "dir/subdir/": "web/other/?"
            }
        }
    }
    ```
    
    Preset:
    
    ```
    web/
        other/
            file1.txt - Recently modified
            file2.txt - Old rotten file
    ```
    
    Result:

    ```
    web/
        other/
            file1.txt - Not changed
            file2.txt - Replaced
    ```
    
5. Filter source files by regexp pattern

    ```
    {
        "extra": {
            "copy-file": {
                "dir/subdir#1\.\w{3}$": "web/other/"
            }
        }
    }
    ```

    Result:

    ```
    web/
        other/
            file1.txt
    ```
