# ==========================================
# 批量下载图片（CSV + 按月份目录 + 断点续传 + CSV 日报）
# ==========================================

$csvFile      = "C:\Users\Administrator\Desktop\2023-01.csv"
$downloadRoot = "D:\Images"
$reportCsv    = "D:\report.csv"

# ===========================
# 1. 读取 CSV 总行数
# ===========================
Write-Host "[INFO] 正在读取 CSV 总行数..."
$lineCount = 0
$reader = [System.IO.File]::OpenText($csvFile)
while ($null -ne $reader.ReadLine()) { $lineCount++ }
$reader.Close()
Write-Host "[INFO] CSV 共 $lineCount 行"

# ===========================
# 2. 初始化统计
# ===========================
$success = 0
$failed  = 0
$rowIndex = 0
$lastOutput = Get-Date

# ===========================
# 3. 下载函数（简单可靠 + 断点续传）
# ===========================
function Download-File {
    param (
        [string]$url,
        [string]$savePath
    )

    $maxRetry = 3

    $folder = Split-Path $savePath -Parent
    if (!(Test-Path $folder)) { New-Item -ItemType Directory -Force -Path $folder | Out-Null }

    # 已存在文件直接跳过
    if (Test-Path $savePath) { return $true }

    for ($i=1; $i -le $maxRetry; $i++) {
        try {
            Invoke-WebRequest -Uri $url -OutFile $savePath -TimeoutSec 30 -UseBasicParsing -Headers @{
                "User-Agent" = "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
            }
            return $true
        } catch {
            Start-Sleep -Seconds 1
        }
    }
    return $false
}

# ===========================
# 4. 执行下载
# ===========================
Write-Host "`n[INFO] 开始下载任务..."
$reader = [System.IO.File]::OpenText($csvFile)
$reader.ReadLine() | Out-Null  # 跳过标题行

while ($null -ne ($line = $reader.ReadLine())) {
    $rowIndex++

    $parts = $line.Split(',')
    $fileName = $parts[0].Trim('"')
    $date     = $parts[1].Trim('"')
    $url      = "https://zhtc.aldwxa.top/file/$fileName"

    # 创建月份目录
    try { $monthFolder = (Get-Date $date).ToString("yyyy-MM") }
    catch { Write-Host "[WARN] 无效日期: $date"; $failed++; continue }

    $saveDir = Join-Path $downloadRoot $monthFolder
    $savePath = Join-Path $saveDir $fileName

    if (Download-File -url $url -savePath $savePath) { $success++ } else { $failed++ }

    $now = Get-Date
    if (($now - $lastOutput).TotalSeconds -ge 1) {
        Write-Host "[进度] $rowIndex / $lineCount (成功:$success 失败:$failed)"
        $lastOutput = $now
    }
}
$reader.Close()

# ===========================
# 5. 生成 CSV 日报
# ===========================
$report = [PSCustomObject]@{
    Date    = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    Total   = $lineCount - 1
    Success = $success
    Failed  = $failed
}

$report | Export-Csv $reportCsv -Encoding UTF8 -NoTypeInformation

Write-Host "`n[完成] 下载结束！成功：$success，失败：$failed"
Write-Host "CSV 日报已生成：$reportCsv"
