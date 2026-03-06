<?php
// FFMpeg連結用のリストを作成し
// 連結時の再生開始時間と曲の時間、曲名(ファイル名)情報をリスト出力する

define('FF_PROBE_PATH', 'D:\\ffmpeg\\ffmpeg-5.1.2-essentials_build\\bin\\ffprobe.exe');

// usage:
// php script.php "D:\music"
// ffmpeg -f concat -safe 0 -i list.dat -c copy merged.mp3

$strRootDir = $argv[1] ?? (__DIR__);
makeList2($strRootDir);

function makeList2(string $strRootDir): void
{
    $strRootDir = rtrim($strRootDir, "\\/");

    $dat = '';
    $list = '';

    //ディレクトリ（アルバム）を取得
    $dirs = scandir($strRootDir);
    if ($dirs === false) {
        exit("ディレクトリを読み取れません: {$strRootDir}\n");
    }

    $listDir = [];
    $listFile = [];

    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }

        $fullPath = $strRootDir . DIRECTORY_SEPARATOR . $dir;
        if (is_dir($fullPath)) {
            $listDir[] = $fullPath;
        }
    }

    // 自然ソート
    natcasesort($listDir);
    $listDir = array_values($listDir);
    
    //アルバムから曲(ファイル）を取得
    foreach ($listDir as $dir) {
        $files = scandir($dir);
        if ($files === false) {
            continue;
        }

        $wkFiles = [];
        foreach ($files as $file) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_file($fullPath) && preg_match('/\.mp3$/i', $file)) {
                $wkFiles[] = $fullPath;
            }
        }

        // 自然ソート
        natcasesort($wkFiles);
        $wkFiles = array_values($wkFiles);

        $listFile = array_merge($listFile, $wkFiles);
    }

    $totalSeconds = 0.0;

    foreach ($listFile as $file) {
        // ffmpeg concat 用。Windowsの単一引用符問題を避けるなら
        // file 行は / に寄せると扱いやすいことがあります
        $ffmpegPath = str_replace('\\', '/', $file);
        $escapedForConcat = str_replace("'", "'\\''", $ffmpegPath);

        $list .= "file '{$escapedForConcat}'\n";

        $time = getLength(FF_PROBE_PATH, $file);
        $seconds = durationToSeconds($time);

        $dat .= basename($file)
            . "　【開始時間】" . formatSeconds($totalSeconds)
            . "　(再生時間 " . formatDuration($seconds) . ")\n";

        $totalSeconds += $seconds;
    }

    file_put_contents("info.txt", $dat);
    file_put_contents("list.dat", $list);
}

//ffprobeを使って再生時間を取得
function getLength(string $ffprobe, string $file): string
{
    // ffprobe の duration を直接取得
    $cmd = escapeshellarg($ffprobe)
        . ' -v error -show_entries format=duration'
        . ' -of default=noprint_wrappers=1:nokey=1 '
        . escapeshellarg($file);

    $ret = shell_exec($cmd);
    if ($ret === null) {
        echo "ffprobe実行失敗: {$file}\n";
        return '00:00:00.00';
    }

    $ret = trim($ret);
    if ($ret === '' || !is_numeric($ret)) {
        echo "再生時間を取得できませんでした: {$file}\n";
        return '00:00:00.00';
    }

    return secondsToDuration((float)$ret);
}

function durationToSeconds(string $time): float
{
    if (!preg_match('/^(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?$/', $time, $m)) {
        return 0.0;
    }

    $hours = (int)$m[1];
    $minutes = (int)$m[2];
    $seconds = (int)$m[3];
    $fraction = isset($m[4]) ? (float)('0.' . $m[4]) : 0.0;

    return $hours * 3600 + $minutes * 60 + $seconds + $fraction;
}

function secondsToDuration(float $seconds): string
{
    $hours = (int)floor($seconds / 3600);
    $seconds -= $hours * 3600;

    $minutes = (int)floor($seconds / 60);
    $seconds -= $minutes * 60;

    return sprintf('%02d:%02d:%05.2f', $hours, $minutes, $seconds);
}

function formatSeconds(float $seconds): string
{
    $hours = (int)floor($seconds / 3600);
    $seconds -= $hours * 3600;

    $minutes = (int)floor($seconds / 60);
    $seconds -= $minutes * 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, (int)floor($seconds));
}

function formatDuration(float $seconds): string
{
    $hours = (int)floor($seconds / 3600);
    $seconds -= $hours * 3600;

    $minutes = (int)floor($seconds / 60);
    $seconds -= $minutes * 60;

    if ($hours > 0) {
        return sprintf('%02d:%02d:%05.2f', $hours, $minutes, $seconds);
    }

    return sprintf('%02d:%05.2f', $minutes, $seconds);
}