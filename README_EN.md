# MP3 Album Merger List Generator

A PHP CLI script that generates:

-   **`list.dat`** -- a file list for FFmpeg's `concat` feature
-   **`info.txt`** -- a track list with start time and duration of each
    song

The script scans album directories containing `.mp3` files and produces
files that make it easy to merge multiple albums into a single MP3 using
FFmpeg.

------------------------------------------------------------------------

# Features

-   Automatically detects album directories
-   Sorts MP3 files before merging
-   Uses `ffprobe` to retrieve track duration
-   Generates `list.dat` for FFmpeg concat
-   Outputs track start times and durations to `info.txt`

------------------------------------------------------------------------

# Directory Structure

The script assumes a directory structure like this:

    music/
    ├─ Album1/
    │  ├─ 01 Song.mp3
    │  ├─ 02 Song.mp3
    │
    ├─ Album2/
    │  ├─ 01 Track.mp3
    │  ├─ 02 Track.mp3

Each album directory contains MP3 files.

------------------------------------------------------------------------

# Requirements

-   PHP 7.4 or later
-   FFmpeg
-   ffprobe

On Windows, set the path to `ffprobe` in the script:

``` php
define('FF_PROBE_PATH',"D:\\ffmpeg\\bin\\ffprobe.exe");
```

------------------------------------------------------------------------

# Usage

## 1. Generate the merge list

    php make_list.php <music_directory>

Example:

    php make_list.php D:\music

If no argument is provided, the script uses the same directory as the
script itself.

------------------------------------------------------------------------

## 2. Generated files

### list.dat

A file list used by FFmpeg concat.

    file 'Album1/01 Song.mp3'
    file 'Album1/02 Song.mp3'
    file 'Album2/01 Track.mp3'

### info.txt

Track names with start times.

    01 Song.mp3  [Start Time] 00:00:00  (Duration 03:12)
    02 Song.mp3  [Start Time] 00:03:12  (Duration 04:05)

------------------------------------------------------------------------

## 3. Merge MP3 files

Use the generated `list.dat` with FFmpeg:

    ffmpeg -f concat -safe 0 -i list.dat -c copy merged.mp3

------------------------------------------------------------------------

# How It Works

1.  Reads album directories from the specified root directory
2.  Collects `.mp3` files from each album
3.  Sorts files to determine merge order
4.  Uses `ffprobe` to obtain track duration
5.  Generates `list.dat` and `info.txt`

------------------------------------------------------------------------

# Notes

-   Only `.mp3` files are processed
-   MP3 files directly under the root directory are ignored
-   Tracks are processed in directory order and file order

------------------------------------------------------------------------

# License

MIT License
