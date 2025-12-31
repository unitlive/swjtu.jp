$baseDir = "h:\github\swjtu.jp\photos\2025年以前の写真"
if (!(Test-Path $baseDir)) { New-Item -ItemType Directory -Path $baseDir }

$events = @(
    @{
        Name = "20241222_FourUniv_YearEndParty"
        Urls = @(
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/fa7034fe063994179964cee5614d53ff5.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f1f54033340fb488995fed036838b2697.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/ff5ea0d4a697447a9a7818e78c9837298.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/fdeda8497431341539bb6a5df2e32f6a9.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f6d146a86cf0d42239d02ce683d2411e7.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f88b004f0afde410dbb561f2dab81dee8.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f745a642306424d52a56f879426eb0fa2.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f87ff833ef561490ab515037b6a9bfff6.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f6e8e7d81eb064d62b4a20d90e2edfd95.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/fc584d83726f34b958d12ba380660dfc0.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f61c0c355c0354d249c2706322e7e6399.png",
            "https://picture01.52hrttpic.com/image/infoImage/202412/27/f4a55936acb624289a809d6aabdea4e9f.png"
        )
    },
    @{
        Name = "20240404_CherryBlossom"
        Urls = @(
            "https://picture01.52hrttpic.com/image/infoImage/202404/04/G1711951536579.png",
            "https://picture01.52hrttpic.com/image/infoImage/202404/04/G1711951536577.png",
            "https://picture01.52hrttpic.com/image/infoImage/202404/04/G1711951536575.png",
            "https://picture01.52hrttpic.com/image/infoImage/202404/04/G1711951536578.png",
            "https://picture01.52hrttpic.com/image/infoImage/202404/04/G1711951536568.png"
        )
    },
    @{
        Name = "20151121_FoundingCeremony"
        Urls = @(
            "http://duan.jp/news/2015/1121.files/image002.jpg",
            "http://duan.jp/news/2015/1121.files/image004.jpg"
        )
    }
)

foreach ($evt in $events) {
    $targetDir = Join-Path $baseDir $evt.Name
    if (!(Test-Path $targetDir)) { New-Item -ItemType Directory -Path $targetDir }
    
    $count = 1
    foreach ($url in $evt.Urls) {
        $ext = [System.IO.Path]::GetExtension($url.Split('?')[0])
        if ([string]::IsNullOrEmpty($ext)) { $ext = ".png" } # Default to png if unknown
        $filename = "pic($count)$ext"
        $filepath = Join-Path $targetDir $filename
        
        Write-Host "Downloading $url to $filepath"
        try {
            Invoke-WebRequest -Uri $url -OutFile $filepath -UserAgent "Mozilla/5.0"
        } catch {
            Write-Host "Failed to download $url : $_"
        }
        $count++
    }
    
    # Create simple README
    $readmePath = Join-Path $targetDir "README.txt"
    Set-Content -Path $readmePath -Value $evt.Name
}
